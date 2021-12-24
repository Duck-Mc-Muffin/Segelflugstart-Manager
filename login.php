<? require_once __DIR__ . '/src/general.php';
global $twig;

// Login status
if (CheckLogin())
{
	header("location: /index.php");
	exit;
}

// ============================= Forgot password mail ===================================
$email_was_sent_notification = false;
if (!empty($_POST["password_email"]))
{
	// Get User
    global $db;
	$query = $db->prepare("SELECT * FROM user
						   WHERE password_email = :password_email
							 AND password_email IS NOT NULL
							 AND password_email != ''");
	$query->bindParam(':password_email', $_POST["password_email"]);
	$query->execute();
	if ($row = $query->fetch(PDO::FETCH_ASSOC))
	{
		// Check E-Mail cooldown
		$time = new DateTime();
		$time->sub(new DateInterval(EMAIL_LIMIT_TIME));
		$query = $db->prepare("SELECT count(*) AS amount FROM mail_log
							   WHERE user_id = :user_id
								 AND inserted_at > :time");
		$query->bindParam(':user_id', $row["id"]);
		$query->bindValue(':time', $time->format('Y-m-d H:i:s'));
		$query->execute();
		if ($query->fetch(PDO::FETCH_ASSOC)["amount"] < EMAIL_LIMIT_AMOUNT)
		{
			// Set Login Hash
			$token = GenerateUserToken('login_token', $row["id"]);

			// Send Mail
			$data = [ 'link' => 'https://' . $_SERVER["SERVER_NAME"] . '/login.php?user_id=' . $row["id"] . '&login_token=' . $token ];
			SendMail($row["id"], $row["password_email"], $row["name"], 'Passwort vergessen?', 'pw_email.php', $data);
		}
	}
	$email_was_sent_notification = true;
}
// ====================================================================================

?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Segelflugstart-Manager | Login</title>
	<? $twig->display('head.twig'); ?>
</head>
<body class="d-flex flex-column h-100">
	<header class="container-sm my-3 text-center">
		<h1><?= WEB_APP_TITLE ?></h1>
	</header>
	<section>
		<article class="container-sm" style="max-width: 500px">
            <h2 class="text-center">Login</h2>
			<?
			if (!empty($_REQUEST["wrong_credentials"]))
			{
				?>
				<div class="alert alert-danger" role="alert">
					Der Benutzername oder das Passwort ist nicht korrekt.
				</div>
				<?
			}
			if (!empty($_REQUEST["invald_token"]))
			{
				?>
				<div class="alert alert-danger" role="alert">
					Der Login-Link ist abgelaufen oder ungültig.
				</div>
				<?
			}
			if (!empty($_REQUEST["google_acc_not_found"]))
			{
				?>
				<div class="alert alert-danger" role="alert">
					Ger Google-Account ist nicht auf dieser Seite registriert.
				</div>
				<?
			}
			if (!empty($_REQUEST["acc_not_approved"]))
			{
				?>
				<div class="alert alert-warning" role="alert">
					Der Administrator hat deinen Account noch nicht freigeschaltet.
				</div>
				<?
			}
			else if (!empty($_REQUEST["approval_notice"]))
			{
				?>
				<div class="alert alert-success" role="alert">
					Deine Daten wurden erfolgreich registriert.
					Aus Sicherheitsgründen muss dein Account noch von einem Administrator freigeschaltet werden.
					Informier einen Administrator oder versuch dich zu einem späteren Zeitpunkt einzuloggen.
				</div>
				<?
			}
			if ($email_was_sent_notification)
			{
				?>
				<div class="alert alert-success" role="alert">
					Eine E-Mail um das Passwort zurückzusetzen wird an "<?= $_POST["password_email"] ?>" gesendet.
					Stelle sicher, dass du Zugang zu dem genannten Postfach hast<br><strong>und überprüfe auch den Spam-Ordner!</strong>
				</div>
				<?
			}
			?>
            <form id="login_form" class="row g-3" action="/index.php" method="POST">
				<div class="form-group col-12">
					<label for="name_field">Name</label>
					<input id="name_field" type="text" class="form-control" name="name" required="required">
				</div>
				<div class="form-group col-12">
					<label for="password_field">Passwort</label>
					<input id="name_field" type="password" class="form-control" name="pw" required="required">
				</div>
				<div class="form-group col-12">
					<label class="form-check-label">
						<input type="checkbox" name="set_remember_me" class="form-check-input">
						Angemeldet bleiben
					</label>
				</div>
				<div class="form-group col-12">
					<button type="submit" class="form-control btn btn-primary">Login</button>
				</div>
				<div class="form-group col-12 text-center">
					<button type="button" class="btn btn-sm" x-data @click="toggleAllForms()">Passwort vergessen?</button>
				</div>
            </form>
			<?
			if (!empty(GOOGLE_CLIENT_ID))
			{
				?>
				<form id="google_login_form" class="row g-3 mt-4" action="/index.php" method="POST">
					<input type="hidden" name="google_user_id_token">
					<div class="col-12 text-center">
						<p>Oder melde dich mit deinem Google-Account an:</p>
						<div class="g-signin2 d-inline-block" data-onsuccess="LogInViaGoogle"></div>
					</div>
					<div class="col-12">
						<label class="form-check-label">
							<input type="checkbox" name="set_remember_me" class="form-check-input">
							Angemeldet bleiben
						</label>
					</div>
					<div class="col-12 text-center google_signed_in d-none">
						<button type="submit" class="form-control btn btn-primary">Login via Google</button>
					</div>
				</form>
				<?
			}
			?>
            <form id="forgot_pw_form" class="row g-3 d-none" action="/login.php" method="POST">
				<div class="form-group col-8">
					<label for="email_field">Hinterlegte E-Mail-Adresse</label>
					<input id="email_field" type="email" class="form-control" placeholder="Deine E-Mail-Adresse" name="password_email" required="required">
				</div>
				<div class="form-group col-4">
					<label>&nbsp;</label>
					<button type="submit" class="form-control btn btn-primary">Senden</button>
				</div>
				<div class="col-12 text-center">
					<button type="button" class="btn btn-sm" x-data @click="toggleAllForms()">zurück</button>
				</div>
			</form>
		</article>
		<article class="container-md text-center py-5">
            <p>Noch keinen Account?</p>
			<a class="btn btn-outline-secondary" href="/welcome.php">Zum Registrieren hier!</a>
		</article>
	</section>
<? $twig->display('footer.twig');