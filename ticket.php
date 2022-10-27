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
			<h5 class="card-text text-center"><?php if($_GET["e"] == 7) { echo _("Grazie per la segnalazione")."!"; } else { echo _("Si è verificato un errore"); } ?></h5>
			<p class="card-text text-center">
				<?php if ($_GET["e"] == 1) {echo _("Segnalazioni disabilitate.");} elseif ($_GET["e"] == 2) {echo _("Imposta tutti i parametri.");} elseif ($_GET["e"] == 3) {echo _("Un altro ticket risulta già inviato, attendi che l'altro venga chiuso");} elseif ($_GET["e"] == 4) {echo _("Si è verificato un errore di esecuzione interno.");} elseif ($_GET["e"] == 5) {echo _("Si è verificato un errore di token, impossibile continuare.");} elseif ($_GET["e"] == 6) {echo _("Seleziona il tipo di ticket da inviare."); } elseif ($_GET["e"] == 7) {echo _("Il tuo aiuto è fondamentale per rendere questa piattaforma sempre migliore."); } ?>
			</p>
		  </div>
		</div>
		</div>
		<?php } ?>
		<!-- FINE AVVISO -->
		
        <form id="form" name="form" action="ticketDB.php" method="POST">
			<input name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>"></input>
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="12%" alt="Logo">
                <h2><?php echo _("Apri un Ticket"); ?></h2>
                <?php
                    $stmt = $connection->prepare("SELECT ID, Spiegazione, Data, Stato, Risposta, TelegramUsername, TipoRichiesta FROM $table_tickets WHERE IDUtente = ? AND (Stato = 1 OR Stato = 2)");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $esito = $stmt->get_result();
                    $riga = $esito->fetch_assoc();
                    $nrighe = $esito->num_rows;
                    if ($nrighe > 0) {
                        if ($riga["Stato"] == 1) {
                            ?>
							<div class="card text-white bg-dark border-danger mb-3">
							  <div class="card-body">
								<p class="card-text text-left">
									<b style="color:lightcoral;"><?php echo _("Un altro ticket risulta già inviato, attendi che venga chiuso"); ?>.</b>
									<br><br>
									<b><?php echo _("ID ticket"); ?>:</b> <?php echo $riga["ID"]; ?>
									<br>
									<b><?php echo _("Tipo ticket"); ?>:</b> <?php if($riga["TipoRichiesta"] == "question") { echo _("Domanda"); } elseif($riga["TipoRichiesta"] == "bug") { echo _("Bug / Errore"); } elseif($riga["TipoRichiesta"] == "falseflag") { echo _("Falso positivo"); } elseif($riga["TipoRichiesta"] == "other") { echo _("Altro"); } else { echo "???"; } ?>
									<br>
									<b><?php echo _("Spiegazione ticket"); ?>:</b> <?php echo $riga["Spiegazione"]; ?>
									<br>
									<b><?php echo _("Username di Telegram"); ?>:</b> <?php if($riga["TelegramUsername"] == "") { echo "("._("Non fornito").")"; } else { echo $riga["TelegramUsername"]; } ?>
									<br>
									<b><?php echo _("Data invio"); ?>:</b> <?php echo formatDate($riga["Data"], true); ?>
								</p>
							  </div>
							</div>
							<hr>
							<?php
                    } else {
						?>
						<div class="card text-white bg-dark border-success mb-3">
						  <div class="card-body">
							<p class="card-text text-left">
								<b style="color:lightgreen;"><?php echo _("Un membro dello Staff ha visto il tuo ticket. Ecco la risposta:"); ?></b>
								<br>
								<?php echo $riga["Risposta"]; ?>
							</p>
						  </div>
						</div>
						<hr>
						<?php
							$stmt = $connection->prepare("UPDATE $table_tickets SET Stato = 0 WHERE IDUtente = ? AND Stato = 2");
							$stmt->bind_param("i", $id);
							$stmt->execute();
							$nrighe = 0;
						}
					}
					?>
                <div class="container" style="width: 90%">
                    <p><?php if($tickets) { echo _("In questo form è possibile richiedere assistenza o segnalare problemi relativi alla piattaforma")." ".$solutionname; ?>. <?php echo _("Una volta inviata la richiesta non sarà possibile inviarne altre finché la precedente non sarà visionata. Ti ringraziamo per la collaborazione."); } ?></p>
                </div>
				<br>
				
				<?php if(!$tickets) { ?>
					<div class="card text-white bg-danger mb-3" style="width: 100%;">
					  <div class="card-body">
						<h5 class="card-text text-left"><?php echo _("Tickets disabilitati"); ?></h5>
						<p class="card-text text-left"><?php echo _("Attualmente il sistema dei tickets è offline. Ci scusiamo per il disagio."); ?></p>
					  </div>
					</div>
				<?php } else { ?>
					<div class="container text-center" style="width: 60%">
						<select class="form-control" name="tiporichiesta" <?php if($nrighe > 0) { echo "disabled"; } ?>>
						<?php if($nrighe == 0) { ?>
							<option value="null"><?php echo _("Seleziona il tipo di ticket da inviare"); ?></option>
							<option value="bug"><?php echo _("Bug / Errore"); ?></option>
							<option value="falseflag"><?php echo _("Falso positivo"); ?></option>
						<?php } else { ?>
							<option value="null"><?php echo _("In attesa della risposta..."); ?></option>
						<?php } ?>
						</select>
						<br>
						<textarea <?php if ($nrighe > 0) {echo "disabled";}?> maxlength="2500" class="form-control" rows="5" name="spiegazione" placeholder="<?php if ($nrighe > 0) {echo _("In attesa della risposta...");} else {echo _("Descrivi il problema o la richiesta, limite caratteri: 2500");}?>" required></textarea>
						<br>
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<span class="oi oi-target"></span>
								</div>
							</div>
							<input class="form-control" type="text" name="telegramusername" placeholder="<?php if ($nrighe > 0) {echo _("In attesa della risposta...");} else {echo _("Username di Telegram (Opzionale)");}?>"></input>
						</div>
					</div>
					<br>
					<button <?php if ($nrighe > 0) {echo "disabled title='In attesa...'";}?> class="btn btn-success" type="submit" name="send"><?php if ($nrighe > 0) {echo _("In attesa della risposta...");} else {echo _("Apri il ticket");}?></button>
				<?php } ?>
				
				<br>
            </div>
        </form>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>