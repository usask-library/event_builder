$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Authorization': 'Bearer ' + ((typeof apiToken === 'undefined') ? 'null' : apiToken)
    }
});

function toast(status = 'success', title = 'Notification', message = 'Hi.') {
    let statuses = new Set(['success', 'info', 'warning', 'danger']);

    if (! statuses.has(status)) {
        status = 'success';
    }
    let icon = '<i class="far fa-check-circle"></i>';
    $('#toast-header').html('<span class="text-' + status + '">' + icon + ' ' + title + '</span>');
    $('#toast-body').html('<div class="text-' + status + '">' + message + '</div>');
    $("#toast").toast('show');
}

$(function() {
    // Prevents specific elements inside a collapse region from triggering the expand/collapse
    $('body').on('click', '.no-collapse', function(e) {
        e.stopPropagation();
    });

});
