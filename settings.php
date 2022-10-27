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

if(isset($_GET["interval"])) {
	if($_GET["interval"] == "month" || $_GET["interval"] == "year") {
		$interval = $_GET["interval"];
	} else {
		$interval = "month";
	}
} else {
	$interval = getPreference($id, "Intervallo");
}

if($stmt = $connection -> prepare("SELECT * FROM $table_historyanalyzer WHERE IDUtente = ? AND Data BETWEEN DATE_SUB(NOW(), INTERVAL 1 ".$interval.") AND NOW() ORDER BY Data DESC")) {
	$stmt->bind_param("i", $id);
	$stmt -> execute();
	$result = $stmt -> get_result();
	$nrighe = $result -> num_rows;
} else {
	$nrighe = 0;
}

if($stmt = $connection -> prepare("SELECT * FROM $table_tickets WHERE IDUtente = ? ORDER BY Data DESC")) {
	$stmt->bind_param("i", $id);
	$stmt -> execute();
	$result_ticket = $stmt -> get_result();
	$nrighe_ticket = $result_ticket -> num_rows;
} else {
	$nrighe_ticket = 0;
}

$redirect_preferenziale = getPreference($id, "LoginRedirect");
if($redirect_preferenziale == 0) {
	$redirect_preferenziale_completo = _("HomePage");
} elseif($redirect_preferenziale == 1) {
	$redirect_preferenziale_completo = _("Impostazioni");
} elseif($redirect_preferenziale == 2) {
	$redirect_preferenziale_completo = _("Test CPS");
} else {
	$redirect_preferenziale_completo = _("Sconosciuto");
}

$lingua_preferenziale = getPreference($id, "Lingua");
foreach($language_list as $chiave => $valore) {
	if($valore == $lingua_preferenziale) {
		$lingua_preferenziale_completa = $language_list_complete[$chiave];
	}
}

$intervallo_preferenziale = getPreference($id, "Intervallo");
if($intervallo_preferenziale == "month") {
	$intervallo_preferenziale_completo = _("Mese");
} elseif($intervallo_preferenziale == "year") {
	$intervallo_preferenziale_completo = _("Anno");
} else {
	$intervallo_preferenziale_completo = _("Sconosciuto");
}

$numero_riepilogo_scansioni = getPreference($id, "RiepilogoScansioni");

$esito_datarequest = getUserDataRequests($id);
$esito_cambiousername = getUserUsernameChange($id);

