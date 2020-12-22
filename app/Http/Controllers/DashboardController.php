<?php

namespace App\Http\Controllers;

use App\Offer;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Strona główna widoczna po zalogowaniu.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View widok strony głównej widocznej po zalogowaniu.
     */
    public function index()
    {
        $wallets = Wallet::where('user_id', Auth::user()->id)->orderBy('available_founds','desc')->orderBy('currency','asc')->get();
        return view('dashboard.index',compact('wallets'));
    }

    public function test()
    {
        dd( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]");
        dd(Offer::with('wallet')->find(38));
    }
}
