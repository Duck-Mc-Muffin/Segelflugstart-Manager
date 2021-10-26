<? require_once __DIR__ . '/src/general.php';
global $date_formatter_calendar;

// Login status
if (!CheckLogin())
{
	header("location: /welcome.php");
	exit;
}

// Data
require_once __DIR__ . '/src/data.php';
$flight_day_info = GetFlightDayInfo(new DateTime());

// Main page
?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Kalender</title>
	<? require_once __DIR__ . '/src/templates/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
	<header class="container-lg my-3 text-center">
		<h1>Kalender</h1>
	</header>
	<section class="container-lg">
		<article>
			<div class="list-group">
                <?
				$day = new DateTime();
                for($i = 0; $i < CALENDAR_DAY_SPAN; $i++)
                {
					$row_indicator = in_array($day->format('N'), [6, 7]) ? 'list-group-item-info' : '';
					if (!empty($flight_day_info[$day->format('Y-m-d')]["user_present"])) $row_indicator = 'list-group-item-success';
                    ?>
                    <a href="/index.php?flight_day=<?= $day->format('Y-m-d') ?>" class="<?= $row_indicator ?> list-group-item list-group-item-action d-flex justify-content-between align-items-center">
						<?= $date_formatter_calendar->format($day) . ($i == 0 ? ' (heute)' : '') ?>
                        <?
                            if (!empty($flight_day_info[$day->format('Y-m-d')]))
                            {
								$info = $flight_day_info[$day->format('Y-m-d')];
                                ?>
								<span>
									<?
										foreach(ATTENDANCE_ROLES as $role_number => $role)
										{
											if ($role_number == 0 || empty($info[$role_number])) continue;
											echo '<span class="badge bg-' . $role["bootstrap_color"] . ' rounded-pill">' . $info[$role_number] . ' ' . $role["symbol"] . '</span> ';
										}
									?>
									<span class="badge bg-primary rounded-pill"><?= $info["all"] ?></span>
								</span>
								<?
                            }
                        ?>
                    </a>
                    <?
					$day->add(new DateInterval('P1D'));
                }
                ?>
            </div>
		</article>
	</section>
	<div class="text-center py-3">
		<a class="btn btn-outline-secondary btn-sm" href="/index.php"><i class="fas fa-arrow-left"></i> zurück</a>
	</div>
<? include __DIR__ . '/src/templates/footer.php';