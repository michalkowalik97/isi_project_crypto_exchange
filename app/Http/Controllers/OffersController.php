<?php

namespace App\Http\Controllers;

use App\Market;
use App\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffersController extends Controller
{


    public function getActiveList(Request $request)
    {
        $markets = Market::all();
        $market = $request->get('market');
        $offers = Offer::where(['completed' => false, 'user_id' => Auth::user()->id])->with('market');
        if ($market) {
            $offers->where('market_id', $market);
        }
        $offers = $offers->paginate(50);


        return view('offers.index',compact('markets','offers'));
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


        return view('offers.index',compact('markets','offers'));
    }
}
