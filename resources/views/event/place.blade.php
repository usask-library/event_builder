@isset($place)
    {!! $place->name !!}
    <span class="text-muted">(Place {{ $place->id }})</span>
@else
    <span class="text-muted">none</span>
@endisset
