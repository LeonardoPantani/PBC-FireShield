<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";
require_once 'GoogleAuthenticator.php';

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();

licenseInformation($licenza, true);
networkInformation($infonetwork);

// CONTROLLO CODICE GOOGLE AUTHENTICATOR
$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $ga->createSecret();
$qr = $ga->getQRCodeGoogleUrl($solutionname." (" . $username . ")", $secret);
$code = $ga->getCode($secret);
$result = $ga->verifyCode($secret, $code, 3);
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
    <div class="container-fluid text-center text-white" style="width:90%;">
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="10%" alt="Logo"><h3 style="display:inline;"><?php echo _("Autenticazione a 2 fattori (2FA)"); ?></h3>
			<br>
			<?php if(!hasTwoFactorAuthentication($id)) {
				echo "<img title='"._("Codice QR")."' src='" . $qr . "'><br>";
				if ($result != 1) {
					header("Refresh:0");
				}
			?>
			<b><?php echo $secret; ?></b>
			<br><br>
			<p>
				<?php echo _("Un codice 2FA è un insieme di 6 cifre casuali generate ogni 30 secondi dall'app Google Authenticator"); ?>.
				<br>
				<font color="lightgreen"><?php echo _("Se non sai cosa fare, consulta la sezione 'Tutorial' scorrendo questa pagina verso il basso"); ?>.</font>
			</p>
			<form id="form" name="form" action="create2FADB.php" method="POST">
				<div class="container text-center" style="width: 50%">
					<div class="input-group mb-3">
						<div class="input-group-prepend">
						<div class="input-group-text">
							<span class="oi oi-text"></span>
						</div>
						</div>
						<input class="form-control" type="text" id="secret" name="secret" placeholder="<?php echo _('Codice 2FA'); ?>" autocomplete="off" required></input>
					</div>
				</div>
				<br>
				<input type="text" id="secret_generated" name="secret_generated" style="display:none;" value="<?php echo $secret; ?>"></input>
				<button class="btn btn-success" type="submit" name="conferma"><?php echo _("Conferma codice QR"); ?></button>
			</form>
			<hr>
			<h3><?php echo _("Tutorial"); ?></h3>
			<div class="container-fluid text-left">
			<p>
			<ol>
				<li><?php echo _("L'app Google Authenticator è scaricabile sia per dispositivi"); ?> <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=it"><?php echo _("Android"); ?></a> <?php echo _("che per dispositivi"); ?> <a href="https://itunes.apple.com/it/app/google-authenticator/id388497605?mt=8"><?php echo _("Apple"); ?></a>.</li>
				<li><?php echo _("Una volta scaricata l'applicazione, cliccare sul tasto '+' in basso a destra e selezionare l'opzione 'Leggi codice a barre'"); ?>.<br>(<?php echo _("Qualora il dispositivo fosse sprovvisto di fotocamera selezionare l'opzione 'Inserisci un codice fornito' ed inserire questo testo:"); echo " <b>".$secret."</b>"; ?>)</li>
				<li><?php echo _("Inquadra il codice QR con la fotocamera in modo che il quadrato di selezione combaci con il codice QR"); ?>.</li>
				<li><?php echo _("Una volta completata la procedura comparirà un riquadro contenente un codice di 6 cifre casuale e sotto di esso il nome dell'account associato a")." ".$solutionname; ?>.</li>
				<li><?php echo _("Inserire il codice fornito dall'app dentro il riquadro 'Codice 2FA' nel campo soprastante"); ?>.</li>
				<li><?php echo _("Verificare che nel codice non ci siano errori e cliccare su 'Conferma codice QR'"); ?>.</li>
				<li><?php echo _("Una volta completata questa procedura sarà richiesto di nuovo il login, dove questa volta dovrai inserire anche il nuovo codice fornito oltre all'Username e la Password"); ?>.</li>
			</ol>
			</p>
			<!-- AVVERTIMENTO -->
			<div class="card text-white bg-danger mb-3" style="width: 100%;">
			  <div class="card-body">
				<h5 class="card-text text-left"><?php echo _("Avvertimento"); ?></h5>
				<p class="card-text text-left"><?php echo _("I codici di Google scadono ogni 30 secondi, pertanto bisogna essere sicuri di aver cliccato il pulsante 'Conferma codice QR' prima che il codice fornito scada."); ?></p>
			  </div>
			</div>
			<!-- In caso di problemi -->
			<div class="container text-center">
				<?php echo _("In caso di problemi con il codice 2FA, sei pregato di scriverci"); ?> <a target="_blank" href="mailto:<?php echo $issuesmail; ?>"><?php echo _("una Email"); ?></a>.
			</div>
			</div>
			<?php } else { ?>
				<br><br>
				<h5 style="color:lightcoral;"><?php echo _("L'autenticazione a 2 fattori è già attiva, non è necessario rieffettuare l'operazione"); ?>.</h5>
				<br><br>
				<button class="btn btn-secondary" type="button" onClick="location.href='/'"><?php echo _("Torna alla Homepage"); ?></button>
				<br>
			<?php } ?>
			<br>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
document.getElementById('jquery').addEventListener('load', function() {
    $("#form").submit(function(e) {
        e.preventDefault();
		swal({
		  title: "<?php echo _('Attendi'); ?>",
		  text: "<?php echo _("Elaborazione richiesta"); ?>...",
		  type: "info"
		});
		if(!$("#secret").val().match(/^[\d\s]+$/)) {
			swal({
			  title: "<?php echo _('Si è verificato un errore'); ?>",
			  text: "<?php echo _("Il codice inserito non è valido"); ?>.",
			  type: "error"
			});
		} else {
			$.post('create2FADB.php', {
				secret_generated: $("#secret_generated").val(),
				secret: $("#secret").val()
			},
			function(result) {
				if(result == "success") {
					swal({
					  title: "<?php echo _('Successo'); ?>",
					  text: "<?php echo _("Il codice è stato confermato"); ?>.",
					  type: "success"
					});
					setTimeout( function(){
						location.href = "2FARecovery.php";
					}, 1500);
				} else if (result == "failed") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("Il codice inserito è errato, controlla di averlo inserito correttamente e che non sia scaduto"); ?>.",
					  type: "error"
					});
				} else if (result == "failed_2") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("Non è stato possibile salvare il codice nel database"); ?>.",
					  type: "error"
					});
				} else if (result == "failed_3") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("Non è stato possibile salvare i codici di recupero nel database"); ?>.",
					  type: "error"
					});
				} else if (result == "unset") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("Inserisci il valore segreto dall'app Google Authenticator"); ?>.",
					  type: "error"
					});
				} else if (result == "noid") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("Utente inesistente"); ?>.",
					  type: "error"
					});
				} else {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: result,
					  type: "error"
					});
				}
			}
			);	
		}
    });
});
</script>