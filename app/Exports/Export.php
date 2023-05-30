<?php

namespace App\Exports;

use App\Event;
use App\Traits\ExportXml;
use App\Traits\ExportCsvGraph;
use App\Traits\ExportCsv;
use App\Traits\ExportGraphviz;

class Export
{
    use ExportXml, ExportCsvGraph, ExportCsv, ExportGraphviz {
        ExportCsv::putcsv insteadof ExportCsvGraph;
    }

    private $events;
    private $people;
    private $places;
    private $objects;
    private $types;
    private $annotations;

    private $event_ids  = [];
    private $object_ids = [];
    private $event_people = [];
    private $event_objects = [];


    public function __construct($events = null, bool $expand = true)
    {
        $this->events = collect();
        if (empty($events)) {
            $this->events = Event::all();
        } elseif ($expand) {
            $this->expand($events);
        }

        $this->removeDuplicates();
    }

    private function expand($events) {
        $new_objects = [];
        // Examine each event to determine if it has already been processed
        foreach ($events as $event) {
            if (! isset($this->event_ids[$event->id])) {
                // New event. Save it and keep track of the ID, so it's not processed again
                $this->events->push($event);
                $this->event_ids[$event->id] = $event->id;

                // Examine each object for this event
                foreach ($event->items as $object) {
                    if (! isset($this->object_ids[$object->id])) {
                        // New object. Keep track of it so it's not processed again
                        $this->object_ids[$object->id] = $object->id;
                        $new_objects[$object->id] = $object;
                    }
                }
            }
        }
        // Examine the new objects found so far and include any unseen aliases
        foreach ($new_objects as $new_object) {
            // Get all the other aliases used for the object
            foreach ($object->aliases as $alias) {
                if (! isset($this->object_ids[$alias->id])) {
                    // New alias (object). Keep track of it so it's not processed again
                    $this->object_ids[$alias->id] = $alias->id;
                    $new_objects[$alias->id] = $alias;
                }
            }
        }

        // Recursively process the events of all newly found objects
        foreach ($new_objects as $object) {
            if (count($object->events) > 0) {
                $this->expand($object->events);
            }
        }
    }


    /**
     * Given an initial collection of Events, expand that collection to include related events based on the
     * Objects involved in those events.  If the object is an alias for one in the original Ark database,
     * get all others aliases for that object and include any events created using those.
     *
     * @param $events
     * @return array
     */
    private static function getFingerprint($event) {
        $fingerprint = ucfirst($event->class) . '|' . ucfirst($event->type);

        $people = [];
        foreach ($event->people as $person) {
            $key = 'P' . $person->id . ucfirst(substr($person->details->role, 0, 1));
            $people[$key][] = $person->id;
        }
        ksort($people);
        $fingerprint .= '|' . implode('_', array_keys($people));

        $places = [];
        foreach ($event->places as $place) {
            $key = 'L' . $place->id . ucfirst(substr($place->details->role, 0, 1));
            $places[$key] = $place->id;
        }
        ksort($places);
        $fingerprint .= '|' . implode('_', array_keys($places));

        $objects = [];
        foreach ($event->items as $item) {
            if (isset($item->item_id)) {
                $key = 'O' . $item->item_id;
            } else {
                $key = 'I' . $item->id;
            }
            $objects[$key][] = $item->id;
        }
        ksort($objects);
        $fingerprint .= '|' . implode('_', array_keys($objects));

        return $fingerprint;
    }

    private function removeDuplicates() {
        $events = $this->events;
        $this->events = [];

        foreach ($events as $event) {
            // Unique fingerprint for this event
            $fingerprint = $this::getFingerprint($event);

            // Start building the merged/de-duped event list
            $this->events[$fingerprint]['class'] = $event->class;
            $this->events[$fingerprint]['type'] = $event->type;

            $this->events[$fingerprint]['event_id'][] = $event->id;
            if (isset($event->year)) {
                $this->events[$fingerprint]['year'][] = $event->year;
            }

            foreach ($event->people as $person) {
                if (! isset($this->event_people[$fingerprint])) {
                    $this->event_people[$fingerprint] = 0;
                }
                $this->event_people[$fingerprint]++;

                $this->people[$person->id]['id'] = $person->id;
                $this->people[$person->id]['last'] = $person->last;
                $this->people[$person->id]['first'] = $person->first;
                foreach ($person->roles as $role) {
                    $this->people[$person->id]['roles'][$role->name] = $role->name;
                }
                $this->people[$person->id]['events'][$person->details->event_id]['role'] = $person->details->role;
                $this->people[$person->id]['events'][$person->details->event_id]['description'] = $person->details->description;

                $key = 'P' . $person->id . ucfirst(substr($person->details->role, 0, 1));
                $this->events[$fingerprint]['people'][$key] = [
                    'key' => $key,
                    'id' => $person->id,
                    'first' => $person->first,
                    'last' => $person->last,
                    'role' => ucfirst($person->details->role),
                ];
                $this->events[$fingerprint]['annotations'][$person->details->event_id]['description']['person'][$person->details->person_id] = $person->details->description;
            }

            foreach ($event->places as $place) {
                $this->places[$place->id]['id'] = $place->id;
                $this->places[$place->id]['name'] = $place->name;
                //$this->places[$place->id]['name'] = $place->details->description;
                $this->places[$place->id]['events'][$place->details->event_id]['role'] = $place->details->role;
                $this->places[$place->id]['events'][$place->details->event_id]['description'] = $place->details->description;

                $key = 'L' . $place->id . ucfirst(substr($place->details->role, 0, 1));
                $this->events[$fingerprint]['annotations'][$place->details->event_id]['description']['place'][$place->details->place_id] = $place->details->description;
                $this->events[$fingerprint]['places'][$key] = [
                    'key' => $key,
                    'id' => $place->id,
                    'name' => $place->name,
                    'role' => ucfirst($place->details->role),
                ];
            }

            foreach ($event->items as $item) {
                if (isset($item->item_id)) {
                    $key = 'O' . $item->item_id;
                    $this->objects[$key]['object_id'] = $item->item_id;
                } else {
                    $key = 'I' . $item->id;
                    $this->objects[$key]['item_id'] = $item->id;
                }

                if (! isset($this->event_objects[$key])) {
                    $this->event_objects[$key] = 0;
                }
                $this->event_objects[$key]++;

                $this->objects[$key]['key'] = $key;
                $this->objects[$key]['name'] = $item->name;
                $this->objects[$key]['events'][$item->details->event_id]['identifier'] = $item->details->item_identifier;
                $this->objects[$key]['events'][$item->details->event_id]['description'] = $item->details->description;
                foreach ($item->aliases as $alias) {
                    $this->objects[$key]['mentions'][$alias->id] = $alias->identifier;
                }

                $this->events[$fingerprint]['objects'][$key]['key'] = $key;
                $this->events[$fingerprint]['objects'][$key]['id'] = $item->item_id;
                $this->events[$fingerprint]['objects'][$key]['name'] = $item->name;
                $this->events[$fingerprint]['objects'][$key]['mentions'][] = [
                    'id' => $item->id,
                    'identifier' => $item->details->item_identifier,
                    'document' => $event->document,
                    'description' => $item->details->description,
                ];
                $this->events[$fingerprint]['annotations'][$item->details->event_id]['description']['object'][$item->details->item_identifier] = $item->details->description;
            }
        }

        ksort($this->events);
    }

}
