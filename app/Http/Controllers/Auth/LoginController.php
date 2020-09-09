<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';//RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        return redirect($this->redirectTo);
    }

    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();

            $dbUser = User::where('email', $user->email)->first();
            if ($dbUser) {
                Auth::loginUsingId($dbUser->id);
                return redirect($this->redirectTo);
            } else {
                $dbUser = new User();
                $dbUser->name = $user->name;
                $dbUser->email = $user->email;
                $dbUser->password = Hash::make($user->id);
                $dbUser->role = 'user';
                $dbUser->save();
                event(new UserRegistered($dbUser));
                Auth::loginUsingId($dbUser->id);

                return redirect($this->redirectTo);
            }
        } catch (\Exception $e) {
            return redirect('/login')->with(['loginError'=>'Wystąpił błąd podczas logowania, spróbuj jeszcze raz.']);
        }
    }
}
