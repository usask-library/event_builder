@foreach($people as $person)
    <Person ID="{{ $person['id'] }}">
        @isset($person['last'])
            <LastName>{{ trim($person['last']) }}</LastName>
        @endisset
        @isset($person['first'])
            <FirstName>{{ trim($person['first']) }}</FirstName>
        @endisset
        @isset($person['roles'])
            @foreach($person['roles'] as $role)
                <Role>{{ $role }}</Role>
            @endforeach
        @endisset
    </Person>
@endforeach
