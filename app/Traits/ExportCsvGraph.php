<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use App\Traits\CsvBase;

trait ExportCsvGraph {
    use CsvBase;

    public function toCSVGraph($returnFormat = 'ZIP') {
        // CSV column headers
        $nodeData[] = "id,label,type,class";
        $edgeData[] = "source,target,type";
        $logData[]  = "Status,Event ID,Message";

        $includeInExport = [];
        foreach ($this->events as $fingerprint => $event) {
            $event_id = 'Event_' . implode('_', $event['event_id']);

            // Exclude the event if any of the objects are involved in just a single event
            // AND that event has only 1 person
            if (! empty($event['objects'])) {
                foreach($event['objects'] as $key => $item) {
                    if (($this->event_objects[$key] == 1) &&
                        isset($this->event_people[$fingerprint]) &&
                        ($this->event_people[$fingerprint] == 1))
                    {
                        $logData[] = self::putcsv(['DEBUG', $event_id, 'Object ' . $key . ' is involved in ' . $this->event_objects[$key] . ' event (' . $fingerprint . ') and that event has only ' . $this->event_people[$fingerprint] . ' person']);
                        continue 2;
                    }
                }
            }

            // Node data for the event itself
            $includeInExport['events'][$event_id] = true;
            $nodeData[] = self::putcsv([
                $event_id,
                ucfirst($event['class']),
                'EVENT',
                ucfirst($event['class'])
            ]);

            // Edge data -- People
            if (! empty($event['people'])) {
                foreach($event['people'] as $person) {
                    $includeInExport['people'][$person['id']] = true;
                    $edgeData[] = self::putcsv([
                        $event_id,
                        'Person_' . $person['id'],
                        ucfirst($person['role'])
                    ]);
                }
            } else {
                $logData[] = self::putcsv(['WARNING', $event_id, 'Event has no people']);
            }
            // Edge data -- Places
            if (! empty($event['places'])) {
                foreach($event['places'] as $place) {
                    $includeInExport['places'][$place['id']] = true;
                    $edgeData[] = self::putcsv([
                        $event_id,
                        'Place_' . $place['id'],
                        ucfirst($place['role'])
                    ]);
                }
            } else {
                $logData[] = self::putcsv(['WARNING', $event_id, 'Event has no places']);
            }
            // Edge data -- Objects
            if (! empty($event['objects'])) {
                foreach($event['objects'] as $key => $item) {
                    $includeInExport['objects'][$key] = true;
                    $edgeData[] = self::putcsv([
                        $event_id,
                        $item['key'],
                        ucfirst($event['class'])
                    ]);
                }
            } else {
                $logData[] = self::putcsv(['WARNING', $event_id, 'Event has no objects']);
            }

        }
        // Node data -- People
        if (! empty($this->people)) {
            foreach ($this->people as $person) {
                if (isset($includeInExport['people'][$person['id']])) {
                    $nodeData[] = self::putcsv([
                        'Person_' . $person['id'],
                        html_entity_decode($person['last'] . ($person['first'] ? ', ' . $person['first'] : null), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
                        'PERSON'
                    ]);
                }
            }
        }
        // Node data -- Places
        if (! empty($this->places)) {
            foreach ($this->places as $place) {
                if (isset($includeInExport['places'][$place['id']])) {
                    $nodeData[] = self::putcsv([
                        'Place_' . $place['id'],
                        html_entity_decode($place['name'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
                        'PLACE'
                    ]);
                }
            }
        }
        // Node data -- Objects
        if (! empty($this->objects)) {
            foreach ($this->objects as $object) {
                if (isset($includeInExport['objects'][$object['key']])) {
                    $name = $object['name'] ?: ($object['description'] ?: 'not described');
                    $nodeData[] = self::putcsv([
                        $object['key'],
                        html_entity_decode(trim($name), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
                        'OBJECT'
                    ]);
                }
            }
        }

        if (count($nodeData) == 1) {
            $nodeData[] = self::putcsv(['Error', 'The event list is empty, or no events met the selected criteria', 'EVENT', '']);
        }
        $nodes = implode(PHP_EOL, $nodeData);
        $edges = implode(PHP_EOL, $edgeData);
        $log   = implode(PHP_EOL, $logData);

        if ($returnFormat == 'STRING') {
            // Return CSV data an array of strings
            return ['nodes' => $nodes, 'edges' => $edges];
        } else {
            // Return CSV files in a ZIP container
            Storage::put('nodes.csv', $nodes);
            Storage::put('edges.csv', $edges);
            Storage::put('log.csv', $log);

            // Create a ZIP archive of all the files
            $zip = new \ZipArchive();
            if ($zip->open(storage_path('app/graph_data.zip'), \ZipArchive::CREATE) == TRUE) {
                $zip->addFile(storage_path('app/nodes.csv'), 'nodes.csv');
                $zip->addFile(storage_path('app/edges.csv'), 'edges.csv');
                $zip->addFile(storage_path('app/log.csv'), 'log.csv');
                $zip->close();
                // Cleanup the temporary files
                Storage::delete('nodes.csv');
                Storage::delete('edges.csv');
                Storage::delete('log.csv');
            }

            return storage_path('app/graph_data.zip');
        }
    }

}
