<?php

namespace App\Http\Controllers;

use App\Helpers\BitBay\PublicRest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Compound;

class IntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $token = Auth::user()->public_token;

        if ($token)
            return view('integration.index', compact('token'));
        else
            return view('integration.create');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('integration.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'klucz_publiczny' => 'required|min:15',
            'klucz_prywatny' => 'required|min:15',

        ]);

        $user = Auth::user();
        $user->public_token = $request->klucz_publiczny;
        $user->private_token = $request->klucz_prywatny;
        $user->save();

       return redirect('/account/settings/integration')->with('message', "Klucze zostały zapisane pomyślnie.");
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $user = Auth::user();
        $user->public_token = null;
        $user->private_token = null;
        $user->save();

        return redirect('/account/settings/integration')->with('message', "Klucze zostały usunięte pomyślnie.");
    }
}
