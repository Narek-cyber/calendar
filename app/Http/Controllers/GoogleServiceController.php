<?php

namespace App\Http\Controllers;

use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Models\GoogleEvent;
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
     * @return Google_Service_Calendar
     */
    private function getGoogleService(): Google_Service_Calendar
    {
        $user = auth()->user();
        $accessToken = $user->{'google_token'};
        $this->client->setAccessToken($accessToken);
        return new Google_Service_Calendar($this->client);
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
        $events = $user->events()->orderBy('created_at', 'desc')->get();
        return view('dashboard.index', compact('user', 'events'));
    }

    /**
     * @param StoreEventRequest $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function addGoogleCalendarEvent(StoreEventRequest $request): RedirectResponse
    {
        $service = $this->getGoogleService();
        $validated = $request->validated();

        $event = new Google_Service_Calendar_Event([
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => [
                'dateTime' => date('c', strtotime($validated['start'])),
                'timeZone' => 'America/Los_Angeles',
            ],
            'end' => [
                'dateTime' => date('c', strtotime($validated['end'])),
                'timeZone' => 'America/Los_Angeles',
            ],
        ]);

        $calendarId = 'primary';
        $eventGoogle = $service->events->insert($calendarId, $event);

        GoogleEvent::query()->create([
            'user_id' => auth()->id(),
            'event_id' => $eventGoogle->id,
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
        ]);
        return redirect()->back()->with('success', 'Event added successfully.');
    }

    /**
     * @param $id
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function editGoogleCalendarEvent($id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = auth()->user();
        $event = GoogleEvent::query()->findOrFail($id);
        return view('dashboard.edit', compact('user', 'event'));
    }

    /**
     * @param UpdateEventRequest $request
     * @param $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function updateGoogleCalendarEvent(UpdateEventRequest $request, $id): RedirectResponse
    {
        $service = $this->getGoogleService();
        $validated = $request->validated();
        $event = GoogleEvent::query()->findOrFail($id);
        $googleEvent = new Google_Service_Calendar_Event([
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => [
                'dateTime' => date('c', strtotime($validated['start'])),
                'timeZone' => 'America/Los_Angeles',
            ],
            'end' => [
                'dateTime' => date('c', strtotime($validated['end'])),
                'timeZone' => 'America/Los_Angeles',
            ],
        ]);

        $service->events->update('primary', $event->{'event_id'}, $googleEvent);

        $event->update([
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Event updated successfully.');
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete($id): RedirectResponse
    {
        $service = $this->getGoogleService();
        $event = GoogleEvent::query()->findOrFail($id);
        $service->events->delete('primary', $event->{'event_id'});
        $event->delete();
        return redirect()->back()->with('success', 'Event deleted successfully.');
    }
}
