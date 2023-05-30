@extends('layouts.app')

@section('title', 'Objects')

@section('content')
    <div class="container-fluid">
        <div class="d-flex">
            <div class="flex-grow-1">
                <h1>Object/Item/Document Identifiers</h1>
            </div>
            @auth
                <div>
                    <a class="btn btn-text text-success" href="{{ route('item.create') }}" title="Add a new item"><i class="far fa-plus-square"></i></a>
                </div>
            @endauth
        </div>

        @include('status')

        <table class="table" id="objectTable">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Object ID</th>
                <th>Identifier</th>
                <th>Name</th>
            </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td><a href="{{ route('item.show', $item->id) }}">{{ $item->id }}</a></td>
                        <td><a href="{{ url('object', $item->item_id) }}">{{ $item->item_id }}</a></td>
                        <td><a href="{{ route('item.show', $item->id) }}">{{ $item->identifier }}</a></td>
                        <td>{!! $item->name !!}</td>
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
                { width: '5.5em' },
                { width: '7em' },
                null
            ],
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]]
        });
    </script>
@endsection
