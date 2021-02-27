<? require_once $_SERVER["DOCUMENT_ROOT"] . '/src/general.php';

// Login status
if (!CheckLogin() || empty(GetSessionUser()->is_moderator))
{
	header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
    exit;
}

// =========================== DELETE old, not approved accounts ==============================
if (!empty(DB_CLEAN_NOT_APPROVED_TIME))
{
    $delete_not_approved_SQL = "FROM user u
                                LEFT JOIN attendance a
                                    ON a.user_id = u.id
                                WHERE is_approved = 0
                                    AND a.id IS NULL
                                    AND u.inserted_at < :time";

    // Show
    $time = new DateTime();
    $time->sub(new DateInterval(DB_CLEAN_NOT_APPROVED_TIME));
    $query = $db->prepare("SELECT u.* " . $delete_not_approved_SQL);
    $query->bindValue(':time', $time->format('Y-m-d H:i:s'));
    $query->execute();
    echo "<p>Folgende nicht bestätigte Accounts werden gelöscht (älter als " . $time->format('Y-m-d H:i:s') . "):</p>";
    echo "<table><tr><th>Name</th><th>registriert am</th></tr>";
    $empty = true;
    while ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        $empty = false;
        echo "<tr><td>" . $row["name"] . "</td><td>" . $row["inserted_at"] . "</td></tr>";
    }
    if ($empty) echo "<tr><td colspan=\"2\"><i>keine Einträge</i></td></tr>";
    echo "</table>";

    // DELETE
    $query = $db->prepare("DELETE u " . $delete_not_approved_SQL);
    $query->bindValue(':time', $time->format('Y-m-d H:i:s'));
    $query->execute();
    echo "<p>" . $query->rowCount() . " Datensätze gelöscht</p><hr>";
}

// ========================== "Block" approved but inactive accounts ============================
if (!empty(DB_CLEAN_INACTIVE_ACC_TIME))
{
    // Show
    $time = new DateTime();
    $today = new DateTime();
    $time->sub(new DateInterval(DB_CLEAN_INACTIVE_ACC_TIME));
    $query = $db->prepare("SELECT u.* FROM user u
                            LEFT JOIN attendance a
                                ON a.user_id = u.id
                                AND a.flight_day > :time
                                AND a.flight_day <= :today
                                AND a.is_planned = 0
                            WHERE a.id IS NULL
                                AND u.inserted_at < :time");
    $query->bindValue(':time', $time->format('Y-m-d H:i:s'));
    $query->bindValue(':today', $today->format('Y-m-d'));
    $query->execute();
    echo "<p>Folgende Accounts sind/werden als inaktiv \"blockiert\" (nicht aktiv gewesen seit " . $time->format('Y-m-d H:i:s') . "):</p>";
    echo "<table><tr><th>Name</th><th>registriert am</th></tr>";
    $empty = true;
    while ($row = $query->fetch(PDO::FETCH_ASSOC))
    {
        $empty = false;
        echo "<tr><td>" . $row["name"] . "</td><td>" . $row["inserted_at"] . "</td></tr>";
    }
    if ($empty) echo "<tr><td colspan=\"2\"><i>keine Einträge</i></td></tr>";
    echo "</table>";

    // UPDATE
    $query = $db->prepare("UPDATE user u
                            LEFT JOIN attendance a
                                ON a.user_id = u.id
                                AND a.flight_day > :time
                                AND a.flight_day <= :today
                                AND a.is_planned = 0
                            SET u.is_approved = 0
                            WHERE a.id IS NULL
                                AND u.inserted_at < :time");
    $query->bindValue(':time', $time->format('Y-m-d H:i:s'));
    $query->bindValue(':today', $today->format('Y-m-d'));
    $query->execute();
    echo "<p>" . $query->rowCount() . " Datensätze bearbeitet</p>";
}