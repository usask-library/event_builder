<?php

namespace App\Http\Controllers\API;

use App\Event;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\EventSearchRequest;
use App\Http\Requests\API\EventStoreBulkAcquisitionRequest;
use App\Http\Requests\API\EventStoreRequest;
use App\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'status' => 'SUCCESS',
            'events' => Event::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EventStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EventStoreRequest $request)
    {
        // Grab the validated form submission
        $validated = $request->validated();

        // Create the event
        $event = Event::create($validated);

        // Create any missing Items, then attach each object to the Event
        foreach ($validated['objects'] as $object) {
            $item = Item::firstOrCreate(['identifier' => $object['id']]);
            $event->items()->attach($object['id'], ['description' => ($object['description'] ?: null)]);
        }

        // Determine the role of the people involved and attach them to the event
        switch ($validated['class']) {
            case 'acquisition':
                if (isset($validated['person1']['id'])) {
                    $event->people()->attach($validated['person1']['id'], ['role' => 'source', 'description' => ($validated['person1']['description'] ?: null)]);
                }
                $event->people()->attach($validated['person2']['id'], ['role' => 'recipient', 'description' => ($validated['person2']['description'] ?: null)]);
                break;
            case 'production':
                if (isset($validated['person1']['id'])) {
                    $event->people()->attach($validated['person1']['id'], ['role' => 'producer', 'description' => ($validated['person1']['description'] ?: null)]);
                }
                break;
            case 'manipulation':
                if (isset($validated['person1']['id'])) {
                    $event->people()->attach($validated['person1']['id'], ['role' => 'source', 'description' => ($validated['person1']['description'] ?: null)]);
                }
                if (isset($validated['person2']['id'])) {
                    $event->people()->attach($validated['person2']['id'], ['role' => 'recipient', 'description' => ($validated['person2']['description'] ?: null)]);
                }
                if (isset($validated['person3']['id'])) {
                    $event->people()->attach($validated['person3']['id'], ['role' => 'agent', 'description' => ($validated['person3']['description'] ?: null)]);
                }
                break;
            case 'observation':
                $event->people()->attach($validated['person1']['id'], ['role' => 'observer', 'description' => ($validated['person1']['description'] ?: null)]);
                break;
        }
        // Determine the role of the places involved and attach them to the event
        switch ($validated['class']) {
            case 'acquisition':
            case 'production':
            case 'observation':
                if (isset($validated['origin']['id'])) {
                    $event->places()->attach($validated['origin']['id'], ['role' => 'place', 'description' => ($validated['origin']['description'] ?: null)]);
                }
                break;
            case 'manipulation':
                if (isset($validated['origin']['id'])) {
                    $event->places()->attach($validated['origin']['id'], ['role' => 'origin', 'description' => ($validated['origin']['description'] ?: null)]);
                }
                if (isset($validated['destination']['id'])) {
                    $event->places()->attach($validated['destination']['id'], ['role' => 'destination', 'description' => ($validated['destination']['description'] ?: null)]);
                }
                break;
        }

        // Return the completed event
        $event = Event::find($event->id);
        return response()->json([
            'status' => 'SUCCESS',
            'request' => $event,
        ]);
    }


    /**
     * Store several newly created resources in storage.
     *
     * @param EventStoreBulkAcquisitionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBulkAcquitisions(EventStoreBulkAcquisitionRequest $request)
    {
        // Grab the validated form submission
        $validated = $request->validated();

        foreach ($validated['objects'] as $object) {
            $event = Event::create([
                'class' => 'acquisition',
                'type' => $validated['type'],
                'year' => null,
                'document' => $validated['document']
            ]);
            // Create the item if it doesn't exist already, then attach it to the Event
            $item = Item::firstOrCreate(['identifier' => $object['id']]);
            $event->items()->attach($object['id'], ['description' => ($object['description'] ?: null)]);
            $event->people()->attach($validated['person2']['id'], ['role' => 'recipient', 'description' => ($validated['person2']['description'] ?: null)]);

            // Setup the response data
            $events[] = Event::find($event->id);
        }

        return response()->json([
            'status' => 'SUCCESS',
            'request' => $events,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Event $event)
    {
        return response()->json([
            'status' => 'SUCCESS',
            'events' => $event,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EventStoreRequest $request, Event $event)
    {
        // Grab the validated form submission
        $validated = $request->validated();

        // Create any missing Items; tracks all items for later updates
        foreach ($validated['objects'] as $object) {
            $item = Item::firstOrCreate(['identifier' => $object['id']]);
            $items[$object['id']] = ['description' => ($object['description'] ?: null)];
        }
        // Determine the role of the people involved and keep track of them for later update
        $people = [];
        switch ($validated['class']) {
            case 'acquisition':
                if (isset($validated['person1']['id'])) {
                    $people[$validated['person1']['id']][] = ['role' => 'source', 'description' => ($validated['person1']['description'] ?: null)];
                }
                $people[$validated['person2']['id']][] = ['role' => 'recipient', 'description' => ($validated['person2']['description'] ?: null)];
                break;
            case 'production':
                if (isset($validated['person1']['id'])) {
                    $people[$validated['person1']['id']][] = ['role' => 'producer', 'description' => ($validated['person1']['description'] ?: null)];
                }
                break;
            case 'manipulation':
                // People are optional
                if (isset($validated['person1']['id'])) {
                    $people[$validated['person1']['id']][] = ['role' => 'source', 'description' => ($validated['person1']['description'] ?: null)];
                }
                if (isset($validated['person2']['id'])) {
                    $people[$validated['person2']['id']][] = ['role' => 'recipient', 'description' => ($validated['person2']['description'] ?: null)];
                }
                if (isset($validated['person3']['id'])) {
                    $people[$validated['person3']['id']][] = ['role' => 'agent', 'description' => ($validated['person3']['description'] ?: null)];
                }
                break;
            case 'observation':
                $people[$validated['person1']['id']][] = ['role' => 'observer', 'description' => ($validated['person1']['description'] ?: null)];
                break;
        }
        // Determine the role of the places involved and keep track of them for later update
        $places = [];
        switch ($validated['class']) {
            case 'acquisition':
            case 'production':
            case 'observation':
                if (isset($validated['origin']['id'])) {
                    $places[$validated['origin']['id']] = ['role' => 'place', 'description' => ($validated['origin']['description'] ?: null)];
                }
                break;
            case 'manipulation':
                if (isset($validated['origin']['id'])) {
                    $places[$validated['origin']['id']] = ['role' => 'origin', 'description' => ($validated['origin']['description'] ?: null)];
                }
                if (isset($validated['destination']['id'])) {
                    $places[$validated['destination']['id']] = ['role' => 'destination', 'description' => ($validated['destination']['description'] ?: null)];
                }
                break;
        }

        $event->items()->sync($items);
        if (count($people) > 0) {
            $event->people()->detach();
            foreach ($people as $id => $roles) {
                foreach ($roles as $role) {
                    $event->people()->attach($id, $role);
                }
            }
        }
        $event->places()->sync($places);

        $event->class = $validated['class'];
        $event->type = $validated['type'];
        $event->year = $validated['year'] ?? null;
        $event->document = $validated['document'];

        if ($event->isDirty()) {
            $event->save();
        }

        // Return the completed event
        $event = Event::find($event->id);
        return response()->json([
            'status' => 'SUCCESS',
            'request' => $event,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Event $event
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Event $event)
    {
        $event->items()->detach();
        $event->people()->detach();
        $event->places()->detach();
        $event->delete();

        return response()->json([
            'status' => 'SUCCESS',
            'results' => 'Event deleted',
        ]);
    }

    /**
     * Display a listing of matching Events.
     *
     * @param Request $request
     * @param bool $simpleMatching Determines if matching is done only on specified objects (default) or in conjunction with people and places
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function search(EventSearchRequest $request, $simpleMatching = true)
    {
        $personEvents = collect();
        $placeEvents  = collect();
        $objectEvents = collect();

        $matchingEvents = collect();

        // Grab the validated form submission
        $validated = $request->validated();

        // Events involving any of the specified objects
        if (! empty($validated['objects'])) {
            $objectEvents = Event::whereHas('items', function ($query) use ($validated) {
                $ids = Arr::pluck($validated['objects'], 'id');
                $query->whereIn('identifier', $ids);
            })->get();
        }

        if ($simpleMatching) {
            $matchingEvents = $objectEvents->unique();
        } else {
            // Events involving any of the specified people
            if (! empty($validated['people'])) {
                $personEvents = Event::whereHas('people', function ($query) use ($validated) {
                    $ids = Arr::pluck($validated['people'], 'id');
                    $query->whereIn('person_id', $ids);
                })->get();
            }

            // Events involving any of the specified places
            if (! empty($validated['places'])) {
                $placeEvents = Event::whereHas('places', function ($query) use ($validated) {
                    $ids = Arr::pluck($validated['places'], 'id');
                    $query->whereIn('place_id', $ids);
                })->get();
            }

            // Given a Venn diagram of People, Places and Objects, the most desirable search result is
            // the intersection between all three (i.e. the very center portion of the Venn diagram)
            $results = $personEvents->intersect($placeEvents)->intersect($objectEvents);

            // A less precise search result (which still may be useful) are the sections of the Venn diagram where only
            // two of the circles intersect. i.e. the union of these three sets:
            //   - People and Places
            //   - People and Objects
            //   - Objects and Places
            $a = $personEvents->intersect($placeEvents);
            $b = $personEvents->intersect($objectEvents);
            $c = $objectEvents->intersect($placeEvents);

            $matchingEvents = $results->concat($a)->concat($b)->concat($c)->unique();
        }
        $matchingEvents = $matchingEvents->sortBy('id');

        $html = '';
        foreach ($matchingEvents as $event) {
            if (in_array($event->class, ['acquisition', 'production', 'manipulation', 'observation'])) {
                if (auth('sanctum')->user()) {
                    $event->allowEdit = true;
                }
                // Cheat a little bit, and let the backend return HTML formatted events to the frontend
                $html .= view('search.event', compact('event'))->render();
            }
        }
        return response()->json([
            'status' => 'SUCCESS',
            'results' => $matchingEvents,
            'html' => $html,
        ]);
    }
}
