<?php

namespace App\Http\Controllers;

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
        $jobs = BotJob::where('user_id', auth()->id())->with('market')->get();

        return view('bot.jobs', compact('jobs'));
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
        $profit=0;
        if ($job->history && count($job->history) > 0) {
            $bought = 0;
            $sold = 0;
            foreach ($job->history as $history) {
                if ($history->offer->type == 'buy') {
                    $bought = $history->offer->amount * $history->offer->realise_rate;
                } elseif ($history->offer->type == 'sell') {
                    $sold = $history->offer->amount * $history->offer->realise_rate;
                }
            }
            $profit = $sold - $bought;
        }

        return view('bot.show', compact('job','profit'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
        $jobs = BotJob::where('active', true)->with(/*'user',*/ 'offer', 'fiatWallet', 'market')->get();//
        //TODO: przy dodaniu więcej niż 1 zadania fiatWallet znajduje tylko do jednego
        //dd($jobs->get());

        if (count($jobs) <= 0) {
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
                        //$history->save();
                        $this->newOfferBuy($job);
                        //new_offer() > check aviliable founds > create offer_buy
                    }
                } elseif ($job->offer->type == 'buy') {
                    if ($job->offer->completed == false) {
                        continue;
                    } else {
                        $history = BotHistory::firstOrCreate(['bot_job_id' => $job->id, 'offer_id' => $job->offer->id, 'user_id' => $job->user_id]);
                        //$history->save();
                        $this->newOfferSell($job);
                        //new_offer() > check aviliable founds > create offer_sell
                    }
                }
            } else {
                $this->newOfferBuy($job);

                //new offer buy
            }

        }
        //  dd($jobs);
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
            return null;
        }
        $wallet = Wallet::where(['user_id' => Auth::user()->id, 'currency' => $botJob->market->second_currency])->first();

        if (!$wallet) {
            return null;//response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        $bitBay = new PublicRest();
        $ticker = $bitBay->ticker($botJob->market->market_code);
        if (is_object($ticker) && strtoupper($ticker->status) == "OK") {
            //highestBid / max_value
            list($ra, $ca) = $this->getBuyParametersFromTicker($botJob, $ticker);
            if ($ra == null || $ca == null) {
                return null;
            }
        }

        $sum = $ca * $ra;
        if ($sum > $botJob->fiatWallet->available_founds) {
            return null;// response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }
        if ($botJob->offer) {
            if ($ra >= $botJob->offer->realise_rate) {
                return null;
            }
        }
        try {
            DB::beginTransaction();
            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'buy';
            $offer->active = true;
            $offer->user_id = $botJob->user_id;// Auth::user()->id;
            $offer->market_id = $botJob->market->id;
            $offer->save();

            $wallet->locked_founds = ($wallet->locked_founds + $sum);
            $wallet->available_founds = ($wallet->available_founds - $sum);
            $wallet->save();

            $botJob->offer_id = $offer->id;
            $botJob->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
            return null;// response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        list($ra, $ca) = $this->getSellParameters($botJob);

        //$sum = $ca * $ra;
        if ($ca > $wallet->available_founds) {
            return null;//response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }

        try {
            DB::beginTransaction();
            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'sell';
            $offer->active = true;
            $offer->user_id = $botJob->user_id; //Auth::user()->id;
            $offer->market_id = $botJob->market->id;
            $offer->save();

            $wallet->locked_founds = ($wallet->locked_founds + $ca);
            $wallet->available_founds = ($wallet->available_founds - $ca);
            $wallet->save();

            $botJob->offer_id = $offer->id;
            $botJob->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
        $ra = $ticker->ticker->highestBid;
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
        return [$ra, $ca];
    }

    private function loadFiatWallets($jobs)
    {
        foreach ($jobs as $job) {
            if ($job->fiatWallet == null) {
                $wallet = Wallet::where(['user_id' => $job->user_id, 'currency' => 'PLN'])->first();
                $job->fiatWallet = $wallet;
            }
        }
    }
}