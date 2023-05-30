@extends('layouts.app')

@section('title')
    {{ $object->name }}
@endsection

@section('graphData')
    nodes = `{!! $nodes !!}`
    links = `{!! $links !!}`
    graphData = {
        "nodes": d3.csvParse(nodes),
        "links": d3.csvParse(links)
    }
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex">
            <div class="flex-grow-1">
                <h1>{{ $object->name }}</h1>
            </div>
            @auth
                <div>
                    <a class="btn btn-text text-info" href="{{ route('object.edit', $object->id) }}" title="Edit this object"><i class="far fa-edit"></i></a>
                </div>
                <div>
                    <form action="{{ route('object.destroy', $object->id) }}" method="POST">
                        @method('DELETE')
                        @csrf
                        <button class="btn btn-text text-danger" title="Delete this object"><i class="far fa-trash-alt"></i></button>
                    </form>
                </div>
            @endauth
        </div>

        @include('status')

        <div class="my-4">
            <div class="row">
                <div class="col-3 col-md-2 font-weight-bold">ID:</div>
                <div class="col">{{ $object->id }}</div>
            </div>
            <div class="row">
                <div class="col-3 col-md-2 font-weight-bold">Witnesses:</div>
                <div class="col">
                    @if ($object->items->isEmpty())
                        <i>None</i>
                    @else
                        @foreach($object->items as $item)
                            <a href="{{ route('item.show', $item->id) }}">{{ $item->identifier }}</a>@if (! $loop->last),@endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-auto mr-auto"><h3>Events</h3></div>
            <div class="col-auto">
                <button type="button" class="btn btn-lg btn-link" data-toggle="modal" data-target="#graphModal" title="View network graph">
                    <i class="fas fa-project-diagram"></i>
                </button>
                <a href="{{ route('object.export', ['object' => $object->id, 'format' => 'csv', 'csv.format' => 'graph']) }}" type="button" class="btn btn-lg btn-link" title="Export CSV Graph data">
                    <i class="fas fa-file-csv"></i>
                </a>
                <a href="{{ route('object.export', ['object' => $object->id, 'format' => 'xml']) }}" target="export" type="button" class="btn btn-lg btn-link" title="Export XML data">
                    <i class="fas fa-file-code"></i>
                </a>
                <a href="{{ route('object.export', ['object' => $object->id, 'format' => 'graphviz']) }}" target="export" type="button" class="btn btn-lg btn-link" title="Export Graphviz data">
                    <i class="fas fa-file-alt"></i>
                </a>
            </div>
        </div>

        <nav>
            <div class="nav nav-pills" id="nav-tab" role="tablist">
                <a class="nav-link active" id="nav-all-tab" data-toggle="tab" href="#nav-all" role="tab" aria-controls="nav-all" aria-selected="true">All</a>
                <a class="nav-link" id="nav-acquisition-tab" data-toggle="tab" href="#nav-acquisition" role="tab" aria-controls="nav-home" aria-selected="true">Acquisition</a>
                <a class="nav-link" id="nav-production-tab" data-toggle="tab" href="#nav-production" role="tab" aria-controls="nav-profile" aria-selected="false">Production</a>
                <a class="nav-link" id="nav-manipulation-tab" data-toggle="tab" href="#nav-manipulation" role="tab" aria-controls="nav-contact" aria-selected="false">Manipulation</a>
                <a class="nav-link" id="nav-observation-tab" data-toggle="tab" href="#nav-observation" role="tab" aria-controls="nav-contact" aria-selected="false">Observation</a>
            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-all" role="tabpanel" aria-labelledby="nav-all-tab">
                @include('event.list', ['events' => $object->events, 'showClass' => true])
            </div>
            <div class="tab-pane fade" id="nav-acquisition" role="tabpanel" aria-labelledby="nav-acquisition-tab">
                @include('event.list', ['events' => $object->acquisitions])
            </div>
            <div class="tab-pane fade" id="nav-production" role="tabpanel" aria-labelledby="nav-production-tab">
                @include('event.list', ['events' => $object->productions])
            </div>
            <div class="tab-pane fade" id="nav-manipulation" role="tabpanel" aria-labelledby="nav-manipulation-tab">
                @include('event.list', ['events' => $object->manipulations])
            </div>
            <div class="tab-pane fade" id="nav-observation" role="tabpanel" aria-labelledby="nav-observation-tab">
                @include('event.list', ['events' => $object->observations])
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        $(document).ready(function() {
            $("body").tooltip({ selector: '[data-toggle=tooltip]' });
        });    </script>
@endsection
