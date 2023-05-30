@extends('layouts.app')

@section('title', 'Places')

@section('content')
    <div class="container-fluid">
        <div class="d-flex">
            <div class="flex-grow-1">
                <h1>Places</h1>
            </div>
            @auth
                <div>
                    <a class="btn btn-text text-success" href="{{ route('place.create') }}" title="Add a new place"><i class="far fa-plus-square"></i></a>
                </div>
            @endauth
        </div>


        @include('status')


        <table class="table" id="placeTable">
            <thead class="thead-dark">
            <tr>
                <th class="nowrap">Place ID</th>
                <th>Location</th>
            </tr>
            </thead>
            <tbody>
            @foreach($places as $place)
                <tr>
                    <td><a href="{{ route('place.show', $place->id) }}">{{ $place->id }}</a></td>
                    <td>{!! $place->name  !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer')
    <script>
        $('#placeTable').DataTable({
            "autoWidth": false,
            "pageLength": 100,
            "order": [[1, 'asc']],
            "columns": [
                { width: '6em' },
                null
            ],
            "lengthMenu": [[100, 500, 1000, -1], [100, 500, 1000, "All"]]
        });
        $.fn.dataTable.intlOrder('fr', {
            sensitivity: 'base'
        } );
    </script>
@endsection
