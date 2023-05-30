@foreach($events as $event)
    <Event ID="{{ implode('_', $event['event_id']) }}" class="{{ ucfirst($event['class']) }}" @isset($event['type'])type="{{ ucfirst($event['type']) }}"@endisset>
        @isset($event['people'])
            @foreach($event['people'] as $ID => $person)
                <Person ID="{{ $person['id'] }}" role="{{ ucfirst($person['role']) }}">{{ trim(implode(', ', array_filter([$person['last'], $person['first']]))) }}</Person>
            @endforeach
        @endisset
        @isset($event['places'])
            @foreach($event['places'] as $ID => $place)
                <Place ID="{{ $place['id'] }}" role="{{ ucfirst($place['role']) }}">{{ trim($place['name']) }}</Place>
            @endforeach
        @endisset
        @foreach($event['objects'] as $ID => $item)
            <Object ID="{{ $ID }}">
                @foreach($item['mentions'] as $mention)
                    <Mention Document="{{ $mention['document'] }}" ID="{{ $mention['id'] }}"  Identifier="{{ $mention['identifier'] }}">{{ $mention['description'] }}</Mention>
                @endforeach
            </Object>
        @endforeach
        @isset($event['year'])
            @foreach($event['year'] as $year)
                <Date>{{ $year }}</Date>
            @endforeach
        @endisset
    </Event>
@endforeach
