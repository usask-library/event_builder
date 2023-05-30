@extends('layouts.app')

@section('title', 'Event Builder')

@section('navbar')
    <form class="form-inline my-1 my-lg-0">
        {{-- <span class="navbar-text mx-sm-2">File</span>  --}}
        <select class="form-control form-control-sm mr-sm-2 col-2" id="xmlFile" required>
            <option value="" hidden>Select an XML file</option>
            <?php
            foreach (glob('XML/*.xml') as $file) {
                $filename = str_replace('XML/', '', $file);
                echo '<option value="' . $filename . '">' . $filename . '</option>';
            }
            ?>
        </select>
        {{-- <span class="navbar-text  mr-sm-2">Container</span> --}}
        <select class="form-control form-control-sm mr-sm-2 col-2" id="container" required>
            <option value="" hidden>Select an element</option>
            <option value="item">item</option>
            <option value="seg">seg</option>
            <option value="p">p</option>
            <option value="div">div</option>
        </select>
        {{-- <span class="navbar-text  mr-sm-2">Collector</span> --}}
        <select  id="collector" class="form-control form-control-sm mr-sm-2 selectpicker" title="Select a collector" data-live-search="true" required>
            @foreach($people as $person)
                <option value="{{ $person->id }}">Per{{ $person->id }} - {!! implode(', ', array_filter([$person->last, $person->first])) !!}</option>
            @endforeach
        </select>
        @auth
        <button id="add-acquisitions" type="button" class="btn btn-primary btn-sm" disabled>Add Acquisitions</button>
        @endauth
    </form>
@endsection

@section('content')
    <!-- Position toasts -->
    <div class="position-fixed bottom-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
        <div id="toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <span  id="toast-header">
                <strong class="mr-auto">Hey there!</strong>
                </span>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body" id="toast-body">
                This is a message.
            </div>
        </div>
    </div>

    <div id="content" class="container-fluid">
        <div class="row">
            <div id="xmlSource" class="col-6 border">
                <ol>
                    <li>Select an XML file from the list above, as well as the XML element that is used to contain the events.</li>
                    <li>Each matching event will be surrounded by an orange box.</li>
                    <li>Click on an event to view its relationships.</li>
                </ol>
            </div>
            <div id="editor" class="col-6 border">
                @auth
                <div id="new_event" style="display: none;">
                    <h2 id="event-title">Add New Event</h2>
                    <form id="add-event-form">
                        <div class="form-group text-center">
                            <div class="form-check form-check-inline col-auto">
                                <input class="form-check-input" type="radio" name="class" id="class_acquisition" value="acquisition" checked>
                                <label class="form-check-label" for="class_acquisition">Acquisition</label>
                            </div>
                            <div class="form-check form-check-inline col-auto">
                                <input class="form-check-input" type="radio" name="class" id="class_production" value="production">
                                <label class="form-check-label" for="class_production">Production</label>
                            </div>
                            <div class="form-check form-check-inline col-auto">
                                <input class="form-check-input" type="radio" name="class" id="class_manipulation" value="manipulation">
                                <label class="form-check-label" for="class_manipulation">Manipulation</label>
                            </div>
                            <div class="form-check form-check-inline col-auto">
                                <input class="form-check-input" type="radio" name="class" id="class_observation" value="observation">
                                <label class="form-check-label" for="class_observation">Observation</label>
                            </div>
                        </div>

                        <div class="form-group row" id="typeDiv">
                            <label for="type" class="col-4 col-md-3 col-xl-2 col-form-label required">Type</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="type" name="type" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="donation">Donation or gift</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="div-person1">
                            <label for="person1" class="col-4 col-md-3 col-xl-2 col-form-label">Source</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="person1" name="person1">
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="div-person2">
                            <label for="person2" class="col-4 col-md-3 col-xl-2 col-form-label required">Recipient</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="person2" name="person2" required>
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="div-person3" style="display: none;">
                            <label for="person3" class="col-4 col-md-3 col-xl-2 col-form-label hidden">Agent</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="person3" name="person3">
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="objectDiv">
                            <label for="objects" class="col-4 col-md-3 col-xl-2 col-form-label required">Objects(s)</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="objects" name="objects[]" size="5" multiple required>
                                    <option value="" disabled>Choose...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="originDiv">
                            <label for="origin" class="col-4 col-md-3 col-xl-2 col-form-label">Place</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="origin" name="origin">
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="destinationDiv" style="display: none;">
                            <label for="destination" class="col-4 col-md-3 col-xl-2 col-form-label hidden">Destination</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="destination" name="destination">
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="dateDiv">
                            <label for="year" class="col-4 col-md-3 col-xl-2 col-form-label hidden">Date</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="year" name="year">
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-8 col-md-9 col-xl-10 offset-sm-2 offset-4 offset-md-3 offset-xl-2">
                                <button type="submit" class="btn btn-primary" id="addEvent">Add event</button>
                                <button type="submit" class="btn btn-primary" id="editEvent" style="display: none;">Update event</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endauth
                <div id="events">
                    <p>The list of objects, persons and places for the selected event will appear here, allowing you to create and review relationships.</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')

    @auth
    <!-- Add Acquisitions Modal -->
    <div class="modal fade" id="addAcquisitionsModal" tabindex="-1" aria-labelledby="addAcquisitionsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAcquisitionsLabel">Add Acquisitions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add-bulk-event-form">
                        <p>The following is a list of all objects identified in the XML document. Each will be added as <strong>Acquisitions</strong> for the selected collector <span id="addAcquisitionCollectorName"></span>.</p>
                        <div id="addAcquisitionsMessages" style="display: none;"></div>
                        <div class="form-group row" id="typeDiv">
                            <label for="addAcquisitionType" class="col-4 col-md-3 col-xl-2 col-form-label required">Type</label>
                            <div class="col-8 col-md-9 col-xl-10">
                                <select class="form-control" id="addAcquisitionType" name="type" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="donation">Donation or gift</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" id="addAcquisitionsToggle" style="display: none;">
                            <div class="col-10"><p>Uncheck any objects that should not be included at this time.</p></div>
                            <div class="col-2 text-right"><div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="addAcquisitionsToggleAll" checked><label class="custom-control-label" for="addAcquisitionsToggleAll">All</label></div></div>
                        </div>

                        <input type="hidden" name="person2" id="addAcquisitionCollectorId" value="">
                        <div id="addAcquisitionObjectList"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addAcquisitionsSubmit">Add selected</button>
                </div>
            </div>
        </div>
    </div>
    @endauth

<script>
    var apiUrl = '{{ url('api') }}'
    var apiToken = {!! session('api_token') ? "'" . session('api_token') . "'" : 'null' !!}
</script>
<script src="{{ asset('js/editor.js')}}"></script>
@endsection
