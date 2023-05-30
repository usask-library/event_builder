@if (empty($event->places) || (count($event->places) < 1))
    <span class="text-muted">none</span>
@else
    @foreach($event->places as $place)
        <div>
            <i>{{ ucfirst($place->details->role) }}</i>:
            "{!! $place->details->description !!}"
            (<a href="{{ route('place.show', $place->id) }}">{!! $place->name !!}</a>
            <span class="text-muted">ID {{ $place->id }}</span>)
        </div>
    @endforeach
@endisset
