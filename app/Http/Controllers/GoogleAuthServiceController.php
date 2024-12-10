<?php

namespace App\Http\Controllers;

use App\Http\Services\Google\GoogleService;
use App\Models\User;
use Google\Service\Exception;
use Google_Service_Oauth2;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class GoogleAuthServiceController extends Controller
{
    /**
     * @param GoogleService $googleService
     */
    public function __construct(
        protected GoogleService $googleService
    )
    {
        //
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function dashboard(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = auth()->user();
        $events = $user->events()->orderBy('created_at', 'DESC')->paginate(30);
        return view('dashboard.index', compact('user', 'events'));
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
        return redirect()->to($this->googleService->getClient()->createAuthUrl());
    }

    /**
     * @param Request $request
     * @return Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     * @throws Exception
     */
    public function handleGoogleCallback(Request $request): Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        if ($request->has('code')) {
            $token = $this->googleService->getClient()->fetchAccessTokenWithAuthCode($request->get('code'));
            if (!array_key_exists('error', $token)) {
                $this->googleService->getClient()->setAccessToken($token);
                $oauth2 = new Google_Service_Oauth2($this->googleService->getClient());
                $googleUser = $oauth2->userinfo->get();
                $localUser = User::query()->where('email', $googleUser->email)->first();
                if (!$localUser) {
                    $localUser = User::query()->create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'google_token' => json_encode($token),
                    ]);
                } else if ($token) {
                    $localUser->update([
                        'google_token' => json_encode($token),
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
        $user = auth()->user();
        $user->{'google_token'} = null;
        $user->save();
        $this->googleService->revokeToken();
        Auth::logout();
        return redirect('/');
    }
}
