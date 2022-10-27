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
<style> .popover-header { color:black; } </style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
		<!-- AVVISO -->
		<?php if (isset($_GET["e"])) { ?>
		<div class="container text-center">
		<div class="card text-white bg-<?php if($_GET["e"] == 7) { echo "success"; } else { echo "danger"; } ?> mb-2" style="width: 100%;">
		  <div class="card-body">
			<h5 class="card-text text-center"><?php if($_GET["e"] == 7) { echo _("Grazie per la proposta!"); } else { echo _("Si è verificato un errore."); } ?></h5>
			<p class="card-text text-center">
				<?php if ($_GET["e"] == 1) {echo _("Proposte disabilitate.");} elseif ($_GET["e"] == 2) { echo _("Imposta tutti i parametri.");} elseif ($_GET["e"] == 3) { echo _("Token non valido, impossibile continuare.");} elseif ($_GET["e"] == 4) { echo _("Imposta tutti i campi.");} elseif ($_GET["e"] == 5) { echo _("Tipo stringa non valido.");} elseif ($_GET["e"] == 6) { echo _("Errore di esecuzione interno.");} elseif ($_GET["e"] == 7) { echo _("La tua proposta sarà visionata quanto prima da un nostro membro dello staff.");} ?>
			</p>
		  </div>
		</div>
		</div>
		<?php } ?>
		<!-- FINE AVVISO -->
		
        <form id="form" name="form" action="suggestStringDB.php" method="POST">
			<input name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>"></input>
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="12%" alt="Logo">
                <h2><?php echo _("Proponi una Stringa"); ?></h2>
                <div class="container" style="width: 90%">
                    <p><?php echo sprintf(_("In questo form puoi proporre una stringa che identifica un client. In seguito all'invio, verificheremo noi la validità della proposta.%sGrazie per l'aiuto a rendere %s sempre migliore!"), "<br>", "<b>".$solutionname_short."</b>"); ?></p>
                </div>
				<br>
                <div class="container text-center" style="width: 60%">
					<select class="form-control" name="tipostringa" <?php if (!$stringsuggestions) {echo "disabled";}?>>
						<option value="null"><?php echo _("Seleziona il tipo di processo"); ?></option>
						<option value="javaw">Javaw.exe</option>
						<option value="dwm">Dwm.exe</option>
						<option value="msmpeng">MsMpEng.exe</option>
						<option value="lsass">lsass.exe</option>
						<option value="other"><?php echo _("Altro"); ?></option>
					</select>
					<br>
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-flag"></span>
							</div>
						</div>
						<input <?php if (!$stringsuggestions) {echo "disabled";}?> class="form-control" type="text" name="stringa" id="stringa" placeholder="<?php echo _("Inserisci la stringa che identifica il cheat"); ?>" required>
					</div>
					<br>
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-laptop"></span>
							</div>
						</div>
						<input <?php if (!$stringsuggestions) {echo "disabled";}?> class="form-control" type="text" name="client" id="client" placeholder="<?php echo _("Inserisci il nome del client identificato dalla stringa"); ?>" required></input>
					</div>
					<br>
					<textarea <?php if (!$stringsuggestions) {echo "disabled";}?> maxlength="1000" class="form-control" rows="5" name="note" placeholder="<?php if (!$stringsuggestions) {echo _("Proposte disabilitate.");} else {echo _("Note riguardanti la proposta, limite caratteri: 1000");}?>" required></textarea>
				</div>
                <br>
                <button <?php if(!$stringsuggestions) { echo "disabled"; } ?> class="btn btn-success" type="submit" name="send"><?php echo _("Invia proposta"); ?></button>
                <br><br>
            </div>
        </form>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>