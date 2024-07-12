<?php

namespace App\Http\Services\Google;

use App\Models\GoogleEvent;
use DateTime;
use DateTimeZone;
use Google_Service_Calendar_Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Exception;
class GoogleCalendarEventService
{
    /**
     * @param $id
     * @return Model|Collection|Builder|array|null
     */
    public function findEvent($id): Model|Collection|Builder|array|null
    {
        return GoogleEvent::query()->findOrFail($id);
    }

    /**
     * @param array $validated
     * @return Google_Service_Calendar_Event
     * @throws Exception
     */
    private function createGoogleCalendarEvent(array $validated): Google_Service_Calendar_Event
    {
        $timezone = $validated['timezone'] ?? config('app.timezone');
        $startDateTime = new DateTime($validated['start'], new DateTimeZone($timezone));
        $endDateTime = new DateTime($validated['end'], new DateTimeZone($timezone));

        return new Google_Service_Calendar_Event([
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => [
                'dateTime' => $startDateTime->format('c'),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $endDateTime->format('c'),
                'timeZone' => $timezone,
            ],
        ]);
    }

    /**
     * @param $data
     * @return void
     * @throws Exception
     */
    public function storeEvent($data): void
    {
        $service = $data['service'];
        $validated = $data['validated'];
        $event = $this->createGoogleCalendarEvent($validated);
        $eventGoogle = $service->events->insert('primary', $event);

        GoogleEvent::query()->create([
            'user_id' => auth()->id(),
            'event_id' => $eventGoogle->id,
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'timezone_code' => $validated['timezone'],
        ]);
    }

    /**
     * @param $data
     * @return void
     * @throws Exception
     */
    public function updateEvent($data): void
    {
        $service = $data['service'];
        $validated = $data['validated'];
        $event = $data['event'];
        $googleEvent = $this->createGoogleCalendarEvent($validated);
        $service->events->update('primary', $event->{'event_id'}, $googleEvent);

        $event->update([
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'timezone_code' => $validated['timezone'],
        ]);
    }

    /**
     * @param $data
     * @return void
     */
    public function deleteEvent($data): void
    {
        $service = $data['service'];
        $event = $data['event'];
        $service->events->delete('primary', $event->{'event_id'});
        $event->delete();
    }
}
