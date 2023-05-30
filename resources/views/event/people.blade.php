@if (empty($event->people) || (count($event->people) < 1))
    <span class="text-muted">none</span>
@else
    @foreach($event->people as $person)
        <div>
            <i>{{ ucfirst($person->details->role) }}</i>:
            "{!! $person->details->description !!}"
            (<a href="{{ route('person.show', $person->id) }}">{!! implode(', ', array_filter([$person->last, $person->first])) !!}</a>
            <span class="text-muted">ID {{ $person->id }}</span>)
        </div>
    @endforeach
@endisset
