<div class="table-responsive my-4">
    @if ($events->isEmpty())
        <div class="alert alert-warning">No matching events were found.</div>
    @else
        @if( method_exists($events,'links') )
            {{  $events->links() }}
        @endif
        <table class="table eventTable">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                @isset($showClass)
                    <th>Event</th>
                @endisset
                <th>Type</th>
                <th>People</th>
                <th>Places</th>
                <th>Objects</th>
                <th>Date</th>
                <th>File</th>
                @auth
                    <th>Delete</th>
                @endauth
            </tr>
            </thead>
            <tbody>
            @foreach($events as $event)
                <tr>
                    <td>{{ $event->id }}</td>
                    @isset($showClass)
                        <td>{{ ucfirst($event->class) }}</td>
                    @endisset
                    <td>
                        @empty($event->type)
                            -
                        @else
                            {{ ucfirst($event->type) }}
                        @endempty
                    </td>
                    <td>@include('event.people')</td>
                    <td>@include('event.places')</td>
                    <td>
                        @isset($event->items)
                            <ul>
                                @foreach($event->items as $item)
                                    <li>"{{ $item->details->description }}"
                                        <a href="{{ route('item.show', $item->id) }}">{{ $item->identifier }}</a> <span class="text-muted">({!! $item->name !!})</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">none</span>
                        @endisset
                    </td>
                    <td>{{ $event->year }}</td>
                    <td class="text-nowrap">
                        @isset($event->document)
                        <a href="{{route('home')}}?xmlFile={{ $event->document }}&id={{ $event->items[0]->identifier }}" target="builder">{{ $event->document }} <i class="fas fa-external-link-alt"></i></a>
                        @endisset
                    </td>
                    @auth
                        <td>
                            <form action="{{ route('event.destroy', $event->id) }}" method="POST">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-link"><i class="far fa-trash-alt text-danger"></i></button>
                            </form>
                        </td>
                    @endauth
                </tr>
            @endforeach
            </tbody>
        </table>
        @if( method_exists($events,'links') )
            {{  $events->links() }}
        @endif
    @endif
</div>
