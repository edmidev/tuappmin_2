<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TokenFirebaseUser;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $data = $request->all();
        $token = TokenFirebaseUser::where('token', $data['token_firebase'])
        ->where('user_id', Auth::id())->first();

        if(!$token && $data['token_firebase']){
            $tokenFirebaseUser = new TokenFirebaseUser;
            $tokenFirebaseUser->token = $data['token_firebase'];
            $tokenFirebaseUser->acceso = 'Web';
            $tokenFirebaseUser->user_id = Auth::id();
            $tokenFirebaseUser->save();
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        TokenFirebaseUser::where('token', $request->token_firebase)
        ->where('user_id', Auth::id())->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
