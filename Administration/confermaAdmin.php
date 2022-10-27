<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickNonAdminUser();

networkInformation($infonetwork);

if ($twofactor == true) {
    $CSRFtoken = bin2hex(openssl_random_pseudo_bytes(16));
    $esito = setcookie("CSRFtoken", $CSRFtoken, time() + 60 * 60 * 24, "", "", true, true); //Imposto cookie di sicurezza contro vulnerabilità CSRF
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Conferma identità"); ?> <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <form id="form" name="form" action="Utils/confermaAdminDB.php" method="POST">
            <input name="CSRFtoken" id="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"> <h3 style="display: inline-block;"><?php echo _("Conferma la tua identità"); ?></h3>
                <br><br><br>
				<div class="container" style="width:50%;">
					<p>
						<?php echo sprintf(_("Ciao, %s. Prima di farti accedere dobbiamo accertarci che sia proprio tu, visto che stai tentando di entrare in un'area riservata."), '<b><font style="color:gold;">'.$username.'</font></b>'); ?>
						<br><br>
						<?php echo _("Per favore inserisci di nuovo il codice 2FA per continuare."); ?>
					</p>
				</div>
				<div class="container text-center" style="width: 50%">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-key"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" name="secretcode" id="secretcode" placeholder="<?php echo _("Conferma l'identità inserendo il codice 2FA"); ?>" autocomplete="off" required></input>
                    </div>
                </div>
				<br>
				<button id="form_submit" class="btn btn-success" type="submit" name="send"><?php echo _("Effettua l'accesso al pannello"); ?></button>
				<br><br>
            </div>
        </form>
    </div>
</body>
</html>
<script>
    document.getElementById('jquery').addEventListener('load', function() {
        $("#form").submit(function(e) {
			e.preventDefault();
			swal({
			  title: "<?php echo _("Attendi..."); ?>",
			  text: "<?php echo _("Verifico il codice che hai inserito"); ?>",
			  type: "info"
			});
			$.post('Utils/confermaAdminDB.php', {
				secretcode: $("#secretcode").val(),
				CSRFtoken: $("#CSRFtoken").val()
			},
			function(result) {
				if(result == "success") {
					swal({
					  title: "<?php echo _("Successo"); ?>",
					  text: "<?php echo _("Trasferimento al pannello di amministrazione in corso..."); ?>",
					  type: "success"
					});
					setTimeout( function(){
						location.href = "pannello.php";
					}, 2500 );
				} else if(result == "failure") {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Il codice che hai inserito è errato"); ?>",
					  type: "error"
					});
				} else if(result == "unset") {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Imposta tutti i parametri."); ?>",
					  type: "error"
					});
				} else if(result == "token_error") {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Errore token: Impossibile continuare"); ?>",
					  type: "error"
					});
				} else {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: result,
					  type: "error"
					});
				}
			}
			);
		});
	});
</script>
