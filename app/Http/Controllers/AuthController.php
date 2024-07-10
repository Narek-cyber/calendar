<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly Google_Client $client
    )
    {
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT'));
//        $this->client->addScope('https://www.googleapis.com/auth/calendar.events.owned');
        $this->client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
        $this->client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $this->client->addScope(Google_Service_Oauth2::OPENID);
    }

    public function login()
    {
        return redirect('/');
    }

    public function redirectToGoogle()
    {
        return redirect()->to($this->client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('code')) {
            $token = $this->client->fetchAccessTokenWithAuthCode($request->get('code'));

            if (!array_key_exists('error', $token)) {
                $this->client->setAccessToken($token);
                $oauth2 = new Google_Service_Oauth2($this->client);
                $googleUser = $oauth2->userinfo->get();
                $localUser = User::query()->where('email', $googleUser->email)->first();
                if (!$localUser) {
                    $localUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                    ]);
                }
                Auth::login($localUser);
                return redirect()->route('dashboard');
            }
        }
        return redirect('/');
//        $this->client->authenticate($request->get('code'));

//        dd($request->session()->get('oauth2state'), $request->get('state'));
//        if ($request->session()->get('oauth2state') !== $request->get('state')) {
//            return redirect('/')->with('error', 'Invalid state.');
//        }
//        $client = new Google_Client();
//        $client->setClientId(env('GOOGLE_CLIENT_ID'));
//        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
//        $client->setRedirectUri(env('GOOGLE_REDIRECT'));
//        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
//        $request->session()->put('access_token', $token);

//        return view('dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function dashboard()
    {
        $user = auth()->user();
        return view('dashboard', compact('user'));
    }
}
