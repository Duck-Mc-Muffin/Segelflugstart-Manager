<? require_once __DIR__ . '/src/general.php';
global $date_formatter_title;

// Login status
if (!CheckLogin())
{
	header("location: " . INDEX_LANDING_PAGE);
	exit;
}

// Flight day
$flight_day = new DateTime();
$flight_day->setTime(0, 0, 0, 0);
$is_today = true;
if (!empty($_REQUEST["flight_day"]))
{
	$is_today = false;
	$day = DateTime::createFromFormat('Y-m-d', $_REQUEST["flight_day"]);
	if ($day === false)
	{
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not found");
        exit;
	}
	$day->setTime(0, 0, 0, 0);
	if ($day == $flight_day)
	{
		header("location: /index.php");
        exit;
	}
	$flight_day = $day;
}
$_SESSION['last_flight_day_visited'] = $flight_day->format('Y-m-d');

// Data
require_once __DIR__ . '/src/data.php';
$list_all = GetAttendanceListAll($flight_day);
$list_all_planned = GetAttendanceListAll($flight_day, true);
$list_by_plane = GetAttendanceListByPlane($flight_day);
$list_by_plane_planned = GetAttendanceListByPlane($flight_day, true);
$user_att = GetAttendanceByUser($_SESSION["user_id"], $flight_day);
$planes = GetPlanes();
$selected_planes = GetPlanesFromFlightDay($flight_day);

// Main page
?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Segelflugstart-Manager</title>
	<? require_once __DIR__ . '/src/templates/head.php'; ?>
	<link type="text/css" rel="stylesheet" href="css/main.min.css"/>
</head>
<body class="d-flex flex-column h-100">
	<header class="container my-3 text-center">
		<h1>Flugtag <?= $date_formatter_title->format($flight_day) ?></h1>
		<? if (!$is_today) RenderFlightDayBtn('zum heutigen Flugtag'); ?>
	</header>
	<section class="container my-3 px-sm-3 px-0">
		<?
		if (empty($list_all["att"]) && empty($list_all_planned["att"])) echo '<p class="text-center"><em>Noch niemand eingetragen ...</em></p>';
		else
		{
			?>
			<article>
				<ul class="nav nav-tabs nav-fill" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active d-block" style="width: 100%" id="plane_tab_all"
							data-bs-toggle="tab" data-bs-target="#plane_tab_content_all"
							type="button" role="tab" aria-controls="plane_tab_content_all" aria-selected="true">
							Alle
						</button>
					</li>
					<?
					foreach ($selected_planes as $plane_id => $plane)
					{
						?>
						<li class="nav-item" role="presentation">
							<button class="nav-link d-block" style="width: 100%" id="plane_tab_<?= $plane_id ?>"
								data-bs-toggle="tab" data-bs-target="#plane_tab_content_<?= $plane_id ?>"
								type="button" role="tab" aria-controls="plane_tab_content_<?= $plane_id ?>" aria-selected="true">
								<?= $plane->alias ?>
							</button>
						</li>
						<?
					}
					?>
				</ul>
				<div class="tab-content border border-top-0">
					<div class="tab-pane fade show active p-sm-2 att_table_all" id="plane_tab_content_all" role="tabpanel" aria-labelledby="plane_tab_all">
						<?
						if (!empty($list_all["att"])) RenderAttendanceTable("Am Platz:", $list_all["att"], $list_all["sel"]);
						else if ($is_today) echo '<p class="text-center pt-4"><em>Noch niemand am Platz ...</em></p>';
						if (!empty($list_all_planned["att"])) RenderAttendanceTable("Plant zu kommen:", $list_all_planned["att"], $list_all_planned["sel"]);
						?>
					</div>
					<?
					foreach ($selected_planes as $plane_id => $plane)
					{
						?>
						<div class="tab-pane fade p-sm-2" id="plane_tab_content_<?= $plane_id ?>" role="tabpanel" aria-labelledby="plane_tab_<?= $plane_id ?>">
							<?
							if (!empty($list_by_plane[$plane_id])) RenderAttendanceTable("Am Platz (" . $plane->alias . ")", $list_by_plane[$plane_id]);
							if (!empty($list_by_plane_planned[$plane_id])) RenderAttendanceTable("Plant zu kommen (" . $plane->alias . ")", $list_by_plane_planned[$plane_id] ?? []);
							?>
						</div>
						<?
					}
					?>
				</div>
			</article>
			<?
		}
		if (empty($user_att))
		{
			?>
			<article class="container-fluid mt-4">
				<h2 class="text-center">Willst du <?= $is_today ? 'heute' : 'am ' . $date_formatter_title->format($flight_day) ?> mitfliegen?</h2>
				<? RenderAttendanceForm($planes, $flight_day); ?>
			</article>
			<?
		}
		else if ($is_today && $user_att->is_planned)
		{
			?>
			<article class="container-fluid mt-2 row g-3">
				<div class="col position_error d-none">
					<div class="alert alert-danger m-0" role="alert">
						Dein Browser erlaubt nicht die Übertragung deiner Position oder die Positionsdaten sind nicht verfügbar.
					</div>
				</div>
				<div class="col-md col-12 distance_valid d-none">
					<div class="alert alert-success" role="alert">
						Du bist nah genug an den Hallen. Jetzt kannst du dich in der Liste eintragen.
					</div>
					<form action="src/Controller/AttendanceController.php" method="POST">
						<input type="hidden" name="action" value="update"/>
						<input type="hidden" name="id" value="<?= $user_att->id ?>">
						<input type="hidden" name="is_planned" value="0">
						<input type="hidden" name="pos_longitude"/>
						<input type="hidden" name="pos_latitude"/>
						<div class="text-center">
							<button type="submit" class="btn btn-primary">Trag mich ein!</button>
						</div>
					</form>
				</div>
				<div class="col-md col-12 distance_invalid d-none">
					<div class="alert alert-warning" role="alert">
						Du bist noch <strong class="distance">(unbekannt) m</strong> zu weit von den Hallen entfernt um dich als <b>anwesend</b> einzutragen.
						<div class="text-center pt-2">
							<button class="btn btn-secondary">aktualisieren</button>
						</div>
					</div>
				</div>
			</article>
			<?
		}
		else
		{
			if (!RESTRICT_MANUAL_ENTRY_PLANNED || (RESTRICT_MANUAL_ENTRY_PLANNED && empty($user_att->is_planned)))
			{
				?>
				<article class="container-fluid mt-4 text-center">
					<a class="btn btn-sm btn-outline-secondary add_user_btn"
                        href="/edit.php?manual=1&flight_day=<?= $flight_day->format('Y-m-d') ?>"
                        x-data="manual_user_drag_and_drop"
                        @click="linkAddManualUser()">
						<i class="fas fa-user-edit"></i>
						Andere Person manuell eintragen.
					</a>
				</article>
				<?
			}
		}
		?>
	</section>
<? include __DIR__ . '/src/templates/footer.php';