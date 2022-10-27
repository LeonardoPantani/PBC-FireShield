<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

if($recaptcha_check) {
	require_once '2FACode/GoogleAuthenticator.php';
	$ga = new PHPGangsta_GoogleAuthenticator();
}

kickLoggedUser();

if(isset($_GET["fl"])) {
	$fl = $_GET["fl"];
}

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
	
	<?php if($recaptcha_check) { ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $recaptcha_sitekey3; ?>"></script>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('<?php echo $recaptcha_sitekey3; ?>', { action: 'login' }).then(function (token) {
                var recaptchaResponse = document.getElementById('recaptchaResponse');
                recaptchaResponse.value = token;
				<?php if($login) { ?>
					$("#buttonlogin").prop('disabled', false);
				<?php } ?>
            });
        });
    </script>
	<?php } ?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<style> .popover-header { color:black; } </style>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
		<!-- AVVISO IN CASO DI ERRORI -->
		<?php if (isset($_GET["e"])) { ?>
		<div class="container text-center">
		<div class="card text-white bg-danger mb-2" style="width: 100%;">
		  <div class="card-body">
			<h5 class="card-text text-center"><?php echo _("Si è verificato un errore"); ?></h5>
			<p class="card-text text-center">
				<?php if ($_GET["e"] == 1) {echo _("Login disabilitati.");} elseif ($_GET["e"] == 2) {echo _("Imposta tutti i parametri.");} elseif ($_GET["e"] == 3) {echo _("Username errato.");} elseif ($_GET["e"] == 4) {echo _("Password errata.");} elseif ($_GET["e"] == 5) {echo _("Errore cercando di impostare la sessione.");} elseif ($_GET["e"] == 6) {echo _("Errore token, impossibile continuare.");} elseif ($_GET["e"] == 7) {echo _("Login richiesto per accedere a questa zona.");} elseif ($_GET["e"] == 8) {echo _("Login richiesto per effettuare scansioni.");} elseif ($_GET["e"] == 9) {echo _("Codice 2FA richiesto per il login.");} elseif ($_GET["e"] == 10) {echo _("Verifica codice 2FA fallita.");} elseif ($_GET["e"] == 11) {echo _("Questo account è disabilitato.")." "._("Contatta lo staff per maggiori informazioni");} elseif ($_GET["e"] == 12) {echo _("Questo account è in utilizzo da un'altra posizione e per questo motivo sei stato disconnesso.");} elseif ($_GET["e"] == 13) {echo _("È stata rilevata un'attività insolita del tuo client, riprova più tardi.");}?>
			</p>
		  </div>
		</div>
		</div>	<?php } ?>
		<!-- FINE AVVISO -->
		
        <form id="form" name="form" action="loginDB.php" method="POST">
			<?php if($recaptcha_check) { ?><input type="hidden" name="recaptcha_response" id="recaptchaResponse"><?php } ?>
            <input name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo">
                <h2><?php echo _("Login"); ?></h2>
				<?php if($mobile) { ?>
					<div class="card text-white bg-danger mb-3" style="width: 100%;">
					  <div class="card-body">
						<h5 class="card-text text-left"><?php echo _("Avviso di compatibilità"); ?></h5>
						<p class="card-text text-left"><?php echo $solutionname." "; echo _("è ottimizzato per l'utilizzo Desktop. La navigazione con un dispositivo mobile potrebbe causare problemi grafici o di funzionalità"); ?>.</p>
					  </div>
					</div>
				<?php } else { ?>
					<br>
				<?php } ?>
                <div class="container text-center" style="width: 60%">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-person"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" name="username" placeholder="<?php echo _('Username'); ?>" required></input>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-key"></span>
                        </div>
                        </div>
                        <input class="form-control" type="password" name="password" placeholder="<?php echo _('Password'); ?>" required></input>
                    </div>
                    <?php if ($twofactor == true && $fl != 1) { // se l'autenticazione a 2 fattori è attiva oppure se è il primo login ?> 
                    <br>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-signpost"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" name="secretcode" placeholder="<?php echo _('Codice Segreto 2FA'); ?>" autocomplete="off"></input>
					    <div class="input-group-append">
							<div class="input-group-text">
							  <a data-toggle="popover" data-trigger="hover" title="<?php echo _('Codice 2FA non valido o smarrito?'); ?>" data-html="true" data-content="<?php echo _('Mandaci una mail a questo indirizzo'); ?>: <br><b><?php echo $issuesmail; ?></b>."><span class="oi oi-question-mark"></span></a>
							</div>
						</div>
                    </div>
                    <?php }?>
                </div>
                <small><?php echo _("Password dimenticata?"); ?> <a href="Password/passwordRecovery.php"><?php echo _("Clicca qui!"); ?></a></small>
				<br>
                <button disabled id="buttonlogin" <?php if (!$login) {?>disabled title='<?php echo _("Login disabilitati."); ?>.' <?php }?> class="btn btn-success" type="submit" name="send"><?php echo _("Effettua il Login"); ?></button>
                <br><br>
				<p><?php echo _("Compra una licenza"); ?> <a href="buyLicense/buy.php"><?php echo _("qui"); ?></a> <?php echo _("per poter accedere"); ?>.</p>
			</div>
        </form>
    </div>
	<br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
	document.getElementById('jquery').addEventListener('load', function() {	
		// Inizializzazione Popup
		$(function () {
			$('[data-toggle="popover"]').popover();
		});
		
		<?php if(!$recaptcha_check) { ?>
			$("#buttonlogin").prop('disabled', false);
		<?php } ?>
		
		$("#form").submit(function(e) {
			$("#buttonlogin").text("<?php echo _('Caricamento'); ?>...");
		});
	});
	
</script>