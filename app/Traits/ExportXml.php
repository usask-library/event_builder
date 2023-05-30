<?php

namespace App\Traits;

use Illuminate\View\View;

trait ExportXml {
    /**
     * Export the current set of events in XML format.
     * The XML is generated from defined Blade templates.
     *
     * @return View
     */
    public function toXML() {
        return view('export.xml.index', ['events' => $this->events, 'people' => $this->people, 'places' => $this->places, 'objects' => $this->objects]);
    }

}
