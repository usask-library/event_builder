@extends('layouts.app')

@section('title', 'People')

@section('content')
    <div class="container-fluid">
        <div class="d-flex">
            <div class="flex-grow-1">
                <h1>People</h1>
            </div>
            @auth
                <div>
                    <a class="btn btn-text text-success" href="{{ route('person.create') }}" title="Add a new person"><i class="far fa-plus-square"></i></a>
                </div>
            @endauth
        </div>


        @include('status')

        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="collectorsOnly">
            <label class="custom-control-label" for="collectorsOnly">Show only collectors</label>
        </div>

        <table class="table" id="personTable">
            <thead class="thead-dark">
            <tr>
                <th class="nowrap">Person ID</th>
                <th>Name (Last, First)</th>
                <th>Roles</th>
            </tr>
            </thead>
            <tbody>
            @foreach($people as $person)
                <tr>
                    <td><a href="{{ route('person.show', $person->id) }}">{{ $person->id }}</a></td>
                    <td>{!! implode(', ', array_filter([$person->last, $person->first])) !!}</td>
                    <td>{{ $person->roles->pluck('name')->implode(', ')  }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer')
    <script>
        $.fn.dataTable.intlOrder('enm');
        $('#personTable').DataTable({
            "autoWidth": false,
            "pageLength": 100,
            "order": [[1, 'asc']],
            "columns": [
                { width: '6em' },
                null,
                { width: '40%' },
            ],
            "lengthMenu": [[100, 500, 1000, -1], [100, 500, 1000, "All"]]
        });

        $('#collectorsOnly').on('click', function(event) {
            if($(this).prop("checked") == true){
                $('#personTable').DataTable().column(2).data().search('Collector').draw();
            }
            else if($(this).prop("checked") == false){
                $('#personTable').DataTable().column(2).data().search('').draw();
            }
        });
    </script>
@endsection
