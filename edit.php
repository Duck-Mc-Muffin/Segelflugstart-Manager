<? require_once $_SERVER["DOCUMENT_ROOT"] . '/src/general.php';

// Login status
if (!CheckLogin())
{
	header("location: " . INDEX_LANDING_PAGE);
	exit;
}

// Flight day
$today = new DateTime();
$today->setTime(0, 0, 0, 0);
$req_flight_day = clone $today;
if (!empty($_REQUEST["flight_day"])) $req_flight_day = DateTime::createFromFormat('Y-m-d', $_REQUEST["flight_day"]);

if (!empty($_REQUEST["id"])) $att = Attendance::GetByID($_REQUEST["id"]);
if (empty($att)) $att = new Attendance(["flight_day" => ($req_flight_day === false ? $today->format('Y-m-d') : $req_flight_day->format('Y-m-d'))]);

// Data
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/data.php';
$plane_selection = GetPlaneSelectionByAttendanceID($att->id);
$planes = GetPlanes();

// Main page
?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Eintrag bearbeiten</title>
	<? require_once $_SERVER["DOCUMENT_ROOT"] . '/src/templates/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
	<header class="container my-3 text-center">
		<h1>Flugtag <?= $date_formatter_title->format($att->flight_day); ?></h1>
	</header>
    <section class="container">
        <article class="container-fluid mt-4">
			<?
			if ($att->flight_day < $today)
			{
				?>
				<div class="alert alert-warning" role="alert">
					Vergangene Flugtage können nicht mehr bearbeitet werden.
				</div>
				<?
			}
			else if (!empty($_REQUEST["manual"]))
			{
				?><h2 class="text-center">Manueller Eintrag</h2><?
			}
			else if (empty($att->id))
			{
				?><h2 class="text-center">Fliegst du mit?</h2><?
			}
			else
			{
				?><h2 class="text-center">Eintrag bearbeiten</h2><?
			}
			if (!empty($_REQUEST["time"])) $att->time = Attendance::parseTime($_REQUEST["time"]);
			RenderFlightDayBtn('zurück', $att->flight_day);
			RenderAttendanceForm($planes, $att->flight_day, $att, $plane_selection, !empty($_REQUEST["manual"]) || !empty($att->manual_entry));
			RenderFlightDayBtn('zurück', $att->flight_day);
			?>
        </article>
    </section>
<? include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/footer.php';