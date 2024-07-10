<?php

namespace App\Http\Controllers;

use App\Http\Requests\Events\StoreEventRequest;
use App\Models\User;
use Google\Service\Calendar\Event;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Oauth2;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class GoogleServiceController extends Controller
{
    public function __construct(
        private readonly Google_Client $client
    )
    {
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT'));
        $this->client->addScope('https://www.googleapis.com/auth/calendar.events.owned');
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
                        'google_token' => $token['access_token'],
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

    public function addGoogleCalendarEvent(StoreEventRequest $request)
    {
        $user = auth()->user();
        $accessToken = $user->{'google_token'};
        $this->client->setAccessToken($accessToken);
        $service = new Google_Service_Calendar($this->client);
        $validated = $request->validated();
//dd($service->calendars);
//        $event = new Google_Service_Calendar_Event([
//            'summary' => $validated['summary'],
//            'location' => $validated['location'],
//            'description' => $validated['description'],
//            'start' => [
//                'dateTime' => date('c', strtotime($validated['start'])),
//                'timeZone' => 'America/Los_Angeles',
//            ],
//            'end' => [
//                'dateTime' => date('c', strtotime($validated['end'])),
//                'timeZone' => 'America/Los_Angeles',
//            ],
//        ]);

//        $event = new Google_Service_Calendar_Event(array(
//            'summary' => 'Google I/O 2024',
//            'location' => '800 Howard St., San Francisco, CA 94103',
//            'description' => 'A chance to hear more about Google\'s developer products.',
//            'start' => array(
//                'dateTime' => '2024-07-28T09:00:00-07:00',
//                'timeZone' => 'America/Los_Angeles',
//            ),
//            'end' => array(
//                'dateTime' => '2024-07-28T17:00:00-07:00',
//                'timeZone' => 'America/Los_Angeles',
//            ),
//            'recurrence' => array(
//                'RRULE:FREQ=DAILY;COUNT=10000'
//            ),
//            'attendees' => array(
//                array('email' => 'lpage@example.com'),
//                array('email' => 'sbrin@example.com'),
//            ),
//            'reminders' => array(
//                'useDefault' => FALSE,
//                'overrides' => array(
//                    array('method' => 'email', 'minutes' => 24 * 60),
//                    array('method' => 'popup', 'minutes' => 10),
//                ),
//            ),
//        ));
        // Insert the event into the user's calendar
        //$calendarId = 'primary'; // Use 'primary' for the user's primary calendar
//        $event1 = $service->events->insert($calendarId, $event);
//        dd($service->calendars->get('primary'));
    }
}
