<? require_once __DIR__ . '/src/general.php'; ?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Datenschutzerklärung | Segelflugstart-Manager</title>
	<? require_once __DIR__ . '/src/templates/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
<header class="container-md my-3">
    <h1 class="text-center">Impressum & Datenschutzerklärung</h1>
	<? RenderFlightDayBtn('zurück'); ?>
</header>
<?
$legal = __DIR__ . '/src/templates/legal.php';
if (is_file($legal)) include $legal;
else
{
    ?>
    <section class="container">
        <div class='alert alert-danger text-center'>
            Impressum und Datenschutz wurde noch nicht eingebunden!
        </div>
    </section>
    <?
}

include __DIR__ . '/src/templates/footer.php';