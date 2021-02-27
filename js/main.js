$(document).ready(function()
{
    // Fehlermeldungen
    $('.toast').toast({ autohide: false });
    if ($('#error_toast').length > 0) $('#error_toast').toast('show');

    // Geolocation
    $('.position_error').hide();
    $('.distance_valid').hide();
    $('.distance_invalid').hide();
    getPosition();
});

// Geolocation
function getPosition()
{
    if (navigator.geolocation)
    {
        let options = { maximumAge: 10000, timeout: 10000, enableHighAccuracy: true };
        navigator.geolocation.getCurrentPosition(validatePosition, showError, options);
    }
    else $('.position_error').show();
}
function validatePosition(position)
{
    $('input[name="pos_latitude"]').val(position.coords.latitude);
    $('input[name="pos_longitude"]').val(position.coords.longitude);

    let zone_lat = $('meta[name="zone_latitude"]').attr('content');
    let zone_long = $('meta[name="zone_longitude"]').attr('content');
    let zone_radius = $('meta[name="zone_radius"]').attr('content');
    let distance = distanceCoordinates(zone_lat, zone_long, position.coords.latitude, position.coords.longitude);

    // Debugging
    //$('.your_google_pos').attr("href", "https://www.google.de/maps/@" + position.coords.latitude + "," + position.coords.longitude + ",18z");
    //$('.zone_google_pos').attr("href", "https://www.google.de/maps/@" + zone_lat + "," + zone_long + ",18z");

    distance -= zone_radius;
    if (distance < 1000) $('.distance').text(distance + " m");
    else $('.distance').text(Math.round(distance / 100) / 10 + " km");
    $('.distance_accuracy').text(position.coords.accuracy);

    setPositionStatus(distance <= 0); // valid|invalid
}
function showError(error)
{
    switch(error.code)
    {
        case error.PERMISSION_DENIED:
            $('.position_error alert').html("Dein Browser hat die Erfassung deiner Position abgelehnt. Um dich als <strong>anwesend</strong> einzutragen, "
                                    + "musst du in den Einstellungen deines Browsers die Funktion wieder aktivieren.");
            break;
        case error.POSITION_UNAVAILABLE:
            $('.position_error alert').html("Die Positionsdaten sind leider nicht verfügbar. "
                                        + "Eventuell besitzt dein Endgerät keine Möglichkeit zur Erfassung der Position.");
            break;
        case error.TIMEOUT:
            $('.position_error alert').html("Beim Erfassen deiner Positionsdaten ist ein Fehler aufgetreten (Time Out).");
            break;
        case error.UNKNOWN_ERROR:
            $('.position_error alert').html("Beim Erfassen deiner Positionsdaten ist ein unbekannter Fehler aufgetreten.");
            break;
    }
    setPositionStatus(-1); // error
}
function distanceCoordinates(lat1, lon1, lat2, lon2)
{
	if ((lat1 == lat2) && (lon1 == lon2)) return 0;
	else
    {
		let radlat1 = Math.PI * lat1/180;
		let radlat2 = Math.PI * lat2/180;
		let theta = lon1-lon2;
		let radtheta = Math.PI * theta/180;
		let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
		if (dist > 1) dist = 1;
		dist = Math.acos(dist);
		dist = dist * 180/Math.PI;
		dist = dist * 60 * 1.1515;
		return Math.round(dist * 1609.344); // Meter
	}
}
function setPositionStatus(status)
{
    if (status < 0)
    {
        $('.distance_invalid').show();
        $('.distance_valid').hide();
        $('.position_error').show();
        $('input[name="is_planned"].set_by_location').val(1);
        $('input[name="pos_longitude"]').val(0);
        $('input[name="pos_latitude"]').val(0);
    }
    else if (status == 0)
    {
        $('.distance_invalid').show();
        $('.distance_valid').hide();
        $('.position_error').hide();
        $('input[name="is_planned"].set_by_location').val(1);
    }
    else 
    {
        $('.distance_invalid').hide();
        $('.distance_valid').show();
        $('.position_error').hide();
        $('input[name="is_planned"].set_by_location').val(0);
    }
}
$('.btn_get_position').on('click', getPosition);

