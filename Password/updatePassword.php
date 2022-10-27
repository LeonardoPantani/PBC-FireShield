<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();

licenseInformation($licenza);
networkInformation($infonetwork);

$stmt = $connection->prepare("SELECT ID FROM $table_users WHERE AggiornaPassword = 1 AND ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
if($nrighe == 0) {
    header("Location:/");
    exit;
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
		<!-- AVVISO -->
		<?php if (isset($_GET["e"])) { ?>
		<div class="container text-center">
		<div class="card text-white bg-danger mb-2" style="width: 100%;">
		  <div class="card-body">
			<h5 class="card-text text-center"><?php echo _("Si è verificato un errore"); ?></h5>
			<p class="card-text text-center">
				<?php if ($_GET["e"] == 1) {echo _("Compila tutti i campi.");} elseif ($_GET["e"] == 2) {echo _("Le password non devono essere vuote e devono essere uguali.");} elseif ($_GET["e"] == 3) {echo _("Non è stato possibile aggiornare la password a causa di un problema interno.");} elseif ($_GET["e"] == 4) { echo _("La password vecchia e quella nuova non devono corrispondere.");} ?>
			</p>
		  </div>
		</div>
		</div>
		<?php } ?>
		<!-- FINE AVVISO -->
		
        <form id="form" name="form" action="updatePasswordDB.php" method="POST">
            <input name="token" type="hidden" style="display: none;" value="<?php echo $_GET['token']; ?>">
            <input name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"> <span><b><?php echo _("Aggiornamento Password"); ?></b></span>
                <br>
				<div class="container" style="width:50%;">
					<p><?php echo _("Un amministratore ha richiesto che cambi la tua password per motivi di sicurezza. La procedura è obbligatoria per poter continuare"); ?>.</p>
				</div>
				<br>
				<p><?php echo _("Inserisci la nuova password nei campi qui sotto."); ?></p>
                <div class="container text-center" style="width: 40%">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-key"></span>
                        </div>
                        </div>
                        <input class="form-control" type="password" id="password1" name="password1" placeholder="<?php echo _('Nuova Password'); ?>" autocomplete="off" required></input>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-key"></span>
                        </div>
                        </div>
                        <input class="form-control" type="password" id="password2" name="password2" placeholder="<?php echo _('Ripeti Nuova Password'); ?>" autocomplete="off" required></input>
                    </div>
                </div>
                <span id="esito"></span>
                <br><br>
                <button class="btn btn-success" type="submit" name="invio"><?php echo _("Aggiorna Password"); ?></button>
                <br><br>
            </div>
        </form>
    </div>
	<br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
    document.getElementById('jquery').addEventListener('load', function() {
        $("#form").submit(function(e) {
			var form = this;
			e.preventDefault();
            if($("#password1").val() == $("#password2").val()) {
                if($("#password1").val().length >= <?php echo $password_minlength; ?> && $("#password1").val().length <= <?php echo $password_maxlength; ?>) {
                    $("#esito").css("color", "lightgreen");
                    $("#esito").html("<?php echo _('Password confermate!'); ?>");
                    form.submit();
			    } else {
				    $("#esito").css("color", "red");
				    $("#esito").html("<?php echo _('Le password devono avere una lunghezza tra i'); echo " ".$password_minlength; ?> <?php echo _('ed i'); echo " ".$password_maxlength; echo _(' caratteri'); ?>.");
                }
            } else {
                $("#esito").css("color", "red");
                $("#esito").html("<?php echo _('Le password non corrispondono'); ?>.");
            }
        });
    });
</script>