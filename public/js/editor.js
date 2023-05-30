'use strict';

$(function() {
    // "Global" variables in the outer-most scope.
    // Other functions will ensure these have the latest data for whatever state the editor is in
    var people = [];
    var places = [];
    var objects = [];
    var dates = [];
    var events = [];

    /**
     * Find any existing events that match the current set of people, places and objects
     *
     * @returns {string}
     */
    function findEvents() {
        $('#events').html('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>');

        let message = '<h3>Potentially Matching Events</h3>';
        if (objects.length < 1) {
            message += '<p class="alert alert-info">The selected element does not contain any objects.</p>';
        } else {
            $.ajax({
                method: 'POST',
                url: apiUrl + '/events/search',
                data: { objects:objects, people:people, places:places },
                async: true,
            }).done(function(data) {
                events = data.results;
                if (data.html.length > 0) {
                    message += '<p>Click to expand.</p>';
                    message += data.html;
                } else {
                    message += '<p class="alert alert-warning">No matching events were found.</p>';
                }
                $('#events').html(message);
            }).fail(function (data) {
                message += '<p class="alert alert-danger">No matching events were found.</p>';
                $('#events').html(message);
            });
        }
        $('#events').html(message);
    }

    /**
     * Return detailed information about the supplied list of people IDs
     *
     * @param {Array.<string>} people
     * @returns {Array.<Object>}
     */
    function fetchPeople(people) {
        let results = {};

        $.ajax({
            method: 'POST',
            url: apiUrl + '/person/search',
            data: { people: people },
            async: false,
        }).done(function(response) {
            results = response.data;
        }).fail(function (response) {
            toast('danger', 'Search complete', response.responseJSON.message);
        });

        return results;
    }

    /**
     * Return detailed information about the supplied list of place IDs
     *
     * @param {Array.<string>} places
     * @returns {Array.<Object>}
     */
    function fetchPlaces(places) {
        let results = {};

        $.ajax({
            method: 'POST',
            url: apiUrl + '/place/search',
            data: { places: places },
            async: false,
        }).done(function(response) {
            results = response.data;
        }).fail(function (response) {
            toast('danger', 'Search complete', response.responseJSON.message);
        });

        return results;
    }

    /**
     * Return detailed information about the supplied list of object/item IDs
     *
     * @param {Array.<string>} objects
     * @returns {Array.<Object>}
     */
    function fetchObjects(objects) {
        let results = {};

        $.ajax({
            method: 'POST',
            url: apiUrl + '/object/search',
            data: { objects: objects },
            async: false,
        }).done(function(response) {
            results = response.data;
        }).fail(function (response) {
            toast('danger', 'Search complete', response.responseJSON.message);
        });

        return results;
    }

    /**
     * Load the selected XML file into the source pane.
     *
     * The change event will set the variable xmlFileLoading to the Ajax promise so that it can be checked
     * by other functions to determine when the XML file has completed loading.
     */
    var xmlFileLoading;
    $('#xmlFile').change(function () {
        // Reset form validate classes and hide the form
        $('.is-invalid').removeClass('is-invalid');
        $('#new_event').hide();

        $('#events').html('');

        xmlFileLoading = $.ajax({
            url: 'XML/' + $('#xmlFile').val(),
            dataType: 'xml',
        }).done(function(data) {
            // Extract only the <text> element from the TEI file
            // let text = $(data).find('text');
            let text = $(data).find('text').first();

            // Populate the XML source pane with the TEI <text> element
            // TEI can contain a <body> element that will cause rendering error; replace it with a <div>
            $('#xmlSource').html(text);
            $('#xmlSource body').replaceWith($('<div class="teiBody"/>').html($('#xmlSource body').html()));
            $('#xmlSource text').replaceWith($('<div class="teiText"/>').html($('#xmlSource text').html()));

            if ($('#container :selected').val()) {
                $('#events').html('<div class="alert alert-info">Select an element from the right-hand pane</div>');
            } else {
                $('#events').html('<div class="alert alert-info">Select a containing element from the menu above.</div>');
            }
            if ($('#container').val() === "seg") {
                $('seg[type=object], seg[type=objectSet], seg[type=objectGroup], seg[type=collection], seg[type=coin], seg[type=medal]').addClass('event');
            } else if ($('#container').val() === "item") {
                $('item').addClass('event');
            } else if ($('#container').val() === "p") {
                $('#xmlSource p').addClass('event');
            } else if ($('#container').val() === "div") {
                $('#xmlSource div').addClass('event');
            } else {
            }
            toast('success', 'Success', 'Successfully loaded ' + $('#xmlFile').val());
        });
    });

    /**
     * Set the classes appropriately on the selected elements of the loaded XML file
     */
    $('#container').change(function () {
        // Reset form validate classes and hide the form
        $('.is-invalid').removeClass('is-invalid');
        $('#new_event').hide();

        $('#events').html('<div class="alert alert-info">Select an element from the right-hand pane</div>');
        $('.event').removeClass('event');
        $('#xmlSource .active').removeClass('active');
        if ($(this).val() === "seg") {
            $('seg[type=object], seg[type=objectSet], seg[type=objectGroup], seg[type=collection], seg[type=coin], seg[type=medal]').addClass('event');
        } else if ($(this).val() === "item") {
            $('item').addClass('event');
        } else if ($(this).val() === "p") {
            $('#xmlSource p').addClass('event');
        } else if ($(this).val() === "div") {
            $('#xmlSource div').addClass('event');
        }
    });

    /**
     * Watch for changes to the Collector menu
     */
    $('#collector').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        if (clickedIndex !== '') {
            $('#add-acquisitions').prop('disabled', false);
        }
    });

    /**
     * Watch for changes to the Event Class radio button and update the form as required
     */
    $('input[type=radio][name=class]').change(function() {
        let types = {};

        // Reset form validate classes
        $('.is-invalid').removeClass('is-invalid');

        $('#typeDiv').show();
        $('#typeDiv label').addClass('required');
        $('#type').attr('required', true);
        $('#div-person1 label').removeClass('required');
        $('#person1').removeAttr('required');
        $('#div-person2').hide();
        $('#div-person2 label').removeClass('required');
        $('#person2').removeAttr('required');
        $('#div-person3').hide();
        $('#div-person3 label').removeClass('required');
        $('#person3').removeAttr('required');
        $('#originDiv label').text('Place');
        $('#destinationDiv').hide();

        if (this.value === 'acquisition') {
            $('#div-person1 label').text('Source');
            $('#div-person2').show();
            $('#div-person2 label').addClass('required');
            $('#person2').attr('required', true);

            types = {"donation": "Donation or Gift", "purchase": "Purchase", "other": "Other"};
        }
        else if (this.value === 'production') {
            $('#div-person1 label').text('Producer');

            types = {'natural': 'Natural', 'artificial': 'Artificial'};
        }
        else if (this.value === 'manipulation') {
            $('#div-person1 label').text('Source');
            $('#div-person2 label').text('Recipient');
            $('#div-person3 label').text('Agent');
            $('#div-person1').show();
            $('#div-person2').show();
            $('#div-person3').show();
            $('#originDiv label').text('Origin');
            $('#destinationDiv').hide();

            types = {'transmission': 'Transmission', 'extraction': 'Extraction', 'other': 'Other'};
        }
        else if (this.value === 'observation') {
            $('#typeDiv').hide();
            $('#typeDiv label').removeClass('required');
            $('#type').removeAttr('required');
            $('#div-person1 label').addClass('required').text('Observer');
            $('#person1').attr('required', true);

            types = {};
        }

        let options = '<option value="" disabled selected>Choose...</option>';
        for (let [key, value] of Object.entries(types)) {
            options += '<option value="' + key + '">' + value + '</option>';
        }
        $('#type').html(options);

        $('button#addEvent').show();
        $('button#editEvent').hide();
    });

    /**
     * Watch for changes to the Event Type drop-down and update the form as required
     */
    $('#type').change(function () {
        let eventClass = $('input[type=radio][name=class]:checked').val();
        let eventType  = this.value;

        if (eventClass === 'manipulation') {
            if ((eventClass === 'manipulation') && (eventType === 'extraction')) {
                $('#destinationDiv').hide();
            } else {
                $('#destinationDiv').show();
            }
        }
    });


    /**
     *  Handle the click event of the selected containing element
     */
    $("#xmlSource").on("click", ".event", function(e) {
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();

        // Reset form validate classes
        $('.is-invalid').removeClass('is-invalid');

        // Mark the newly selected element as active
        $('.active').removeClass('active');
        $(this).addClass('active');

        // Reset the global people, places and objects variables
        // New values will be extracted from the selected containing element below
        people = [];
        places = [];
        objects = [];
        dates  =[];
        events = [];

        // Make sure the new event form is shown
        $('#event-title').html('Add New Event');
        $('button#addEvent').show();
        $('button#editEvent').hide();
        $('#new_event').show();

        // The selected event could be a seg, in which case it may have an ID
        if ($(this)[0].hasAttribute('xml:id') && $(this)[0].hasAttribute('type')) {
            let objectID = $(this).attr('xml:id');
            let objectName = $(this).find('> rs[type=object], > rs[type=objectGroup], > rs[type=objectSet]').text();
            if (objectName === '') {
                objectName = $(this).find('rs[type=object], rs[type=objectGroup], rs[type=objectSet]').map(function(i, element) { return $(element).text() }).get().join(' and ');
            }
            if (objectName === '') {
                if ($(this)[0].hasAttribute('type')) {
                    objectName = $(this).attr('type')
                } else {
                    objectName = 'n/a';
                }
            }
            objects.push({
                id: objectID,
                name: objectName
            });
        }

        // Extract any Objects from the selected container element
        $(this).find('seg[type=object], seg[type=objectSet], seg[type=objectGroup], seg[type=collection], seg[type=coin], seg[type=medal]').each(function () {
            if ($(this)[0].hasAttribute('xml:id')) {
                let objectID = $(this).attr('xml:id');
                let objectName = $(this).children('rs[type=object],rs[type=objectGroup],rs[type=objectSet]').text();
                if (objectName === '') {
                    objectName = $(this).find('rs[type=object],rs[type=objectGroup],rs[type=objectSet]').map(function(i, element) { return $(element).text() }).get().join(' and ');
                }
                if (objectName === '') {
                    if ($(this)[0].hasAttribute('type')) {
                        objectName = $(this).attr('type')
                    } else {
                        objectName = 'n/a';
                    }
                }
                objects.push({
                    id: objectID,
                    name: objectName,
                    active: false
                });
            }
        });

        // Check if a Collector has been selected
        if ($('#collector').val()) {
            // Ensure the selected Collector is a Person that can be used in events
            people.push({
                id: $('#collector').val(),
                description: $('#collector').find(':selected').text().match(/^Per\d+\s+\-\s+(.*)/)[1],
            });
        }

        // Extract any People from the selected container element, removing duplicates
        $(this).find('name[type=person]').each(function () {
            if ($(this)[0].hasAttribute('ref') && Number($(this).attr('ref'))) {
                let person = {
                    id: $(this).attr('ref'),
                    description: $(this).text().trim().replace(/[\s\r\n]+/g, ' '),
                };
                people.push(person);
            }
        });
        people = people.filter((person, index, self) => self.findIndex(t => t.id === person.id && t.description === person.description) === index);

        // Extract any Places from the selected container element, removing duplicates
        $(this).find('name[type=place]').each(function () {
            if ($(this)[0].hasAttribute('ref') && Number($(this).attr('ref'))) {
                let place = {
                    id: $(this).attr('ref'),
                    description: $(this).text().trim().replace(/[\s\r\n]+/g, ' '),
                };
                places.push(place);
            }
        });
        places = places.filter((place, index, self) => self.findIndex(t => t.id === place.id && t.description === place.description) === index);

        // Extract any Dates from the selected container element, removing duplicates
        $(this).find('date').each(function () {
            if ($(this)[0].hasAttribute('when')) {
                dates.push($(this).attr('when'));
            }
            if ($(this)[0].hasAttribute('atLeast')) {
                dates.push($(this).attr('atLeast'));
            }
            if ($(this)[0].hasAttribute('atMost')) {
                dates.push($(this).attr('atMost'));
            }
        });
        dates = [...new Set(dates)];

        // Update source, recipient and agent drop down list
        let options = '<option value="">Choose...</option>';
        if (people.length > 0) {
            const peopleNames = fetchPeople(people);
            people.sort((a,b) => a.id - b.id);
            people.forEach(function (item) {
                let name = 'Per' + item.id + ' - "' + item.description + '"';
                if (peopleNames.hasOwnProperty(item.id)) {
                    name += ' (' + peopleNames[item.id].last + ((peopleNames[item.id].first) ? ', ' + peopleNames[item.id].first : '') + ')';
                }
                options += '<option value="' + item.id + '" data-id="' + item.id + '" data-description="' + item.description + '">' + name + '</option>';
            });
        }
        $('#person1').html(options);
        $('#person2').html(options);
        $('#person3').html(options);

        // Update object list
        options = '<option value="" disabled>Choose...</option>';
        if (objects.length > 0) {
            const objectNames = fetchObjects(objects);
            objects.sort((a,b) => a.id - b.id);
            $.each(objects, function (index, value) {
                let name = value.id + ' - "' + value.name + '"';
                if (objectNames.hasOwnProperty(value.id)) {
                    name += ' (' + objectNames[value.id].name + ')';
                    value.active = true;
                } else {
                    value.active = true;
                    name += ' (n/a)';
                }
                options += '<option value="' + value.id + '" data-id="' + value.id + '" data-description="' + value.name + '"' + (value.active ? '' : ' disabled') + '>' + name + '</option>';
            });
        }
        $('#objects').html(options);

        // Update origin and destination drop down list
        options = '<option value="" disabled selected>Choose...</option>';
        if (places.length > 0) {
            const placeNames = fetchPlaces(places);
            places.sort((a,b) => a.id - b.id);
            places.forEach(function (item) {
                let name = 'Place ' + item.id + ' - "' + item.description + '"';
                if (placeNames.hasOwnProperty(item.id)) {
                    name += ' (' + placeNames[item.id].name + ')';
                }
                options += '<option value="' + item.id + '" data-id="' + item.id + '" data-description="' + item.description + '">' + name + '</option>';
            });
        }
        $('#origin').html(options);
        $('#destination').html(options);

        // Update date list
        options = '<option value="" selected>Choose...</option>';
        $.each(dates, function (index, value) {
            options += '<option value="' + value + '">' + value + '</option>';
        });
        $('#year').html(options);

        // Display any matching events
        findEvents();
    });


    /**
     * Handle the click event of the Add Event button.
     */
    $('#add-event-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);

        let submitButton = e.originalEvent.submitter.id;

        // Reset form validate classes
        $('.is-invalid').removeClass('is-invalid');

        // Gather information from the form
        let formData = {
            document: $('#xmlFile').val(),
            class: $('input[type=radio][name=class]:checked').val(),
            type: $('#type').val(),
        };

        let selectedObjects = [];
        $("#objects option:selected").each(function(index, item){
            selectedObjects.push({
                description: $(item).data('description'),
                id: $(item).data('id'),
            });
        })
        if (! $.isEmptyObject(selectedObjects)) {
            formData.objects = selectedObjects;
        }
        if ($('#person1 :selected').data('id')) {
            formData.person1 = {
                id: $('#person1 option:selected').data('id'),
                description: $('#person1 option:selected').data('description'),
            };
        }
        if ($('#person2 :selected').data('id')) {
            formData.person2 = {
                id: $('#person2 option:selected').data('id'),
                description: $('#person2 option:selected').data('description'),
            };
        }
        if ($('#person3 :selected').data('id')) {
            formData.person3 = {
                id: $('#person3 option:selected').data('id'),
                description: $('#person3 option:selected').data('description'),
            };
        }
        if ($('#origin :selected').data('id')) {
            formData.origin = {
                id: $('#origin option:selected').data('id'),
                description: $('#origin option:selected').data('description'),
            };
        }
        if ($('#destination :selected').data('id')) {
            formData.destination = {
                id: $('#destination option:selected').data('id'),
                description: $('#destination option:selected').data('description'),
            };
        }
        if ($('#year :selected').val()) {
            formData.year = $('#year option:selected').val();
        }

        let apiEndpoint = apiUrl + '/events';
        let apiMethod = 'POST';
        let eventAction = 'created';
        if (submitButton === 'editEvent') {
            apiEndpoint += '/' + $('#' + submitButton).data('eventId');
            apiMethod = 'PUT';
            eventAction = 'updated';
        }

        $.ajax({
            method: apiMethod,
            url: apiEndpoint,
            data: formData,
        }).done(function(data) {
            toast('success', 'Event ' + eventAction, 'The event was successfully ' + eventAction);
            $('#add-event-form').trigger("reset");
            $('#event-title').html('Add New Event');
            $('button#addEvent').show();
            $('button#editEvent').hide();
            $('#new_event').show();
            findEvents();
        }).fail(function (data) {
            toast('danger', 'The event could not be ' + eventAction, data.responseJSON.message);
            $.each(data.responseJSON.errors, function (index, value) {
                $('#' + index).addClass('is-invalid');
            });
        });
    });


    /**
     * Handle the click event of the Add Acquisitions button.
     */
    $('#add-acquisitions').on('click', function(e) {
        e.preventDefault();

        // Hide any previous messages
        $('#addAcquisitionsMessages').hide();
        $('#addAcquisitionsToggle').hide();
        $('#addAcquisitionsSubmit').prop('disabled', true);

        // Get the collector ID and name from the dropdown
        const collectorId = $('#collector').val();
        const collectorName = $('#collector').find(':selected').text().match(/^Per\d+\s+\-\s+(.*)/)[1];

        // Extract all objects from the XML document
        let documentObjects = [];
        let objectList = '<p class="alert alert-warning">The XML document does not appear to contain any object markup.</p>';
        $('#xmlSource').find('seg[type=object], seg[type=objectSet], seg[type=objectGroup], seg[type=collection], seg[type=coin], seg[type=medal]').each(function () {
            if ($(this)[0].hasAttribute('xml:id')) {
                let objectID = $(this).attr('xml:id');
                let objectName = $(this).children('rs[type=object],rs[type=objectGroup],rs[type=objectSet]').text();
                if (objectName === '') {
                    objectName = $(this).find('rs[type=object],rs[type=objectGroup],rs[type=objectSet]').map(function(i, element) { return $(element).text() }).get().join(' and ');
                }
                if (objectName === '') {
                    if ($(this)[0].hasAttribute('type')) {
                        objectName = $(this).attr('type')
                    } else {
                        objectName = 'n/a';
                    }
                }
                documentObjects.push({
                    id: objectID,
                    description: objectName.trim().replace(/[\s\r\n]+/g, ' ')
                });
            }
        });
        // Remove exact duplicates
        documentObjects = documentObjects.filter((object, index, self) => self.findIndex(t => t.id === object.id && t.description === object.description) === index);

        if (documentObjects.length > 0) {
            const matchingObjects = fetchObjects(documentObjects);
            documentObjects.sort((a,b) => a.id - b.id);

            // Build up a list of all objects
            objectList = '';
            $.each(documentObjects, function (index, value) {
                let name = value.id + ' - "' + value.description + '"';
                if (matchingObjects.hasOwnProperty(value.id)) {
                    name += ' <span class="text-muted">(' + matchingObjects[value.id].name + ')</span>';
                }
                const checkbox =
                    '<div class="custom-control custom-switch">' +
                    '  <input class="custom-control-input" type="checkbox" name="objects[]" value="' + value.id + '" id="check' + value.id + '" data-id="' + value.id + '" data-description="' + value.description + '" checked>' +
                    '  <label class="custom-control-label" for="check' + value.id + '">' +
                    name +
                    '</label>' +
                    '</div>\n';
                objectList += checkbox;
            });
            $('#addAcquisitionsToggle').show();
            $('#addAcquisitionsSubmit').prop('disabled', false);
        }

        // Present the Add Acquisitions options to the user
        $('#addAcquisitionCollectorId').val(collectorId);
        $('#addAcquisitionCollectorName').html('<strong>' + collectorName + '</strong>');
        $('#addAcquisitionObjectList').html(objectList);
        $('#addAcquisitionsModal').modal('show');
    });

    // Toggle all checkboxes on the Add Acquisitions modal
    $("input#addAcquisitionsToggleAll").on('click', function (e) {
        $('#addAcquisitionObjectList :checkbox').prop('checked', this.checked);
    });


    /**
     * Handle the click event of the Add Selected button (i.e. add all the acquisitions events for the selected collector).
     */
    $('#addAcquisitionsSubmit').on('click', function(e) {
        e.preventDefault();
        const form = $('#add-bulk-event-form');

        // Hide any previous messages
        $('#addAcquisitionsMessages').hide();
        $('#addAcquisitionObjectList input').removeClass('is-valid');

        // Gather information from the form
        let formData = {
            document: $('#xmlFile').val(),
            person2: {
                id: $('#collector').val(),
                description: $('#collector').find(':selected').text().match(/^Per\d+\s+\-\s+(.*)/)[1]
            }
        };

        // Get a list of all the selected objects
        let selectedObjects = [];
        $('#addAcquisitionObjectList input:checked').each(function (i) {
            selectedObjects.push({
                id: $(this).data('id'),
                description: $(this).data('description'),
            });
        });

        if (selectedObjects.length < 1) {
            const message = '<p class="alert alert-danger">No objects were selected.</p>';
            $('#addAcquisitionsMessages').html(message);
            $('#addAcquisitionsMessages').show();
        } else {
            formData.objects = selectedObjects;
            formData.type = $('#addAcquisitionType').val();

            $.ajax({
                method: 'POST',
                url: apiUrl + '/events/bulk_acquisition',
                dataType: 'json',
                data: JSON.stringify(formData),
                contentType: 'application/json; charset=utf-8'
            }).done(function(data) {
                $.each(data.request, function (index, value) {
                    $('#addAcquisitionsMessages').html('<div class="alert alert-success">An Acquisition event was successfully added for all of the objects shown in green below.</div>');
                    $('#addAcquisitionsMessages').show();

                    $.each(value.items, function (o, object) {
                        $('#check' + object.identifier).addClass('is-valid');
                    });
                });
                //findEvents();
            }).fail(function (data) {
                toast('danger', 'The event could not be created.', data.responseJSON.message);
                $.each(data.responseJSON.errors, function (index, value) {
                    $('#' + index).addClass('is-invalid');
                });
            });
        }
    });

    /**
     * Handle the click event of the Delete button
     */
    $('body').on('click', '.delete-event', function(e) {
        e.preventDefault();

        const id = $(this).data('event-id');
        const answer = confirm('Are you sure you want to delete event ' + id + '?');
        if (answer) {
            // Find any existing events that match the current set of people, places and objects
            $.ajax({
                method: 'DELETE',
                url: '/events/api/events/' + id,
            }).done(function(data) {
                $('#card-' + id).remove();
                findEvents();
            }).fail(function (data) {
                //console.log("Failed");
            });
        }
    });

    /**
     * Handle the click event of the Edit button
     */
    $('body').on('click', '.edit-event', function(e) {
        e.preventDefault();

        // Reset the form fields
        $('#add-event-form').trigger('reset');

        // Grab the event ID from the button
        const eventId = $(this).data('event-id');
        $('#event-title').html('Edit Event ' + eventId);

        // The global array 'events' should contain all events displayed in the events pane
        // Locate the details of the event selected for edit
        let event = [];
        Object.entries(events).forEach(entry => {
            const [key, value] = entry;
            if (value.id === eventId) {
                event = value;
            }
        })
        if (event === undefined) {
            alert('An error occurred looking for the details of event ' + eventId);
            return;
        }

        // Set up the form with information from the event

        // The event class and type
        $('input[type=radio][name=class][value="' + event.class + '"]').prop('checked','checked').change();
        if (event.class != 'observation') {
            $('select#type option[value="' + event.type + '"]').prop('selected','selected').change();
        }

        // The people
        if (event.people.length > 0) {
            // Update source and recipient drop down list
            let person1IDs = [];
            let person2IDs = [];
            let person3IDs = [];
            event.people.forEach(function (p) {
                let person = {
                    id: p.id.toString(),
                    description: p.details.description,
                };
                if (p.details.role === 'recipient') {
                    person2IDs.push(p.id);
                } else if (p.details.role === 'agent') {
                    person3IDs.push(p.id);
                } else {
                    person1IDs.push(p.id);
                }
                people.push(person);
            });
            people = people.filter((person, index, self) => self.findIndex(t => t.id === person.id && t.description === person.description) === index);

            let options = '<option value="">Choose...</option>';
            if (people.length > 0) {
                const peopleNames = fetchPeople(people);
                people.sort((a,b) => a.id - b.id);
                people.forEach(function (item) {
                    let name = 'Per' + item.id + ' - "' + item.description + '"';
                    if (peopleNames.hasOwnProperty(item.id)) {
                        name += ' (' + peopleNames[item.id].last + ((peopleNames[item.id].first) ? ', ' + peopleNames[item.id].first : '') + ')';
                    }
                    options += '<option value="' + item.id + '" data-id="' + item.id + '" data-description="' + item.description + '">' + name + '</option>';
                });
            }
            $('#person1').html(options);
            $('#person2').html(options);
            $('#person3').html(options);

            $('#person1').val(person1IDs);
            $('#person2').val(person2IDs);
            $('#person3').val(person3IDs);
        }

        // The places
        if (event.places.length > 0) {
            // Update origin and destination drop down list
            let originIDs = [];
            let destinationIDs = [];
            event.places.forEach(function (p) {
                let place = {
                    id: p.id.toString(),
                    description: p.details.description,
                };
                if (p.details.role === 'destination') {
                    destinationIDs.push(p.id);
                } else {
                    originIDs.push(p.id);
                }
                places.push(place);
            });
            places = places.filter((place, index, self) => self.findIndex(t => t.id === place.id && t.description === place.description) === index);

            let options = '<option value="" disabled selected>Choose...</option>';
            if (places.length > 0) {
                const placeNames = fetchPlaces(places);
                places.sort((a,b) => a.id - b.id);
                places.forEach(function (item) {
                    let name = 'Place ' + item.id + ' - "' + item.description + '"';
                    if (placeNames.hasOwnProperty(item.id)) {
                        name += ' (' + placeNames[item.id].name + ')';
                    }
                    options += '<option value="' + item.id + '" data-id="' + item.id + '" data-description="' + item.description + '">' + name + '</option>';
                });
            }
            $('#origin').html(options);
            $('#destination').html(options);

            $('#origin').val(originIDs);
            $('#destination').val(destinationIDs);
        }

        // The objects/items
        if (event.items.length > 0) {
            // Update origin and destination drop down list
            let itemIDs = [];
            event.items.forEach(function (i) {
                let object = {
                    id: i.identifier,
                    name: i.details.description,
                };
                itemIDs.push(i.identifier);
                objects.push(object);
            });
            objects = objects.filter((object, index, self) => self.findIndex(t => t.id === object.id && t.name === object.name) === index);

            let options = '<option value="" disabled>Choose...</option>';
            if (objects.length > 0) {
                const objectNames = fetchObjects(objects);
                objects.sort((a,b) => a.id - b.id);
                $.each(objects, function (index, value) {
                    let name = value.id + ' - "' + value.name + '"';
                    if (objectNames.hasOwnProperty(value.id)) {
                        name += ' (' + objectNames[value.id].name + ')';
                        value.active = true;
                    } else {
                        value.active = true;
                        name += ' (n/a)';
                    }
                    options += '<option value="' + value.id + '" data-id="' + value.id + '" data-description="' + value.name + '"' + (value.active ? '' : ' disabled') + '>' + name + '</option>';
                });
            }
            $('#objects').html(options);
            $('#objects').val(itemIDs);
        }

        if (event.year) {
            dates.push(event.year);
            dates = [...new Set(dates)];
            let options = '<option value="" selected>Choose...</option>';
            $.each(dates, function (index, value) {
                options += '<option value="' + value + '">' + value + '</option>';
            });
            $('#year').html(options);
            $('select#year option[value="' + event.year + '"]').prop('selected','selected');
        }

        $('button#addEvent').hide();
        $('button#editEvent').show();
        $('button#editEvent').data('eventId', eventId);
        $('div#new_event').get(0).scrollIntoView();
    });


    /**
     *  The XML file and selected identifier are allowed to be passed as URL arguments
     */
    let searchParams = new URLSearchParams(window.location.search);
    if (searchParams.has('xmlFile')) {
        let xmlFile = searchParams.get('xmlFile');
        let xmlFilevalid = $('#xmlFile option').filter(function(){ return $(this).val() == xmlFile; }).length;
        if (xmlFilevalid) {
            // If the file argument is one of the known XML files, load it
            $('#xmlFile option[value="' + xmlFile + '"]').prop("selected", true);
            $('#xmlFile').val(xmlFile).change();

            xmlFileLoading.promise().done(function () {
                if (searchParams.has('id')) {
                    // Mark the given xml:id as active (which in turn will display any events on that ID)
                    let xmlId = searchParams.get('id');
                    $('[xml\\:id="' + xmlId + '"]').addClass('event active').trigger('click');
                    $('[xml\\:id="' + xmlId + '"]').get(0).scrollIntoView();
                }
            });
        }
    }

});
