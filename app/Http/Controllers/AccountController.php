<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('account.index');
    }

    public function editPassword()
    {
        return view('account.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password'=>['required', 'string', 'min:8'],
            'new_password'=>['required', 'string', 'min:8',/*'confirmed'*/],
            'password_confirmation'=>['same:new_password']
        ]);

        if (Hash::check($request->old_password,Auth::user()->getAuthPassword())){
            $user = User::find(Auth::user()->id);
            $user->password=Hash::make($request->new_password);
            $user->save();

            return redirect('/account/settings')->with(['message'=>'Hasło zostało zmienione.']);
        }else{
            return redirect()->back()->with(['message'=>'Podane stare hasło jest niepoprawne.']);
        }

        return redirect()->back()->with(['message'=>'Wystąpił błąd spróbuj jeszcze raz.']);

    }

}
