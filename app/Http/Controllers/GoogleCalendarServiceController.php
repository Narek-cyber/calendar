<?php

namespace App\Http\Controllers;

use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Services\Google\GoogleCalendarEventService;
use App\Http\Services\Google\GoogleService;
use Google_Service_Calendar;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleCalendarServiceController extends Controller
{
    public function __construct(
        protected GoogleService $googleService,
        protected GoogleCalendarEventService $googleEvent
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
     */
    public function addGoogleCalendarEvent(StoreEventRequest $request): RedirectResponse
    {
        try {
            $service = $this->getGoogleService();
            $validated = $request->validated();
            $data = [
                'service' => $service,
                'validated' => $validated,
            ];
            $this->googleEvent->storeEvent($data);
            return redirect()->back()->with('success', 'Event added successfully.');
        } catch (Exception $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . "->" . $e->getMessage());
            return redirect()->back()->with('error', 'Try again.');
        }
    }

    /**
     * @param $id
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function editGoogleCalendarEvent($id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = auth()->user();
        $event = $this->googleEvent->findEvent($id);
        return view('dashboard.edit', compact('user', 'event'));
    }

    /**
     * @param UpdateEventRequest $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateGoogleCalendarEvent(UpdateEventRequest $request, $id): RedirectResponse
    {
        try {
            $service = $this->getGoogleService();
            $validated = $request->validated();
            $event = $this->googleEvent->findEvent($id);
            $data = [
                'service' => $service,
                'validated' => $validated,
                'event' => $event,
            ];
            $this->googleEvent->updateEvent($data);
            return redirect()->route('dashboard')->with('success', 'Event updated successfully.');
        } catch (Exception $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . "->" . $e->getMessage());
            return redirect()->back()->with('error', 'Try again.');
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete($id): RedirectResponse
    {
        try {
            $service = $this->getGoogleService();
            $event = $this->googleEvent->findEvent($id);
            $data = [
                'service' => $service,
                'event' => $event,
            ];
            $this->googleEvent->deleteEvent($data);
            return redirect()->back()->with('success', 'Event deleted successfully.');
        } catch (Exception $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . "->" . $e->getMessage());
            return redirect()->back()->with('error', 'Try again.');
        }
    }
}
