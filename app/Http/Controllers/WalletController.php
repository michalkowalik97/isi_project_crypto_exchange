<?php

namespace App\Http\Controllers;

use App\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wallet = Wallet::find($id);
        return view('wallet.show', compact('wallet'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    public function paypal($id)
    {

        return view('wallet.paypal', compact('id'));
    }

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
