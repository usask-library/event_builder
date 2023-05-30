<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>

<Data>
    @isset($events)
        <Events>
            @include('export.xml.events')
        </Events>
    @endisset
    @isset($people)
        <People>
            @include('export.xml.people')
        </People>
    @endisset
    @isset($places)
        <Places>
            @include('export.xml.places')
        </Places>
    @endisset
    @isset($objects)
        <Objects>
            @include('export.xml.objects')
        </Objects>
    @endisset
</Data>
