@foreach($objects as $id => $object)
    <Object ID="{{ $id }}" @isset($object['object_id'])SourceID="{{ $object['object_id'] }}"@endisset  @isset($object['item_id'])ItemID="{{ $object['item_id'] }}"@endisset>
        <Description>{{ trim($object['name']) }}</Description>
        @isset($object['mentions'])
            @foreach($object['mentions'] as $mention_id => $identifier)
                <Witness id="{{ $mention_id }}">{{ trim($identifier) }}</Witness>
            @endforeach
        @endisset
    </Object>
@endforeach
