<?php

namespace App\Exports;

use App\Event;
use App\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class csvGraph
{
    protected $events;
    private $people;
    private $places;
    private $objects;
    private $event_ids = [];
    private $object_ids = [];

    /**
     * @param array|null $events
     */
    public function __construct($events = [])
    {
        if (! empty($events)) {
            $this->events = collect();
            $this->expand($events);
            $this->people  = $this->events->pluck('people')->flatten()->unique('id');
            $this->places  = $this->events->pluck('places')->flatten()->unique('id');
            $this->objects = $this->events->pluck('items')->flatten()->unique('id');
        }
    }

    /**
     * Return the list of events
     *
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * Given an initial set of events, expand that list recursively so that it includes related events based on the
     * objects involved in those events.
     *
     * @param $events
     * @return void
     */
    private function expand($events) {
        $new_objects = [];
        // Examine each event to determine if it has already been processed
        foreach ($events as $event) {
            if (! isset($this->event_ids[$event->id])) {
                // New event. Save it and keep track of the ID, so it's not processed again
                $this->events->push($event);
                $this->event_ids[$event->id] = $event->id;
            }
        }
        // The graph of related data will recursively expand outward using the related Objects,
        // so each object must be tracked as well.
        $objects = $this->events->pluck('items')->flatten()->unique('id');
        foreach ($objects as $object) {
            if (! isset($this->object_ids[$object->id])) {
                // New object. Keep track of it so it's not processed again
                $this->object_ids[$object->id] = $object->id;
                $new_objects[$object->id] = $object;
            }
        }
        // Recursively process the events of all new related objects
        foreach ($new_objects as $object) {
            $this->expand($object->events);
        }
    }

    /**
     * Export both a node and edge CSV file, stored in a ZIP container.
     *
     * The node.csv file will contain all people, places, objects and events.
     * The edge.csv file will construct the relationships between those elements.
     *
     * @return string|array
     */
    public function export($returnFormat = 'ZIP')
    {
        // If the class was created without a default set of events, export all events in the database
        if (empty($this->events)) {
            $events = $this->exportAll();
        } else {
            $events = $this->exportEvents();
        }

        if ($returnFormat == 'ARRAY') {
            // Return CSV data as an array
            return $events;
        } elseif ($returnFormat == 'STRING') {
            // Return CSV data an array of strings
            $arr = [];

            // Save the node data.
            $csvData = "id,label,type,class\n";
            foreach ($events['nodes'] as $datum) {
                $csvData .= self::putcsv($datum) . "\n";
            }
            $arr['nodes'] = $csvData;
            // Save the edge data.
            $csvData = "source,target,type\n";
            foreach ($events['edges'] as $datum) {
                $csvData .= self::putcsv($datum) . "\n";
            }
            $arr['edges'] = $csvData;

            return $arr;
        } else {
            // Return CSV files in a ZIP container

            // Save the node data.
            $csvData = "ID,Label,Type,Class\n";
            foreach ($events['nodes'] as $datum) {
                $csvData .= self::putcsv($datum) . "\n";
            }
            Storage::put('nodes.csv', $csvData);

            // Save the edge data.
            $csvData = "Source,Target,Type\n";
            foreach ($events['edges'] as $datum) {
                $csvData .= self::putcsv($datum) . "\n";
            }
            Storage::put('edges.csv', $csvData);
            // Save the log data
            $csvData = "Status,Event ID,Message\n";
            foreach ($events['log'] as $datum) {
                $csvData .= self::putcsv($datum) . "\n";
            }
            Storage::put('log.csv', $csvData);

            // Create a ZIP archive of all the files
            $zip = new \ZipArchive();
            if ($zip->open(storage_path('app/graph_data.zip'), \ZipArchive::CREATE) == TRUE) {
                $zip->addFile(storage_path('app/nodes.csv'), 'nodes.csv');
                $zip->addFile(storage_path('app/edges.csv'), 'edges.csv');
                $zip->addFile(storage_path('app/log.csv'),   'log.csv');
                $zip->close();
                // Cleanup the temporary files
                Storage::delete('nodes.csv');
                Storage::delete('edges.csv');
                Storage::delete('log.csv');
            }

            return storage_path('app/graph_data.zip');
        }

    }


    /**
     * Return an array containing both the edge and node data for all events.
     *
     * @return array
     */
    private function exportAll()
    {
        $nodeData = [];
        $edgeData = [];
        $log = [];

        // Grab a list of event IDs and the number of people involved
        $result = DB::select('SELECT event_id, count(person_id) as person_count FROM event_person GROUP BY event_id');
        $eventPeopleCount = [];
        foreach ($result as $row) {
            $eventPeopleCount[$row->event_id] = $row->person_count;
        }

        // Grab a list of real object IDs
        $result = DB::select('SELECT CASE WHEN ii.item_id IS NULL THEN ii.identifier ELSE ii.item_id::varchar END AS object_id, COUNT(ei.event_id) AS event_count
FROM event_item ei INNER JOIN item_identifier ii on ii.identifier = ei.item_identifier LEFT JOIN items i on i.id = ii.item_id
GROUP BY object_id
ORDER BY event_count DESC;');

        $eventObjectCount = [];
        foreach ($result as $row) {
            $eventObjectCount[$row->object_id] = $row->event_count;
        }

        // Extract all the event information
        $result = DB::select('SELECT
    e.id, e.document, e.class, e.type, e.year,
    ep.person_id, ep.role AS person_role, ep.description AS person_description, p2.last, p2.first,
    p.place_id, p.role AS place_role, p.description AS place_description, p3.name AS place_name,
    ei.item_identifier, ei.description AS item_description, ii.item_id, i.name AS item_name
FROM events e
    LEFT JOIN event_person ep on e.id = ep.event_id
    LEFT JOIN event_place p on e.id = p.event_id
    LEFT JOIN event_item ei on e.id = ei.event_id
    LEFT JOIN people p2 on p2.id = ep.person_id
    LEFT JOIN places p3 on p3.id = p.place_id
    LEFT JOIN item_identifier ii on ii.identifier = ei.item_identifier
    LEFT JOIN items i on ii.item_id = i.id
ORDER BY e.id;');

        // In the above, some event data will be repeated (event ID, type, documents etc)
        // This is because each row also contains people, place and object data
        $events = [];
        foreach ($result as $row) {
            $events[$row->id]['id'] = $row->id;
            $events[$row->id]['document'] = $row->document;
            $events[$row->id]['class'] = $row->class;
            $events[$row->id]['type'] = $row->type;
            $events[$row->id]['year'] = $row->year;

            if (! empty($row->person_id)) {
                // Q. Can a single person have 2 roles in an event?
                $events[$row->id]['people'][$row->person_id]['id'] = $row->person_id;
                $events[$row->id]['people'][$row->person_id]['role'] = $row->person_role;
                $events[$row->id]['people'][$row->person_id]['last'] = trim($row->last);
                $events[$row->id]['people'][$row->person_id]['first'] = trim($row->first);
                $events[$row->id]['people'][$row->person_id]['description'] = trim(preg_replace("/[\r\n\s]+/m", ' ', $row->person_description));
            }

            if (! empty($row->place_id)) {
                // Q. Can a single place have 2 roles in an event?
                $events[$row->id]['places'][$row->place_id]['id'] = $row->place_id;
                $events[$row->id]['places'][$row->place_id]['role'] = $row->place_role;
                $events[$row->id]['places'][$row->place_id]['name'] = trim(preg_replace("/[\r\n\s]+/m", ' ', $row->place_name));
                $events[$row->id]['places'][$row->place_id]['description'] = trim(preg_replace("/[\r\n\s]+/m", ' ', $row->place_description));
            }

            if ((! empty($row->item_identifier)) OR (! empty($row->item_id))) {
                // Use the item_id as the Object ID if one is given, otherwise use the identifier
                $object_id = $row->item_id ?: $row->item_identifier;

                $events[$row->id]['objects'][$object_id]['id'] = $object_id;
                $events[$row->id]['objects'][$object_id]['item_identifier'] = $row->item_identifier;
                $events[$row->id]['objects'][$object_id]['item_id'] = $row->item_id;
                $events[$row->id]['objects'][$object_id]['name'] = trim(preg_replace("/[\r\n\s]+/m", ' ', $row->item_name));
                $events[$row->id]['objects'][$object_id]['description'] = trim(preg_replace("/[\r\n\s]+/m", ' ', $row->item_description));
            }
        }

        // Process all the events, building up the arrays that will form the CSV export
        $people  = [];
        $places  = [];
        $objects = [];
        $event_count = 0;
        foreach ($events as $event) {
            // Exclude the event if any of the objects are involved in just a single event
            // AND that event has only 1 person
            foreach ($event['objects'] as $object_id => $object) {
                if (($eventObjectCount[$object_id] == 1) && isset($eventPeopleCount[$event['id']])  && ($eventPeopleCount[$event['id']] == 1)) {
                    $log[] = ['DEBUG', $event['id'], 'Object ' . $object_id . ' is involved in ' . $eventObjectCount[$object_id] . ' event (' . $event['id'] . ') and that event has only ' . $eventPeopleCount[$event['id']] . ' person'];
                    continue 2;
                }
            }

            // All good!
            $event_count++;
            $nodeData[] = ['Event' . $event['id'], ucfirst($event['class']), 'EVENT', ucfirst($event['class'])];

            // People
            if (! empty($event['people'])) {
                foreach($event['people'] as $person) {
                    $people[$person['id']] = $person;
                    $edgeData[] = ['Event' . $event['id'], 'Person' . $person['id'], ucfirst($person['role'])];
                }
            } else {
                $log[] = ['WARNING', $event['id'], 'Event has no people'];
            }
            // Places
            if (! empty($event['places'])) {
                foreach($event['places'] as $place) {
                    $places[$place['id']] = $place;
                    $edgeData[] = ['Event' . $event['id'], 'Place' . $place['id'], ucfirst($place['role'])];
                }
            } else {
                $log[] = ['WARNING', $event['id'], 'Event has no places'];
            }
            // Objects
            if (! empty($event['objects'])) {
                foreach($event['objects'] as $object) {
                    $objects[$object['id']] = $object;
                    $edgeData[] = ['Event' . $event['id'], 'Object' . $object['id'], ucfirst($event['class'])];
                }
            } else {
                $log[] = ['WARNING', $event['id'], 'Event has no objects'];
            }
        }
        // People
        if (! empty($people)) {
            foreach ($people as $person) {
                $nodeData[] = ['Person' . $person['id'], html_entity_decode($person['last'] . ($person['first'] ? ', ' . $person['first'] : null), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 'PERSON'];
            }
        }
        // Places
        if (! empty($places)) {
            foreach ($places as $place) {
                $nodeData[] = ['Place' . $place['id'], html_entity_decode($place['name'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 'PLACE'];
            }
        }
        // Objects
        if (! empty($objects)) {
            foreach ($objects as $object) {
                $name = $object->name ?: ($object->description ?: 'not described');
                $nodeData[] = ['Object' . $object['id'], html_entity_decode(trim($name), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 'OBJECT'];
            }
        }

        return [
            'nodes' => $nodeData,
            'edges' => $edgeData,
            'log'   => $log,
        ];
    }


    /**
     * Return an array containing both the edge and node data for a specified set of events.
     *
     * It is assumed that the given event data is a collection of Events
     *
     * @return array
     */
    private function exportEvents()
    {
        $nodeData = [];
        $edgeData = [];
        $log = [];

        $event_count = 0;
        foreach ($this->events as $event) {
            // Exclude the event if any of the objects are involved in just a single event
            // AND that event has only 1 person
//            foreach ($event['objects'] as $object_id => $object) {
//                if (($eventObjectCount[$object_id] == 1) && isset($eventPeopleCount[$event['id']])  && ($eventPeopleCount[$event['id']] == 1)) {
//                    $log[] = ['DEBUG', $event['id'], 'Object ' . $object_id . ' is involved in ' . $eventObjectCount[$object_id] . ' event (' . $event['id'] . ') and that event has only ' . $eventPeopleCount[$event['id']] . ' person'];
//                    continue 2;
//                }
//            }

            // All good!
            $event_count++;
            $nodeData[] = ['Event' . $event->id, ucfirst($event['class']), 'EVENT', ucfirst($event->class)];

            // People
            if (! empty($event->people)) {
                foreach($event->people as $person) {
                    $people[$person->id] = $person;
                    $edgeData[] = ['Event' . $event->id, 'Person' . $person->id, ucfirst($person->details->role)];
                }
            } else {
                $log[] = ['WARNING', $event->id, 'Event has no people'];
            }
            // Places
            if (! empty($event->places)) {
                foreach($event->places as $place) {
                    $places[$place->id] = $place;
                    $edgeData[] = ['Event' . $event->id, 'Place' . $place->id, ucfirst($place->details->role)];
                }
            } else {
                $log[] = ['WARNING', $event->id, 'Event has no places'];
            }
            // Objects
            if (! empty($event->items)) {
                foreach($event->items as $object) {
                    $objects[$object->id] = $object;
                    $edgeData[] = ['Event' . $event->id, 'Object' . $object->id, ucfirst($event->class)];
                }
            } else {
                $log[] = ['WARNING', $event->id, 'Event has no objects'];
            }
        }
        // People
        if (! empty($this->people)) {
            foreach ($this->people as $person) {
                $nodeData[] = ['Person' . $person->id, html_entity_decode($person->last . ($person->first ? ', ' . $person->first : null), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 'PERSON'];
            }
        }
        // Places
        if (! empty($this->places)) {
            foreach ($this->places as $place) {
                $nodeData[] = ['Place' . $place->id, html_entity_decode($place->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 'PLACE'];
            }
        }
        // Objects
        if (! empty($this->objects)) {
            foreach ($this->objects as $object) {
                $name = $object->name ?: ($object->description ?: 'not described');
                $nodeData[] = ['Object' . $object->id, html_entity_decode(trim($name), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 'OBJECT'];
            }
        }

        return [
            'nodes' => $nodeData,
            'edges' => $edgeData,
            'log'   => $log,
        ];
    }

    private function putcsv(array $fields) : string {
        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $fields) === false) {
            return false;
        }
        rewind($f);
        $csv_line = stream_get_contents($f);
        return rtrim($csv_line);
    }


}
