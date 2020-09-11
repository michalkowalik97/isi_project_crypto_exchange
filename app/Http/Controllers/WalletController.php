<?php

namespace App\Http\Controllers;

use App\Market;
use App\Offer;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{

    /**
     * Metoda wyświetlająca szczegóły dla wybranego portfela
     *
     * @param $id id portfela
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View widok ze szczegółami
     */
    public function show($id)
    {
        $wallet = Wallet::find($id);
        $markets = Market::where('first_currency',$wallet->currency)->orWhere('second_currency',$wallet->currency)->get();
        $offers=[];
        if (count($markets) > 0){
        $offers = Offer::where('user_id',Auth::user()->id)->whereIn('market_id',$markets->pluck('id')->toArray())->withTrashed()->with('market')->get();
        }
        return view('wallet.show', compact('wallet','offers'));
    }

    /**
     * Metoda zwracająca widok z szybkimi płatnościami
     *
     * @param $id id portfela
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View widok
     */
    public function paypal($id)
    {

        return view('wallet.paypal', compact('id'));
    }

    /**
     * Metoda zapisująca dane płątności
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paypalStore(Request $request)
    {
        $wallet = Wallet::find($request->wallet);
        if ($wallet) {
            $amount = str_ireplace(',', '.', $request->amount);
            $wallet->available_founds = ($wallet->available_founds + $amount);
            $wallet->all_founds = ($wallet->all_founds + $amount);
            $wallet->save();
            return response()->json(['success' => true, 'message' => 'Płatność zakończona sukcesem.']);
        }

        return response()->json(['success' => false, 'message' => 'Wystąpił błąd, spróbuj ponownie.']);

    }
}
