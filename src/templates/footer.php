<footer class="footer mt-auto text-center text-white" style="background-color: #f1f1f1;">
	<?
	$current_user = GetSessionUser();
	if (!empty($current_user))
	{
		?>
		<div class="container pt-4">
			<section class="mb-4">
				<a
					class="btn btn-sm btn-outline-primary btn-floating m-1"
					href="/calendar.php"
					role="button">
					<i class="far fa-calendar"></i> Kalender
				</a>
				<?
					if ($current_user->is_moderator)
					{
						?>
						<a
							class="btn btn-sm btn-outline-secondary btn-floating m-1"
							href="/user_list.php"
							role="button">
							<i title="Gast" class="far fa-user"></i> Nutzer bestätigen
						</a>
						<?
					}
				?>
				<a
					class="btn btn-sm btn-outline-secondary btn-floating m-1"
					href="/user.php"
					role="button">
					<i class="far fa-edit"></i> Account
				</a>
				<a
					class="btn btn-sm btn-outline-danger btn-floating m-1"
					href="?logout"
					role="button">
					<i class="fas fa-sign-out-alt"></i> Logout
				</a>
			</section>
		</div>
		<?
	}
	?>
	<div class="text-center text-dark p-3" style="background-color: rgba(0, 0, 0, 0.2);">
		<a class="btn link-secondary" href="https://github.com/Duck-Mc-Muffin/Segelflugstart-Manager">Segelflugstart-Manager auf GitHub <i class="fab fa-github"></i></a>
		<br><small class="form-text text-muted">made by Steven Pauls</small>
		<br><a class="btn link-secondary" href="/impressum_und_datenschutz.php">Impressum & Datenschutzerklärung</a>
	</div>
</footer>
<?
if (!empty($_SESSION["error"]))
{
	$_SESSION["error"] = str_replace("\n", '<br/>', $_SESSION["error"]);
	?>
	<div class="toast bg-danger text-white border-0" id="error_toast" style="position: absolute; top: 3%; right: 3%;" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="toast-header">
			<strong class="me-auto">Fehler</strong>
			<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
		</div>
		<div class="toast-body">
			<?= $_SESSION["error"] ?>
		</div>
	</div>
	<?
	unset($_SESSION["error"]);
}
?>
<script type="text/javascript" src="lib/bootstrap/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="lib/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="js/main.min.js"></script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
</body>
</html>