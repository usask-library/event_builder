<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarEventsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Events
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarEventsDropdown">
        <a class="dropdown-item" href="{{ route('event.index') }}">All Events</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('event.index', 'acquisition') }}">Acquisitions</a>
        <a class="dropdown-item" href="{{ route('event.index', 'production') }}">Productions</a>
        <a class="dropdown-item" href="{{ route('event.index', 'manipulation') }}">Manipulations</a>
        <a class="dropdown-item" href="{{ route('event.index', 'observation') }}">Observations</a>
    </div>
</li>
<li class="nav-item"><a class="nav-link" href="{{ route('person.index') }}">People</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('place.index') }}">Places</a></li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarObjectDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Objects
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarObjectDropdown">
        <a class="dropdown-item" href="{{ route('item.index') }}">Document Identifiers</a>
        <a class="dropdown-item" href="{{ route('object.index') }}">Objects</a>
    </div>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarExportDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Export
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarExportDropdown">
        <a class="dropdown-item" href="{{ route('event.export', ['format' => 'csv']) }}"><i class="fas fa-file-csv"></i> CSV</a>
        <a class="dropdown-item" href="{{ route('event.export', ['format' => 'csv', 'csv.format' => 'graph']) }}"><i class="fas fa-file-csv"></i> CSV (Graph Data)</a>
        <a class="dropdown-item" href="{{ route('event.export', 'xml') }}" target="export"><i class="fas fa-file-code"></i> XML</a>
        <a class="dropdown-item" href="{{ route('event.export', 'graphviz') }}" target="export"><i class="fas fa-file-alt"></i> Graphviz</a>
    </div>
</li>