$ore = date("H");
if($ore >= 5 && $ore < 13) {
	$saluto = _("Ciao");
} elseif($ore >= 13 && $ore < 17) {
	$saluto = _("Buon pomeriggio");
} else {
	$saluto = _("Buonasera");
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo sprintf(_("Impostazioni di %s"), $username); ?> | <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
	<script src="/CSS/Charts/js/fusioncharts.js"></script>
	<script src="/CSS/Charts/js/themes/fusioncharts.theme.candy.js"></script>
	<script src="/CSS/Scripts/clipboard.js"></script>
	<script src="/CSS/Scripts/pushjs/push.min.js"></script>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<style type="text/css">
    g[class^='raphael-group-'][class$='-creditgroup'] {
         display:none !important;
    }
	.popover-header { 
		color:black; 
	}
</style>
<body onload="avvioInterval()" <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
	<?php include "$droot/CSS/Charts/integrations/php/fusioncharts-wrapper/fusioncharts.php";?>
    <div class="container-fluid text-center text-white" style="width:85%;">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<div class="container">
				<div class="row">
					<div class="col text-right">
						<img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="25%" alt="Logo">
					</div>
					
					<div class="col text-left">
						<br>
						<h3 style="display: inline-block;"><?php echo $saluto.", "; if($tipo == 0) { echo "<font color='lightblue'>"; } elseif($tipo == 1) { echo "<font color='gold'>"; } elseif($tipo == 2) { echo "<font color='lightgreen'>"; } echo $username; echo "</font>!"; ?><br><font style="font-size:15px;"><?php echo _("Siamo così felici di rivederti!"); ?></font></h3>
					</div>
				</div>
			</div>
			
			<?php if($licenza[7] == true) { ?><br><a href="/" title="<?php echo _('Vai allo Scanner'); ?>"><small><?php echo _("Clicca qui per andare allo Scanner"); ?>&nbsp;<span class="oi oi-external-link"></span></small></a><?php } ?>
			
			<!-- LICENZA -->
			<hr>
			<?php if(@$licenza != false && $licenza[3] != "2000-01-01") { ?>
			<div class="card text-white bg-dark border-<?php if($licenza[7] == true) { echo "success"; } else { echo "danger"; } ?> mb-3" style="width: 100%;">
			  <div class="card-body">
				<p class="card-text text-center">
					<?php if($licenza[7] == true) { echo "<h5 style='color:lightgreen;'>"._("ATTIVA"); } else { echo "<h5 style='color:lightcoral;'>"._("NON ATTIVA"); } ?></h5>
					<?php echo _("ID Licenza").": ".$licenza[0]; ?>
					<br>
					<?php echo _("Tipo licenza").": "; if($licenza[1] == "monthly") { echo _("Mensile"); } elseif($licenza[1] == "permanent") { echo _("Permanente"); } echo " ("; if($licenza[2] == "single") { echo _("Singola"); } elseif($licenza[2] == "network") { echo _("Network"); } echo ")"; ?>
					<br>
					<?php if ($licenza[7] == true) {
						echo _("Scadenza licenza").": ";
						if ($licenza[1] == "permanent") {
							echo _("Mai");
						} else {
							echo formatDate($licenza[3]); echo " ("._("Scade tra"); ?> <b><?php echo $licenza[6]; ?></b> <?php if($licenza[6] == 1) { echo _("giorno"); } else { echo _("giorni"); } echo ")";
						}
					} else {
						echo _("Licenza scaduta il").": "; echo formatDate($licenza[3]); echo " ("._("Scaduta da"); ?> <b><?php echo $licenza[6]; ?></b> <?php if($licenza[6] == 1) { echo _("giorno"); } else { echo _("giorni"); } echo ")";
					} ?>
					<br>
					<?php
					if ($licenza[7] == false) {
						echo "<br>";
						echo _("La licenza risulta scaduta, puoi riattivarla cliccando il pulsante sottostante").".";
						echo "<br>";
						if($payments_live) { ?>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="hosted_button_id" value="757ZG7Q2L23DL">
								<input type="hidden" name="custom" value="<?php echo $id; ?>">
								<input type="image" src="https://www.paypalobjects.com/<?php echo $lang; ?>/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
								<img alt="" border="0" src="https://www.paypalobjects.com/<?php echo $lang; ?>/i/scr/pixel.gif" width="1" height="1">
							</form> <?php
						} elseif($tipo == 1) { // SE L'UTENTE È ADMIN PUO' UTILIZZARE LA SANDBOX ?>
							<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="hosted_button_id" value="MPS2B9KRAQJA8">
								<input type="hidden" name="custom" value="<?php echo $id; ?>">
								<input type="image" src="https://www.sandbox.paypal.com/it_IT/IT/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal è il metodo rapido e sicuro per pagare e farsi pagare online.">
								<img alt="" border="0" src="https://www.sandbox.paypal.com/it_IT/i/scr/pixel.gif" width="1" height="1">
							</form> <?php
						} else {
							echo "<b>"._("Pagamenti in manutenzione")."</b>";
							echo "<br>";
							echo _("Attualmente il sistema di licenze è in fase di manutenzione, pertanto non è possibile fare acquisti mentre lo staff indaga sul problema. Ci scusiamo per il disagio.");
						}
					} ?>
				</p>
			  </div>
			</div>
			<?php } else { ?>
			<div class="card text-white bg-dark border-danger mb-3" style="width: 100%;">
			  <div class="card-body">
				<p class="card-text text-center text-danger">
					<h5 style="color:lightcoral;">⚠️ <?php echo _("Errore Licenza"); ?> ⚠️</h5>
					<?php echo _("Si è verificato un errore cercando di ottenere la scadenza della licenza.")." "._("Contatta lo staff."); ?>
				</p>
			  </div>
			</div>
			<?php } ?>
			
			<hr>
			
			<!-- CODICE OTP -->
			<h5><?php echo _("Codice OTP (One Time Password)"); ?> <small><a title="<?php echo _('Clicca qui per vedere un tutorial'); ?>" style="cursor: pointer;" onClick="window.open('CSS/Images/tutorial_otp.mp4', 'OTP Tutorial', 'width=600, height=400, resizable, status, scrollbars=1, location');"><span class="oi oi-info" style="color:lightblue;"></span></a></small></h5> 
			<p id="notification_status"></p>
			<button title="<?php echo _('Genera un nuovo codice OTP, se ne esiste già uno quello precedente viene sovrascritto'); ?>" id="generate_otp" type="button" class="btn btn-success" <?php if($licenza[7] == false) { echo "disabled"; } ?>><?php echo _("Genera codice OTP"); ?></button> <button title="<?php echo _('Elimina l’attuale codice OTP'); ?>" id="delete_otp" type="button" class="btn btn-danger" <?php if($licenza[7] == false) { echo "disabled"; } ?>><?php echo _("Elimina codice OTP"); ?></button> <button title="<?php echo _('Copia l’attuale codice OTP'); ?>" id="copy_otp" class="btn btn-info" onClick="copy();" <?php if($licenza[7] == false) { echo "disabled"; } ?>><?php echo _("Copia codice OTP"); ?></button>
			<br><br>
			<span id="otp_clipboard" style="display:none;"></span> <!-- Qui viene copiato il codice OTP -->
			<table class="table table-dark" align="center">
				<tr>
					<th><?php echo _("Codice OTP"); ?></th>
					<th><?php echo _("Inizio sessione"); ?></th>
					<th><?php echo _("Stato sessione"); ?></th>
					<th><?php echo _("Scansioni eseguite"); ?></th>
					<th><?php echo _("La sessione terminerà tra"); ?></th>
				</tr>
				
				<tbody class="table-striped">
					<tr>
						<td id="otp_sessioncode"><?php echo _("Caricamento..."); ?></td>
						<td id="otp_sessionstart"><?php echo _("Caricamento..."); ?></td>
						<td id="otp_sessionstatus"><?php echo _("Caricamento..."); ?></td>
						<td id="otp_scannumber"><?php echo _("Caricamento..."); ?></td>
						<td id="otp_sessionend"><?php echo _("Caricamento..."); ?></td>
					</tr>
				</tbody>
			</table>
			<hr>
			<h5><?php echo _("Preferenze"); ?></h5>
			<p><?php echo sprintf(_("In questa sezione puoi modificare alcune impostazioni relative al tuo account %s."), "<b>".$solutionname_short."</b>"); ?>
			<br>
			<div class="row">
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("NewsLetter"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_newsletter">
									<?php if(getPreference($id, "NewsLetter") == 1) { 
										?>
										<option value="1"><?php echo _("Autorizzato"); ?></option>
										<option value="0"><?php echo _("Non autorizzato"); ?></option>
										<?php
									} elseif(getPreference($id, "NewsLetter") == 0) {
										?>
										<option value="0"><?php echo _("Non autorizzato"); ?></option>
										<option value="1"><?php echo _("Autorizzato"); ?></option>
										<?php
									} ?>
								</select>
							</div>
							<?php echo sprintf(_("Consentici di inviarti Email promozionali e di aggiornamento alla casella di posta collegata al tuo account %s."), "<b>".$solutionname_short."</b>"); ?>
						</p>
					</div>
				</div>
				
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Lingua preferenziale"); ?> <span id="preferenza_lingua_aggiorna"></span></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_lingua">
									<option value="<?php echo $lingua_preferenziale; ?>"><?php echo $lingua_preferenziale_completa; ?></option>
									<?php foreach($language_list as $chiave => $valore) {
										if($valore != $lingua_preferenziale) { ?>
											<option value="<?php echo $valore; ?>"><?php echo $language_list_complete[$chiave]; ?></option>
									<?php } } ?>
								</select>
							</div>
							<?php echo _("Seleziona la lingua che l'interfaccia deve assumere, indipendentemente dall'indirizzo IP e dalla tua posizione attuale."); ?>
						</p>
					</div>
				</div>
				
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Login redirect"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_loginredirect">
									<option value="<?php $redirect_preferenziale; ?>"><?php echo $redirect_preferenziale_completo; ?></option>
									<?php foreach($redirect_pages_link as $chiave => $valore) {
										if($chiave !== $redirect_preferenziale) { ?>
											<option value="<?php echo $chiave; ?>"><?php if($chiave == 0) { echo _("HomePage"); } elseif($chiave == 1) { echo _("Impostazioni"); } elseif($chiave == 2) { echo _("Test CPS"); } else { echo _("Sconosciuto"); } ?></option>
										<?php }
									} ?>
								</select>
							</div>
							<?php echo _("Seleziona la pagina dove vuoi essere portato una volta effettuato il login. Puoi scegliere tra:")." ";
							foreach($redirect_pages_link as $chiave => $valore) { 
								if($chiave == 0) { 
									echo _("HomePage"); 
								} elseif($chiave == 1) {
									echo _("Impostazioni");
								} elseif($chiave == 2) {
									echo _("Test CPS");
								} else { 
									echo _("Sconosciuto");
								}
								
								if(count($redirect_pages)-1 != $chiave) {
									echo ", ";
								} else {
									echo ".";
								}
							} ?>
						</p>
					</div>
				</div>
				
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Popup scansione"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_popupscansione">
									<?php if(getPreference($id, "PopupScansione") == 1) { 
										?>
										<option value="1"><?php echo _("Sì"); ?></option>
										<option value="0"><?php echo _("No"); ?></option>
										<?php
									} elseif(getPreference($id, "PopupScansione") == 0) {
										?>
										<option value="0"><?php echo _("No"); ?></option>
										<option value="1"><?php echo _("Sì"); ?></option>
										<?php
									} ?>
								</select>
							</div>
							<?php echo _("Scegli se vuoi visualizzare automaticamente la schermata di informazioni aggiuntive delle scansioni con esito 'Cheat' o 'Sospetto'."); ?>
						</p>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Intervallo preferenziale"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_intervallopreferenziale">
									<option value="<?php echo $intervallo_preferenziale; ?>"><?php echo $intervallo_preferenziale_completo; ?></option>
									<?php foreach($interval_list as $chiave => $valore) {
										if($valore != $intervallo_preferenziale) { ?>
											<option value="<?php echo $valore; ?>"><?php if($valore == "month") { echo _("Mese"); } elseif($valore == "year") { echo _("Anno"); } else { echo _("Sconosciuto"); } ?></option>
									<?php } } ?>
								</select>
							</div>
							<?php echo _("Scegli l'intervallo preferito di visualizzazione del grafico sottostante. Puoi scegliere tra:")." ";
							foreach($interval_list as $chiave => $valore) { 
								if($valore == "month") {
									echo _("Mese");
								} elseif($valore == "year") {
									echo _("Anno");
								} else {
									echo _("Sconosciuto");
								}
								
								if(count($interval_list)-1 != $chiave) {
									echo ", ";
								} else {
									echo ".";
								}
							} ?>
						</p>
					</div>
				</div>
				
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Formato data"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 80%;">
								<select class="form-control" id="preferenza_formatodata">
									<?php if(getPreference($id, "FormatoData") == 0) { // GIORNO MESE ANNO
										?>
										<option value="0"><?php echo _("Giorno")."/"._("Mese")."/"._("Anno"); ?></option>
										<option value="1"><?php echo _("Mese")."/"._("Giorno")."/"._("Anno"); ?></option>
										<option value="2"><?php echo _("Anno")."/"._("Mese")."/"._("Giorno"); ?></option>
										<?php
									} elseif(getPreference($id, "FormatoData") == 1) { // MESE GIORNO ANNO
										?>
										<option value="1"><?php echo _("Mese")."/"._("Giorno")."/"._("Anno"); ?></option>
										<option value="0"><?php echo _("Giorno")."/"._("Mese")."/"._("Anno"); ?></option>
										<option value="2"><?php echo _("Anno")."/"._("Mese")."/"._("Giorno"); ?></option>
										<?php
									} elseif(getPreference($id, "FormatoData") == 2) { // ANNO MESE GIORNO
										?>
										<option value="2"><?php echo _("Anno")."/"._("Mese")."/"._("Giorno"); ?></option>
										<option value="0"><?php echo _("Giorno")."/"._("Mese")."/"._("Anno"); ?></option>
										<option value="1"><?php echo _("Mese")."/"._("Giorno")."/"._("Anno"); ?></option>
										<?php
									} ?>
								</select>
							</div>
							<?php echo _("Scegli il formato della data che vuoi venga mostrato in tutta la piattaforma. L'ora sarà sempre mostrata con il formato a 24 ore."); ?>
						</p>
					</div>
				</div>
				
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Riepilogo scansioni"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_riepilogoscansioni">
									<option value="<?php echo $numero_riepilogo_scansioni; ?>"><?php echo $numero_riepilogo_scansioni; ?> <?php echo _("scansioni"); ?></option>
									<?php foreach($scans_summary_numbers as $chiave => $valore) {
										if($valore != $numero_riepilogo_scansioni) {
											?>
											<option value="<?php echo $valore; ?>"><?php echo $valore; ?> <?php echo _("scansioni"); ?></option>
											<?php
										}
									} ?>
								</select>
							</div>
							<?php echo _("Scegli quante scansioni devono essere visualizzate nella sezione 'Le tue ultime scansioni'."); ?>
						</p>
					</div>
				</div>
				
				<div class="card text-white bg-dark border-light" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Esito Scansioni Autom."); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_ricaricamentoautomatico">
									<?php if(getPreference($id, "ReloadAuto") == 1) { 
										?>
										<option value="1"><?php echo _("Sì"); ?></option>
										<option value="0"><?php echo _("No"); ?></option>
										<?php
									} elseif(getPreference($id, "ReloadAuto") == 0) {
										?>
										<option value="0"><?php echo _("No"); ?></option>
										<option value="1"><?php echo _("Sì"); ?></option>
										<?php
									} ?>
								</select>
							</div>
							<?php echo _("Scegli se andare alla sezione<br>'Le tue ultime scansioni' automaticamente al termine di una sessione OTP."); ?>
						</p>
					</div>
				</div>
			</div>
			<?php if($tipo == 1) { ?>
			<br>
			<div class="row">
				<div class="card text-white bg-dark border-danger" style="width: 18rem; float: none; margin: 0 auto;">
					<div class="card-body">
						<h5 class="card-title"><?php echo _("Autenticazione a 2 fattori"); ?></h5>
						<p class="card-text">
							<div class="container" style="width: 75%;">
								<select class="form-control" id="preferenza_autenticazione2fa" <?php if($tipo != 1) { ?>disabled<?php } ?>>
									<?php if(getPreference($id, "2FARichiesto") == 1) { 
										?>
										<option value="1"><?php echo _("Sì"); ?></option>
										<option value="0"><?php echo _("No"); ?></option>
										<?php
									} elseif(getPreference($id, "2FARichiesto") == 0) {
										?>
										<option value="0"><?php echo _("No"); ?></option>
										<option value="1"><?php echo _("Sì"); ?></option>
										<?php
									} ?>
								</select>
							</div>
							<?php echo _("Scegli se abilitare l'autenticazione a 2 fattori. Consigliamo di tenere abilitata questa impostazione."); ?>
						</p>
					</div>
				</div>
			</div>
			<?php } ?>
			
			<hr>
			<h5 id="auth2fa"><?php echo _("Autenticazione a 2 fattori"); ?></h5>
			<div class="card text-white bg-dark border-<?php if(hasTwoFactorAuthentication($id) && has2FARecoveryCodes($id)) { ?>success<?php } else { ?>danger<?php } ?> mb-3" style="width: 100%;">
			<?php if(hasTwoFactorAuthentication($id)) { ?>
				  <div class="card-body">
					<p class="card-text text-center text-success">
						<h5 style="color:lightgreen;"><?php echo _("ATTIVA"); ?> </h5>
						<?php echo sprintf(_("Un codice 2FA è composto da 6 cifre casuali generate ogni 30 secondi dall'app %sGoogle Authenticator%s."), "<a href='https://support.google.com/accounts/answer/1066447?co=GENIE.Platform%3DAndroid'>", "</a>"); ?>
						<br>
						<?php if(has2FARecoveryCodes($id)) {
							echo _("Nel caso di smarrimento dei codici 2FA è possibile usare i codici di recupero visibili"); ?> <a href="2FACode/2FARecovery.php?from=settings"><?php echo _("qui"); ?>.</a>
						<?php } else {
							?><font color="lightcoral"><?php echo _("Per favore genera dei codici di recupero, nel caso non avessi più accesso ai codici 2FA! Accedi a"); ?> <a href="2FACode/2FARecovery.php?from=settings"><?php echo _("questa pagina"); ?>.</a></font>
						<?php } ?>
					</p>
				  </div>
			<?php } else { ?>
				<div class="card text-white bg-dark border-danger mb-3" style="width: 100%;">
				  <div class="card-body">
					<p class="card-text text-center text-danger">
						<h5 style="color:lightcoral;"><?php echo _("NON ATTIVA"); ?></h5>
						<?php echo _("Il tuo account non è totalmente al sicuro, in quanto un malintenzionato con le tue credenziali potrebbe essere in grado di accedere a dati sensibili e al tuo account senza il tuo consenso."); ?>
						<br>
						<?php echo _("Per favore, abilita la autenticazione a 2 fattori")." "; ?><a href="2FACode/create2FA.php" title="<?php echo _("Clicca per iniziare"); ?>"><?php echo _("cliccando qui"); ?></a>.
					</p>
				  </div>
			<?php } ?>
			</div>
		</div>
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<br>
			<h5><?php echo _("Statistiche"); ?></h5>
			<p><?php echo _("Visualizzando le scansioni effettuate in questo"); ?> <?php if($interval == "month") { echo _("Mese"); } elseif($interval == "year") { echo _("Anno"); } ?>&nbsp;
			<?php if($interval == "month") { 
				?><a title="<?php echo _('Passa a visualizzazione annuale'); ?>" href="settings.php?interval=year"><small><span class="oi oi-bar-chart"></span></small></a><?php
			} elseif($interval == "year") {
				?><a title="<?php echo _('Passa a visualizzazione mensile'); ?>" href="settings.php?interval=month"><small><span class="oi oi-bar-chart"></span></small></a><?php
			} else {
				?><a href="settings.php?interval=year"><?php echo _("Passa a visualizzazione annuale"); ?></a><?php
			} ?></p>
			<?php
				if($interval == "year") {
					renderizzaGrafico("year");
				} elseif($interval == "month") {
					renderizzaGrafico("month");
				} else {
					renderizzaGrafico("month");
				}
			?>
			<div id="stats-graph" align="center">
			</div>
			<hr>
			<h5><?php echo _("Le tue ultime")." "; echo $numero_riepilogo_scansioni; echo " "._("scansioni"); ?></h5>
			<br>
			<?php if($nrighe == 0) { 
				?><p style="color:lightgray;"><?php echo _("Nessuna scansione trovata."); ?></p><?php
			} else { ?>
				<table class="table table-dark" align="center">
					<tr>
						<th><?php echo _("ID scansione"); ?></th>
						<th><?php echo _("Data scansione"); ?></th>
						<th><?php echo _("Durata scansione"); ?></th>
						<th><?php echo _("Tipo scansione"); ?></th>
						<th><?php echo _("Esito scansione"); ?></th>
						<th><?php echo _("Log scansione"); ?></th>
					</tr>
					<tbody class="table-striped">
				<?php
					if($interval == "year") {
						renderizzaTabella("year");
					} elseif($interval == "month") {
						renderizzaTabella("month");
					} else {
						renderizzaTabella("month");
					}
				?>
				</tbody>
				</table>
			<?php } ?>
		</div>
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<br>
			<h5><?php echo _("I tuoi Ticket"); ?></h5>
			<p><?php echo _("Qui puoi vedere i ticket inviati da te ai nostri membri dello staff. Puoi passare sopra al testo con il mouse per vedere tutto il testo del ticket e la risposta dello staff (se presente)."); ?></p>
			<hr>
			<div class="container" style="min-width:100%;">
			<?php if($nrighe_ticket == 0) { 
				?><p style="color:lightgray;"><?php echo _("Non hai ancora inviato nessun ticket."); ?></p><?php
			} else { ?>
				<table class="table table-dark" align="center">
					<tr>
						<th><?php echo _("ID"); ?></th>
						<th><?php echo _("Testo"); ?></th>
						<th><?php echo _("Data"); ?></th>
						<th><?php echo _("Tipo"); ?></th>
						<th><?php echo _("Nome staffer"); ?></th>
						<th><?php echo _("Risposta staffer"); ?></th>
						<th><?php echo _("Stato"); ?></th>
					</tr>
					<tbody class="table-striped">
						<?php
							while($riga = $result_ticket->fetch_assoc()) {
								echo "<tr>";
								
								
								echo "<td>#".$riga["ID"]."</td>";
								echo "<td><a title='".$riga["Spiegazione"]."'>".substr($riga["Spiegazione"], 0, 75)."...</a></td>";
								echo "<td>".formatDate($riga["Data"], true)."</td>";
								
								echo "<td>";
								if($riga["TipoRichiesta"] == "question") { 
									echo _("Domanda");
								} elseif($riga["TipoRichiesta"] == "bug") {
									echo _("Bug / Errore");
								} elseif($riga["TipoRichiesta"] == "falseflag") {
									echo _("Falso positivo");
								} elseif($riga["TipoRichiesta"] == "other") {
									echo _("Altro");
								} else {
									echo "???";
								}
								echo "</td>";
								
								echo "<td>";
								if($riga["IDStaffer"] != "") {
									echo getUsernameFromID($riga["IDStaffer"]);
								} else {
									echo "//";
								}
								echo "</td>";
								
								echo "<td>";
								if($riga["IDStaffer"] != "") {
									echo "<a title='".$riga["Risposta"]."'>".substr($riga["Risposta"], 0, 75)."...</a>";
								} else {
									echo "//";
								}
								echo "</td>";
								
								echo "<td>";
								if($riga["Stato"] == 0) {
									echo "<font style='color:lightgreen;'>"._("Chiuso")."</font>";
								} elseif($riga["Stato"] == 1) {
									echo "<font style='color:lightcoral;'>"._("Aperto")."</font>";
								} elseif($riga["Stato"] == 2) {
									echo "<font style='color:orange;'>"._("Da visualizzare")."</font>";
								} else {
									echo "<font style='color:red;'>"._("Sconosciuto")."</font>";
								}
								echo "</td>";
								
								
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			<?php } ?>
			</div>
		</div>
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<br>
			<!-- CAMBIO USERNAME -->
			<h5><?php echo _("Cambio Username"); ?></h5>
			<p>
				<?php echo _("Puoi cambiare il tuo Username in questa sezione."); ?>
				<?php if($esito_cambiousername[0]) { ?><br>
				<font style="color:lightcoral;"><?php echo sprintf(_("Attenzione: Si può fare solo una volta ogni %d giorni."), $dayscooldownchangeusername);?></font>
				<?php } ?>
			</p>
			<form id="formcambiousername" name="form" action="Utils/" method="POST">
				<div class="container text-right" style="width: 50%">
					<div class="input-group mb-3">
						<div class="input-group-prepend">
						<div class="input-group-text">
							<span class="oi oi-pencil"></span>
						</div>
						</div>
						<input class="form-control" type="text" id="usernamenuovo" name="usernamenuovo" placeholder="<?php echo _('Nuovo Username'); ?>" autocomplete="off" required <?php if(!$esito_cambiousername[0]) { echo "disabled"; } ?>></input>
					</div>
					
					<div class="input-group mb-3">
						<div class="input-group-prepend">
						<div class="input-group-text">
							<span class="oi oi-key"></span>
						</div>
						</div>
						<input class="form-control" type="password" id="confermapassword2" name="confermapassword" placeholder="<?php echo _('Conferma Password'); ?>" autocomplete="off" required <?php if(!$esito_cambiousername[0]) { echo "disabled"; } ?>></input>
					</div>
				</div>
				<br>
				<button type="submit" class="btn btn-success" name="invio" <?php if(!$esito_cambiousername[0]) { echo "disabled"; } ?>><?php echo _("Cambia Username"); ?></button>
				<?php 
				if(!$esito_cambiousername[0]) {
					?>
					<br><br>
					<font style="color:lightcoral;"><?php echo _("Hai già effettuato un cambio in data").": ".formatDate($esito_cambiousername[1], true).".<br>"._("Attendi")." ".($esito_cambiousername[2])." "._("giorni")." "._("prima di cambiare nuovamente Username"); ?>.</font>
					<?php
				} ?>
				<br><br>
			</form>
			<hr>
			<!-- CAMBIO PASS E MAIL -->
			<div class="container-fluid">
				<div class="row">
					<div class="col">
						<h5><?php echo _("Cambio Password"); ?></h5>
						<p><?php echo _("Puoi cambiare la password inserendo prima quella vecchia e poi quella nuova nei campi sottostanti."); ?></p>
						<form id="formcambiopassword" name="form" action="Password/changePasswordDB.php" method="POST">
							<div class="container text-left" style="width: 100%">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-hard-drive"></span>
									</div>
									</div>
									<input class="form-control" type="password" id="passwordvecchia" name="passwordvecchia" placeholder="<?php echo _('Vecchia Password'); ?>" autocomplete="off" required></input>
								</div>
								
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<div class="input-group-text">
											<span class="oi oi-key"></span>
										</div>
									</div>
									<input class="form-control" type="password" id="passwordnuova1" name="passwordnuova1" placeholder="<?php echo _('Nuova Password'); ?>" autocomplete="off" required></input>
									<div class="input-group-append">
										<div class="input-group-text">
										  <a data-toggle="popover" data-trigger="hover" title="<?php echo _('Requisiti minimi:'); ?>" data-html="true" data-content="- <?php echo _('Non deve essere vuota'); ?><br>- <?php echo _('Lunghezza minima'); ?>: <?php echo $password_minlength; ?> <?php echo _('caratteri'); ?><br>- <?php echo _('Lunghezza massima'); ?>: <?php echo $password_maxlength; ?> <?php echo _('caratteri'); ?>"><span class="oi oi-question-mark"></span></a>
										</div>
									</div>
								</div>

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<div class="input-group-text">
											<span class="oi oi-key"></span>
										</div>
									</div>
									<input class="form-control" type="password" id="passwordnuova2" name="passwordnuova2" placeholder="<?php echo _('Ripeti Nuova Password'); ?>" autocomplete="off" required></input>
									<div class="input-group-append">
										<div class="input-group-text">
										  <a data-toggle="popover" data-trigger="hover" title="<?php echo _('Requisiti minimi:'); ?>" data-html="true" data-content="- <?php echo _('Non deve essere vuota'); ?><br>- <?php echo _('Lunghezza minima'); ?>: <?php echo $password_minlength; ?> <?php echo _('caratteri'); ?><br>- <?php echo _('Lunghezza massima'); ?>: <?php echo $password_maxlength; ?> <?php echo _('caratteri'); ?>"><span class="oi oi-question-mark"></span></a>
										</div>
									</div>
								</div>
							</div>
							<br>
							<button type="submit" class="btn btn-success" name="invio"><?php echo _("Aggiorna Password"); ?></button>
							<br><br>
						</form>
					</div>
					
					<div class="col">
						<h5><?php echo _("Cambio Email"); ?></h5>
						<p><?php echo _("Puoi cambiare l'indirizzo Email utilizzato per ricevere notifiche e Newsletter qui.")."<br>"; echo _("Indirizzo Email attuale:")." <b>"; if($email != "") { echo $email; } else { echo "???"; } echo "</b>"; ?>
						<form id="formcambioemail" name="form" action="Email/changeEmailDB.php" method="POST">
							<div class="container text-right" style="width: 100%">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-envelope-closed"></span>
									</div>
									</div>
									<input class="form-control" type="email" id="emailnuova" name="emailnuova" placeholder="<?php echo _('Nuova Email'); ?>" autocomplete="off" required></input>
								</div>
								
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-key"></span>
									</div>
									</div>
									<input class="form-control" type="password" id="confermapassword" name="confermapassword" placeholder="<?php echo _('Conferma Password'); ?>" autocomplete="off" required></input>
								</div>
							</div>
							<br>
							<button type="submit" class="btn btn-success" name="invio"><?php echo _("Aggiorna Email"); ?></button>
							<br><br>
						</form>
					</div>
				</div>
			</div>
			<!-- FINE CAMBIO PASS E MAIL -->
		</div>
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<br>
			<div class="container">
				<div class="row">
					<div class="col">
						<h5><?php echo _("Richiesta dati utente"); ?></h5>
						<p>
							<?php echo _("Se desideri, puoi richiedere una copia dei tuoi dati personali, ma solo una volta ogni")." ".$daysbeforedeletedatarequests." "._("giorni").". "._("Tutte le informazioni che possediamo ti saranno inviate tramite Email all'indirizzo di posta che ci hai fornito."); ?>
							<br><br>
							<?php if($esito_datarequest[0]) {
								?>
								<font color="lightgreen"><?php echo _("Puoi effettuare la richiesta adesso"); ?>.</font>
								<?php
							} else {
								?>
								<font color="lightcoral"><?php echo _("Hai già effettuato una richiesta in data").": ".formatDate($esito_datarequest[1], true).".<br>"._("Attendi")." ".($esito_datarequest[2])." "._("giorni")." "._("prima di richiedere nuovamente i dati"); ?>.</font>
								<?php
							} ?>
							<br><br>
							<button type="button" onClick="dataRequest();" class="btn btn-success" name="inviorichiestadati" <?php if(!$esito_datarequest[0]) { echo "disabled"; } ?>><?php echo _("Richiedi dati personali"); ?></button>
						</p>
					</div>
					
					<div class="col">
						<h5><?php echo _("Elimina questo account"); ?></h5>
						<p>
							<?php echo _("Se desideri, puoi richiedere l'eliminazione PERMANENTE di tutti i tuoi dati. Nota che le licenze acquistate non saranno rimborsate. L'operazione non è annullabile."); ?>
						</p>
						<form id="formcancellazioneaccount" name="formcancellazioneaccount" action="Utils/deleteAccountDB.php" method="POST">
							<div class="container" style="width:75%">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-person"></span>
									</div>
									</div>
									<input onKeyUp="checkUsernameTyping();" class="form-control" type="text" id="usernameconfermacancellazione" name="usernameconfermacancellazione" placeholder="<?php echo _("Il tuo Username"); ?>" autocomplete="off" required></input>
								</div>
							</div>
							
							<div class="container" style="width:75%">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-key"></span>
									</div>
									</div>
									<input class="form-control" type="password" id="passwordconfermacancellazione" name="passwordconfermacancellazione" placeholder="<?php echo _("La tua Password"); ?>" autocomplete="off" required></input>
								</div>
							</div>
							<br>
							<button type="submit" class="btn btn-danger" name="inviocancellazioneaccount" id="inviocancellazioneaccount" disabled><?php echo _("Cancella questo account"); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<?php
function renderizzaGrafico($interval = "month") {
	if($interval == "month")
		$titolo = _("Scansioni mensili");
	else
		$titolo = _("Scansioni annuali");
	
	if($interval == "month") 
		$asse_x = _("Giorno");
	else
		$asse_x = _("Mese");
	
	$array = array();
	$arrChartConfig = array(
		"chart" => array(
			"caption" => $titolo,
			"xAxisName" => $asse_x,
			"yAxisName" => _("Scansioni"),
			"theme" => "candy",
			"logoURL" => "CSS/Images/logo.png",
			"logoScale" => "30",
			"logoPosition" => "TR",
			"decimals" => "0",
			"formatNumber" => "0",
			"forceDecimals" => "0",
			"dataLoadStartMessage" => _("Caricamento dati. Attendi."),
			"dataLoadErrorMessage" => _("Errore durante il caricamento dei dati."),
			"dataInvalidMessage" => _("Dati non validi."),
			"dataEmptyMessage" => _("Nessun dato da mostrare."),
			"typeNotSupportedMessage" => _("Tipo di grafico non supportato."),
			"loadMessage" => _("Caricamento grafico. Attendi."),
			"renderErrorMessage" => _("Errore durante il rendering del grafico.")
		)
	);
	
	/*
		MESE:
		Per ogni giorno del mese (partendo da 1), controlla che la data della scansione sia uguale al giorno nel WHILE, se sì, aumenta il contatore delle scansioni per quel giorno.
		
		ANNO:
		Per ogni mese dell'anno (partendo da 1), controlla che la data della scansione sia uguale al mese nel WHILE, se sì, aumenta il contatore delle scansioni per quel giorno
	*/
	if($interval == "month") {
		for($i = 1; $i <= date("d"); $i++) {
			$scansioni = 0;
			mysqli_data_seek($GLOBALS["result"], 0);
			while ($riga = $GLOBALS["result"]->fetch_assoc()) {
				if((date("d", strtotime($riga["Data"]))) == $i) {
					$scansioni++;
				}
			}
			if($i == date("d")) {
				$arraygenerato = array(_("Oggi"), $scansioni);
			} else {
				$arraygenerato = array("$i", $scansioni);
			}
			array_push($array, $arraygenerato);
		}
	} elseif($interval == "year") {
		for($i = 1; $i <= date("n"); $i++) {
			mysqli_data_seek($GLOBALS["result"], 0);
			$scansioni = 0;
			while ($riga = $GLOBALS["result"]->fetch_assoc()) {
				if(date("n", strtotime($riga["Data"])) == $i) {
					$scansioni++;
				}
			}
			if($_SESSION["lang"] == "fr_FR") {
				setlocale(LC_TIME, "fr_FR.UTF-8");
			} else {
				setlocale(LC_TIME, $_SESSION["lang"]);
			}
			$arraygenerato = array(ucfirst(strftime("%B", mktime(0, 0, 0, $i, 1))) , $scansioni);
			array_push($array, $arraygenerato);
		}
	}
	$arrChartData = $array;

	$arrLabelValueData = array();

	for ($i = 0; $i < count($arrChartData); $i++) {
		array_push($arrLabelValueData, array(
			"label" => $arrChartData[$i][0], "value" => $arrChartData[$i][1],
		));
	}

	$arrChartConfig["data"] = $arrLabelValueData;
	$jsonEncodedData = json_encode($arrChartConfig);

	$chart = new FusionCharts("line", _("Scansioni"), "100%", "70%", "stats-graph", "json", $jsonEncodedData);
	$chart->render();
}


function renderizzaTabella($interval = "month") {
	mysqli_data_seek($GLOBALS["result"], 0);
	$i = 0;
	if($interval == "month" || $interval == "year") {
		while($riga = $GLOBALS["result"] -> fetch_assoc()) {
			if($i < $GLOBALS["numero_riepilogo_scansioni"]) {
				if($riga["Controllo"] == 1) { 
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_cheatjava"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 2) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_cheatdwm"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 3) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_cheatmsmpeng"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 4) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_cheatlsass"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 5) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_suspectjava"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 6) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_suspectdwm"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 7) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_suspectmsmpeng"]." WHERE Stringa = ?");
				} elseif($riga["Controllo"] == 8) {
					$stmt = $GLOBALS["connection"] -> prepare("SELECT Client FROM ".$GLOBALS["table_suspectlsass"]." WHERE Stringa = ?");
				}
				$stmt -> bind_param("s", $riga["StringaTrovata"]);
				$stmt -> execute();
				$result = $stmt -> get_result();
				$nrighe = $result -> num_rows;
				if($nrighe > 0) {
					$riga2 = $result -> fetch_assoc();
				}
				echo "<tr>";
				echo "<td>" . $riga["ID"] . "</td>";
				echo "<td>" . formatDate($riga["Data"], true) . "</td>";
				echo "<td>" . $riga["Durata"] . "</td>";
				echo "<td>";
				if($riga["Controllo"] == 1) {
					echo "Javaw";
				} elseif($riga["Controllo"] == 2) {
					echo "Dwm";
				} elseif($riga["Controllo"] == 3) {
					echo "Msmpeng";
				} elseif($riga["Controllo"] == 4) {
					echo "lsass";
				} elseif($riga["Controllo"] == 5) {
					echo "Javaw"." ("._("Sospetto").")";
				} elseif($riga["Controllo"] == 6) {
					echo "Dwm"." ("._("Sospetto").")";
				} elseif($riga["Controllo"] == 7) {
					echo "Msmpeng"." ("._("Sospetto").")";
				} elseif($riga["Controllo"] == 8) {
					echo "lsass"." ("._("Sospetto").")";
				} else {
					echo _("Sconosciuto");
				}
				echo "</td>";
				echo "<td><b>";
				switch ($riga["Esito"]) {
					case 0:
						echo "<a title='"._("Codice errore:")." ".$riga["CodiceErrore"]."'><font color='red'>" . _('Errore') . "</font></a>";
						break;
					case 1:
						echo "<font color='lightgreen'>" . _('Pulito') . "</font>";
						break;
					case 2:
						echo "<font color='orange'>" . _('Sospetto') . "</font>";
						break;
					case 3:
						echo "<font color='lightcoral'>" . _('Cheat') . "</b></font> ";
						if($riga2["Client"] != "") {
							echo "<small>(" . $riga2["Client"]. ")</small>";	
						}
						break;
					default:
						echo "(" . _('Sconosciuto') . ")</font>";
						break;
				}
				echo "</b></td>";
				echo "<td><a title='"._("Clicca per scaricare il log della scansione n°").$riga["ID"]."' href='Utils/generateLog.php?id=".$riga["ID"]."'><span class='oi oi-data-transfer-download'></span></a></td>";
				echo "</tr>";
				$i++;
			}
		}
	}
}
?>


