<?php

namespace App\Http\Controllers;

use App\Helpers\BitBay\PublicRest;
use App\Market;
use App\Offer;
use App\OfferPart;
use App\Transaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class ExchangeController extends Controller
{
    /**
     * Strona główna giełdy.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View widok strony głównej giełdy.
     */
    public function index($selected = "BTC-PLN")
    {
        $markets = Market::all();
        $currencies = explode('-', $selected);
        $marketCodeChart = str_ireplace('-','',$selected);
        $wallets = Wallet::where('user_id', Auth::user()->id)->whereIn('currency', $currencies)->get();

        $selected = $markets->where('market_code', $selected)->first();

        $orderbook = $this->getParsedOrderbook($selected);

        $disabled = !Auth::user()->public_token;

        return view('exchange.index', compact('markets', 'selected', 'orderbook', 'disabled', 'wallets','marketCodeChart'));
    }

    /**
     * Metoda pozwalająca na wybranie rynku na giełdzie.
     *
     * @param Request $request request zawierający kod wybranego rynku
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector przekierowanie do giełdy z wybranym rynkiem
     */
    public function selectMarket(Request $request)
    {
        return redirect('/exchange/' . $request->market);
    }


    /**
     * Metoda sprawdzająca dostępność rynków i aktualizująca informacje w bazie
     * @return bool informacja czy udało się zaktualizować informacje
     */
    public function updateAvailableMarkets()
    {
        $skipCurrencies = ["USD", "EUR"];

        $markets = (new PublicRest())->ticker();
        $markets = $markets;
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
                /* $db = Market::where([
                     "market_code" =>$q["market_code"],
                     "first_currency" =>$q["first_currency"],
                     "second_currency" =>$q["second_currency"],
                 ])->first();
                 if (!$db){
                     continue;
                 }
                 $db->time = $q['time'];
                 $db->active = $q['active'];
                 $db->save();*/
                DB::table('markets')->updateOrInsert($q);
            }

            return true;
        }
        return false;
    }

    /**
     * Metoda służąca do dodawania ofert kupna na giełdzie
     *
     * @param Request $request request zawierający dane złożonej oferty
     * @param $market rynek którego dotyczy oferta
     * @return \Illuminate\Http\JsonResponse odpowiedź w formacie JSON zawierająca informację czy udało się złożyć ofertę
     */
    public function buy(Request $request, $market)
    {
        $ca = (float)str_ireplace(',', '.', $request->ca);
        $ra = (float)str_ireplace(',', '.', $request->ra);

        $market = Market::where('market_code', $market)->first();
        if (!$market) {
            Log::info('...---... ExchangeController->buy: market not found, market: ' . json_encode($market));
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        $wallet = Wallet::where(['user_id' => Auth::user()->id, 'currency' => $market->second_currency])->first();

        if (!$wallet) {
            Log::info('...---...  ExchangeController->buy: wallet not found, market: ' . json_encode($market));
            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }
        $sum = $ca * $ra;
        if ($sum > $wallet->available_founds) {
            return response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }

        try {
            DB::beginTransaction();

            $lockedFounds = $sum;
            $wallet->locked_founds += $lockedFounds;
            $wallet->available_founds = ($wallet->available_founds - $sum);
            $wallet->save();

            $offer = new Offer();
            $offer->amount = $ca;
            $offer->initial_amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'buy';
            $offer->active = true;
            $offer->user_id = Auth::user()->id;
            $offer->market_id = $market->id;
            $offer->locked_founds = $lockedFounds;
            $offer->wallet_id = $wallet->id;
            $offer->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('...---...  exception,  message: ' . $e->getMessage() . ' in line: ' . $e->getLine() . ' stacktrace: ' . $e->getTraceAsString());

            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        return response()->json(['success' => true, 'message' => 'Oferta złożona pomyślnie.']);
    }

    /**
     * Metoda służąca do dodawania ofert sprzedaży na giełdzie
     *
     * @param Request $request request zawierający dane złożonej oferty
     * @param $market rynek którego dotyczy oferta
     * @return \Illuminate\Http\JsonResponse odpowiedź w formacie JSON zawierająca informację czy udało się złożyć ofertę
     */
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
        // $sum = $ca * $ra;
        if ($ca > $wallet->available_founds) {
            return response()->json(['success' => false, 'message' => 'Brak wystarczających środków do złożenia oferty.']);
        }

        try {
            DB::beginTransaction();
            $lockedFounds = $ca;
            $wallet->locked_founds += $lockedFounds;
            $wallet->available_founds = ($wallet->available_founds - $ca);
            $wallet->save();

            $offer = new Offer();
            $offer->amount = $ca;
            $offer->initial_amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'sell';
            $offer->active = true;
            $offer->user_id = Auth::user()->id;
            $offer->market_id = $market->id;
            $offer->locked_founds = $lockedFounds;
            $offer->wallet_id = $wallet->id;
            $offer->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('...---...  exception,  message: ' . $e->getMessage() . ' in line: ' . $e->getLine() . ' stacktrace: ' . $e->getTraceAsString());

            return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj jeszcze raz.']);
        }

        return response()->json(['success' => true, 'message' => 'Oferta złożona pomyślnie.']);
    }

    /**
     * Metoda pozwalająca na pobranie listy aktywnych ofert na danym rynku
     *
     * @param $market kod rynku dla którego mają być pobrane oferty
     * @param bool $visible informacja czy mają być wyświetlone wszystkie oferty czy tylko kilka początkowych
     * @return \Illuminate\Http\JsonResponse odpowiedź w formacie JSON zawierająca aktualne dane
     * @throws \Throwable wyjątek
     */
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
     * Metoda pobierająca aktualną listę ofert z giełdy dla wskazanego rynku
     *
     * @param Market $selected model z wybranym rynkiem
     * @return bool|\Illuminate\Support\Collection|mixed|string|void lista ofert pobrana z giełdy
     */
    private function getOrderbookFromApi(Market $selected)
    {
        $api = new PublicRest();
        try {
            $orderbook = $api->getOrderbook($selected->market_code);
            $orderbook = json_decode($orderbook);
        } catch (\Exception $e) {
            Log::info('...---... Failed to get orderbook from api');
            Log::info('...---...  exception,  message: ' . $e->getMessage() . ' in line: ' . $e->getLine() . ' stacktrace: ' . $e->getTraceAsString());
            $orderbook = collect(['buy' => [], 'sell' => []]);
        }
        return $orderbook;
    }

    /**
     * Metoda łącząca oferty z bazy danych z ofertami pobranymi z API
     * @param $offers oferty pobrane z bazy danych
     * @param $orderbook lista ofert pobrana z API
     * @return \Illuminate\Support\Collection połączona lista ofert
     */
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
        if (isset($orderbook->sell[0])) {
            $sell = Arr::sort($orderbook->sell, function ($value) {
                return $value->ra;
            });
        } else {
            $sell = [];
        }
        if (isset($orderbook->buy[0])) {
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
     * Metoda służąca do pobrania pełnej listy ofert z uwzględnieniem ofert z bazy
     *
     * @param $selected rynek dla którego ma być pobrana lista ofert
     * @return bool|\Illuminate\Support\Collection|mixed|string|void lista ofert
     */
    private function getParsedOrderbook($selected)
    {
        $orderbook = $this->getOrderbookFromApi($selected);
        $offers = Offer::where(['market_id' => $selected->id, 'completed' => false])->get();
        $orderbook = $this->addDbOffersToOrderbook($offers, $orderbook);
        return $orderbook;
    }

    /**
     * Metoda służąca do sprawdzania i oznaczania czy jakaś oferta z bazy może zostać zrealizowana
     */
    public function checkOffers()
    {
        $offers = Offer::where('completed', false)->with('market', 'wallet')->get();
        if (count($offers) > 0) {
            $offers = $offers->groupBy('market.market_code');
            foreach ($offers as $key => $market) {
                $selected = Market::where('market_code', $key)->first();
                $orderbook = $this->getParsedOrderbook($selected);
                foreach ($market as $offer) {

                    if ($offer->type == 'buy') {
                        foreach ($orderbook->sell as $apiOffer) {
                            if (floatval($offer->rate) >= floatval($apiOffer->ra)) {
                                if (floatval($offer->amount) <= floatval($apiOffer->ca)) {
                                    Log::info('---------------------------');
                                    Log::info('BUY: offer rate: ' . floatval($offer->rate) . ' matched api offer rate: ' . floatval($apiOffer->ra));
                                    Log::info('BUY: offer amount: ' . floatval($offer->rate) . ' maountapi offer rate: ' . floatval($apiOffer->ca));

                                    $this->realiseOfferBuy($offer, $apiOffer);
                                    break;
                                    Log::info('---------------------------');
                                } else {
                                    Log::info('...---... partial buy');
                                    $this->realisePartialOfferBuy($offer, $apiOffer);
                                    $offer->fresh();
                                    //partial buy
                                    //$offer->refresh with relationships
                                }
                            }
                        }
                    } elseif ($offer->type == 'sell') {
                        foreach ($orderbook->buy as $apiOffer) {
                            if (floatval($offer->rate) <= floatval($apiOffer->ra)) {
                                if (floatval($offer->amount) <= floatval($apiOffer->ca)) {
                                    Log::info('---------------------------');
                                    Log::info('SELL: offer rate: ' . $offer->rate . ' matched api offer rate: ' . $apiOffer->ra);
                                    Log::info('SELL: offer amount: ' . $offer->amount . ' matched api offer maount: ' . $apiOffer->ca);

                                    $this->realiseOfferSell($offer, $apiOffer);
                                    break;

                                    Log::info('---------------------------');
                                } else {
                                    Log::info('...---... partial sell');
                                    //partial sell()
                                    $this->realisePartialOfferSell($offer, $apiOffer);
                                    $offer->fresh();
                                    //$offer->refresh with relationships

                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Metoda służąca do realizacji oferty kupna
     * @param Offer $offer oferta która ma zostać zrealizowana
     * @param $apiOffer - oferta z giełdy która została dopasowana do oferty z bazy
     */
    private function realiseOfferBuy(Offer $offer, $apiOffer)
    {
        $offer->load('parts');

        if (isset($apiOffer->id)) {
            if ($this->isOfferMatchedBefore($offer, $apiOffer->id)) {
                Log::info('...---... offer buy matched before ==> return null');
                return null;
            }
            $sellOffer = Offer::with('wallet')->findOrFail($apiOffer->id);
            Log::info('BUY: oferta z bazy');
            if ($sellOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashSell($sellOffer, $offer->rate);
                $this->closeOfferAndAddCashBuy($offer, $sellOffer->rate);
            }
        } else {
            Log::info('BUY: oferta z Api');
            if ($this->isOfferMatchedBeforeApi($offer, hash('md5', json_encode($apiOffer)))) {
                Log::info('...---... offer buy matched before ==> return null');
                return null;
            }
            $this->closeOfferAndAddCashBuy($offer, $apiOffer->ra);
        }
    }


    private function realisePartialOfferBuy($offer, $apiOffer)
    {
        // return null;
        $offer->load('parts');
        if (isset($apiOffer->id)) {
            //dopasowana oferta to oferta z bazy
            Log::info('...---... partial buy matched with db offer');

            if ($this->isOfferMatchedBefore($offer, $apiOffer->id)) {
                Log::info('...---... partial buy matched before ==> return null');
                return null;
            }

            $sellOffer = Offer::with('wallet')->findOrFail($apiOffer->id);
            if ($sellOffer->user_id != $offer->user_id) {
                //zamknięcie oferty sprzedaży
                $this->closeOfferAndAddCashSell($sellOffer, $offer->rate);
                $this->partialBuyFromDb($offer, $sellOffer);
                //częściowa realizacja oferty kupna
            } else {
                Log::info('...---... matched offer have same user_id');
            }
        } else {
            //dopasowana oferta to oferta z api
            Log::info('...---... partial buy matched with api offer');

            //sprawdzanie hashu w bazie
            if ($this->isOfferMatchedBeforeApi($offer, hash('md5', json_encode($apiOffer)))) {
                Log::info('...---... partial buy matched before ==> return null');
                return null;
            }
            $this->partialBuyFromApi($offer, $apiOffer);
        }
    }

    private function realisePartialOfferSell($offer, $apiOffer)
    {
      //  return null;
        $offer->load('parts');

        if (isset($apiOffer->id)) {
            Log::info('...---... SELL: partial sell matched with db offer');
            if ($this->isOfferMatchedBefore($offer, $apiOffer->id)) {
                Log::info('...---... SELL: partial sell matched before ==> return null');
                return null;
            }
            $buyOffer = Offer::with('wallet')->findOrFail($apiOffer->id);
            if ($buyOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashBuy($buyOffer, $offer->rate);
                $this->partialSellFromDb($offer, $buyOffer);
            } else {
                Log::info('...---... SELL: matched offer have same user_id');
            }
            //dopasowana oferta to oferta z bazy
        } else {
            Log::info('...---... SELL: partial buy matched with api offer');
            if ($this->isOfferMatchedBeforeApi($offer, hash('md5', json_encode($apiOffer)))) {
                Log::info('...---... SELL: partial sell matched before ==> return null');
                return null;
            }
            $this->partialSellFromApi($offer, $apiOffer);
        }
    }

    private function partialSellFromDb(Offer $offer, Offer $buyOffer)
    {
        Log::info('...---... partialSellFromDb');
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds -= $buyOffer->amount;
        $firstWallet->locked_founds /*= $firstWallet->all_founds*/ -= $buyOffer->amount;
        //$firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $buyOffer->amount * $buyOffer->rate;
        $secondWallet->all_founds += $sum;
        $secondWallet->available_founds += $sum /*(($offer->amount * $offer->rate)-$sum)*/
        ;
        //$secondWallet->locked_founds = ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        // $offer->completed = true;
        // $offer->active = false;
        // $offer->realise_rate = $rate;
        $offer->amount -= $buyOffer->amount;
        $offer->locked_founds -= $buyOffer->amount;
        $offer->save();
        $this->newOfferPartFromDb($offer, $buyOffer);
//        $this->newTransaction($offer);
    }

    private function partialSellFromApi(Offer $offer, $apiOffer)
    {
        Log::info('partialSellFromApi');
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds -= $apiOffer->ca;
        $firstWallet->locked_founds /*= $firstWallet->all_founds*/ -= $apiOffer->ca;
        //$firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $apiOffer->ca * $apiOffer->ra;
        $secondWallet->all_founds += $sum;
        $secondWallet->available_founds += $sum /*(($offer->amount * $offer->rate)-$sum)*/
        ;
        //$secondWallet->locked_founds = ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        // $offer->completed = true;
        // $offer->active = false;
        // $offer->realise_rate = $rate;
        $offer->amount -= $apiOffer->ca;
        $offer->locked_founds -= $apiOffer->ca;
        $offer->save();
        $this->newOfferPartFromApi($offer, $apiOffer);
//        $this->newTransaction($offer);
    }

    /**
     * Metoda służąca do realizacji oferty sprzedaży
     * @param Offer $offer oferta która ma zostać zrealizowana
     * @param $apiOffer - oferta z giełdy która została dopasowana do oferty z bazy
     */
    private function realiseOfferSell(Offer $offer, $apiOffer)
    {
        if (isset($apiOffer->id)) {
            $buyOffer = Offer::with('wallet')->findOrFail($apiOffer->id);
            Log::info('SELL: oferta z bazy');
            if ($this->isOfferMatchedBefore($offer, $apiOffer->id)) {
                Log::info('...---... SELL: offer sell matched before ==> return null');
                return null;
            }
            if ($buyOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashBuy($buyOffer, $offer->rate);
                $this->closeOfferAndAddCashSell($offer, $buyOffer->rate);
            }
        } else {
            Log::info('SELL: oferta z Api');
            if ($this->isOfferMatchedBeforeApi($offer, hash('md5', json_encode($apiOffer)))) {
                Log::info('...---... offer sell matched before ==> return null');
                return null;
            }
            $this->closeOfferAndAddCashSell($offer, $apiOffer->ra);
        }
    }


    /**
     * Metoda służąca do zamknięcia oferty kupna i aktualizacji stanu portfeli
     *
     * @param Offer $offer oferta która ma zostać zrealizowana
     * @param $rate kurs transakcji
     */
    private function closeOfferAndAddCashBuy(Offer $offer, $rate)
    {
        Log::info('BUY: oferta: ' . $offer->toJson());
        Log::info('BUY: kurs: ' . $rate);
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds + $offer->amount;
        $firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $offer->amount * $rate;
        $secondWallet->all_founds = ($secondWallet->all_founds - $sum);
        $secondWallet->available_founds = ($secondWallet->available_founds + (($offer->amount * $offer->rate) - $sum));
        $secondWallet->locked_founds -= $offer->locked_founds;// ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        $offer->completed = true;
        $offer->active = false;
        if (count($offer->parts) > 0) {
            $offer->realise_rate = $this->calculateOfferRealiseRateFromParts($offer);
        } else {
            $offer->realise_rate = $rate;
        }
        $offer->save();
        $this->newTransaction($offer);

    }

    private function partialBuyFromDb(Offer $offer, Offer $sellOffer)
    {
        Log::info('...---... partialBuyFromDb');

        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds + $sellOffer->amount;
        $firstWallet->available_founds = $firstWallet->available_founds + $sellOffer->amount;
        $firstWallet->save();

        $sum = $sellOffer->amount * $sellOffer->rate;
        $secondWallet->all_founds -= $sum;
        // $secondWallet->available_founds += (($offer->amount * $offer->rate) - $sum);
        $secondWallet->locked_founds -= $sum;//$offer->locked_founds;// ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        //$offer->completed = true;
        //$offer->active = false;
        //  $offer->realise_rate = $rate;
        $offer->amount -= $sellOffer->amount;
        $offer->locked_founds -= $sum;
        $offer->save();

        $this->newOfferPartFromDb($offer, $sellOffer);
        //$this->newTransaction($offer);
    }

    private function partialBuyFromApi(Offer $offer, $apiOffer)
    {
        Log::info('...---... partialBuyFromApi');
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds + $apiOffer->ca;
        $firstWallet->available_founds = $firstWallet->available_founds + $apiOffer->ca;
        $firstWallet->save();

        $sum = $apiOffer->ca * $apiOffer->ra;
        $secondWallet->all_founds -= $sum;
        // $secondWallet->available_founds += (($offer->amount * $offer->rate) - $sum);
        $secondWallet->locked_founds -= $sum;//$offer->locked_founds;// ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        //$offer->completed = true;
        //$offer->active = false;
        //  $offer->realise_rate = $rate;
        $offer->amount -= $apiOffer->ca;
        $offer->locked_founds -= $sum;
        $offer->save();

        $this->newOfferPartFromApi($offer, $apiOffer);
    }

    /**
     * Metoda służąca do zamknięcia oferty sprzedaży i aktualizacji stanu portfeli
     *
     * @param Offer $offer oferta która ma zostać zrealizowana
     * @param float $rate kurs transakcji
     */
    private function closeOfferAndAddCashSell(Offer $offer, $rate)
    {
        Log::info('SELL: oferta: ' . $offer->toJson());
        Log::info('SELL: kurs: ' . $rate);
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds - $offer->amount;
        $firstWallet->locked_founds /*= $firstWallet->all_founds*/ -= $offer->amount;
        //$firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $offer->amount * $rate;
        $secondWallet->all_founds = ($secondWallet->all_founds + $sum);
        $secondWallet->available_founds = ($secondWallet->available_founds + $sum /*(($offer->amount * $offer->rate)-$sum)*/);
        //$secondWallet->locked_founds = ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        $offer->completed = true;
        $offer->active = false;
        if (count($offer->parts) > 0) {
            $offer->realise_rate = $this->calculateOfferRealiseRateFromParts($offer);
        } else {
            $offer->realise_rate = $rate;
        }
        $offer->save();
        $this->newTransaction($offer);
    }

    /**
     * Metoda zapisująca transakcję w bazie
     * @param Offer $offer oferta dla której została zrealizowana transakcja
     */
    private function newTransaction(Offer $offer)
    {
        $transaction = new Transaction();
        $transaction->offer_id = $offer->id;
        $transaction->save();
    }

    private function isOfferMatchedBefore(Offer $offer, int $id)
    {
        if (count($offer->parts) <= 0) {
            return false;
        }
        foreach ($offer->parts as $part) {
            if ($part->matched_offer_id == $id) {
                return true;
            }
        }
        return false;
    }

    private function isOfferMatchedBeforeApi($offer, string $hash)
    {
        if (count($offer->parts) <= 0) {
            return false;
        }
        foreach ($offer->parts as $part) {
            if ($part->matched_offer_hash == $hash) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Offer $offer
     * @param Offer $sellOffer
     */
    private function newOfferPartFromDb(Offer $offer, Offer $sellOffer): void
    {
        $offerPart = new OfferPart();
        $offerPart->offer_id = $offer->id;
        $offerPart->amount = $sellOffer->amount;
        $offerPart->rate = $sellOffer->rate;
        $offerPart->matched_offer_id = $sellOffer->id;
        $offerPart->save();
    }

    private function newOfferPartFromApi(Offer $offer, $apiOffer): void
    {
        $offerPart = new OfferPart();
        $offerPart->offer_id = $offer->id;
        $offerPart->amount = $apiOffer->ca;
        $offerPart->rate = $apiOffer->ra;
        $offerPart->matched_offer_hash = hash('md5', json_encode($apiOffer));
        $offerPart->save();
    }

    private function calculateOfferRealiseRateFromParts(Offer $offer)
    {
        $totalSpend = $offer->amount * $offer->rate;
        $totalAmount = $offer->amount;
        foreach ($offer->parts as $part) {
            $totalSpend += ($part->amount * $part->rate);
            $totalAmount += $part->amount;
        }
        return number_format(($totalSpend / $totalAmount), 2, '.', '');
    }

}
