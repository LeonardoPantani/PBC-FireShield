<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

kickLoggedUser();
// -----------------------------

if ($twofactor == true) {
    $CSRFtoken = bin2hex(openssl_random_pseudo_bytes(16));
    $esito = setcookie("CSRFtoken", $CSRFtoken, time() + 60 * 60 * 24, "", "", true, true); //Imposto cookie di sicurezza contro vulnerabilità CSRF
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
	<?php if($recaptcha_check) { ?><script src='https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>'></script><?php } ?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
		<!-- AVVISO -->
		<?php if (isset($_GET["e"])) { ?>
		<div class="container text-center">
		<div class="card text-white bg-<?php if($_GET["e"] == 8) { echo 'success'; } else { echo 'danger'; } ?> mb-2" style="width: 100%;">
		  <div class="card-body">
			<h5 class="card-text text-center"><?php if($_GET["e"] == 8) { echo _("Mail inviata")."!"; } else { echo _("Si è verificato un errore"); } ?></h5>
			<p class="card-text text-center">
				<?php if ($_GET["e"] == 1) {echo _("Compila tutti i campi.");} elseif ($_GET["e"] == 2) {echo _("Imposta tutti i campi.");} elseif ($_GET["e"] == 3) {echo _("Username non trovato.");} elseif ($_GET["e"] == 4) {echo _("Questo Username non ha un indirizzo Email assegnato. Impossibile procedere.");} elseif ($_GET["e"] == 5) {echo _("Impossibile impostare il token di recupero.");} elseif ($_GET["e"] == 6) {echo _("Non è stato possibile inviare la mail.");} elseif ($_GET["e"] == 7) {echo _("Verifica reCaptcha fallita.");} elseif ($_GET["e"] == 8) {echo _("Controlla la casella di Posta.");}?>
			</p>
		  </div>
		</div>
		</div>
		<?php } ?>
		<!-- FINE AVVISO -->
		
        <form id="form" name="form" action="passwordRecoveryDB.php" method="POST">
            <input name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"> <span><b><?php echo _("Recupero Password"); ?></b></span>
                <br>
				<p><?php echo _("Inserisci qui l'Username dell'account che vuoi recuperare."); ?><br><?php echo _("Prepara la casella di Posta perché ti invieremo una mail per recuperare la tua Password."); ?></p>
                <div class="container text-center" style="width: 40%">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-person"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" id="username" name="username" placeholder="<?php echo _('Username utilizzato'); ?>" autocomplete="off" required></input>
                    </div>
					<?php if($recaptcha_check) { ?>
					<div class="text-xs-center">
						<div style="display: inline-block;" name="recaptchaResponse" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_sitekey2; ?>"><?php echo _("Caricamento"); ?>...</div>
					</div>
					<?php } ?>
                </div>
                <span id="esito"><br></span>
                <br>
                <button class="btn btn-success" type="submit" name="invio"><?php echo _("Invia mail di recupero"); ?></button>
                <br><br>
            </div>
        </form>
    </div>
	<br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
	// #spaghetticode
    document.getElementById('jquery').addEventListener('load', function() {
        $("#form").submit(function(e) {
			var form = this;
			e.preventDefault();
			<?php if($recaptcha_check) { ?>
			if(grecaptcha.getResponse().length == 0) {
				$("#esito").css("color", "lightcoral");
				$("#esito").html("<?php echo _('Esegui il reCaptcha per poter continuare'); ?>.<br>");
			} else {
			<?php } ?>
				$.post('../Utils/checkUsername.php', {
						usernamescelto: $("#username").val()
					},
					function(result) {
						if(result == "found") {
							$("#esito").css("color", "lightgreen");
							$("#esito").html("<?php echo _('Username confermato!'); ?><br>");
							form.submit();
						} else if(result == "not_found") {
							$("#esito").css("color", "lightcoral");
							$("#esito").html("<?php echo _('Username non trovato'); ?>.<br>");
						} else if(result == "unset") {
							$("#esito").css("color", "lightcoral");
							$("#esito").html("<?php echo _('Inserisci uno Username'); ?>.<br>");
						} else {
							$("#esito").css("color", "white");
							$("#esito").html("<?php echo _('Caso imprevisto'); ?>: "+result+"<br>");
						}
					}
				);
			<?php if($recaptcha_check) { ?>}<?php } ?>
        });
    });
</script>