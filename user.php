<? require_once $_SERVER["DOCUMENT_ROOT"] . '/src/general.php';

// Login status
if (!CheckLogin())
{
	header("location: /login.php");
	exit;
}

// Get user data
$user = GetSessionUser();

// Form token
$_SESSION["user_data_form_csrf"] = bin2hex(random_bytes(32));

// Page
?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Account</title>
	<? require_once $_SERVER["DOCUMENT_ROOT"] . '/src/templates/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
	<header class="container-sm my-3 text-center mb-5">
		<h1>Accountdetails</h1>
	</header>
	<section>
		<article class="container-sm mb-3" style="max-width: 500px">
			<?
			if (!empty($_REQUEST["user_name_taken"]))
			{
				?>
				<div class="col-12">
					<div class="alert alert-danger" role="alert">
						Der Benutzername ist bereits vergeben.
					</div>
				</div>
				<?
			}
			if (!empty($_REQUEST["password_email_taken"]))
			{
				?>
				<div class="col-12">
					<div class="alert alert-danger" role="alert">
						Die E-Mail-Adresse wird bereits verwendet.
					</div>
				</div>
				<?
			}
			if (!empty($_REQUEST["google_id_taken"]))
			{
				?>
				<div class="col-12">
					<div class="alert alert-danger" role="alert">
						Der Google-Account ist bereits registriert.
					</div>
				</div>
				<?
			}
			else if (!empty($_REQUEST["updated_notice"]))
			{
				?>
				<div class="col-12">
					<div class="alert alert-success" role="alert">
						Deine Daten wurden erfolgreich übernommen.
					</div>
				</div>
				<?
			}
			?>
            <form class="row g-3" action="/src/Controller/UserController.php" method="POST">
				<input type="hidden" name="action" value="update">
				<input type="hidden" name="csrf" value="<?= $_SESSION["user_data_form_csrf"] ?>">
				<input type="hidden" name="id" value="<?= $user->id ?>">
				<div class="form-group col-12">
					<label>Name</label>
					<input type="text" name="name" class="form-control" value="<?= $user->name ?>" required="required">
				</div>
				<div class="form-group col-12">
					<label>Neues Passwort</label>
					<input type="password" class="form-control" name="password">
					<small class="form-text text-muted">Wenn du das Passwort nicht ändern willst, lass das Feld leer</small>
				</div>
				<div class="form-group col-12">
					<label>Passwort vergessen E-Mail</label>
					<input type="email" name="password_email" class="form-control" value="<?= $user->password_email ?>" placeholder="beispiel@gmail.de">
					<small class="form-text text-muted">Diese E-Mail-Adresse wird nur zum zurücksetzen des Passwortes genutzt (optional)</small>
				</div>
				<div class="form-group col-12">
					<button type="submit" class="form-control btn btn-primary">Daten übernehmen</button>
				</div>
            </form>
		</article>
		<?
		if (empty($user->google_user_id))
		{
			?>
			<article class="container-sm my-5" style="max-width: 500px">
				<form action="/src/Controller/UserController.php" method="POST">
					<input type="hidden" name="action" value="update">
					<input type="hidden" name="csrf" value="<?= $_SESSION["user_data_form_csrf"] ?>">
					<input type="hidden" name="id" value="<?= $user->id ?>">
					<input type="hidden" name="google_user_id_token" value="">
					<div class="form-group col-12">
						<p>Hier kannst du deinen Google-Account verknüpfen:</p>
						<div class="g-signin2 d-inline-block" data-onsuccess="LogInViaGoogle"></div>
						<button type="submit" class="form-control text-white btn btn-info google_signed_in d-none">
							Google-Account verknüpfen
						</button>
					</div>
				</form>
			</article>
			<?
		}
		else
		{
			?>
			<article class="container-sm my-5" style="max-width: 500px">
				<form id="unlink_google_form" action="/src/Controller/UserController.php" method="POST">
					<input type="hidden" name="action" value="update">
					<input type="hidden" name="csrf" value="<?= $_SESSION["user_data_form_csrf"] ?>">
					<input type="hidden" name="id" value="<?= $user->id ?>">
					<input type="hidden" name="google_user_id_token" value="">
					<div class="form-group col-12">
						<p>Verknüpfung mit Google-Account entfernen:</p>
						<button type="submit" class="form-control btn btn-danger">
							Google-Account entfernen
						</button>
					</div>
				</form>
			</article>
			<?
		}

		RenderFlightDayBtn('zum heutigen Flugtag');
		?>
	</section>
<? include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/footer.php';