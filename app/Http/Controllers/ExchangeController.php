<?php

namespace App\Http\Controllers;

use App\Helpers\BitBay\PublicRest;
use App\Market;
use App\Offer;
use App\Transaction;
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

        $currencies = explode('-', $market);
        $wallets = Wallet::where('user_id', Auth::user()->id)->whereIn('currency', $currencies)->get();
        $balance = view('exchange.balance', compact('wallets'))->render();

        return response()->json(['success' => true, 'data' => $view, 'balance' => $balance]);
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
                $offerTmp->id = $offer->id;
                if ($offer->type == 'buy') {
                    $orderbook->buy[] = $offerTmp;
                } elseif ($offer->type == 'sell') {
                    $orderbook->sell[] = $offerTmp;
                }
            }
        }
        if (($orderbook->sell[0])) {
            $sell = Arr::sort($orderbook->sell, function ($value) {
                return $value->ra;
            });
        } else {
            $sell = [];
        }
        if (($orderbook->buy[0])) {
            $buy = array_reverse(Arr::sort($orderbook->buy, function ($value) {
                return $value->ra;
            }));
        } else {
            $buy = [];
        }

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

    public function checkOffers()
    {

        $offers = Offer::where('completed', false)->with('market')->get();
        if (count($offers) > 0) {
            $offers = $offers->groupBy('market.market_code');
            foreach ($offers as $key => $market) {
                $selected = Market::where('market_code', $key)->first();
                $orderbook = $this->getParsedOrderbook($selected);
                foreach ($market as $offer) {

                    if ($offer->type == 'buy') {
                        foreach ($orderbook->sell as $apiOffer) {
                            if (floatval($offer->rate) == floatval($apiOffer->ra)) {
                                if (floatval($offer->amount) <= floatval($apiOffer->ca)) {
                                    $this->realiseOfferBuy($offer, $apiOffer);
                                }
                            }
                        }
                    } elseif ($offer->type == 'sell') {
                        foreach ($orderbook->buy as $apiOffer) {
                            if (floatval($offer->rate) == floatval($apiOffer->ra)) {
                                if (floatval($offer->amount) <= floatval($apiOffer->ca)) {
                                    $this->realiseOfferBuy($offer, $apiOffer);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function realiseOfferBuy($offer, $apiOffer)
    {
        if (isset($apiOffer->id)) {
            $sellOffer = Offer::findOrFail($apiOffer->id);
            if ($sellOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashSell($sellOffer);
                $this->closeOfferAndAddCashBuy($offer);
            }
        } else {
            $this->closeOfferAndAddCashBuy($offer);
        }
    }

    private function realiseOfferSell($offer, $apiOffer)
    {
        if (isset($apiOffer->id)) {
            $buyOffer = Offer::findOrFail($apiOffer->id);
            if ($buyOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashBuy($buyOffer);
                $this->closeOfferAndAddCashSell($offer);
            }
        } else {
            $this->closeOfferAndAddCashSell($offer);
        }
    }

    private function closeOfferAndAddCashBuy($offer)
    {
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds + $offer->amount;
        $firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $offer->amount * $offer->rate;
        $secondWallet->all_founds = ($secondWallet->all_founds - $sum);
        $secondWallet->available_founds = ($secondWallet->available_founds - $sum);
        $secondWallet->locked_founds = ($secondWallet->locked_founds - $sum);
        $secondWallet->save();
        $offer->completed = true;
        $offer->save();
        $this->newTransaction($offer);

    }

    private function closeOfferAndAddCashSell($offer)
    {
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds + $offer->amount;
        $firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $offer->amount * $offer->rate;
        $secondWallet->all_founds = ($secondWallet->all_founds - $sum);
        $secondWallet->available_founds = ($secondWallet->available_founds - $sum);
        $secondWallet->locked_founds = ($secondWallet->locked_founds - $sum);
        $secondWallet->save();
        $offer->completed = true;
        $offer->save();
        $this->newTransaction($offer);
    }

    private function newTransaction(Offer $offer)
    {
        $transaction = new Transaction();
        $transaction->offer_id = $offer->id;
        $transaction->save();
    }
}
