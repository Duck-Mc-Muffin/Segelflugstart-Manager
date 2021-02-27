<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Passwort vergessen?</title>
</head>
<body>
    <h3>Hallo <?= $to_name ?>,</h3>
    <p>
        scheint so als hättest du dein Passwort für den "inoffiziellen Segelflugstart-Manager" vergessen.<br>
		Mit dem folgenden Link kannst du dich in deinem Account wieder einloggen und ein neues Passwort setzen:<br><br>
		<a href="<?= $data["link"] ?>"><?= $data["link"] ?></a><br><br>
		Wenn du dein Passwort nicht vergessen hast, dann ignoriere diese E-Mail.
    </p>
</body>
</html>