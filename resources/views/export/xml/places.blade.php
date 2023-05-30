@foreach($places as $place)
    <Place ID="{{ $place['id'] }}">
        <Name>{{ trim($place['name']) }}</Name>
    </Place>
@endforeach
