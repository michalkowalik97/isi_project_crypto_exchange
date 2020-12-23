<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Log;
use App\BotHistory;
use App\BotJob;
use App\Helpers\BitBay\PublicRest;
use App\Market;
use App\Offer;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bot.index');
    }

    public function jobs()
    {
        $jobs = BotJob::where('user_id', auth()->id())->with('market', 'offer', 'history.offer')->get();

        $profit = 0;
        if ($jobs && count($jobs) > 0) {
            foreach ($jobs as $job) {
                $profit += $this->calculateBotJobProfit($job);
            }
        }

        return view('bot.jobs', compact('jobs', 'profit'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $markets = Market::where(['active' => true, 'second_currency' => "PLN"])->get();

        return view('bot.create', compact('markets'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'max_value' => 'required|numeric|min:1',
            'min_profit' => 'required|numeric',
            'market_id' => 'required|numeric',
        ]);

        $job = new BotJob($request->only('max_value', 'min_profit', 'market_id'));
        $job->user_id = auth()->id();
        $job->save();

        return redirect('/bot/jobs')->with(['message' => 'Zapisano pomyślnie.']);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $job = BotJob::with('offer', 'history.offer', 'market')->find($id);

        if (!$job) {
            return redirect()->back()->with(['message' => trans('app.standard_error')]);
        }
        $profit = $this->calculateBotJobProfit($job);

        return view('bot.show', compact('job', 'profit'));
    }

    public function stats(Request $request)
    {
        $from = $to = null;
        if ($request->from != null) {
            $from = new Carbon($request->from);
            $from = $from->startOfDay();
        }
        if ($request->to != null) {
            $to = new Carbon($request->to);
            $to = $to->endOfDay();
        }

        $jobs = BotJob::where('user_id', auth()->id())->with('offer', 'history.offer', 'market');
        if ($from || $to) {
            $jobs->whereHas('history', function ($query) use ($from, $to) {
                if ($from) {
                    $query->where('created_at', '>=', $from->format('Y-m-d H:i:s'));
                }
                if ($to) {
                    $query->where('created_at', '<=', $to->format('Y-m-d H:i:s'));
                }
            })->get();
        }

        $jobs = $jobs->get();


        $markets = [];
        $jobStonks = [];
        $dailyJobProfits = [];
        // podział na dni

        if ($jobs && count($jobs) > 0) {
            foreach ($jobs as $job) {
                if (!isset($markets[$job->market->market_code])) {
                    $markets[$job->market->market_code] = 0;
                }
                $jobStonks[$job->id] = 0;

                $jobProfit = $this->calculateBotJobProfit($job);


                //$dailyJobProfits = $this->calculateDailyProfit($job, $dailyJobProfits);

                $markets[$job->market->market_code] += $jobProfit;
                $jobStonks[$job->id] = ['job' => $job, 'profit' => $jobProfit];
            }
        }

        return view('bot.stats', compact('markets', 'jobStonks'/*'dailyJobsProfits'*/));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $markets = Market::where(['active' => true, 'second_currency' => "PLN"])->get();
        $job = BotJob::find($id);

        return view('bot.edit', compact('job', 'markets'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'max_value' => 'required|numeric|min:1',
            'min_profit' => 'required|numeric',
            'market_id' => 'required|numeric',
        ]);

        $job = BotJob::find($id);
        $job->fill($request->only('max_value', 'min_profit', 'market_id'));

        $job->save();

        return redirect('/bot/jobs')->with(['message' => 'Zapisano pomyślnie.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function toggleActive($id)
    {
        $job = BotJob::find($id);
        if (!$job) {
            return redirect()->back()->with(['message' => trans('app.standard_error')]);
        }
        $status = !$job->active;
        $job->active = $status;
        $job->save();

        if ($status) {
            $messasge = 'Zadanie włączone pomyślnie.';
        } else {
            $messasge = 'Zadanie wyłączone pomyślnie.';
        }
        return redirect()->back()->with(['message' => $messasge]);
    }

    public function cronStonksMaker()
    {
        $jobs = BotJob::where('active', true)->with(/*'user',*/ 'offer', 'fiatWallet', 'market')->get();

        if (count($jobs) <= 0) {
            Log::info('...---... Jobs not found in db');
            return null;
        }
        foreach ($jobs as $job) {
            if (!$job->fiatWallet) {
                continue;
            }

            if (!$job->market) {
                continue;
            }

            if ($job->offer) {
                if ($job->offer->type == 'sell') {
                    if ($job->offer->completed == false) {
                        continue;
                    } else {
                        $history = BotHistory::firstOrCreate(['bot_job_id' => $job->id, 'offer_id' => $job->offer->id, 'user_id' => $job->user_id]);
                        $this->newOfferBuy($job);
                    }
                } elseif ($job->offer->type == 'buy') {
                    if ($job->offer->completed == false) {
                        continue;
                    } else {
                        $history = BotHistory::firstOrCreate(['bot_job_id' => $job->id, 'offer_id' => $job->offer->id, 'user_id' => $job->user_id]);
                        $this->newOfferSell($job);

                    }
                }
            } else {
                $this->newOfferBuy($job);
            }

        }
    }


    /**
     * Metoda służąca do dodawania ofert kupna na giełdzie
     *
     * @param Request $request request zawierający dane złożonej oferty
     * @param $market rynek którego dotyczy oferta
     * @return \Illuminate\Http\JsonResponse odpowiedź w formacie JSON zawierająca informację czy udało się złożyć ofertę
     */
    public function newOfferBuy(BotJob $botJob)
    {
        if ($botJob->fiatWallet->available_founds < 1) {
            Log::info('...---... no avilable founds, job id = '.$botJob->id);
            return null;
        }
        $wallet = Wallet::where(['user_id' => $botJob->user_id, 'currency' => $botJob->market->second_currency])->first();

        if (!$wallet) {
            Log::info('...---... wallet not foud, job id = '.$botJob->id);
            return null;//response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        $bitBay = new PublicRest();
        $ticker = $bitBay->ticker($botJob->market->market_code);
        if (is_object($ticker) && strtoupper($ticker->status) == "OK") {
            //highestBid / max_value
            list($ra, $ca) = $this->getBuyParametersFromTicker($botJob, $ticker);
            if ($ra == null || $ca == null) {
                Log::info('...---... getBuyParametersFromTicker fail, job id = '.$botJob->id);
                return null;
            }
        }else{
            Log::info('...---... Get Ticker fail, job id = '.$botJob->id);
            return null;
        }

        $sum = $ca * $ra;
        if ($sum > $botJob->fiatWallet->available_founds) {
            Log::info('...---... no avilable founds second check, job id = '.$botJob->id);

            return null;// response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }
        /* if ($botJob->offer) {
             if ($ra >= $botJob->offer->realise_rate) {
                 return null;
             }
         }*/
        try {
            DB::beginTransaction();
            $lockedFounds = $sum;
            $wallet->locked_founds += $lockedFounds;
            $wallet->available_founds = ($wallet->available_founds - $sum);
            $wallet->save();

            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'buy';
            $offer->active = true;
            $offer->user_id = $botJob->user_id;// Auth::user()->id;
            $offer->market_id = $botJob->market->id;
            $offer->locked_founds = $lockedFounds;
            $offer->wallet_id = $wallet->id;
            $offer->save();

            $botJob->offer_id = $offer->id;
            $botJob->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('...---...  exception, job id = '.$botJob->id, 'message: '.$e->getMessage(). ' in line: '.$e->getLine().' stacktrace: '.$e->getTraceAsString());

            return null; //response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        return true;
        //return response()->json(['success' => true, 'message' => 'Oferta złożona pomyślnie.']);
    }

    /**
     * Metoda służąca do dodawania ofert sprzedaży na giełdzie
     *
     * @param Request $request request zawierający dane złożonej oferty
     * @param $market rynek którego dotyczy oferta
     * @return \Illuminate\Http\JsonResponse odpowiedź w formacie JSON zawierająca informację czy udało się złożyć ofertę
     */
    public function newOfferSell(BotJob $botJob)
    {
        $wallet = Wallet::where(['user_id' => $botJob->user_id, 'currency' => $botJob->market->first_currency])->first();

        if (!$wallet) {
            Log::info('...---... Wallet not foud, job id = ',$botJob->id);
            return null;// response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        list($ra, $ca) = $this->getSellParameters($botJob);

        //$sum = $ca * $ra;
        if ($ca > $wallet->available_founds) {
            Log::info('...---... No available founds, job id = ',$botJob->id);
            return null;//response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }

        try {
            DB::beginTransaction();
            $lockedFounds = $ca;
            $wallet->locked_founds = $lockedFounds;
            $wallet->available_founds = ($wallet->available_founds - $ca);
            $wallet->save();

            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'sell';
            $offer->active = true;
            $offer->user_id = $botJob->user_id; //Auth::user()->id;
            $offer->market_id = $botJob->market->id;
            $offer->locked_founds = $lockedFounds;
            $offer->wallet_id = $wallet->id;
            $offer->save();

            $botJob->offer_id = $offer->id;
            $botJob->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('...---...  exception, job id = '.$botJob->id, 'message: '.$e->getMessage(). ' in line: '.$e->getLine().' stacktrace: '.$e->getTraceAsString());

            return null;// response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        return true;// response()->json(['success' => true, 'message' => 'Oferta złożona pomyślnie.']);
    }

    private function getBuyParametersFromTicker(BotJob $botJob, $ticker)
    {
        $max_amount = ($botJob->fiatWallet->available_founds >= $botJob->max_value) ? $botJob->max_value : $botJob->fiatWallet->available_founds;
        $minOffer = $this->getMinOfferFromTicker($ticker);
        if ($minOffer == null) {
            return [null, null];
        }
        $ra = $ticker->ticker->lowestAsk;
        $ca = $max_amount / $ra;

        if ($ca >= $minOffer) {
            return [$ra, $ca];
        } else {
            return [null, null];
        }
    }

    private function getMinOfferFromTicker($ticker)
    {
        if ($ticker->ticker->market->first->currency != "PLN") {
            return $ticker->ticker->market->first->minOffer;
        } elseif ($ticker->ticker->market->second->currency != "PLN") {
            return $ticker->ticker->market->second->minOffer;
        } else {
            return null;
        }

    }

    private function getSellParameters(BotJob $botJob)
    {
        $minProfitAmount = ($botJob->offer->realise_rate == null) ? $botJob->offer->rate * $botJob->offer->amount : $botJob->offer->realise_rate * $botJob->offer->amount;
        $minProfitAmount += $botJob->min_profit;
        $ra = $minProfitAmount / $botJob->offer->amount;
        $ca = $botJob->offer->amount;
        return [round($ra, 2, PHP_ROUND_HALF_UP), $ca];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $job
     * @return float|int
     */
    private function calculateBotJobProfit($job)
    {
        $profit = 0;
        if ($job->history && count($job->history) > 0) {
            $bought = 0;
            $sold = 0;
            foreach ($job->history as $key => $history) {
                if ($key == 0) {
                    if ($history->offer->type == 'buy') {
                        continue;
                    }
                }
                if ($history->offer->type == 'buy') {
                    $bought += $history->offer->amount * $history->offer->realise_rate;
                } elseif ($history->offer->type == 'sell') {
                    $sold += $history->offer->amount * $history->offer->realise_rate;
                }
            }
            $profit = $sold - $bought;
        }
        return $profit;
    }

    private function calculateDailyProfit($job, array $dailyProfits)
    {
        if ($job->history && count($job->history) > 0) {
            foreach ($job->history as $key => $history) {
                if (!isset($dailyProfits[$history->created_at->format('d-m-Y')][$job->id/*market->market_code*/])) {
                    if ($history->offer->type == 'buy') {
                        continue;
                    }
                }
                if (!isset($dailyProfits[$history->created_at->format('d-m-Y')][$job->id/*market->market_code*/])) {
                    $dailyProfits[$history->created_at->format('d-m-Y')][$job->id/*market->market_code*/]=0;
                }
                if ($history->offer->type == 'buy') {
                    $dailyProfits[$history->created_at->format('d-m-Y')][$job->id/*market->market_code*/] -= $history->offer->amount * $history->offer->realise_rate;
                } elseif ($history->offer->type == 'sell') {
                    $dailyProfits[$history->created_at->format('d-m-Y')][$job->id/*market->market_code*/] += $history->offer->amount * $history->offer->realise_rate;
                }
            }
        }

        return $dailyProfits;
    }

}
