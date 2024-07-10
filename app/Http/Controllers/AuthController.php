<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Oauth2;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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

    /**
     * @return Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     */
    public function login(): Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        return redirect('/');
    }

    /**
     * @return RedirectResponse
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return redirect()->to($this->client->createAuthUrl());
    }

    /**
     * @param Request $request
     * @return Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     * @throws Exception
     */
    public function handleGoogleCallback(Request $request): Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        if ($request->has('code')) {
            $token = $this->client->fetchAccessTokenWithAuthCode($request->get('code'));

            if (!array_key_exists('error', $token)) {
                $this->client->setAccessToken($token);
                $oauth2 = new Google_Service_Oauth2($this->client);
                $googleUser = $oauth2->userinfo->get();
                $localUser = User::query()->where('email', $googleUser->email)->first();
                if (!$localUser) {
                    $localUser = User::query()->create([
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
    }

    /**
     * @return Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     */
    public function logout(): Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        Auth::logout();
        return redirect('/');
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function dashboard(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = auth()->user();
        return view('dashboard', compact('user'));
    }
}
