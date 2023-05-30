@extends('layouts.app')

@section('title')
    Objects
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
        @foreach($collection as $key => $items)

            <div class="row">
                <div class="col-auto mr-auto">
                    <h1>Object {{ str_replace('|', ' - ', $key) }}</h1>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-lg btn-link" data-toggle="modal" data-target="#graphModal" title="View network graph">
                        <i class="fas fa-project-diagram"></i>
                    </button>
                    <a href="{{ route('object.export', ['id' => explode('|',$key)[0], 'format' => 'csv', 'csv.format' => 'graph']) }}" type="button" class="btn btn-lg btn-link" title="Export CSV Graph data">
                        <i class="fas fa-file-csv"></i>
                    </a>
                    <a href="{{ route('object.export', ['id' => explode('|',$key)[0], 'format' => 'xml']) }}" target="export" type="button" class="btn btn-lg btn-link" title="Export XML data">
                        <i class="fas fa-file-code"></i>
                    </a>
                    <a href="{{ route('object.export', ['id' => explode('|',$key)[0], 'format' => 'graphviz']) }}" target="export" type="button" class="btn btn-lg btn-link" title="Export Graphviz data">
                        <i class="fas fa-file-alt"></i>
                    </a>
                </div>
            </div>


        @foreach($items as $item)
                <div class="mt-5">
                <h4>{{ $item->identifier }}</h4>

                @if ($item->events->isEmpty())
                    <div class="alert alert-warning">{{ $item->identifier }} was not included in any events.</div>
                @else
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
                            @include('event.list', ['events' => $item->events, 'showClass' => true])
                        </div>
                        <div class="tab-pane fade" id="nav-acquisition" role="tabpanel" aria-labelledby="nav-acquisition-tab">
                            @include('event.list', ['events' => $item->acquisitions])
                        </div>
                        <div class="tab-pane fade" id="nav-production" role="tabpanel" aria-labelledby="nav-production-tab">
                            @include('event.list', ['events' => $item->productions])
                        </div>
                        <div class="tab-pane fade" id="nav-manipulation" role="tabpanel" aria-labelledby="nav-manipulation-tab">
                            @include('event.list', ['events' => $item->manipulations])
                        </div>
                        <div class="tab-pane fade" id="nav-observation" role="tabpanel" aria-labelledby="nav-observation-tab">
                            @include('event.list', ['events' => $item->observations])
                        </div>
                    </div>
                @endif
                </div>
            @endforeach
        @endforeach

    </div>
@endsection

@section('footer')
    <script>
        $(document).ready(function() {
            $("body").tooltip({ selector: '[data-toggle=tooltip]' });
        });    </script>
@endsection
