<?php

namespace App\Http\Controllers;

use App\Helpers\BitBay\PublicRest;
use App\Market;
use App\Offer;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExchangeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($selected = "BTC-PLN")
    {
        $markets = Market::all();
        $currencies = explode('-', $selected);

        $wallets = Wallet::where('user_id', Auth::user()->id)->whereIn('currency', $currencies)->get();

        $selected = $markets->where('market_code', $selected)->first();

        $orderbook = $this->getParsedOrderbook($selected);

        $disabled = !Auth::user()->public_token;

        return view('exchange.index', compact('markets', 'selected', 'orderbook', 'disabled', 'wallets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    public function selectMarket(Request $request)
    {
        return redirect('/exchange/' . $request->market);
    }

    public function updateAvailableMarkets()
    {
        $skipCurrencies = ["USD", "EUR"];

        $markets = (new PublicRest())->ticker();
        $markets = json_decode($markets);
        if (strtolower($markets->status) == 'ok') {
            $markets = collect($markets->items);

            $query = [];
            foreach ($markets as $key => $market) {

                if (in_array(strtoupper($market->market->first->currency), $skipCurrencies) || in_array(strtoupper($market->market->second->currency), $skipCurrencies))
                    continue;

                $query[] = ["market_code" => $key, "first_currency" => $market->market->first->currency, "second_currency" => $market->market->second->currency, "time" => $market->time, "active" => true];

            }

            DB::table('markets')->update(['active' => false]);
            foreach ($query as $q) {
                DB::table('markets')->updateOrInsert($q);
            }

            return true;
        }
        return false;

    }

    public function buy(Request $request, $market)
    {
        $ca = (float)str_ireplace(',', '.', $request->ca);
        $ra = (float)str_ireplace(',', '.', $request->ra);

        $market = Market::where('market_code', $market)->first();
        if (!$market) {
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        $wallet = Wallet::where(['user_id' => Auth::user()->id, 'currency' => $market->second_currency])->first();

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        $sum = $ca * $ra;
        if ($sum > $wallet->available_founds) {
            return response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }

        try {
            DB::beginTransaction();
            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'buy';
            $offer->active = true;
            $offer->user_id = Auth::user()->id;
            $offer->market_id = $market->id;
            $offer->save();

            $wallet->locked_founds = ($wallet->locked_founds + $sum);
            $wallet->available_founds = ($wallet->available_founds - $sum);
            $wallet->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        return response()->json(['success' => true, 'message' => 'Oferta złożona pomyślnie.']);
    }

    public function sell(Request $request, $market)
    {
        $ca = (float)str_ireplace(',', '.', $request->ca);
        $ra = (float)str_ireplace(',', '.', $request->ra);

        $market = Market::where('market_code', $market)->first();
        if (!$market) {
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        $wallet = Wallet::where(['user_id' => Auth::user()->id, 'currency' => $market->first_currency])->first();

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        $sum = $ca * $ra;
        if ($ca > $wallet->available_founds) {
            return response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }

        try {
            DB::beginTransaction();
            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'buy';
            $offer->active = true;
            $offer->user_id = Auth::user()->id;
            $offer->market_id = $market->id;
            $offer->save();

            $wallet->locked_founds = ($wallet->locked_founds + $ca);
            $wallet->available_founds = ($wallet->available_founds - $ca);
            $wallet->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        return response()->json(['success' => true, 'message' => 'Oferta złożona pomyślnie.']);
    }

    public function getOrderbook($market, $visible = false)
    {
        $selected = Market::where('market_code', $market)->first();
        if (!$selected) {
            return response()->json(['success' => false, 'data' => []], 500);
        }
        //$selected = $markets->where('market_code', $selected)->first();

        $orderbook = $this->getParsedOrderbook($selected);
        $visible = ($visible == "true") ? true : false;
        $view = view('exchange.offers', compact('orderbook', 'visible'))->render();

        return response()->json(['success' => true, 'data' => $view]);
    }


    /**
     * @param $selected
     * @return bool|\Illuminate\Support\Collection|mixed|string|void
     */
    private function getOrderbookFromApi($selected)
    {
        $api = new PublicRest();
        try {
            $orderbook = $api->getOrderbook($selected->market_code);
            $orderbook = json_decode($orderbook);
        } catch (\Exception $e) {
            $orderbook = collect(['buy' => [], 'sell' => []]);
        }
        return $orderbook;
    }

    private function addDbOffersToOrderbook($offers, $orderbook)
    {
        if (!isset($orderbook->sell) && !isset($orderbook->buy)) {
            $orderbook = collect(['buy' => [], 'sell' => []]);
        }

        if ($offers && count($offers) > 0) {

            foreach ($offers as $offer) {
                $offerTmp = new \stdClass();
                $offerTmp->ra = $offer->rate;
                $offerTmp->ca = $offer->amount;
                $offerTmp->sa = $offer->amount;
                $offerTmp->pa = $offer->amount;
                $offerTmp->co = 1;
                if ($offer->type == 'buy') {
                    $orderbook->buy[] = $offerTmp;
                } elseif ($offer->type == 'sell') {
                    $orderbook->sell[] = $offerTmp;
                }
            }
        }
        //dd($orderbook->sell);
        $sell = Arr::sort($orderbook->sell, function ($value) {
            return $value->ra;
        });
        $buy = array_reverse(Arr::sort($orderbook->buy, function ($value) {
            return $value->ra;
        }));
        $orderbook->sell = $sell;
        $orderbook->buy = $buy;
        return $orderbook;
    }

    /**
     * @param $selected
     * @return bool|\Illuminate\Support\Collection|mixed|string|void
     */
    private function getParsedOrderbook($selected)
    {
        $orderbook = $this->getOrderbookFromApi($selected);
        $offers = Offer::where(['market_id' => $selected->id, 'completed' => false])->get();
        $orderbook = $this->addDbOffersToOrderbook($offers, $orderbook);
        return $orderbook;
    }


}
