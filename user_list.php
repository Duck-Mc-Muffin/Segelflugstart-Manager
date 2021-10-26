<? require_once __DIR__ . '/src/general.php';
global $db, $date_formatter_user_list;

// Login status
if (!CheckLogin())
{
	header("location: /login.php");
	exit;
}

// Check Permission
$current_user = GetSessionUser();
if (empty($current_user->is_moderator))
{
	header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
    exit;
}

// Approve User
if (!empty($_REQUEST["approve_user"]))
{
	$user_to_approve = User::GetByID($_REQUEST["approve_user"]);
	if (empty($user_to_approve)) $_SESSION["error"] = "Nutzer wurde nicht in der Datenbank gefunden (ID " . $_REQUEST["approve_user"] . ")";
	else
	{
		// Set Login Hash
		$query = $db->prepare('UPDATE user SET is_approved = 1 WHERE id = :id');
		$query->bindParam(':id', $user_to_approve->id);
		$query->execute();
	}
}

// Data
require_once __DIR__ . '/src/data.php';
$unapproved_users = GetUnapprovedUserList();

// Main page
?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
	<title>Nutzerliste</title>
	<? require __DIR__ . '/src/templates/head.php'; ?>
</head>
<body class="d-flex flex-column h-100">
	<header class="container-lg my-3 text-center">
		<h1>Nicht bestätigte Nutzer</h1>
	</header>
	<section class="container-lg">
		<article>
			<?
			if (empty($unapproved_users))
			{
				?>
				<p class="text-center"><em>Keine unbestätigten Nutzer in der Warteschlange.</em></p>
				<?
			}
			else
			{
				?>
				<div id="user_list" class="list-group">
                	<?
					foreach($unapproved_users as $user)
					{
						?>
						<a href="/user_list.php?approve_user=<?= $user->id ?>"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            x-data="are_you_sure('Bist du sicher, dass du den Nutzer bestätigen willst?')" @click="prompt">
							<?= $user->name ?>
							<span>
								<span class="badge bg-secondary rounded-pill">
									<?= $date_formatter_user_list->format($user->inserted_at) ?>
								</span>
							</span>
						</a>
						<?
					}
					?>
				</div>
				<?
			}
			?>
		</article>
	</section>
	<div class="text-center py-3">
		<a class="btn btn-outline-secondary btn-sm" href="/index.php"><i class="fas fa-arrow-left"></i> zurück</a>
	</div>
<? include __DIR__ . '/src/templates/footer.php';