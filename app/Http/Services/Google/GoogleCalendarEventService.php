<?php

namespace App\Http\Services\Google;

use App\Models\GoogleEvent;
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
     * @param array $validated
     * @return Google_Service_Calendar_Event
     */
    private static function createGoogleCalendarEvent(array $validated): Google_Service_Calendar_Event
    {
        return new Google_Service_Calendar_Event([
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
    }

    /**
     * @param $data
     * @return void
     */
    public function storeEvent($data): void
    {
        $service = $data['service'];
        $validated = $data['validated'];
        $event = self::createGoogleCalendarEvent($validated);
        $eventGoogle = $service->events->insert('primary', $event);

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
        $googleEvent = self::createGoogleCalendarEvent($validated);

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
