@extends('layouts.app')

@section('title', 'Objects')

@section('content')
    <div class="container-fluid">
        <div class="d-flex">
            <div class="flex-grow-1">
                <h1>Objects</h1>
            </div>
            @auth
                <div>
                    <a class="btn btn-text text-success" href="{{ route('object.create') }}" title="Add a new object"><i class="far fa-plus-square"></i></a>
                </div>
            @endauth
        </div>


        @include('status')

        <table class="table" id="objectTable">
            <thead class="thead-dark">
            <tr>
                <th>Object ID</th>
                <th>Name</th>
            </tr>
            </thead>
            <tbody>
                @foreach($objects as $object)
                    <tr>
                        <td><a href="{{ route('object.show', $object->id) }}">{{ $object->id }}</a></td>
                        <td><a href="{{ route('object.show', $object->id) }}">{{ $object->name }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer')
    <script>
        $.fn.dataTable.intlOrder('enm');
        $('#objectTable').DataTable({
            "autoWidth": false,
            "pageLength": 100,
            "columns": [
                { width: '3em' },
                { width: '7em' },
            ],
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]]
        });
    </script>
@endsection
