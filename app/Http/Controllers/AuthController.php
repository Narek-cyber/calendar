<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Oauth2;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT'));
        $client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
        $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $client->addScope(Google_Service_Oauth2::OPENID);
//        $client->addScope(Google_Service_Calendar::CALENDAR);
        session(['oauth2state' => $client->prepareScopes()]);

        return redirect()->to($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        dd($request->all());
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

}
