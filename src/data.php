<?
function GetAttendanceListAll($flight_day, $is_planned = false)
{
    global $db;
    $query = $db->prepare("SELECT "
                            . Attendance::SQL('a', 'att') . ","
                            . User::SQL('u', 'usr') . ","
                            . Plane::SQL('p', 'pln') . "
                            FROM attendance a
                            JOIN user u
                                ON a.flight_day = :flight_day
                                AND a.user_id = u.id
                                AND a.is_planned = :is_planned
                            LEFT JOIN plane_selection ps
                                ON a.id = ps.attendance_id
                            LEFT JOIN plane p
                                ON ps.plane_id = p.id
                            ORDER BY a.first DESC, a.time");
    $query->bindValue(':flight_day', $flight_day->format('Y-m-d'));
    $query->bindParam(':is_planned', $is_planned);
    $query->execute();

    $entries = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        if (empty($entries["sel"][$row["att_id"]]))
        {
            $att = new Attendance($row, "att_");
            $att->user = new User($row, "usr_");
            $entries["att"][$row["att_id"]] = $att;
        }
        if (empty($row["pln_id"])) $entries["sel"][$row["att_id"]] = [];
        else $entries["sel"][$row["att_id"]][] = new Plane($row, "pln_");
    }
    return $entries;
}

function GetAttendanceListByPlane($flight_day, $is_planned = false)
{
    global $db;
    $query = $db->prepare("SELECT "
                            . Attendance::SQL('a', 'att') . ","
                            . User::SQL('u', 'usr') . ","
                            . Plane::SQL('p', 'pln') . "
                            FROM attendance a
                            JOIN user u
                                ON a.flight_day = :flight_day
                                AND a.user_id = u.id
                                AND a.is_planned = :is_planned
                            JOIN plane_selection ps
                                ON a.id = ps.attendance_id
                            JOIN plane p
                                ON ps.plane_id = p.id
                            ORDER BY a.first DESC, a.time");
    $query->bindValue(':flight_day', $flight_day->format('Y-m-d'));
    $query->bindParam(':is_planned', $is_planned);
    $query->execute();

    $entries = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        $att = new Attendance($row, "att_");
        $att->user = new User($row, "usr_");
        $entries[$row["pln_id"]][] = $att;
    }
    return $entries;
}

function GetPlaneSelectionByAttendanceID($attendance_id)
{
    global $db;
    $query = $db->prepare('SELECT plane_id FROM plane_selection WHERE attendance_id = :attendance_id');
    $query->bindParam(':attendance_id', $attendance_id);
    $query->execute();

    $planes = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) $planes[] = $row["plane_id"];
    return $planes;
}

function GetAttendanceByUser($user_id, $flight_day)
{
    global $db;
    $query = $db->prepare("SELECT *
                           FROM attendance
                           WHERE flight_day = :flight_day
                             AND user_id = :user_id
                             AND (manual_entry IS NULL OR manual_entry = '')");
    $query->bindValue(':user_id', $user_id);
    $query->bindValue(':flight_day', $flight_day->format('Y-m-d'));
    $query->execute();
    
    if ($row = $query->fetch(PDO::FETCH_ASSOC)) return new Attendance($row);
    else return null;
}

function GetFlightDayInfo($date_start, $span_in_days = CALENDAR_DAY_SPAN)
{
    global $db;
    $query = $db->prepare('SELECT
                                flight_day,
                                role,
                                COUNT(id) AS role_count,
                                (
                                    SELECT COUNT(id) FROM attendance b
                                    WHERE a.flight_day = b.flight_day
                                ) AS all_count,
                                (
                                    SELECT COUNT(id) FROM attendance b
                                    WHERE a.flight_day = b.flight_day
                                        AND b.user_id = :user_id
                                ) AS user_present
                            FROM attendance a
                            GROUP BY flight_day, role
                            HAVING flight_day >= :date_start
                                AND flight_day <= ADDDATE(:date_start, :span_in_days)');
    $query->bindValue(':date_start', $date_start->format('Y-m-d'));
    $query->bindParam(':span_in_days', $span_in_days);
    $query->bindParam(':user_id', $_SESSION["user_id"]);
    $query->execute();

    $info_arr = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        $info_arr[$row["flight_day"]][$row["role"]] = $row["role_count"];
        $info_arr[$row["flight_day"]]["all"] = $row["all_count"];
        $info_arr[$row["flight_day"]]["user_present"] = $row["user_present"];
    }
    return $info_arr;
}

function GetPlanes()
{
    global $db;
    $query = $db->prepare('SELECT * FROM plane WHERE available = 1');
    $query->execute();

    $planes = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) $planes[$row["id"]] = new Plane($row);
    return $planes;
}

function GetPlanesFromFlightDay($flight_day)
{
    global $db;
    $query = $db->prepare('SELECT p.*
                            FROM attendance a
                            JOIN plane_selection ps
                                ON a.flight_day = :flight_day
                                AND ps.attendance_id = a.id
                            JOIN plane p
                                ON ps.plane_id = p.id');
    $query->bindValue(':flight_day', $flight_day->format('Y-m-d'));
    $query->execute();

    $planes = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) $planes[$row["id"]] = new Plane($row);
    return $planes;
}

function GetUnapprovedUserList()
{
    global $db;
    $query = $db->prepare('SELECT * FROM user WHERE is_approved != 1');
    $query->execute();

    $users = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) $users[$row["id"]] = new User($row);
    return $users;
}