// Initialize Alpine components
document.addEventListener('alpine:init', () =>
{
    // Plane selection checkboxes
    Alpine.data('plane_selection_option', (init_plane_id) => (
    {
        plane_id: init_plane_id,
        is_selected: false,
        init()
        {
            this.$watch('is_selected', (val) =>
                {
                    if (val) this.plane_selection.push(this.plane_id);
                    else this.plane_selection.splice(this.plane_selection.indexOf(this.plane_id), 1);
                });
        }
    }));

    // Checkbox helper
    Alpine.data('checkbox_helper', (value) => (
    {
        is_checked: !!value,
        is_checked_number: value ? 1 : 0,
        init()
        {
            this.$watch('is_checked', (val) => {
                this.is_checked_number = val ? 1 : 0;
            });
        }
    }));

    // "Are you sure?" Dialoge
    Alpine.data('are_you_sure', (msg) => (
    {
        message: msg,
        prompt()
        {
            if (!confirm(this.message))
            {
                this.$event.preventDefault();
                this.$event.stopPropagation();
            }
        }
    }));

    // Geolocation
    Alpine.data('attendance', () => (
    {
        plane_selection: [],
        distance: '[unbekannt] m',
        distance_valid: false,
        distance_invalid: 1, // for "is_planned" flag
        distance_error: true,
        distance_error_message: 'Dein Browser erlaubt nicht die Übertragung deiner Position oder die Positionsdaten sind nicht verfügbar.',
        accuracy: '[unbekannt] m',
        pos_latitude: null,
        pos_longitude: null,
        zone_lat: document.querySelector('meta[name="zone_latitude"]').content,
        zone_long: document.querySelector('meta[name="zone_longitude"]').content,
        zone_radius: document.querySelector('meta[name="zone_radius"]').content,
        init()
        {
            this.getPosition();
            this.$watch('distance_valid', value => this.distance_invalid = value ? 0 : 1);
        },
        getPosition()
        {
            if (navigator.geolocation)
            {
                let options = { maximumAge: 10000, timeout: 10000, enableHighAccuracy: true };
                let context = this;
                navigator.geolocation.getCurrentPosition(
                    (pos) => context.validatePosition(pos),
                    (err) => context.error(err),
                    options);
            }
            else this.error({ code: null });
        },
        validatePosition(position)
        {
            this.distance_error = false;

            this.accuracy = position.coords.accuracy;
            this.pos_latitude = position.coords.latitude;
            this.pos_longitude = position.coords.longitude;
            let dist = this.distanceCoordinates(this.zone_lat, this.zone_long, this.pos_latitude, this.pos_longitude);

            // Debugging
            //console.log("Your Position: https://www.google.de/maps/@" + position.coords.latitude + "," + position.coords.longitude + ",18z");
            //console.log("Zone Position: https://www.google.de/maps/@" + this.zone_lat + "," + this.zone_long + ",18z");

            dist -= this.zone_radius;
            if (dist < 0) dist = 0;
            this.distance_valid = dist <= 0;

            if (dist < 1000) this.distance = dist + " m";
            else this.distance = Math.round(dist / 100) / 10 + " km";
        },
        error(error)
        {
            this.distance_error = true;
            switch(error.code)
            {
                case error.PERMISSION_DENIED:
                    this.distance_error_message = "Dein Browser hat die Erfassung deiner Position abgelehnt. Um dich als <strong>anwesend</strong> einzutragen, musst du in den Einstellungen deines Browsers die Funktion wieder aktivieren.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    this.distance_error_message = "Die Positionsdaten sind leider nicht verfügbar. Eventuell besitzt dein Endgerät keine Möglichkeit zur Erfassung deiner Position.";
                    break;
                case error.TIMEOUT:
                    this.distance_error_message = "Beim Erfassen deiner Positionsdaten ist ein Fehler aufgetreten (Time Out).";
                    break;
                default:
                    this.distance_error_message = "Beim Erfassen deiner Positionsdaten ist ein unbekannter Fehler aufgetreten.";
                    break;
            }
            this.distance_error = true;
        },
        distanceCoordinates(lat1, lon1, lat2, lon2)
        {
            if ((lat1 === lat2) && (lon1 === lon2)) return 0;
            else
            {
                let radlat1 = Math.PI * lat1 / 180;
                let radlat2 = Math.PI * lat2 / 180;
                let theta = lon1 - lon2;
                let radtheta = Math.PI * theta/180;
                let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) dist = 1;
                dist = Math.acos(dist);
                dist = dist * 180 / Math.PI;
                dist = dist * 60 * 1.1515;
                return Math.round(dist * 1609.344); // Meter
            }
        }
    }));

    // Manual entry (Drag & Drop)
    Alpine.data('manual_user_drag_and_drop', () => (
    {
        setData()
        {
            this.$event.dataTransfer.setData("text", this.$el.dataset.id);
        },
        linkAddManualUser()
        {
            let last_user_in_table = document.querySelector('.att_table_all tbody tr:last-child');
            this.$el.setAttribute('href', this.$el.getAttribute('href')
                + "&time=" + this.calc(last_user_in_table.dataset.time));
        },
        apply(time)
        {
            this.$event.preventDefault();
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "/src/Controller/AttendanceController.php", false);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("action=update&id=" + this.$event.dataTransfer.getData("text")
                + "&time=" + encodeURIComponent(time));
            document.location.href = window.location;
        },
        calc(time_str)
        {
            let time = time_str.split(':');
            let time_obj = new Date(0, 0, 0, time[0], time[1], time[2], 0);
            time_obj.setSeconds(time_obj.getSeconds() + 1);
            return time_obj.toLocaleTimeString(document.documentElement.lang);
        }
    }));
});

// Error message toasts
const err_toast_element = document.getElementById('error_toast');
if (err_toast_element) (new bootstrap.Toast(err_toast_element, { autohide: false })).show();

// Toggle all forms in a document (visibility)
function toggleAllForms()
{
    document.querySelectorAll('form').forEach(
        (item) => item.classList.toggle("d-none")
    );
}

// Login via Google
function SignInViaGoogle(googleUser)
{
    document.querySelector('input[name="name"]').value = googleUser.getBasicProfile().getName();
    document.querySelector('input[name="google_user_id_token"]').value = googleUser.getAuthResponse().id_token;
    document.querySelector('.g-signin2').classList.add("d-none");
    document.querySelector('.google_signed_in').classList.remove("d-none");
    toggleAllForms();
}
function LogInViaGoogle(googleUser)
{
    document.querySelector('input[name="google_user_id_token"]').value = googleUser.getAuthResponse().id_token;
    document.querySelector('.g-signin2').classList.add("d-none");
    document.querySelector('.google_signed_in').classList.remove("d-none");
}