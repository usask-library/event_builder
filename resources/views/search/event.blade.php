<div class="card" id="card-{{ $event->id }}">
    <div class="card-header" data-toggle="collapse" data-target="#card-body-{{ $event->id }}">
        <div class="d-flex align-items-center">
            <h5 class="mr-auto">Event {{ $event->id }}: {{ ucfirst($event->class) }}
                @if (! empty($event->type))
                 - {{ ucfirst($event->type) }}
                @endif
            </h5>
            @if ($event->allowEdit)
            <div role="group">
                <button class="btn btn-sm btn-link no-collapse edit-event text-info" data-event-id="{{ $event->id }}"><i class="fas fa-pencil-alt"></i></button>
                <button class="btn btn-sm btn-link no-collapse delete-event text-danger" data-event-id="{{ $event->id }}"><i class="fas fa-trash-alt"></i></button>
            </div>
            @endif
        </div>
    </div>
    <div class="card-body collapse" id="card-body-{{ $event->id }}">
        <div class="row mb-2">
            <div class="col-2">People:</div>
            <div class="col">
                @if ($event->people->isEmpty())
                    <span class="text-muted">none</span>
                @else
                    @foreach($event->people as $person)
                        <div>
                            <b>{{ ucfirst($person->details->role) }}</b>:
                            "{{ $person->details->description }}"
                            <span class="text-muted">({{ $person->last }}, {{ $person->first }} ID {{ $person->id }})</span>
                        </div>
                    @endforeach
                @endisset
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-2">Places:</div>
            <div class="col">
                @if ($event->places->isEmpty())
                    <span class="text-muted">none</span>
                @else
                    @foreach($event->places as $place)
                        <div>
                            <b>{{ ucfirst($place->details->role) }}</b>:
                            "{{ $place->details->description }}"
                            <span class="text-muted">({{ $place->name }} ID {{ $place->id }})</span>
                        </div>
                    @endforeach
                @endisset
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-2">Object(s):</div>
            <div class="col object-list">
                @isset($event->items)
                    @foreach($event->items as $item)
                        <div>
                            {{ $item->identifier }} "{{ $item->details->description }}" <span class="text-muted">({{ $item->name }})</span>
                        </div>
                    @endforeach
                @else
                    <span class="text-muted">none</span>
                @endisset
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-2">Date:</div>
            <div class="col">
                @isset($event->year)
                    {{ $event->year }}
                @else
                    <span class="text-muted">none</span>
                @endisset
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-2">File:</div>
            <div class="col">{{ $event->document }}</div>
        </div>

        <div class="row">
            <div class="col-2">Created:</div>
            <div class="col">{{ $event->created_at }}</div>
        </div>
    </div>
</div>