<script>
    setInterval(function() {
        checkOTP();
    }, 3000);
	
	var statoprecedente = -1;
	var scansioniprecedenti = -1;
	var scansionifatte = 0;
	
	function dataRequest() {
		swal({
		  title: "<?php echo _('Richiesta dati utente'); ?>",
		  html: "<?php echo _("Cliccando su 'Conferma' i tuoi dati personali saranno inviati al tuo indirizzo Email").". "._("Confermi l'operazione?")."<br><br><font color='lightcoral'>"; echo _("Non potrai effettuare nuove richieste per i prossimi")." ".$daysbeforedeletedatarequests." "._("giorni"); ?>.</font>",
		  footer: "<?php echo _("Il processo potrebbe richiedere alcuni minuti"); ?>.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonText: '<?php echo _("Conferma"); ?>',
		  cancelButtonText: '<?php echo _("Annulla"); ?>'
		})
		.then((result) => {
		  if (result.value) {
			swal({
			  title: "<?php echo _('Attendi'); ?>",
			  text: "<?php echo _('Raccolta dati in corso'); ?>...",
			  type: "info"
			});
			$.ajax({
			type: "POST",
			url: "Utils/requestDataDB.php",
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					swal({
					  title: "<?php echo _('Operazione completata'); ?>",
					  text: "<?php echo _("L'Email con la raccolta dei tuoi dati è stata inviata alla casella di posta")." ".$email; ?>.",
					  type: "success"
					});
					setTimeout( function(){
						window.location.reload();
					}, 5000 );
				} else if(result == "failure") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("Non è stato possibile elaborare la richiesta"); ?>.",
					  type: "error"
					});
				} else if(result == "error_too_early") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _("È passato troppo poco tempo perché tu possa richiedere ancora i tuoi dati"); ?>.",
					  type: "error"
					});
				} else if(result == "invalid_session") {
					swal({
					  title: "<?php echo _('Si è verificato un errore'); ?>",
					  text: "<?php echo _('Sessione utente non valida. Rieffettua il login'); ?>.",
					  type: "error"
					});
				}  else {
					swal({
					  title: "<?php echo _("Errore imprevisto"); ?>",
					  text: result,
					  type: "error"
					});
				}
			}
			});
		  }
		});
	}
	
	function sendSWAL(esito, titolo, testo) {
		if(esito == "success") {
			swal({
			  position: 'top-end',
			  title: titolo,
			  html: testo + ".",
			  type: esito,
			  showConfirmButton: false,
			  toast: true,
			  timer: 2000
			});
		} else {
			swal({
			  title: titolo,
			  html: testo + ".",
			  type: esito
			});
		}
	}
	
	function otpCodeNotification(code) {
		swal({
		  position: 'top-end',
		  title: "<?php echo _('Notifica OTP'); ?>",
		  html: "<?php echo _('Il codice OTP'); ?> <b>"+code+"</b> <?php echo _('è stato utilizzato con successo'); ?>.",
		  type: "info",
		  showConfirmButton: false,
		  toast: true,
		  timer: 5000
		});
		
		if(Push.Permission.has()) {
			Push.create("<?php echo $solutionname; ?>", {
				body: "<?php echo _('Il codice OTP è stato utilizzato con successo'); ?>.",
				icon: '/CSS/Images/logo.png',
				timeout: 5000,
				onClick: function () {
					window.focus();
					this.close();
				}
			});
		}
	}
	
	function copy(){
		var inp =document.createElement('input');
		document.body.appendChild(inp);
		inp.value = document.getElementById("otp_sessioncode").textContent;
		inp.select();
		document.execCommand('copy',false);
		inp.remove();
		$("#copy_otp").text("<?php echo _('Codice OTP copiato!'); ?>");
		setTimeout( function(){
			$("#copy_otp").text("<?php echo _('Copia codice OTP'); ?>");
		}, 1000 );
	}

	function checkOTP() {
		$.ajax({
		type: "POST",
		url: "Utils/viewOTP.php",
		datatype: "html",
		success:function(result)
		{
			if(result == "empty") {
				$("#otp_sessioncode").html('//');
				$("#otp_sessionstart").html("<?php echo _('//'); ?>");
				$("#otp_sessionend").html("<?php echo _('//'); ?>");
				$("#otp_sessionstatus").html("<?php echo _('Chiusa'); ?>");
				$("#otp_scannumber").html("0 / 4 <?php echo _('scansioni'); ?>");
				$("#delete_otp").prop("disabled",true);
				$("#copy_otp").prop("disabled",true);
				<?php if(getPreference($id, "ReloadAuto") == 1) { ?>
					if(statoprecedente == 2) {
						if(scansionifatte > 0) {
							setTimeout(function(){
								window.location.href = "#stats-graph";
								window.location.reload(true);
							}, 500);
						}
					}
				<?php } ?>
			} else if(result == "failure") {
				$("#otp_sessioncode").html("<?php echo _('Errore interno: Esecuzione script fallita'); ?>."+"<br>")
			} else if(result == "invalid_session") {
				$("#otp_sessioncode").html("<?php echo _('Sessione utente non valida. Rieffettua il login'); ?>."+"<br>")
			} else {
				var risposta = result.split("_");
				
				$("#otp_sessioncode").html(risposta[0]);
				if(risposta[1] == "NULL") {
					$("#otp_sessionstart").html("<?php echo _('//'); ?>");
				} else {
					$("#otp_sessionstart").html(risposta[5]);
				}
				if(risposta[2] == "NULL") {
					$("#otp_sessionend").html("<?php echo _('//'); ?>");
				} else {
					var ora = new Date();
					var scadenza = new Date(risposta[2]);
					var diffMS = (scadenza - ora);
					var diffMINS = Math.round(((diffMS % 86400000) % 3600000) / 60000);
					var diffSECS = ((diffMS % 60000) / 1000).toFixed(0);
					if(diffMS >= 0) {
						if(diffMINS == 1) {
							$("#otp_sessionend").html("<a title='"+risposta[6]+"'>"+diffMINS+" <?php echo _('minuto'); ?></a><img src='CSS/Images/scanning.gif' style='width: 30px'>");
						} else {
							if(diffMINS != 0) {
								$("#otp_sessionend").html("<a title='"+risposta[6]+"'>"+diffMINS+" <?php echo _('minuti'); ?></a><img src='CSS/Images/scanning.gif' style='width: 30px'>");
							} else {
								if(diffSECS == 1) {
									$("#otp_sessionend").html("<a title='"+risposta[6]+"'>"+diffSECS+" <?php echo _('secondo'); ?></a><img src='CSS/Images/scanning.gif' style='width: 30px'>");
								} else {
									$("#otp_sessionend").html("<a title='"+risposta[6]+"'>"+diffSECS+" <?php echo _('secondi'); ?></a><img src='CSS/Images/scanning.gif' style='width: 30px'>");	
								}
							}
						}
					} else {
						forceDeleteOTP();
					}
				}
				if(risposta[3] == 0) {
					$("#otp_sessionstatus").html("<?php echo _('Chiusa'); ?>");
					statoprecedente = 0;
					scansioniprecedenti = -1;
				} else if(risposta[3] == 1) {
					$("#otp_sessionstatus").html("<?php echo _('In attesa...'); ?>");
					statoprecedente = 1;
					scansioniprecedenti = 0;
				} else if(risposta[3] == 2) {
					$("#otp_sessionstatus").html("<?php echo _('In uso...'); ?>");
					statoprecedente = 2;
				} else if(risposta[3] == 3) {
					$("#otp_sessionstatus").html("<?php echo _('In chiusura...'); ?>");
					statoprecedente = 3;
				} else {
					$("#otp_sessionstatus").html("<?php echo _('Errore non pianificato'); ?>: "+risposta[3]);
				}
				if(risposta[4] == 1) {
					$("#otp_scannumber").html(risposta[4]+" / 4 <?php echo _('scansione'); ?>");
				} else if(risposta[4] < 4) {
					$("#otp_scannumber").html(risposta[4]+" / 4 <?php echo _('scansioni'); ?>");
				} else {
					$("#otp_scannumber").html("<font color='lightcoral'><?php echo _('Limite raggiunto'); ?></font>");
				}
				if(scansioniprecedenti != (risposta[4])) {
					if(scansioniprecedenti != -1) {
						swal({
						  position: 'top-end',
						  title: "<?php echo _('Notifica OTP'); ?>",
						  html: "<?php echo _('Una scansione effettuata nella sessione OTP è terminata'); ?>.<br><?php echo _('Controlla la condivisione schermo o ricarica questa pagina per maggiori informazioni'); ?>",
						  type: "info",
						  showConfirmButton: false,
						  toast: true,
						  timer: 3000
						});
						
						if(Push.Permission.has()) {
							Push.create("<?php echo $solutionname; ?>", {
								body: "<?php echo _('Una scansione effettuata nella sessione OTP è terminata'); ?>.\n<?php echo _('Clicca qui per tornare al tuo browser'); ?>.",
								icon: '/CSS/Images/logo.png',
								timeout: 3000,
								onClick: function () {
									window.focus();
									this.close();
								}
							});
						}
					}
					scansioniprecedenti = risposta[4];
					scansionifatte++;
				}
				$("#delete_otp").prop("disabled",false);
				$("#copy_otp").prop("disabled",false);
				if((statoprecedente == 0 || statoprecedente == 1) && risposta[3] == 2) {
					otpCodeNotification(risposta[0]);
					statoprecedente = 2;
				}
			}
		}
		});
	}
	
	function forceDeleteOTP() {
		swal({
		  position: 'top-end',
		  title: "<?php echo _('Notifica OTP'); ?>",
		  html: "<?php echo _('La sessione aperta è scaduta ed è stata chiusa'); ?>.",
		  type: "info",
		  showConfirmButton: false,
		  toast: true,
		  timer: 5000
		});
		
		if(Push.Permission.has()) {
			Push.create("<?php echo $solutionname; ?>", {
				body: "<?php echo _('La sessione aperta è scaduta ed è stata chiusa'); ?>.",
				icon: '/CSS/Images/logo.png',
				timeout: 5000,
				onClick: function () {
					window.focus();
					this.close();
				}
			});
		}
		
		$.ajax({
		type: "POST",
		url: "Utils/deleteOTP.php",
		datatype: "html",
		success:function(result)
		{
			if(result == "ok") {
				checkOTP();
				statoprecedente = 0;
			} else if(result == "failure") {
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Errore interno: Esecuzione script fallita"); ?>",
				  type: "error"
				});
			} else if(result == "invalid_session") {
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Sessione utente non valida. Rieffettua il login"); ?>",
				  type: "error"
				});
			} else {
				swal({
				  title: "<?php echo _("Errore non pianificato"); ?>",
				  text: result,
				  type: "error"
				});
			}
		}
		});	
	}
	
	function preferenceUpdated(esito, nomepreferenza, testomessaggio) {
		swal({
		  position: 'top-end',
		  title: nomepreferenza,
		  html: testomessaggio + ".",
		  type: esito, // success | error
		  showConfirmButton: false,
		  toast: true,
		  timer: 2000
		});
	}

	function checkUsernameTyping() {
		if($("#usernameconfermacancellazione").val() != "<?php echo $username; ?>") {
			$("#inviocancellazioneaccount").prop("disabled",true);
		} else {
			$("#inviocancellazioneaccount").prop("disabled",false);
		}
	}
	
	function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	}
	
	function avvioInterval() {
		checkOTP();
	}
	
    document.getElementById('jquery').addEventListener('load', function() {
		// Inizializzazione Popup
		$(function () {
			$('[data-toggle="popover"]').popover()
		});
		
		if(!Push.Permission.has()) {
			$("#notification_status").html("<?php echo sprintf(_('%s invia notifiche al tuo browser durante uno screenshare in determinate circostanze. Se desideri, puoi abilitare le notifiche PUSH %scliccando qui%s.'), $solutionname_short, "<a id='notification' style='color:orange; cursor: pointer;'>", '</a>'); ?>");		
		}
		
		$(document).ready(function() {
			$('#notification').click( function(e) {
				e.preventDefault(); 
				if(Push.Permission.has()) {
					location.reload();
				} else {
					Push.Permission.request(null, null);
				}
				return false; 
			} );
			
			<!-- BEGINNING OF WELCOME & LOCAL. MESSAGES -->
			<?php
			if(isset($_GET["first_login"]) && $_GET["first_login"] == "1") {
				?>
				swal({
				  title: '<?php echo _("Benvenuto su")." ".$solutionname_short; ?>, <?php echo $username; ?>!',
				  type: 'info',
				  html: "<?php echo _('Ci auguriamo il servizio sia di tuo gradimento.'); ?><br><br><?php echo _("I nostri membri dello staff sono a tua completa disposizione."); ?><br><?php echo _("Puoi contattarli inviando una"); ?>&nbsp;<a target='_blank' href='mailto:<?php echo $genericmail; ?>'><?php echo _("Email"); ?></a>.",
				  showCloseButton: false,
				  footer: "<?php echo _("Per iniziare ti consigliamo di dare un`occhiata a"); ?>&nbsp;<a target='_blank' href='tutorial.php'><?php echo _("questo tutorial introduttivo"); ?></a>!",
				  width: 600
				})
				<?php if(isset($_GET["lang_warning"]) && $_GET["lang_warning"] == "1") { ?>
					.then((result) => {
					lang_warning();
					}) <?php } ?>
			<?php } ?>
			
			<?php
			if(isset($_GET["lang_warning"]) && $_GET["lang_warning"] == "1") {
				?>
				function lang_warning() {
					swal({
					  title: '<?php echo _("Localizzazione"); ?>',
					  type: 'info',
					  html: "<?php echo $solutionname." "._('è al momento disponibile ufficialmente in Inglese e Italiano. Le altre traduzioni potrebbero non essere accurate finché non saranno supportate completamente. Grazie per la comprensione.'); ?>",
					  showCloseButton: false,
					  footer: "<a href='settings.php'><?php echo _('Clicca qui'); ?></a>&nbsp;<?php echo _('per modificare la lingua di visualizzazione predefinita'); ?>."
					});
				}
				<?php
			} ?>
			<!-- END OF WELCOME & LOCAL. MESSAGES -->
		});
		
		$('#preferenza_newsletter').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=NewsLetter&preferenza_valore="+$( "#preferenza_newsletter" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('NewsLetter'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('NewsLetter'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('NewsLetter'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('NewsLetter'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('NewsLetter'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('NewsLetter'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_lingua').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=Lingua&preferenza_valore="+$( "#preferenza_lingua" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					$.ajax({
					type: "POST",
					url: "Translations/setLanguage.php?l="+$( "#preferenza_lingua" ).val(),
					datatype: "html",
					success:function(result)
					{
						preferenceUpdated("success", "<?php echo _('Lingua preferenziale'); ?>", "<?php echo _('Preferenze salvate'); ?>");
						setTimeout( function(){
							window.location.reload();
						}, 2500 );
					}
					});
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Lingua preferenziale'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Lingua preferenziale'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Lingua preferenziale'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Lingua preferenziale'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Lingua preferenziale'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_loginredirect').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=LoginRedirect&preferenza_valore="+$( "#preferenza_loginredirect" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Login redirect'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Login redirect'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Login redirect'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Login redirect'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Login redirect'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Login redirect'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_popupscansione').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=PopupScansione&preferenza_valore="+$( "#preferenza_popupscansione" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Popup scansione'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Popup scansione'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Popup scansione'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Popup scansione'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Popup scansione'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Popup scansione'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_intervallopreferenziale').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=Intervallo&preferenza_valore="+$( "#preferenza_intervallopreferenziale" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Intervallo preferenziale'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Intervallo preferenziale'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Intervallo preferenziale'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Intervallo preferenziale'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Intervallo preferenziale'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Intervallo preferenziale'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_formatodata').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=FormatoData&preferenza_valore="+$( "#preferenza_formatodata" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Formato data'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Formato data'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Formato data'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Formato data'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Formato data'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Formato data'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_riepilogoscansioni').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=RiepilogoScansioni&preferenza_valore="+$( "#preferenza_riepilogoscansioni" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Riepilogo scansioni'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Riepilogo scansioni'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Riepilogo scansioni'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Riepilogo scansioni'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Riepilogo scansioni'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Riepilogo scansioni'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_ricaricamentoautomatico').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=ReloadAuto&preferenza_valore="+$( "#preferenza_ricaricamentoautomatico" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Esito Scansioni Autom.'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Esito Scansioni Autom.'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Esito Scansioni Autom.'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Esito Scansioni Autom.'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Esito Scansioni Autom.'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Esito Scansioni Autom.'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		$('#preferenza_autenticazione2fa').change(function(){
			$.ajax({
			type: "POST",
			url: "Utils/updatePreference.php?preferenza_nome=2FARichiesto&preferenza_valore="+$( "#preferenza_autenticazione2fa" ).val(),
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					preferenceUpdated("success", "<?php echo _('Autenticazione a 2 fattori'); ?>", "<?php echo _('Preferenze salvate'); ?>");
				} else if(result == "failure") {
					preferenceUpdated("error", "<?php echo _('Autenticazione a 2 fattori'); ?>", "<?php echo _('Si è verificato un errore'); ?>");
				} else if(result == "unset") {
					preferenceUpdated("error", "<?php echo _('Autenticazione a 2 fattori'); ?>", "<?php echo _('Errore interno: Parametri insufficienti'); ?>");
				} else if(result == "invalid_type") {
					preferenceUpdated("error", "<?php echo _('Autenticazione a 2 fattori'); ?>", "<?php echo _('Errore interno: Tipo non valido'); ?>");
				} else if(result == "invalid_session") {
					preferenceUpdated("error", "<?php echo _('Autenticazione a 2 fattori'); ?>", "<?php echo _('Sessione non valida: Rieffettua il login'); ?>");
				} else {
					preferenceUpdated("error", "<?php echo _('Autenticazione a 2 fattori'); ?>", "<?php echo _('Errore non pianificato'); ?>:"+result);
				}
			}
			});
		});
		
		var regex = new RegExp("<?php echo $string_check_allowed; ?>");
		
        $("#formcambiousername").submit(function(e) {
			var form = this;
			e.preventDefault();
			$.post('Utils/checkPassword', {
				password: $("#confermapassword2").val()
			},
			function(result) {
				if(result == "success") {
					sendSWAL("info", "<?php echo _("Attendi") ?>", "<?php echo _("Elaborazione richiesta") ?>...");
					if($("#usernamenuovo").val() != "<?php echo $username; ?>") {
						if($("#usernamenuovo").val().length < <?php echo $username_minlength; ?> || $("#usernamenuovo").val().length > <?php echo $username_maxlength; ?>) {
							sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _('L′Username deve avere una lunghezza tra i'); echo " ".$username_minlength; ?> <?php echo _('ed i'); echo " ".$username_maxlength; echo _(' caratteri'); ?>");
						} else {
							if(!regex.test($("#usernamenuovo").val())) {
								sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("L′Username contiene caratteri non validi") ?>");
							} else {
								$.post('Utils/checkBannedWords.php', {
									stringa: $("#usernamenuovo").val()
								},
								function(result) {
									if(result == "not_found") {
										$.post('Utils/checkUsername', {
											usernamescelto: $("#usernamenuovo").val()
										},
										function(result) {
											if(result == "not_found") {
												$.post('Utils/changeUsernameDB', {
													usernamenuovo: $("#usernamenuovo").val()
												},
												function(result) {
													if(result == "success") {
														$("#usernamenuovo").val("");
														$("#confermapassword2").val("");
														sendSWAL("success", "<?php echo _("Cambio Username") ?>", "<?php echo _("Username modificato con successo") ?>");
														setTimeout( function(){
															window.location.reload();
														}, 3000 );
													} else if(result == "failure") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Non è stato possibile cambiare l'Username") ?>");
													} else if(result == "unset") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Compila tutti i campi") ?>");
													} else if(result == "already_exists") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Questo Username esiste già") ?>");
													} else if(result == "error_too_early") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("È passato troppo poco tempo perché tu possa cambiare ancora il tuo Username") ?>");
													} else if(result == "length") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("L′Username deve avere una lunghezza tra i"); echo " ".$username_minlength; ?> <?php echo _('ed i'); echo " ".$username_maxlength; echo _(' caratteri'); ?>");
													} else if(result == "regex") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("L′Username contiene caratteri non validi") ?>");
													} else if(result == "banned_word") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Scegli un altro Username") ?>");
													} else if(result == "invalid_session") {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
													} else {
														sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
													}
												}
												);
											} else if(result == "found") {
												sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Questo Username esiste già") ?>");
											} else if(result == "unset") {
												sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Compila tutti i campi") ?>");
											} else if(result == "invalid_session") {
												sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
											} else {
												sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
											}
										}
										);
									} else if(result == "found") {
										sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Scegli un altro Username") ?>");
									} else if(result == "unset") {
										sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Compila tutti i campi") ?>");
									} else if(result == "invalid_session") {
										sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
									} else {
										sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
									}
								}
								);
							}
						}
					} else {
						sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Questo Username è già in uso") ?>");
					}
				} else if(result == "failure") {
					sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("La password non è corretta") ?>");
				} else if(result == "unset") {
					sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Compila tutti i campi") ?>");
				} else if(result == "id_not_found") {
					sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("L'ID non è stato trovato") ?>");
				} else if(result == "invalid_session") {
					sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
				} else {
					sendSWAL("error", "<?php echo _("Cambio Username") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
				}
			}
			);
        });
		
        $("#formcambiopassword").submit(function(e) {
			var form = this;
			e.preventDefault();
            if($("#passwordnuova1").val() == $("#passwordnuova2").val()) {
                if($("#passwordnuova1").val().length >= <?php echo $password_minlength; ?> && $("#passwordnuova1").val().length <= <?php echo $password_maxlength; ?>) {
					if($("#passwordvecchia").val() != $("#passwordnuova1").val()) {
						sendSWAL("info", "<?php echo _("Attendi") ?>", "<?php echo _("Elaborazione richiesta") ?>...");
						$.post('Password/changePasswordDB', {
							passwordvecchia: $("#passwordvecchia").val(),
							passwordnuova1: $("#passwordnuova1").val(),
							passwordnuova2: $("#passwordnuova2").val()
						},
						function(result) {
							$("#passwordvecchia").val("");
							$("#passwordnuova1").val("");
							$("#passwordnuova2").val("");
							if(result == "success") {
								sendSWAL("success", "<?php echo _("Cambio Password") ?>", "<?php echo _("Password cambiata con successo") ?>");
							} else if(result == "failed") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Non è stato possibile cambiare la password") ?>");
							} else if(result == "unset") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Compila tutti i campi") ?>");
							} else if(result == "empty") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Le password non devono essere vuote") ?>");
							} else if(result == "different_1") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Le nuove password devono corrispondere") ?>");
							} else if(result == "different_2") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("La vecchia password non è corretta") ?>");
							} else if(result == "same_password") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("La password vecchia e quella nuova corrispondono") ?>");
							} else if(result == "invalid_id") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("ID Utente non valido") ?>");
							} else if(result == "invalid_session") {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
							} else {
								sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
							}
						}
						);
					} else {
						sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("La password vecchia e la nuova corrispondono") ?>");
					}
			    } else {
				    sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _('Le password devono avere una lunghezza tra i'); echo " ".$password_minlength; ?> <?php echo _('ed i'); echo " ".$password_maxlength; echo _(' caratteri'); ?>");
                }
            } else {
                sendSWAL("error", "<?php echo _("Cambio Password") ?>", "<?php echo _("Le password non corrispondono") ?>");
            }
        });

        $("#formcambioemail").submit(function(e) {
			var form = this;
			e.preventDefault();
            if($("#emailnuova").val() != "<?php echo $email; ?>") {
				if(isValidEmailAddress($("#emailnuova").val())) {
					$.post('Utils/checkPassword', {
						password: $("#confermapassword").val()
					},
					function(result) {
						if(result == "success") {
							sendSWAL("info", "<?php echo _("Attendi") ?>", "<?php echo _("Elaborazione richiesta") ?>...");
							$.post('Email/changeEmailDB', {
								emailnuova: $("#emailnuova").val()
							},
							function(result) {
								$("#emailnuova").val("");
								$("#confermapassword").val("");
								if(result == "success") {
									sendSWAL("success", "<?php echo _("Cambio Email") ?>", "<?php echo _("Mail di conferma inviata all'indirizzo specificato") ?>");
								} else if(result == "failure") {
									sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Non è stato possibile inviare la mail all'indirizzo specificato") ?>");
								} else if(result == "unset") {
									sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Compila tutti i campi") ?>");
								} else if(result == "already_exists") {
									sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Questo indirizzo Email è già in uso") ?>");
								} else if(result == "failure_token") {
									sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Non è stato possibile impostare il token di sicurezza") ?>");
								} else if(result == "invalid_session") {
									sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
								} else {
									sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
								}
							}
							);
						} else if(result == "failure") {
							sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("La password non è corretta") ?>");
						} else if(result == "unset") {
							sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Compila tutti i campi") ?>");
						} else if(result == "id_not_found") {
							sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("L'ID non è stato trovato") ?>");
						} else if(result == "invalid_session") {
							sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Sessione non valida: Rieffettua il login") ?>");
						} else {
							sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Errore non pianificato") ?>: "+result);
						}
					}
					);
				} else {
					sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Questo indirizzo Email non è valido") ?>");
				}
            } else {
				sendSWAL("error", "<?php echo _("Cambio Email") ?>", "<?php echo _("Questo indirizzo Email è uguale a quello attualmente associato a questo account") ?>");
            }
        });

		<?php if($licenza[7] == true) { ?>
		$("#generate_otp").click(function() {
			swal({
			  title: "<?php echo _('Attendi'); ?>",
			  text: "<?php echo _('Elaborazione richiesta'); ?>...",
			  type: "info",
			  showConfirmButton: false
			});
			$("#otp_sessionstatus").html("<?php echo _('In apertura...'); ?>");
			$.ajax({
			type: "POST",
			url: "Utils/generateOTP.php",
			datatype: "html",
			success:function(result)
			{
				if(result == "ok") {
					Swal.closeModal();
					checkOTP();
					statoprecedente = 0;
				} else if(result == "failure") {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Errore interno: Esecuzione script fallita"); ?>",
					  type: "error"
					});
				} else if(result == "invalid_session") {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
					  type: "error"
					});
				} else {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Errore interno: Esecuzione script fallita"); ?>",
					  type: "error"
					});
				}
			}
			});
		});
		
		$("#delete_otp").click(function() {
			if(statoprecedente == 2) {
				swal({
				  title: "<?php echo _("Chiusura sessione"); ?>",
				  html: "<?php echo _("Cliccando su 'Conferma' la sessione OTP attuale sarà chiusa forzatamente. Sei sicuro?"); ?>",
				  footer: "<font color='lightcoral'><?php echo _("Se una scansione è in corso la sessione sarà chiusa al termine dell'operazione"); ?>.</font>",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonText: '<?php echo _("Conferma"); ?>',
				  cancelButtonText: '<?php echo _("Annulla"); ?>'
				})
				.then((result) => {
				  if (result.value) {
					$("#otp_sessionstatus").html("<?php echo _('In chiusura...'); ?>");
					swal({
					  title: "<?php echo _('Attendi'); ?>",
					  text: "<?php echo _('Elaborazione richiesta'); ?>...",
					  type: "info",
					  showConfirmButton: false
					});
					$.ajax({
					type: "POST",
					url: "Utils/deleteOTP.php",
					datatype: "html",
					success:function(result)
					{
						if(result == "ok") {
							Swal.closeModal();
							checkOTP();
						} else if(result == "failure") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Errore interno: Esecuzione script fallita"); ?>",
							  type: "error"
							});
						} else if(result == "invalid_session") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
							  type: "error"
							});
						} else {
							swal({
							  title: "<?php echo _("Errore non pianificato"); ?>",
							  text: result,
							  type: "error"
							});
						}
					}
					});
				  }
				});
			} else {
				$("#otp_sessionstatus").html("<?php echo _('In chiusura...'); ?>");
				swal({
				  title: "<?php echo _('Attendi'); ?>",
				  text: "<?php echo _('Elaborazione richiesta'); ?>...",
				  type: "info",
				  showConfirmButton: false
				});
				$.ajax({
				type: "POST",
				url: "Utils/deleteOTP.php",
				datatype: "html",
				success:function(result)
				{
					if(result == "ok") {
						Swal.closeModal();
						checkOTP();
					} else if(result == "failure") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Errore interno: Esecuzione script fallita"); ?>",
						  type: "error"
						});
					} else if(result == "invalid_session") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
						  type: "error"
						});
					} else {
						swal({
						  title: "<?php echo _("Errore non pianificato"); ?>",
						  text: result,
						  type: "error"
						});
					}
				}
				});
			}
		});
		<?php } ?>
		
		$("#formcancellazioneaccount").submit(function(e) {
			e.preventDefault();
			if($("#usernameconfermacancellazione").val() == "<?php echo $username; ?>") {
				swal({
				  title: "<?php echo _('Attendi'); ?>",
				  text: "<?php echo _('Elaborazione richiesta'); ?>...",
				  type: "info",
				  showConfirmButton: false
				});
				$.post('Utils/checkPassword.php', {
					password: $("#passwordconfermacancellazione").val()
				},
				function(result) {
					if(result == "success") {
						swal({
						  title: "<?php echo _("Elimina questo account"); ?>",
						  html: "<?php echo _("Cliccando su 'Conferma' i tuoi dati personali saranno eliminati PERMANENTEMENTE senza possibilità di recuperarli").". "._("Confermi l'operazione?")."<br><br><font color='lightcoral'>"; echo _("Le licenze acquistate non saranno rimborsate"); ?>.</font>",
						  footer: "<?php echo _("Il processo potrebbe richiedere alcuni minuti"); ?>.",
						  type: "warning",
						  showCancelButton: true,
						  confirmButtonText: '<?php echo _("Conferma"); ?>',
						  cancelButtonText: '<?php echo _("Annulla"); ?>'
						})
						.then((result) => {
						  if (result.value) {
							swal({
							  title: "<?php echo _('Attendi'); ?>",
							  text: "<?php echo _('Elaborazione richiesta'); ?>...",
							  type: "info",
							  showConfirmButton: false
							});
							$.ajax({
							type: "POST",
							url: "Utils/deleteAccountDB.php",
							datatype: "html",
							success:function(result)
							{
								if(result == "success") {
									swal({
									  title: "<?php echo _('Operazione completata'); ?>",
									  text: "<?php echo _("Il tuo account è stato cancellato. Ritorno alla Homepage in corso..."); ?>",
									  type: "success"
									});
									setTimeout( function(){
										window.location.reload();
									}, 3000 );
								} else if(result == "failure") {
									swal({
									  title: "<?php echo _('Si è verificato un errore'); ?>",
									  text: "<?php echo _("Non è stato possibile elaborare la richiesta"); ?>.",
									  type: "error"
									});
								} else if(result == "invalid_session") {
									swal({
									  title: "<?php echo _('Si è verificato un errore'); ?>",
									  text: "<?php echo _('Sessione utente non valida. Rieffettua il login'); ?>.",
									  type: "error"
									});
								}  else {
									swal({
									  title: "<?php echo _("Errore imprevisto"); ?>",
									  text: result,
									  type: "error"
									});
								}
							}
							});
						  }
						});
					} else if(result == "failure") {
						swal({
						  title: "<?php echo _('Si è verificato un errore'); ?>",
						  text: "<?php echo _('Password errata'); ?>.",
						  type: "error"
						});
					} else if(result == "id_not_found") {
						swal({
						  title: "<?php echo _('Si è verificato un errore'); ?>",
						  text: "<?php echo _("L'ID non è stato trovato"); ?>.",
						  type: "error"
						});
					} else if(result == "unset") {
						swal({
						  title: "<?php echo _('Si è verificato un errore'); ?>",
						  text: "<?php echo _("Compila tutti i campi"); ?>.",
						  type: "error"
						});
					} else {
						swal({
						  title: "<?php echo _('Errore non pianificato'); ?>",
						  text: result,
						  type: "error"
						});
					}
				}
				);
			} else {
				swal({
				  title: "<?php echo _('Si è verificato un errore'); ?>",
				  html: "<?php echo _("Inserisci il tuo Username"); ?>",
				  type: "error"
				})	
			}
		});

    });
</script>