// Clipboard
function HtmlToClipboard(element)
{
    var temp = $("<input>");
    $("body").append(temp);
    temp.val($(element).html()).select();
    document.execCommand("copy");
    temp.remove();
}
function HrefToClipboard(element)
{
    var temp = $("<input>");
    $("body").append(temp);
    temp.val($(element).attr("href")).select();
    document.execCommand("copy");
    temp.remove();
}
$('.html_to_clipboard').on('click', function(e)
{
    HtmlToClipboard(this);
    alert("kopiert!");
});
$('.href_to_clipboard').on('click', function(e)
{
    e.preventDefault();
    e.stopPropagation();
    HrefToClipboard(this);
    alert("kopiert!");
});

// Set plane selection in attendance form
$('#attend_form').on('submit', function(e)
{
    let list = '';
    $(this).find('.plane_btn').each(function(index)
    {
        if (this.checked) list = list + ',' + ($(this).data('plane_id'));
    });
    if (list.length > 0) list = list.substr(1);
    $(this).find('input[name="plane_selection"]').val(list);
});

// Set Inputfield to NULL
$('.btn_unset').on('click', function(e)
{
    $(this).parent().find('input').val(null);
});

// Handle Checkboxes
$('input[type="checkbox"].hidden_input').on('change', function(e)
{
    $(this).parent().find('input[type="hidden"]').val(this.checked ? 1 : 0);
});

// Manual entry (Drag & Drop)
$('tr[draggable="true"]').on('dragstart', function(e)
{
    e.originalEvent.dataTransfer.setData("text", $(this).data('id'));
});
$('.attendance_table tr').on('dragover', function(e)
{
    e.preventDefault();
});
$('.attendance_table tbody tr').on('drop', function(e)
{
    e.preventDefault();
    let time = $(this).data('time').split(':');
    let time_obj = new Date(0, 0, 0, time[0], time[1], time[2], 0);
    time_obj.setSeconds(time_obj.getSeconds() + 1);

    // Post request
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/src/Controller/AttendanceController.php", false);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("action=update&id=" + e.originalEvent.dataTransfer.getData("text")
        + "&time=" + encodeURIComponent(time_obj.toLocaleTimeString("de")));

    document.location.href = window.location;
});
$('.attendance_table thead tr').on('drop', function(e)
{
    e.preventDefault();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/src/Controller/AttendanceController.php", false);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("action=update&id=" + e.originalEvent.dataTransfer.getData("text")
        + "&time=" + encodeURIComponent("00:00:00"));
    document.location.href = window.location;
});
$('.add_user_btn').on('click', function(e)
{
    let time_str = $($('.att_table_all tbody tr:last-child()')[0]).data('time');
    let time = time_str.split(':');
    let time_obj = new Date(0, 0, 0, time[0], time[1], time[2], 0);
    time_obj.setSeconds(time_obj.getSeconds() + 1);
    $(this).attr('href', $(this).attr('href') + "&time=" + encodeURIComponent(time_obj.toLocaleTimeString("de")));
});

// Login
$('.toggle_form').on('click', toggleForm);
function toggleForm()
{
    $('form').each(function(index, item)
    {
        if ($(item).hasClass("d-none")) $(item).removeClass("d-none");
        else $(item).addClass("d-none");
    });
}
function SignInViaGoogle(googleUser)
{
    $('input[name="name"]').val(googleUser.getBasicProfile().getName());
    $('input[name="google_user_id_token"]').val(googleUser.getAuthResponse().id_token);
    $('.g-signin2').addClass("d-none");
    $('.google_signed_in').removeClass("d-none");
    toggleForm();
}
function LogInViaGoogle(googleUser)
{
    $('input[name="google_user_id_token"]').val(googleUser.getAuthResponse().id_token);
    $('.g-signin2').addClass("d-none");
    $('.google_signed_in').removeClass("d-none");
}
$('#unlink_google_form').on('submit', function(e)
{
    if (!confirm('Bist du sicher, dass du die Verknüpfung mit deinem Google-Account entfernen möchtest?'))
    {
        e.preventDefault();
        e.stopPropagation();
    }
});
$('#user_list a').on('click', function(e)
{
    if (!confirm('Bist du sicher, dass du den Nutzer bestätigen willst?'))
    {
        e.preventDefault();
        e.stopPropagation();
    }
});