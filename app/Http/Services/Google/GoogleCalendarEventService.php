<?php

namespace App\Http\Services\Google;

use App\Models\GoogleEvent;
use DateTime;
use DateTimeZone;
use Google_Service_Calendar_Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
     * @param $data
     * @return void
     */
    public function storeEvent($data): void
    {
        $service = $data['service'];
        $validated = $data['validated'];
        $timezone = $validated['timezone'] ?? config('app.timezone');
        $startDateTime = new DateTime($validated['start'], new DateTimeZone($timezone));
        $endDateTime = new DateTime($validated['end'], new DateTimeZone($timezone));

        $event = new Google_Service_Calendar_Event([
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
    }

    /**
     * @param $data
     * @return void
     */
    public function updateEvent($data): void
    {
        $service = $data['service'];
        $validated = $data['validated'];
        $event = $data['event'];

        $googleEvent = new Google_Service_Calendar_Event([
            'summary' => $validated['summary'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'start' => [
                'dateTime' => date('c', strtotime($validated['start'])),
                'timeZone' => 'Asia/Yerevan',
            ],
            'end' => [
                'dateTime' => date('c', strtotime($validated['end'])),
                'timeZone' => 'Asia/Yerevan',
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
