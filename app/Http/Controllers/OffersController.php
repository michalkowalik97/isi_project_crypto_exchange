<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Market;
use App\Offer;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OffersController extends Controller
{

    public function destroy($id)
    {
        $offer = Offer::with('market')->find($id);

        if (!$offer) {
            return redirect()->back()->with(['message' => 'Wystąpił błąd, spróbuj ponownie.']);
        }
        try {
            DB::beginTransaction();
            if ($offer->type == "buy") {
                $sum = ($offer->amount * $offer->rate);
                $wallet = Wallet::where(['user_id' => Auth::user()->id, 'currency' => $offer->market->second_currency])->first();
                if (!$wallet) {
                    return redirect()->back()->with(['message' => 'Wystąpił błąd, spróbuj ponownie.']);
                }
                $wallet->locked_founds = ($wallet->locked_founds - $sum);
                $wallet->available_founds = ($wallet->available_founds + $sum);
                $wallet->save();
            }
            if ($offer->type == "sell") {
                $wallet = Wallet::where(['user_id' => Auth::user()->id, 'currency' => $offer->market->first_currency])->first();
                if (!$wallet) {
                    return redirect()->back()->with(['message' => 'Wystąpił błąd, spróbuj ponownie.']);
                }
                $wallet->locked_founds = ($wallet->locked_founds - $offer->amount);
                $wallet->available_founds = ($wallet->available_founds + $offer->amount);
                $wallet->save();
            }
            $offer->delete();
            DB::commit();
            return redirect()->back()->with(['message' => 'Oferta została anulowana.']);

        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with(['message' => 'Wystąpił błąd, spróbuj ponownie.']);
        }

    }

    public function getActiveList(Request $request)
    {
        $markets = Market::all();
        $market = $request->get('market');
        $offers = Offer::where(['completed' => false, 'user_id' => Auth::user()->id])->with('market');
        if ($market) {
            $offers->where('market_id', $market);
        }
        $offers = $offers->paginate(50);


        return view('offers.index', compact('markets', 'offers'));
    }

    public function getHistoryList(Request $request)
    {
        $markets = Market::all();
        $market = $request->get('market');
        $offers = Offer::where(['completed' => true, 'user_id' => Auth::user()->id])->with('market');
        if ($market) {
            $offers->where('market_id', $market);
        }
        $offers = $offers->paginate(50);


        return view('offers.history', compact('markets', 'offers'));
    }

}
