<?php

namespace App\Http\Controllers;

use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Services\GoogleService;
use App\Models\GoogleEvent;
use Google\Service\Exception;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class GoogleCalendarServiceController extends Controller
{
    public function __construct(
        protected GoogleService $googleService
    )
    {
        //
    }

    /**
     * @return Google_Service_Calendar
     */
    private function getGoogleService(): Google_Service_Calendar
    {
        $user = auth()->user();
        $accessToken = $user->{'google_token'};
        $this->googleService->getClient()->setAccessToken($accessToken);
        return new Google_Service_Calendar($this->googleService->getClient());
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
