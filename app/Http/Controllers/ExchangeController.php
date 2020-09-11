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

        $wallets = Wallet::where('user_id', Auth::user()->id)->whereIn('currency', $currencies)->get();

        $selected = $markets->where('market_code', $selected)->first();

        $orderbook = $this->getParsedOrderbook($selected);

        $disabled = !Auth::user()->public_token;

        return view('exchange.index', compact('markets', 'selected', 'orderbook', 'disabled', 'wallets'));
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
                $db = Market::where([
                    "market_code" =>$q["market_code"],
                    "first_currency" =>$q["first_currency"],
                    "second_currency" =>$q["second_currency"],
                ])->first();
                if (!$db){
                    continue;
                }
                $db->time = $q['time'];
                $db->active = $q['active'];
                $db->save();
               // DB::table('markets')->updateOrInsert($q);
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
            $offer = new Offer();
            $offer->amount = $ca;
            $offer->rate = $ra;
            $offer->completed = false;
            $offer->type = 'sell';
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
        $offers = Offer::where('completed', false)->with('market')->get();
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
                                    Log::info('BUY: offer rate: '. floatval($offer->rate).' matched api offer rate: '.floatval($apiOffer->ra));
                                    Log::info('BUY: offer amount: '. floatval($offer->rate).' maountapi offer rate: '.floatval($apiOffer->ca));

                                    $this->realiseOfferBuy($offer, $apiOffer);
                                    break;
                                    Log::info('---------------------------');
                                }
                            }
                        }
                    } elseif ($offer->type == 'sell') {
                        foreach ($orderbook->buy as $apiOffer) {
                            if (floatval($offer->rate) <= floatval($apiOffer->ra)) {
                                if (floatval($offer->amount) <= floatval($apiOffer->ca)) {
                                    Log::info('---------------------------');
                                    Log::info('SELL: offer rate: '.$offer->rate.' matched api offer rate: '.$apiOffer->ra);
                                    Log::info('SELL: offer amount: '.$offer->amount.' matched api offer maount: '.$apiOffer->ca);

                                    $this->realiseOfferSell($offer, $apiOffer);
                                    break;

                                    Log::info('---------------------------');
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
     * @param $offer oferta która ma zostać zrealizowana
     * @param $apiOffer oferta z giełdy która została dopasowana do oferty z bazy
     */
    private function realiseOfferBuy($offer, $apiOffer)
    {
        if (isset($apiOffer->id)) {
            $sellOffer = Offer::findOrFail($apiOffer->id);
            Log::info  ('BUY: oferta z bazy');
            if ($sellOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashSell($sellOffer,$offer->rate);
                $this->closeOfferAndAddCashBuy($offer,$sellOffer->rate);
            }
        } else {
            Log::info  ('BUY: oferta z Api');
            $this->closeOfferAndAddCashBuy($offer, $apiOffer->ra);
        }
    }

    /**
     * Metoda służąca do realizacji oferty sprzedaży
     * @param $offer oferta która ma zostać zrealizowana
     * @param $apiOffer oferta z giełdy która została dopasowana do oferty z bazy
     */
    private function realiseOfferSell($offer, $apiOffer)
    {
        if (isset($apiOffer->id)) {
            $buyOffer = Offer::findOrFail($apiOffer->id);
            Log::info  ('SELL: oferta z bazy');
            if ($buyOffer->user_id != $offer->user_id) {
                $this->closeOfferAndAddCashBuy($buyOffer, $offer->rate);
                $this->closeOfferAndAddCashSell($offer, $buyOffer->rate);
            }
        } else {
            Log::info  ('SELL: oferta z Api');
            $this->closeOfferAndAddCashSell($offer,$apiOffer->ra);
        }
    }

    /**
     * Metoda służąca do zamknięcia oferty kupna i aktualizacji stanu portfeli
     *
     * @param $offer oferta która ma zostać zrealizowana
     * @param $rate kurs transakcji
     */
    private function closeOfferAndAddCashBuy($offer,$rate)
    {
        Log::info  ('BUY: oferta: '.$offer->toJson());
        Log::info  ('BUY: kurs: '.$rate);
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds + $offer->amount;
        $firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $offer->amount * $rate;
        $secondWallet->all_founds = ($secondWallet->all_founds - $sum);
        $secondWallet->available_founds = ($secondWallet->available_founds + (($offer->amount * $offer->rate)-$sum));
        $secondWallet->locked_founds = ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        $offer->completed = true;
        $offer->realise_rate=$rate;
        $offer->save();
        $this->newTransaction($offer);

    }

    /**
     * Metoda służąca do zamknięcia oferty sprzedaży i aktualizacji stanu portfeli
     *
     * @param $offer oferta która ma zostać zrealizowana
     * @param $rate kurs transakcji
     */
    private function closeOfferAndAddCashSell($offer, $rate)
    {
        Log::info  ('SELL: oferta: '.$offer->toJson());
        Log::info  ('SELL: kurs: '.$rate);
        $firstWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->first_currency])->first();
        $secondWallet = Wallet::where(['user_id' => $offer->user_id, 'currency' => $offer->market->second_currency])->first();
        $firstWallet->all_founds = $firstWallet->all_founds - $offer->amount;
        $firstWallet->locked_founds = $firstWallet->all_founds - $offer->amount;
        //$firstWallet->available_founds = $firstWallet->available_founds + $offer->amount;
        $firstWallet->save();
        $sum = $offer->amount * $rate;
        $secondWallet->all_founds = ($secondWallet->all_founds + $sum);
        $secondWallet->available_founds = ($secondWallet->available_founds + $sum /*(($offer->amount * $offer->rate)-$sum)*/);
        //$secondWallet->locked_founds = ($secondWallet->locked_founds - ($offer->amount * $offer->rate));
        $secondWallet->save();
        $offer->completed = true;
        $offer->realise_rate=$rate;
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
}
