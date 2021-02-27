<?
namespace AttendanceController;
use \AppException;
use \Attendance;
use \DateTime;
use \PDO;

require_once $_SERVER["DOCUMENT_ROOT"] . '/src/general.php';

// Login status
if (!CheckLogin())
{
    header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
    exit;
}

// Actions
try
{
    switch ($_REQUEST["action"])
    {
        case 'insert': insert(); break;
        case 'update': update(); break;
        case 'delete': delete(); break;
    }
}
catch(AppException $ex)
{
    $_SESSION["error"] = $ex->getMessage();
}

if (empty($_SESSION['last_flight_day_visited'])) header('location: /index.php');
else header('location: /index.php?flight_day=' . $_SESSION['last_flight_day_visited']);
exit;

function insert()
{
    // Validate user privileges
    if (empty($_REQUEST["user_id"])) throw new AppException("Es wurde für die Anfrage keine UserID mitgesendet.");
    if ($_SESSION["user_id"] !== $_REQUEST["user_id"])
    {
        header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
        exit;
    }

    // Validate flight day
    if (empty($_REQUEST["flight_day"])) throw new AppException("Es wurde für die Anfrage kein Flugtag mitgesendet.");
    $today = new DateTime();
    $today->setTime(0, 0, 0, 0);
    $req_flight_day = DateTime::createFromFormat('Y-m-d', $_REQUEST["flight_day"]);
    $req_flight_day->setTime(0, 0, 0, 0);

    // Validate "is planned" status
    $is_planned = !empty($_REQUEST["is_planned"]);
    $is_manual = !empty($_REQUEST["manual_entry"]);
    if (!$is_planned && !$is_manual) ValidatePosition();
    if ($today < $req_flight_day && !$is_planned)
    {
        throw new AppException("Der Flugtag für den du dich als <b>anwesend</b> eintragen willst hat noch nicht begonnen (" . $req_flight_day->format('d.m.') . ").");
    }

    // Validate manual entry
    require_once $_SERVER["DOCUMENT_ROOT"] . '/src/data.php';
    $att = GetAttendanceByUser($_SESSION["user_id"], $req_flight_day);
    if ($is_manual && RESTRICT_MANUAL_ENTRY_ZONE && empty($att)) throw new AppException("Nur Nutzer die selber am Platz sind können manuelle Einträge erstellen.");
    if ($is_manual && RESTRICT_MANUAL_ENTRY_PLANNED && ($is_planned || $today > $req_flight_day)) throw new AppException('Personen können nicht als "plant zu kommen" manuell eingetragen werden.');

    // Prevent duplicate entry
    if (!$is_manual && !empty($att)) throw new AppException("Du bist bereits in der Liste eingetragen.");
    
    // Set time
    $time = new DateTime();
    if ($is_planned || $is_manual) $time = Attendance::parseTime($_REQUEST["time"]);

    // Enlist user
    global $db;
    $query = $db->prepare('INSERT INTO attendance(flight_day, user_id, time, pos_longitude, pos_latitude, manual_entry, is_planned, role, first)
                                       VALUES(:flight_day, :user_id, :time, :pos_longitude, :pos_latitude, :manual_entry, :is_planned, :role, :first);');
    $query->bindValue(':flight_day', $req_flight_day->format('Y-m-d'));
    $query->bindParam(':user_id', $_REQUEST["user_id"]);
    $query->bindValue(':time', isset($time) ? $time->format('H:i:s') : null);
    $query->bindParam(':pos_longitude', $_REQUEST["pos_longitude"]);
    $query->bindParam(':pos_latitude', $_REQUEST["pos_latitude"]);
    $query->bindParam(':manual_entry', $_REQUEST["manual_entry"]);
    $query->bindParam(':is_planned', $is_planned);
    $query->bindValue(':role', $_REQUEST["role"] ?? 0);
    $query->bindValue(':first', $_REQUEST["first"] ?? 0);
    $query->execute();
    $new_attendance_id = $db->lastInsertId();

    // Save plane selection
    if (!empty($_REQUEST["plane_selection"])) InsertPlaneSelection($new_attendance_id, explode(',', $_REQUEST["plane_selection"]));
}

function update()
{
    // Validate
    if (empty($_REQUEST["id"])) throw new AppException("Es wurde keine ID für den Eintrag mitgesendet.");
    $att = Attendance::GetByID($_REQUEST["id"]);
    if (empty($att)) throw new AppException("Der Eintrag wurde nicht in der Datenbank gefunden (ID " . $_REQUEST["id"] . ").");
    if ($att->user_id != $_SESSION["user_id"])
    {
        header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
        exit;
    }    

    // Prevent editing of past attendances
    $today = new DateTime();
    $today->setTime(0, 0, 0, 0);
    if ($att->flight_day < $today) throw new AppException("Einträge von vergangenen Flugtagen können nicht bearbeitet werden.");

    // Set Values
    $att_new = clone $att;
    $att_new->fill($_REQUEST);

    // Manual entry
    if (empty($att->manual_entry) !== empty($att_new->manual_entry)) throw new AppException('Der Status "manueller Eintrag" kann nicht nachträglich geändert werden.');
    
    // Validate position (is_planned status)
    if (empty($att_new->manual_entry) && $att->is_planned && !$att_new->is_planned) ValidatePosition();

    // Validate flight day
    if ($att_new->flight_day < $today) throw new AppException("Das Datum kann nicht auf vergangene Tage gesetzt werden.");
    else if ($att_new->flight_day > $today && empty($att_new->is_planned)) throw new AppException('Zukünftige Flugtage können nur als "geplant" eingetragen werden.');

    // Set Time
    if (!$att_new->is_planned && empty($att_new->manual_entry))
    {
        if ($att->is_planned) $att_new->time = new DateTime();
        else $att_new->time = $att->time;
    }
    
    // Update entity
    global $db;
    $query = $db->prepare('UPDATE attendance SET
                                flight_day = :flight_day,
                                time = :time,
                                pos_longitude = :pos_longitude,
                                pos_latitude = :pos_latitude,
                                manual_entry = :manual_entry,
                                is_planned = :is_planned,
                                role = :role,
                                first = :first,
                                updated_at = NOW()
                            WHERE id = :id');
    $query->bindParam(':id', $att->id);
    $query->bindValue(':flight_day', $att_new->flight_day->format('Y-m-d'));
    $query->bindValue(':time', isset($att_new->time) ? $att_new->time->format('H:i:s') : null);
    $query->bindParam(':pos_longitude', $att_new->pos_longitude);
    $query->bindParam(':pos_latitude', $att_new->pos_latitude);
    $query->bindParam(':manual_entry', $att_new->manual_entry);
    $query->bindParam(':is_planned', $att_new->is_planned);
    $query->bindParam(':role', $att_new->role);
    $query->bindValue(':first', $att_new->first);
    $query->execute();

    // Update plane selection
    if (isset($_REQUEST["plane_selection"]))
    {
        // Delete current
        $query = $db->prepare('DELETE FROM plane_selection
                                WHERE attendance_id = :id');
        $query->bindParam(':id', $_REQUEST["id"]);
        $query->execute();

        // Save new
        InsertPlaneSelection($_REQUEST["id"], explode(',', $_REQUEST["plane_selection"]));
    }
}

function delete()
{
    // Validate request
    if (empty($_REQUEST["attendance_id"])) throw new AppException("Es wurde für die Anfrage keine ID für den Datensatz mitgesendet.");
    $att = Attendance::GetByID($_REQUEST["attendance_id"]);
    if (empty($att)) throw new AppException("Datensatz wurde nicht in der Datenbank gefunden.");

    // Delete plane selection and attendance
    global $db;
    $query = $db->prepare('DELETE plane_selection
                        FROM plane_selection
                        JOIN attendance
                            ON attendance_id = :attendance_id
                            AND id = :attendance_id
                            AND user_id = :user_id
                            AND flight_day >= curdate();

                        DELETE FROM attendance
                        WHERE id = :attendance_id
                        AND user_id = :user_id
                        AND flight_day >= curdate();');
    $query->bindParam(':attendance_id', $_REQUEST["attendance_id"]);
    $query->bindParam(':user_id', $_SESSION["user_id"]);
    $query->execute();
}

function InsertPlaneSelection($att_id, $selection)
{
    if (empty($selection) || empty($selection[0])) return;
    global $db;
    $values_str = '(:att_id, :plane_0)';
    for($i = 1; $i < count($selection); $i++) $values_str .= ',(:att_id, :plane_' . $i . ')';
    $query = $db->prepare('INSERT INTO plane_selection (attendance_id, plane_id) VALUES ' . $values_str);
    for($i = 0; $i < count($selection); $i++) $query->bindParam(':plane_' . $i, $selection[$i]);
    $query->bindParam(':att_id', $att_id);
    $query->execute();
}

function ValidatePosition($att = null)
{
    if (empty($_REQUEST["pos_longitude"]) || empty($_REQUEST["pos_latitude"])) throw new AppException("Die Positionsdaten sind unvollständig!");

    $lat1 = $_REQUEST["pos_latitude"];
    $lon1 = $_REQUEST["pos_longitude"];
    $lat2 = ENLIST_ZONE_LATITUDE;
    $lon2 = ENLIST_ZONE_LONGITUDE;
    $dist = 0;
    if (!(($lat1 == $lat2) && ($lon1 == $lon2)))
    {
        $radlat1 = pi() * $lat1 / 180;
        $radlat2 = pi() * $lat2 / 180;
        $theta = $lon1 - $lon2;
        $radtheta = pi() * $theta / 180;
        $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);
        if ($dist > 1) $dist = 1;
        $dist = acos($dist);
        $dist = $dist * 180 / pi();
        $dist = $dist * 60 * 1.1515;
        $dist = round($dist * 1609.344) - ENLIST_ZONE_RADIUS; // Meter
    }

    if ($dist > 0) throw new AppException("Du bist noch " . $dist . " m zu weit weg um dich als anwesend einzutragen!");
}