<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

if (isLogged()) {
    header("Location:/");
    exit;
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
				<?php if ($_GET["e"] == 1) {echo _("Login OTP disabilitati.");} elseif ($_GET["e"] == 2) {echo _("Imposta tutti i parametri.");} elseif ($_GET["e"] == 3) {echo _("Codice OTP non valido.");} elseif ($_GET["e"] == 4) {echo _("Errore token, impossibile continuare.");} elseif ($_GET["e"] == 5) {echo _("Errore cercando di impostare la sessione.");} elseif ($_GET["e"] == 6) {echo _("Questo account è disabilitato.")." "._("Contatta lo staff per maggiori informazioni");} elseif ($_GET["e"] == 7) {echo _("L'account principale non ha l'autenticazione a 2 fattori abilitata.");}?>
			</p>
		  </div>
		</div>
		</div>
		<?php } ?>
		<!-- FINE AVVISO -->
		
        <form id="form" name="form" action="loginOTPDB.php" method="POST">
            <input name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo">
                <h2><?php echo _("Login"); ?> OTP</h2>
				<div class="container text-center" style="width: 75%">
					<p><?php echo _("Benvenuto nel login OTP del")." <b>".$solutionname."</b>"; ?>!
					<br><br>
					<?php echo _("Se sei un utente aspetta che il membro dello staff inserisca qui il suo codice OTP (One Time Password)"); ?>.
					<br>
					<?php echo _("Se sei un membro dello staff inserisci il codice OTP fornito da")." <a href='settings.php'>"._("questa pagina")."</a> "._("per continuare"); ?>.</p>
				</div>
                <br>
                <div class="container text-center" style="width: 40%">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-lock-locked"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" name="otp" placeholder="<?php echo _('Codice OTP (One time password)'); ?>" autocomplete="off" required></input>
					    <div class="input-group-append">
							<div class="input-group-text">
							  <a data-toggle="popover" data-trigger="hover" title="<?php echo _('Cosa devo fare qui?'); ?>" data-html="true" data-content="<?php echo _('Inserisci il codice monouso creato dalla relativa sezione nella pagina Impostazioni'); ?>."><span class="oi oi-question-mark"></span></a>
							</div>
						</div>
                    </div>
                </div>
				<br>
                <button id="buttonlogin" <?php if (!$loginotp) {?>disabled title='<?php echo _("Login OTP disabilitati."); ?>.' <?php }?> class="btn btn-success" type="submit" name="send"><?php echo _("Effettua il Login sicuro"); ?></button>
                <br><br>
			</div>
        </form>
    </div>
	<br><br><br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
	document.getElementById('jquery').addEventListener('load', function() {	
		// Inizializzazione Popup
		$(function () {
			$('[data-toggle="popover"]').popover();
		});
		
		$("#form").submit(function(e) {
			$("#buttonlogin").text("<?php echo _('Caricamento'); ?>...");
		});
	});
	
</script>