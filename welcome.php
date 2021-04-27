<?
require_once $_SERVER["DOCUMENT_ROOT"] . '/src/general.php';

// Login status
if (CheckLogin())
{
	header("location: /index.php");
	exit;
}
?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Willkommen | Segelflugstart-Manager</title>
	<? require_once $_SERVER["DOCUMENT_ROOT"] . '/src/templates/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
	<header class="container-md my-md-5 my-3 text-center">
		<h1>Willkommen zum inoffiziellen Haxterberg Segelflugstart-Manager!</h1>
	</header>
	<section>
		<article class="container-sm" style="max-width: 800px">
			<p>
				So viele Flugschüler und alle wollen sie fliegen, aber wer war noch nicht dran und wer kommt als Nächstes? Wie oft war jeder?
				Viel wichtiger ... wer darf als Erstes? Eine Liste muss her!
			</p>
			<p>
				Eine Papierliste vielleicht? Pff ... ok Boomer.
				Wie es sich für's 21. Jahrhundert gehört, kann sich hier jetzt jeder selbst in eine digitale Liste eintragen.
			</p>
			<p>
				Bist du am Wochenende dabei? Lass es die anderen wissen und trag dich (und was du fliegen willst) schonmal vorher ein.
				Wenn du dann in der Nähe vom Platz bist, kannst du dich mit einem Klick als anwesend eintragen.
			</p>
			<?
				if (empty(APPROVE_ACCOUNTS_BY_DEFAULT))
				{
					?>
					<div class="col-12">
						<div class="alert alert-warning" role="alert">
							<i class="fas fa-exclamation-triangle"></i>
							Diese Seite ist <strong>privat</strong>. Nach der Registrierung muss ein Administrator deinen Account erst freischalten.
						</div>
					</div>
					<?
				}
			?>
			<div class="row">
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
				?>
			</div>
            <form class="row g-3" action="/src/Controller/UserController.php" method="POST">
                <input type="hidden" name="action" value="insert"/>
				<div class="form-group col-sm-6 col-12">
					<label>Name</label>
					<input type="text" class="form-control" name="name" placeholder="Am besten Vor- und Nachname" required="required">
				</div>
				<div class="form-group col-sm-6 col-12">
					<label>Passwort</label>
					<input type="password" minlength="<? echo LOGIN_PW_MIN_LENGTH; ?>" placeholder="Bitte nicht 12345" class="form-control" name="pw" required="required">
				</div>
				<div class="form-group col-12">
					<label>Passwort vergessen E-Mail</label>
					<input type="email" class="form-control" placeholder="beispiel@gmail.de" name="password_email">
					<small class="form-text text-muted">Diese E-Mail-Adresse wird nur zum zurücksetzen des Passwortes genutzt (optional)</small>
				</div>
				<div class="form-group col-12">
					<input type="checkbox" class="form-check-input" required="required">
					Ich akzeptiere die <a href="/impressum_und_datenschutz.php">Datenschutzbestimmungen</a>
				</div>
				<div class="form-group col-12">
					<label class="form-check-label">
						<input type="checkbox" name="set_remember_me" class="form-check-input">
						Angemeldet bleiben
					</label>
				</div>
				<div class="form-group col-12 text-center">
					<button type="submit" class="form-control btn btn-primary">Registrieren</button>
				</div>
				<?
				if (!empty(GOOGLE_CLIENT_ID))
				{
					?>
					<div class="col-12 text-center">
						<p>Oder registriere dich mit deinem Google-Account:</p>
						<div class="g-signin2 d-inline-block" data-onsuccess="SignInViaGoogle"></div>
						<button type="button" class="btn btn-sm google_signed_in d-none toggle_form">Klick hier!</button>
					</div>
					<?
				}
				?>
            </form>
			<?
			if (!empty(GOOGLE_CLIENT_ID))
			{
				?>
				<form class="row g-3 d-none" action="/src/Controller/UserController.php" method="POST">
					<input type="hidden" name="action" value="insert"/>
					<input type="hidden" name="google_user_id_token">
					<div class="form-group col-12">
						<label>Name</label>
						<input type="text" class="form-control" name="name" placeholder="Am besten Vor- und Nachname" required="required">
					</div>
					<div class="form-group col-12">
						<input type="checkbox" class="form-check-input" required="required">
						Ich akzeptiere die <a href="/impressum_und_datenschutz.php">Datenschutzbestimmungen</a>
					</div>
					<div class="form-group col-12">
						<label class="form-check-label">
							<input type="checkbox" name="set_remember_me" class="form-check-input">
							Angemeldet bleiben
						</label>
					</div>
					<div class="form-group col-12">
						<button type="submit" class="form-control btn btn-primary">Mit Google-Acc registrieren</button>
					</div>
					<div class="col-12 text-center">
						<button type="button" class="btn btn-sm toggle_form">zurück (ohne Google-Account registrieren)</button>
					</div>
				</form>
				<?
			}
			?>
        </article>
		<article class="container-md text-center py-5">
            <p>Schon Registriert?</p>
			<a class="btn btn-outline-secondary" href="/login.php">Zum Login hier!</a>
		</article>
	</section>
<? include $_SERVER["DOCUMENT_ROOT"] . '/src/templates/footer.php';