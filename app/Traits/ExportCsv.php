<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use App\Traits\CsvBase;

trait ExportCsv {
    use CsvBase;

    public function toCSV($returnFormat = 'STRING') {
        // CSV column headers
        $csvData = ['"Event ID",Class,Type,"People (ID::Name::Role)","Places (ID::Name::Role)","Objects (ID::Name)"'];

        foreach ($this->events as $fingerprint => $event) {
            $event_id = implode('_', $event['event_id']);
            $class = ucfirst($event['class']);
            $type = ucfirst($event['type']);

            $people = [];
            if (isset($event['people'])) {
                foreach ($event['people'] as $key => $person) {
                    $people[] = implode('::', [
                        $person['id'],
                        html_entity_decode($person['last'] . ($person['first'] ? ', ' . $person['first'] : null), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
                        $person['role']
                    ]);
                }
            }
            $places = [];
            if (isset($event['places'])) {
                foreach ($event['places'] as $key => $place) {
                    $places[] = implode('::', [
                        $place['id'],
                        html_entity_decode($place['name'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
                        $place['role']
                    ]);
                }
            }
            $objects = [];
            if (! empty($event['objects'])) {
                foreach ($event['objects'] as $key => $object) {
                    $name = $object['name'] ?: ($object['description'] ?: 'not described');
                    $objects[] = implode('::', [
                        $object['key'],
                        html_entity_decode(trim($name), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
                    ]);
                }
            }

            $csvData[] = $this->putcsv([
                $event_id,
                $class,
                $type,
                implode('|', $people),
                implode('|', $places),
                implode('|', $objects),
            ]);

        }

        $csvFile = implode(PHP_EOL, $csvData);
        Storage::put('export.csv', $csvFile);
        $path = 'app/export.csv';

        // Return CSV files in a ZIP container if desired
        if ($returnFormat == 'ZIP') {
            // Create a ZIP archive of all the files
            $zip = new \ZipArchive();
            if ($zip->open(storage_path('app/export_data.zip'), \ZipArchive::CREATE) == TRUE) {
                $zip->addFile(storage_path('app/export.csv'), 'export.csv');
                $zip->close();
                // Cleanup the temporary files
                Storage::delete('export.csv');
            }
            $path = 'app/export_data.zip';
        }
        return storage_path($path);
    }

}
