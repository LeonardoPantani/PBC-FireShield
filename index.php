<!DOCTYPE html>
<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";
$id = 0;
readUserData($id, $email, $username, $tipo, $network, $otp);

licenseInformation($licenza, true);
networkInformation($infonetwork);

$risposta_news = getNewsText();
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
	<!-- SPECIALI -->
	<?php if(!isLogged()) { ?><script defer src="https://cdnjs.cloudflare.com/ajax/libs/fitvids/1.2.0/jquery.fitvids.min.js" crossorigin="anonymous"></script><?php } ?>
</head>

<style>
.popover-header { color:black; } 

#page-container {
  position: relative;
}
</style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <audio id="audio">
        <source src="CSS/Sounds/Complete.mp3" type="audio/mpeg" preload="auto"/>
    </audio>
    <div class="container text-center text-white" id="page-container">
        <br><?php if($rispostanews) { echo "<br>"; } ?>
		<!-- FILE IN INPUT -->
		<input hidden type="file" id="file1" name="file1" accept=".zip" required></input>
		<input hidden type="file" id="file2" name="file2" accept=".zip" required></input>
		<input hidden type="file" id="file3" name="file3" accept=".zip" required></input>
		<input hidden type="file" id="file4" name="file4" accept=".zip" required></input>
		<!-- FINE FILE IN INPUT -->
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<?php if (!$analysis && !in_array(getClientIP(), $whitelisted_ips)) { ?>
				<img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="25%" alt="Logo"><h2 style="display:inline;"><?php echo $solutionname; ?></h2>
				<br><br><br>
				<h4 style="color:lightcoral;"><?php echo _("Operazioni di manutenzione in corso, ci scusiamo per il disagio"); ?>.</h4>
				<h6><?php echo _("Il servizio di scansione tornerà disponibile quanto prima"); ?>.</h6>
				<hr>
				<div class="container text-left">
					<h6><?php echo _("Durante la manutenzione si può").":"; ?></h6>
					<ul>
						<li><?php echo _("Effettuare il login"); ?>;</li>
						<li><?php echo _("Acquistare licenze"); ?>;</li>
						<li><?php echo _("Accedere alle proprie Impostazioni"); ?>;</li>
						<li><?php echo _("Effettuare il test dei CPS"); ?>;</li>
						<li><?php echo _("Segnalare problemi riguardanti la piattaforma"); ?>;</li>
						<li><?php echo _("Scaricare lo Screenshare Tool"); ?>.</li>
					</ul>
				</div>
			<?php } elseif($loginrequired && !isLogged()) { ?>
				<br>
				<div class="container" id="container_yt" style="width:75%">
					<iframe src="https://www.youtube.com/embed/pvp5D2OTA38" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
				</div>
				<br>
				<h4><?php echo _("Benvenuto su")." <font color='orange'>".$solutionname_short."</font>!"; ?></h4>
				<h6><?php echo _("Per effettuare scansioni è necessario acquistare una licenza")." <a href='buyLicense/buy.php'>"._("qui")."</a>."; ?></h6>
				<?php if($softwaredownload) { ?>
				<p><?php echo _("Abbiamo anche rilasciato uno Screenshare tool <b>gratuito</b> dotato di numerose funzionalità scaricabile")." <a href='Client/software.php'>"._("qui")."</a>."; ?></p>
				<?php } ?>
				<script>
				document.getElementById('jquery').addEventListener('load', function() {
					  $(document).ready(function(){
						$("#container_yt").fitVids();
					  });
				});
				</script>
			<?php } elseif(!hasTwoFactorAuthentication($id) && isLogged()) { ?>
				<img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="22%" alt="Logo"><h2 style="display:inline;"><?php echo $solutionname; ?></h2>
				<br><br><br>
				<h4 style="color:lightcoral;"><?php echo _("Il servizio di scansioni non è disponibile"); ?></h4>
				<h6><?php echo _("Abilita l'autenticazione a 2 fattori per poter effettuare scansioni"); ?>.</h6>
				<h5><a href="2FACode/create2FA.php" title="<?php echo _("Clicca per iniziare"); ?>"><?php echo _("Iniziamo"); ?>!</a></h5>
			<?php } else { ?>
			<div class="container">
				<div class="row">
					<div class="col-7" style="text-align:right;">
						<img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="30%" alt="Logo">
					</div>
					<?php if($error_message_title != "" && ($error_message_object != "" || $error_message_custom != "")) { ?>
					<div class="col-5">
						<br>
						<div class="card text-white bg-dark border-<?php echo $error_message_color; ?> mb-3" style="width: 100%;">
						  <div class="card-body">
							<p class="card-text text-right text-<?php echo $error_message_color; ?>">
								<b>⚠️ <?php if($error_message_title != "CLOSE") { echo $error_message_title; } else { echo sprintf(_("CHIUSURA DI %s"), strtoupper($solutionname_short)); } ?></b>
								<br>
								<?php if($error_message_custom != "") {
										if($error_message_custom == "CLOSE") { 
											echo sprintf(_("Il %s chiuderà definitivamente in data %s. Per maggiori informazioni clicca %squi%s."), $solutionname, $data_error, "<a href='$link_error'>", "</a>");
										} else { 
											echo $error_message_custom;
										}
									} elseif($error_message_color == "danger") {
									echo _("È sorto un problema grave con la seguente funzionalità"); echo ": ".$error_message_object.". "; echo _("Per questo motivo quest'ultima potrebbe non funzionare come previsto e consigliamo di non utilizzarla"); ?>.<?php  _("Ci scusiamo per il disagio"); ?>
								<?php } elseif($error_message_color == "warning") {
									echo _("È sorto un problema con la seguente funzionalità"); echo ": ".$error_message_object.". "; echo _("Per questo motivo quest'ultima potrebbe subire rallentamenti o errori durante il periodo di permanenza di questo avviso"); ?>.<?php  _("Ci scusiamo per il disagio"); ?>
								<?php } elseif($error_message_color == "info") {
									echo _("È in fase di modifica e aggiornamento la seguente funzionalità"); echo ": ".$error_message_object.". "; echo _("Per questo motivo quest'ultima potrebbe essere temporaneamente non disponibile mentre viene aggiornata"); ?>.<?php  _("Ci scusiamo per il disagio"); ?>
								<?php } else {
									echo _("È sorto un problema con"); echo ": ".$error_message_object.". "; echo _("Per questo motivo"); echo " ".$solutionname_short." "; echo _("potrebbe non funzionare come previsto"); ?>. <?php  _("Ci scusiamo per il disagio"); ?>.
								<?php } // In caso di problemi viene stampato il messaggio di errore standard
								?> 
							</p>
						  </div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
			<p>
			<?php echo _("Inserisci i risultati di ricerca di ProcessHacker™ nella rispettiva sezione. La dimensione massima per ogni file è")." <b>".($pesomax/1000000)."MB</b>"; ?>.<br>
			</p>
			<div class="container text-center text-dark" style="width: 90%">
				<div class="row">
					<div class="card bg-light" style="width: 13rem; float: none; margin: 0 auto;"> <!-- Reale: 1 -->
						<div class="card-body">
							<h5 class="card-title"><a id="popover_check1" title="<?php echo _('Durata scansione stimata'); ?>" data-toggle="tooltip"><?php echo _("Check"); ?> 1</a></h5>
							<p class="card-text" id="r1"><img alt="<?php echo _("Check"); ?> 1" src="CSS/Images/result_default.png" style="width: 32px"><br><br></p>
							<span class="card-text" id="time1"><br></span>
							<div class="progress" style="height: 15px;">
								<div id="barraupload1" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>

					<div class="card bg-light" style="width: 13rem; float: none; margin: 0 auto;"> <!-- Reale: 2 -->
						<div class="card-body">
							<h5 class="card-title"><a id="popover_check2" title="<?php echo _('Durata scansione stimata'); ?>" data-toggle="tooltip"><?php echo _("Check"); ?> 2</a></h5>
							<p class="card-text" id="r2"><img alt="<?php echo _("Check"); ?> 2" src="CSS/Images/result_default.png" style="width: 32px"><br><br></p>
							<span class="card-text" id="time2"><br></span>
							<div class="progress" style="height: 15px;">
								<div id="barraupload2" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>

					<div class="card bg-light" style="width: 13rem; float: none; margin: 0 auto;"> <!-- Reale: 3 -->
						<div class="card-body">
							<h5 class="card-title"><a id="popover_check3" title="<?php echo _('Durata scansione stimata'); ?>" data-toggle="tooltip"><?php echo _("Check"); ?> 3</a></h5>
							<p class="card-text" id="r3"><img alt="<?php echo _("Check"); ?> 3" src="CSS/Images/result_default.png" style="width: 32px"><br><br></p>
							<span class="card-text" id="time3"><br></span>
							<div class="progress" style="height: 15px;">
								<div id="barraupload3" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>

					<div class="card bg-light" style="width: 13rem; float: none; margin: 0 auto;"> <!-- Reale: 4 -->
						<div class="card-body">
							<h5 class="card-title"><a id="popover_check4" title="<?php echo _('Durata scansione stimata'); ?>" data-toggle="tooltip"><?php echo _("Check"); ?> 4</a></h5>
							<p class="card-text" id="r4"><img alt="<?php echo _("Check"); ?> 4" src="CSS/Images/result_default.png" style="width: 32px"><br><br></p>
							<span class="card-text" id="time4"><br></span>
							<div class="progress" style="height: 15px;">
								<div id="barraupload4" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="container text-center" style="width: 80%">
				<div class="row">
					<div class="col-xs-4 inserimento" style="float: none; margin: 0 auto">
						<button <?php if ($otp && $_SESSION["scansioni_effettuate"] == 4) {echo "disabled title='"._("Sono consentite solo 4 scansioni per i login OTP")."'";}?> class="btn btn-secondary btn-lg btn-block btn-sm" type="button" id="button1"><?php echo _("Inserisci"); ?> Javaw.exe</button>
					</div>
					
					<div class="col-xs-4 inserimento" style="float: none; margin: 0 auto">
						<button <?php if ($otp && $_SESSION["scansioni_effettuate"] == 4) {echo "disabled title='"._("Sono consentite solo 4 scansioni per i login OTP")."'";}?> class="btn btn-secondary btn-lg btn-block btn-sm" type="button" id="button2"><?php echo _("Inserisci"); ?> Dwm.exe</button>
					</div>

					<div class="col-xs-4 inserimento" style="float: none; margin: 0 auto">
						<button <?php if ($otp && $_SESSION["scansioni_effettuate"] == 4) {echo "disabled title='"._("Sono consentite solo 4 scansioni per i login OTP")."'";}?> class="btn btn-secondary btn-lg btn-bloc btn-sm" type="button" id="button3"><?php echo _("Inserisci"); ?> MsMpEng.exe <small><?php echo _("o Log Antivirus"); ?></small></button>
					</div>

					<div class="col-xs-4 inserimento" style="float: none; margin: 0 auto">
						<button <?php if ($otp && $_SESSION["scansioni_effettuate"] == 4) {echo "disabled title='"._("Sono consentite solo 4 scansioni per i login OTP")."'";}?> class="btn btn-secondary btn-lg btn-block btn-sm" type="button" id="button4"><?php echo _("Inserisci"); ?> lsass.exe</button>
					</div>
				</div>
			</div>
			<br>
			<p id="log">
				<small><?php echo _("Inserisci TUTTI i file per effettuare una scansione più completa ed efficace. Alcuni cheats possono essere trovati solo inserendo tutti i file."); ?></small>
			</p>
			<!-- Messaggio di errore -->
			<div id="error_log" style="display: none;">
				<div class="card text-white bg-danger mx-auto" style="width: 50%;">
				  <div class="card-body" align="center">
					<p class="card-text">⚠️ <b><?php echo _("Codice errore"); ?>: <span id="error_code">???</span></b>
					<br>
					<span id="error_text"><?php echo _("Errore non specificato"); ?></span>
					<br>
					<span id="error_report"><a href="ticket.php"></a></span>
					</p>
				  </div>
				</div>
				<br>
			</div>
			<!-- -->
			<button <?php if ($otp && $_SESSION["scansioni_effettuate"] == 4) {echo "disabled title='"._("Sono consentite solo 4 scansioni per i login OTP")."'";}?> class="btn btn-success" type="button" id="invio"><?php echo _("Inizia la scansione"); ?></button>
			<?php } ?>
			<br>
			<div class="container text-right">
				<a target="_blank" title="Telegram" href="https://t.me/NewsFSCD"><img src="CSS/Images/logo_telegram.png" width="25px" length="25px"/></a> &nbsp; <a target="_blank" title="Discord" href="https://discord.gg/H6BWDeF"><img src="CSS/Images/logo_discord.png" width="25px" length="25px"/></a>
			</div>
			<?php if ($risposta_news) { 
				if(($mainnews_condition == "not_logged" && !isLogged()) || ($mainnews_condition == "logged" && isLogged()) || $mainnews_condition == "always") {
				?><hr>
				<marquee scrolldelay="60" behavior="scroll" direction="left"><b><?php echo $risposta_news[0];
				echo ":</b> ";
				echo $risposta_news[1]; ?></marquee>
				<?php } else {
					echo "<br>";
				}
			}
			?>
		</div>
		<?php
			bindtextdomain("tutorial", $locale);
			bind_textdomain_codeset("tutorial", 'UTF-8');
			textdomain("tutorial");
			
		if (($analysis || in_array(getClientIP(), $whitelisted_ips)) && isLogged()) { ?>
		<br><br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<br>
            <h3><?php echo sprintf(_("Tutorial del %s"), $solutionname); ?></h3>
			<p><small><?php echo sprintf(_("Se preferisci, abbiamo pubblicato un video che spiega come utilizzare lo Scanner: %sclicca qui per vederlo%s."), "<a href='https://youtu.be/uRvcUNI1XJ4'>", "</a>"); ?></small></p>
			<hr>
            <div class="container">
                <div class="row">
                    <div class="col-sm">
                    <a href="https://i.imgur.com/MKO7FlH.jpg"><img width="100%" src="https://i.imgur.com/MKO7FlH.jpg"></a>
                    </div>
                    <div class="col-sm text-right">
					<?php echo sprintf(_("%sPasso 1:%s Scarica Process Hacker da %squesto link%s o premi il pulsante 'Process Hacker' nel %s Software."), "<b>", "</b>", "<a href='https://wj32.org/processhacker/rel/processhacker-2.39-bin.zip'>", "</a>", $solutionname); ?>
                    </div>
                </div>
                <hr> <!-- Fine prima parte -->
                <div class="row">
                    <div class="col-sm text-left">
					<?php echo sprintf(_("%sPasso 2:%s Per trovare i cheat più sofisticati è necessario l'utilizzo di 4 processi: Javaw.exe, Dwm.exe, MsMpEng.exe (o log Antivirus) e lsass.exe.<br><br>Mostreremo come ottenere le stringhe da uno solo di questi processi.<br><br>In questo caso abbiamo scelto il processo Javaw.exe."), "<b>", "</b>"); ?>
                    </div>
                    <div class="col-sm">
                    <a href="https://i.imgur.com/3PA9P0m.png"><img width="100%" src="https://i.imgur.com/3PA9P0m.png"></a>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm">
                    <a href="https://i.imgur.com/4UoJKCW.png"><img width="75%" src="https://i.imgur.com/4UoJKCW.png"></a>
                    </div>
                    <div class="col-sm text-right">
					<?php echo sprintf(_("%sPasso 3:%s Click destro e poi 'Proprietà' oppure clicca sul processo e poi premi Invio; leva la spunta da 'nascondi regioni vuote', clicca su 'Strings', scrivi 4 e spunta 'image' e 'mapped' e premi 'Ok'."), "<b>", "</b>"); ?>
                    </div>
                </div>
			    <hr> <!-- Fine seconda parte -->
                <div class="row">
                    <div class="col-sm text-left">
                    <?php echo sprintf(_("%sPasso 4:%s Ora clicca su 'Salva...' per salvare i risultati della ricerca.<br><br>ATTENZIONE: Potrebbe volerci un po' di tempo in base alle prestazioni del computer dell'utente."), "<b>", "</b>"); ?>
                    </div>
                    <div class="col-sm">
                    <a href="https://i.imgur.com/Ea9wraZ.png""><img width="75%" src="https://i.imgur.com/Ea9wraZ.png"></a>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm">
                    <a href="https://i.imgur.com/dZziwgn.png"><img width="100%" src="https://i.imgur.com/dZziwgn.png"></a>
                    </div>
                    <div class="col-sm text-right">
                    <?php echo sprintf(_("%sPasso 5:%s Scegli una cartella dove vuoi salvare il file contenente le stringhe estratte da Process Hacker e poi premi 'Salva'."), "<b>", "</b>"); ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm text-left">
                    <?php echo sprintf(_("%sPasso 6:%s Se desideri una velocità di upload migliore, puoi comprimere il file utilizzando il %s Software. Altrimenti premi il tasto destro del mouse sul file .txt e comprimilo.<br><br>Si possono anche utilizzare altri strumenti di compressione come 7Zip o WinRar, basta che l'estensione del file sia .zip."), "<b>", "</b>", $solutionname); ?>
                    </div>
                    <div class="col-sm">
                    <a href="https://i.imgur.com/k6AYnYJ.png"><img width="75%" src="https://i.imgur.com/k6AYnYJ.png"></a>
                    </div>
                </div>
			    <hr> <!-- Fine terza parte -->
                <div class="row">
                    <div class="col-sm">
                    <a href="https://i.imgur.com/qdcIjJE.jpg"><img width="100%" src="https://i.imgur.com/qdcIjJE.jpg"></a>
                    </div>
                    <div class="col-sm text-right">
                    <?php echo sprintf(_("%sPasso 7:%s Ripeti questi passi per gli altri processi (opzionale).<br>Adesso passa allo Scanner e clicca su 'Inserisci *nomeprocesso*' per caricare i risultati di ricerca di Process Hacker.<br>Minimo: 1 file, Massimo: 4 file.<br><br>Consigliamo di caricare tutti e 4 i file per avere una scansione più accurata e completa, ma puoi anche caricare i 4 file separatamente."), "<b>", "</b>"); ?>
                    </div>
                </div>
			    <hr> <!-- Fine quarta parte -->
                <div class="row">
                    <div class="col-sm text-left">
                    <?php echo sprintf(_("%sPasso finale:%s Clicca su 'Inizia la scansione' e aspetta che lo scanner completi la scansione. La durata approssimativa per ogni file è visibile passando il mouse sopra l'icona 'i' di ogni processo."), "<b>", "</b>"); ?>
                    </div>
                    <div class="col-sm">
                    <a href="https://i.imgur.com/b2GOO0O.jpg"><img width="100%" src="https://i.imgur.com/b2GOO0O.jpg"></a>
                    </div>
                </div>
			    <hr> <!-- Fine quinta parte -->
                <div class="row">
                    <div class="col-sm">
					<?php echo _("Se il giocatore non sta facendo uso di cheats, tutti e 4 i risultati mostreranno 'Pulito'."); ?>
                    </div>
                    <div class="col-sm text-right">
					<?php echo _("Altrimenti lo Scanner ti informerà con un avviso di colore rosso (Cheat) o giallo (Sospetto Cheat)."); ?>
                    </div>
                </div>
				<br>
                <div class="row">
                    <div class="col-sm">
					<a href="https://i.imgur.com/b2GOO0O.jpg"><img width="100%" src="https://i.imgur.com/b2GOO0O.jpg"></a>
                    </div>
                    <div class="col-sm text-right">
                    <a href="https://i.imgur.com/qM9mZ6d.jpg"><img width="100%" src="https://i.imgur.com/qM9mZ6d.jpg"></a>
                    </div>
                </div>
			    <hr> <!-- Fine tutorial -->
                <br>
                <?php echo sprintf(_("Grazie per aver scelto %s%s%s"), "<b>", $solutionname, "</b>!"); ?>
                <br><br>
            </div>
        </div>
		
		<?php
			}
			bindtextdomain("index", $locale);
			bind_textdomain_codeset("index", 'UTF-8');
			textdomain("index");
		?>
    </div>
	<?php if(!$analysis || $loginrequired) { echo "<br><br><br><br>"; } ?>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<?php if(($analysis && ($loginrequired && isLogged())) || ($analysis && !$loginrequired) || (!$analysis && in_array(getClientIP(), $whitelisted_ips))) { ?>
<script>
    var scansione = false;
    window.onbeforeunload = function() {
        if(scansione) {
            return true;
        }
    };
	
	function bigFileWarning(pesofile) {
		swal({
		  position: 'top-end',
		  type: 'warning',
		  title: '<?php echo _("Avviso di file pesante"); ?> ('+pesofile+'MB)',
		  html: '<?php echo _("La scansione potrebbe richiedere diversi minuti."); ?>',
		  footer: '<?php echo _("Consigliamo il nostro Screenshare Tool che contiene il compressore."); ?>&nbsp;<a target="_blank" href="Client/download.php?tipofile=5"><?php echo _("Download"); ?></a>',
		  showConfirmButton: false,
		  toast: true,
		  timer: 10000
		})	
	}
	
	function generaInfo(id1, id2, id3, id4, r1, r2, r3, r4, r) {
		if(r1 != null || r2 != null || r3 != null || r4 != null) {
			$("#log").html("<a href='#' onClick='moreInfo(`"+id1+"`, `"+id2+"`, `"+id3+"`, `"+id4+"`, `"+r1+"`, `"+r2+"`, `"+r3+"`, `"+r4+"`, `"+r+"`); return false;'><?php echo _("Clicca qui per maggiori informazioni sulla scansione"); ?></a>");
		}
	}
	
	function rimuoviInfo() {
		$("#log").html("<small><?php echo _('Inserisci TUTTI i file per effettuare una scansione più completa ed efficace. Alcuni cheats possono essere trovati solo inserendo tutti i files'); ?>.</small>");
	}
	
	function moreInfo(id1, id2, id3, id4, r1, r2, r3, r4, r) {
		swal({
		  title: '<?php echo _("Maggiori informazioni"); ?>',
		  type: 'info',
		  html:
			(r1 != "undefined" && r1 != null ? "<?php echo _('Risultato check'); ?> 1: <a title='<?php echo _("Clicca per altre informazioni"); ?>' target='_blank' href='scan.php?id="+id1+"'>"+r1+"</a><br>" : "") +
			(r2 != "undefined" && r2 != null ? "<?php echo _('Risultato check'); ?> 2: <a title='<?php echo _("Clicca per altre informazioni"); ?>' target='_blank' href='scan.php?id="+id2+"'>"+r2+"</a><br>" : "") +
			(r3 != "undefined" && r3 != null ? "<?php echo _('Risultato check'); ?> 3: <a title='<?php echo _("Clicca per altre informazioni"); ?>' target='_blank' href='scan.php?id="+id3+"'>"+r3+"</a><br>" : "") +
			(r4 != "undefined" && r4 != null ? "<?php echo _('Risultato check'); ?> 4: <a title='<?php echo _("Clicca per altre informazioni"); ?>' target='_blank' href='scan.php?id="+id4+"'>"+r4+"</a><br>" : ""),
		  showCloseButton: false,
		  footer: "<i>"+(r == "result_suspect" ? "<?php echo _('Avviso'); ?>: <?php echo _('Il risultato di questa scansione non è sicuro al 100%').".<br>"; echo _('Sono necessarie ulteriori verifiche'); ?>." : "<?php echo _('Consiglio'); ?>: <?php echo _('Puoi cliccare sul risultato per visualizzare tutte le informazioni relative a quella scansione'); ?>")+"</i>"
		});
	}
	
	<?php if($otp) { ?>
		function endOTPScans() {
			$("#invio").prop("disabled", true);
			$("#invio").prop("title", "<?php echo _('Sono consentite solo 4 scansioni per i login OTP'); ?>");
			
			for(var i = 1; i <= 4; i++) {
				$("#button"+i).prop("disabled", true);
				$("#button"+i).prop("title", "<?php echo _('Sono consentite solo 4 scansioni per i login OTP'); ?>");
			}
		}
	<?php } ?>
	
	console.log("[i] <?php echo _('Inizializzazione del servizio di scansione'); ?>...");
	document.getElementById('jquery').addEventListener('load', function() {
		$("#audio")[0].load();
		console.log("[i] <?php echo _('Servizio di scansione caricato con successo'); ?>.");
		// Inizializzazione Popup
		$(function () {
			$('[data-toggle="popover"]').popover();
		});
		
		
		function showErrorMessage(codice_errore, testo) {
			$("#error_code").html(codice_errore);
			$("#error_text").html(testo);
			$("#error_report").html("<small><a href='ticket.php'><?php echo _("Clicca qui per segnalare il problema"); ?></a></small>");
			$("#log").hide();
			$("#error_log").show();
		}
		
		function hideErrorMessage() {
			$("#log").show();
			$("#error_log").hide();
		}
		
		$(document).ready(function() {
			$(document).on("click", "#invio", function() {
				var fileselezionati = 0, completati = 0, errore = false, i, form_data;
				var risposta1, risposta2, risposta3, risposta4;
				var id1, id2, id3, id4;
				var esito1, esito2, esito3, esito4;
				
				for(i = 1; i <= 4; i++) {
					if($("#file"+i).get(0).files.length != 0) {
						fileselezionati++;
					}
				}
				if(fileselezionati != 0) {
					// --- CONTROLLO CHE I FILE SELEZIONATI SIANO .zip
					var problema = false;
					var peso = false;
					for(i = 1; i <= 4; i++) {
						if($("#file"+i).get(0).files.length != 0 && $("#file"+i).val().split(".").pop() != "zip") {
							problema = true;
							break;
						} else {
							switch(i) {
								case 1:
									if($("#file1").get(0).files.length != 0) {
										var file1 = document.getElementById("file"+i).files[0];
										if((document.getElementById("file"+i).files[0].size / 1000000) > <?php echo $pesomax / 1000000; ?>) {
											peso = true;
										}
									}
								break;
								case 2:
									if($("#file2").get(0).files.length != 0) {
										var file2 = document.getElementById("file"+i).files[0];
										if((document.getElementById("file"+i).files[0].size / 1000000) > <?php echo $pesomax / 1000000; ?>) {
											peso = true;
										}
									}
								break;
								case 3:
									if($("#file3").get(0).files.length != 0) {
										var file3 = document.getElementById("file"+i).files[0];
										if((document.getElementById("file"+i).files[0].size / 1000000) > <?php echo $pesomax / 1000000; ?>) {
											peso = true;
										}
									}
								break;
								case 4:
									if($("#file4").get(0).files.length != 0) {
										var file4 = document.getElementById("file"+i).files[0];
										if((document.getElementById("file"+i).files[0].size / 1000000) > <?php echo $pesomax / 1000000; ?>) {
											peso = true;
										}
									}
								break;
							}
						}
					}
					if(!problema) {
						if(!peso) {
							$("#invio").prop("disabled", true);
							hideErrorMessage()
							rimuoviInfo();
							for(i = 1; i <= 4; i++) {
								$('#barraupload'+i).removeClass("bg-success bg-warning bg-danger");
								$('#barraupload'+i).addClass("progress-bar-striped progress-bar-animated bg-info");
								$('#barraupload'+i).css('width', 0+'%');
								$('#barraupload'+i).attr('aria-valuenow', 0);
								$('#barraupload'+i).text("");
								$('#r'+i).html("<br>");
								$(document).prop('title', '<?php echo _("Scansiono..."); ?> | <?php echo $solutionname; ?>');
								scansione = true;
							}
							//---------- 1° FILE
							if(typeof file1 !== 'undefined') {
								form_data = new FormData();
								form_data.append("file", file1);
								form_data.append("controllo", 1);
								form_data.append("idutente", <?php echo $id; ?>);
								$.ajax({
								// Your server script to process the upload
								url: 'analysis.php',
								data: form_data,
								type: 'POST',
								cache: false,
								contentType: false,
								processData: false,

								beforeSend: function(risposta) {
									$("#file1").prop("disabled", true);
									$("#button1").prop("disabled", true);
									scansione = true;
								},
								success: function(risposta) {
									$("#popover_check1").tooltip('hide');
									$('#popover_check1').attr('title', "<?php echo _("Durata scansione stimata"); ?>")
									scansione = false;
									errore = false;
									risposta = risposta.split(":");
									stringa_risposta = risposta[2];
									id_risposta = risposta[1];
									risposta = risposta[0];
									esito1 = risposta;
									switch(risposta) {
										// Esiti
										//--------- CHEAT
										case "result_cheat":
											risposta1 = stringa_risposta;
											id1 = id_risposta;
											$('#r1').html("<img src='CSS/Images/result_cheat.png' style='width: 32px'><br><?php echo _('Cheat Rilevati'); ?>");
											$("#barraupload1").removeClass("bg-info");
											$("#barraupload1").addClass("bg-danger");
										break;

										//--------- SUSPECT
										case "result_suspect":
											risposta1 = stringa_risposta;
											id1 = id_risposta;
											$('#r1').html("<img src='CSS/Images/result_suspect.png' style='width: 32px'><br><?php echo _('Sospetto'); ?>");
											$("#barraupload1").removeClass("bg-info");
											$("#barraupload1").addClass("bg-warning");
										break;

										//--------- CLEAN
										case "result_clean":
											$('#r1').html("<img src='CSS/Images/result_clean.png' style='width: 32px'><br><?php echo _('Pulito'); ?>");
											$("#barraupload1").removeClass("bg-info");
											$("#barraupload1").addClass("bg-success");
										break;

										// Errori

										// --- di sistema
										case "error_remove":
											showErrorMessage(1, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unset":
											showErrorMessage(2, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unzip":
											showErrorMessage(3, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_extract":
											showErrorMessage(4, "<?php echo _('Il file compresso deve avere lo stesso nome del file .txt!'); ?>");
											errore = true;
										break;

										case "error_rename":
											showErrorMessage(5, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_open":
											showErrorMessage(6, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_getstring":
											showErrorMessage(7, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historycheat":
											showErrorMessage(8, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historyanalyzer":
											showErrorMessage(9, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										// --- utente

										case "error_upload":
											showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
											errore = true;
										break;

										case "error_size":
											showErrorMessage(11, "<?php echo _('Il file che hai caricato è troppo grande.'); ?>");
											errore = true;
										break;

										case "error_extension":
											showErrorMessage(12, "<?php echo _('L’estensione del file caricato non è valida.'); ?>");
											errore = true;
										break;
										
										case "error_invalid":
											showErrorMessage(13, "<?php echo _('Il file che hai caricato non è valido.'); ?>");
											errore = true;
										break;
										
										case "error_max_otp":
											showErrorMessage(14, "<?php echo _('Il limite di scansioni per account OTP è stato raggiunto.'); ?>");
											errore = true;
										break;
										
										// ---

										case "error_unavailable":
											showErrorMessage(15, "<?php echo _('Il servizio non è al momento disponibile, riprova più tardi.'); ?>");
											errore = true;
										break;
										

										case "error_loginrequired":
											showErrorMessage(16, "<?php echo _('Il login è richiesto per poter effettuare scansioni.'); ?>");
											errore = true;
										break;
										
										case "error_license":
											showErrorMessage(17, "<?php echo _('La licenza è scaduta.'); ?>");
											errore = true;
										break;

										// Default

										default:
											showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
											errore = true;
										break;
									}
									if(errore) {
										$('#r1').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
									}
								},
								error: function(risposta) {
									if(risposta == "[object Object]") {
										showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
									} else {
										showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
									}
									errore = true;
									$('#r1').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
								},
								complete: function(risposta) {
									completati++;
									$("#file1").prop("disabled", false);
									$("#file1").val("");
									$("#button1").prop("disabled", false);
									$("#button1").html("<?php echo _('Inserisci'); ?> Javaw.exe");
									if(completati == fileselezionati) {
										$("#invio").prop("disabled", false);
										$(document).prop('title', '<?php echo _("Scansione completata!"); ?> | <?php echo $solutionname; ?>');
										$("#audio")[0].play();
										scansione = false;
										
										<?php 
										if($otp) {
											?>
											if(completati == 4) {
												endOTPScans();
											}
											<?php
										} 
										?>
										
										generaInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito1);
										<?php
										if(getPreference($id, "PopupScansione") == 1) {
											?>
											if(risposta1 != null || risposta2 != null || risposta3 != null || risposta4 != null) {
												moreInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito1);
											}
											<?php
										}
										?>
									}
								},
								xhr: function() {
									var myXhr = $.ajaxSettings.xhr();
									var percentuale;
									if (myXhr.upload) {
										var iniziato_a = new Date();
										// For handling the progress of the upload
										myXhr.upload.addEventListener('progress', function(e) {
											if (e.lengthComputable) {
												// Calcolo tempo stimato
												var dati_caricati = e.loaded;
												var dati_totali = e.total;
												var secondi_passati = (new Date().getTime() - iniziato_a.getTime())/1000;
												var byte_al_secondo = secondi_passati ? dati_caricati / secondi_passati : 0 ;
												var byte_rimasti = dati_totali - dati_caricati;
												var secondi_rimasti = secondi_passati ? Math.round(byte_rimasti / byte_al_secondo) : "<?php echo _('Calcolo...'); ?>";
												if(secondi_rimasti == 1) {
													$("#time1").html("<small>"+secondi_rimasti+" <?php echo _('secondo rimasto'); ?></small>");
												} else {
													$("#time1").html("<small>"+secondi_rimasti+" <?php echo _('secondi rimasti'); ?></small>");
												}
												
												percentuale = (100 * e.loaded) / e.total;
												percentuale = Math.round(percentuale);
												if(percentuale != 100) {
													console.log("<?php echo _('Progresso upload'); ?> (1): "+percentuale+"%");
													$("#r1").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Carico...'); ?>'><br><?php echo _('Carico...'); ?>");
												} else {
													console.log("<?php echo _('Upload completato!'); ?> (1)");
													$("#r1").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Scansiono...'); ?>'><br><?php echo _('Scansiono...'); ?>");
													$("#barraupload1").removeClass("progress-bar-striped progress-bar-animated");
													$("#time1").html("<br>");
													
													$.post('Utils/getApproximateTime.php', {
														pesofile: e.total,
														tipocontrollo: 1
													},
													function(result) {
														$('#popover_check1').attr('title', "<?php echo _("Durata scansione stimata"); ?>: "+result)
														$("#popover_check1").tooltip('show');
													});
												}
												$('#barraupload1').css('width', percentuale+'%');
												$('#barraupload1').attr('aria-valuenow', percentuale);
												$('#barraupload1').text(percentuale+"%");
											}
										} , false);
									}
									return myXhr;
								}
								});
							} else {
								$('#r1').html("<img src='CSS/Images/result_skipped.png' style='width: 32px'><br>(<?php echo _('Saltato'); ?>)");
							}

							//---------- FINE 1° FILE

							//---------- 2° FILE
							if(typeof file2 !== 'undefined') {
								form_data = new FormData();
								form_data.append("file", file2);
								form_data.append("controllo", 2);
								form_data.append("idutente", <?php echo $id; ?>);
								$.ajax({
								// Your server script to process the upload
								url: 'analysis.php',
								data: form_data,
								type: 'POST',
								cache: false,
								contentType: false,
								processData: false,

								beforeSend: function(risposta) {
									$("#file2").prop("disabled", true);
									$("#button2").prop("disabled", true);
									scansione = true;
								},
								success: function(risposta) {
									$("#popover_check2").tooltip('hide');
									$('#popover_check2').attr('title', "<?php echo _("Durata scansione stimata"); ?>")
									scansione = false;
									errore = false;
									risposta = risposta.split(":");
									stringa_risposta = risposta[2];
									id_risposta = risposta[1];
									risposta = risposta[0];
									esito2 = risposta;
									switch(risposta) {
										// Esiti
										//--------- CHEAT
										case "result_cheat":
											risposta2 = stringa_risposta;
											id2 = id_risposta;
											$('#r2').html("<img src='CSS/Images/result_cheat.png' style='width: 32px'><br><?php echo _('Cheat Rilevati'); ?>");
											$("#barraupload2").removeClass("bg-info");
											$("#barraupload2").addClass("bg-danger");
										break;

										//--------- SUSPECT
										case "result_suspect":
											risposta2 = stringa_risposta;
											id2 = id_risposta;
											$('#r2').html("<img src='CSS/Images/result_suspect.png' style='width: 32px'><br><?php echo _('Sospetto'); ?>");
											$("#barraupload2").removeClass("bg-info");
											$("#barraupload2").addClass("bg-warning");
										break;

										//--------- CLEAN
										case "result_clean":
											$('#r2').html("<img src='CSS/Images/result_clean.png' style='width: 32px'><br><?php echo _('Pulito'); ?>");
											$("#barraupload2").removeClass("bg-info");
											$("#barraupload2").addClass("bg-success");
										break;

										// Errori

										// --- di sistema
										case "error_remove":
											showErrorMessage(1, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unset":
											showErrorMessage(2, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unzip":
											showErrorMessage(3, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_extract":
											showErrorMessage(4, "<?php echo _('Il file compresso deve avere lo stesso nome del file .txt all’interno.'); ?>");
											errore = true;
										break;

										case "error_rename":
											showErrorMessage(5, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_open":
											showErrorMessage(6, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_getstring":
											showErrorMessage(7, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historycheat":
											showErrorMessage(8, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historyanalyzer":
											showErrorMessage(9, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										// --- utente

										case "error_upload":
											showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
											errore = true;
										break;

										case "error_size":
											showErrorMessage(11, "<?php echo _('Il file che hai caricato è troppo grande.'); ?>");
											errore = true;
										break;

										case "error_extension":
											showErrorMessage(12, "<?php echo _('L’estensione del file caricato non è valida.'); ?>");
											errore = true;
										break;
										
										case "error_invalid":
											showErrorMessage(13, "<?php echo _('Il file che hai caricato non è valido.'); ?>");
											errore = true;
										break;
										
										case "error_max_otp":
											showErrorMessage(14, "<?php echo _('Il limite di scansioni per account OTP è stato raggiunto.'); ?>");
											errore = true;
										break;
										
										// ---

										case "error_unavailable":
											showErrorMessage(15, "<?php echo _('Il servizio non è al momento disponibile, riprova più tardi.'); ?>");
											errore = true;
										break;
										

										case "error_loginrequired":
											showErrorMessage(16, "<?php echo _('Il login è richiesto per poter effettuare scansioni.'); ?>");
											errore = true;
										break;

										case "error_license":
											showErrorMessage(17, "<?php echo _('La licenza è scaduta.'); ?>");
											errore = true;
										break;

										// Default

										default:
											showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
											errore = true;
										break;
									}
									if(errore) {
										$('#r2').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
									}
								},
								error: function(risposta) {
									if(risposta == "[object Object]") {
										showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
									} else {
										showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
									}
									errore = true;
									$('#r2').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
								},
								complete: function(risposta) {
									completati++;
									$("#file2").prop("disabled", false);
									$("#file2").val("");
									$("#button2").prop("disabled", false);
									$("#button2").html("<?php echo _('Inserisci'); ?> Dwm.exe");
									if(completati == fileselezionati) {
										$("#invio").prop("disabled", false);
										$(document).prop('title', '<?php echo _("Scansione completata!"); ?> | <?php echo $solutionname; ?>');
										$("#audio")[0].play();
										scansione = false;
										
										<?php 
										if($otp) {
											?>
											if(completati == 4) {
												endOTPScans();
											}
											<?php
										} 
										?>
										
										generaInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito2);
										<?php
										if(getPreference($id, "PopupScansione") == 1) {
											?>
											if(risposta1 != null || risposta2 != null || risposta3 != null || risposta4 != null) {
												moreInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito2);
											}
											<?php
										}
										?>
									}
								},
								xhr: function() {
									var myXhr = $.ajaxSettings.xhr();
									var percentuale;
									if (myXhr.upload) {
										var iniziato_a = new Date();
										// For handling the progress of the upload
										myXhr.upload.addEventListener('progress', function(e) {
											if (e.lengthComputable) {
												// Calcolo tempo stimato
												var dati_caricati = e.loaded;
												var dati_totali = e.total;
												var secondi_passati = (new Date().getTime() - iniziato_a.getTime())/1000;
												var byte_al_secondo = secondi_passati ? dati_caricati / secondi_passati : 0 ;
												var byte_rimasti = dati_totali - dati_caricati;
												var secondi_rimasti = secondi_passati ? Math.round(byte_rimasti / byte_al_secondo) : "<?php echo _('Calcolo...'); ?>";
												if(secondi_rimasti == 1) {
													$("#time2").html("<small>"+secondi_rimasti+" <?php echo _('secondo rimasto'); ?></small>");
												} else {
													$("#time2").html("<small>"+secondi_rimasti+" <?php echo _('secondi rimasti'); ?></small>");
												}
												
												percentuale = (100 * e.loaded) / e.total;
												percentuale = Math.round(percentuale);
												if(percentuale != 100) {
													console.log("<?php echo _('Progresso upload'); ?> (2): "+percentuale+"%");
													$("#r2").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Carico...'); ?>'><br><?php echo _('Carico...'); ?>");
												} else {
													console.log("<?php echo _('Upload completato!'); ?> (2)");
													$("#r2").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Scansiono...'); ?>'><br><?php echo _('Scansiono...'); ?>");
													$("#barraupload2").removeClass("progress-bar-striped progress-bar-animated");
													$("#time2").html("<br>");
													
													$.post('Utils/getApproximateTime.php', {
														pesofile: e.total,
														tipocontrollo: 2
													},
													function(result) {
														$('#popover_check2').attr('title', "<?php echo _("Durata scansione stimata"); ?>: "+result)
														$("#popover_check2").tooltip('show');
													});
												}
												$('#barraupload2').css('width', percentuale+'%');
												$('#barraupload2').attr('aria-valuenow', percentuale);
												$('#barraupload2').text(percentuale+"%");												
											}
										} , false);
									}
									return myXhr;
								}
								});
							} else {
								$('#r2').html("<img src='CSS/Images/result_skipped.png' style='width: 32px'><br>(<?php echo _('Saltato'); ?>)");
							}
							//---------- FINE 2° FILE

							//---------- 3° FILE
							if(typeof file3 !== 'undefined') {
								form_data = new FormData();
								form_data.append("file", file3);
								form_data.append("controllo", 3);
								form_data.append("idutente", <?php echo $id; ?>);
								$.ajax({
								// Your server script to process the upload
								url: 'analysis.php',
								data: form_data,
								type: 'POST',
								cache: false,
								contentType: false,
								processData: false,

								beforeSend: function(risposta) {
									$("#file3").prop("disabled", true);
									$("#button3").prop("disabled", true);
									scansione = true;
								},
								success: function(risposta) {
									$("#popover_check3").tooltip('hide');
									$('#popover_check3').attr('title', "<?php echo _("Durata scansione stimata"); ?>")
									scansione = false;
									errore = false;
									risposta = risposta.split(":");
									stringa_risposta = risposta[2];
									id_risposta = risposta[1];
									risposta = risposta[0];
									esito3 = risposta;
									switch(risposta) {
										// Esiti
										//--------- CHEAT
										case "result_cheat":
											risposta3 = stringa_risposta;
											id3 = id_risposta;
											$('#r3').html("<img src='CSS/Images/result_cheat.png' style='width: 32px'><br><?php echo _('Cheat Rilevati'); ?>");
											$("#barraupload3").removeClass("bg-info");
											$("#barraupload3").addClass("bg-danger");
										break;

										//--------- SUSPECT
										case "result_suspect":
											risposta3 = stringa_risposta;
											id3 = id_risposta;
											$('#r3').html("<img src='CSS/Images/result_suspect.png' style='width: 32px'><br><?php echo _('Sospetto'); ?>");
											$("#barraupload3").removeClass("bg-info");
											$("#barraupload3").addClass("bg-warning");
										break;

										//--------- CLEAN
										case "result_clean":
											$('#r3').html("<img src='CSS/Images/result_clean.png' style='width: 32px'><br><?php echo _('Pulito'); ?>");
											$("#barraupload3").removeClass("bg-info");
											$("#barraupload3").addClass("bg-success");
										break;

										// Errori

										// --- di sistema
										case "error_remove":
											showErrorMessage(1, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unset":
											showErrorMessage(2, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unzip":
											showErrorMessage(3, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_extract":
											showErrorMessage(4, "<?php echo _('Il file compresso deve avere lo stesso nome del file .txt!'); ?>");
											errore = true;
										break;

										case "error_rename":
											showErrorMessage(5, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_open":
											showErrorMessage(6, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_getstring":
											showErrorMessage(7, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historycheat":
											showErrorMessage(8, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historyanalyzer":
											showErrorMessage(9, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										// --- utente

										case "error_upload":
											showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
											errore = true;
										break;

										case "error_size":
											showErrorMessage(11, "<?php echo _('Il file che hai caricato è troppo grande.'); ?>");
											errore = true;
										break;

										case "error_extension":
											showErrorMessage(12, "<?php echo _('L’estensione del file caricato non è valida.'); ?>");
											errore = true;
										break;
										
										case "error_invalid":
											showErrorMessage(13, "<?php echo _('Il file che hai caricato non è valido.'); ?>");
											errore = true;
										break;
										
										case "error_max_otp":
											showErrorMessage(14, "<?php echo _('Il limite di scansioni per account OTP è stato raggiunto.'); ?>");
											errore = true;
										break;
										
										// ---

										case "error_unavailable":
											showErrorMessage(15, "<?php echo _('Il servizio non è al momento disponibile, riprova più tardi.'); ?>");
											errore = true;
										break;
										

										case "error_loginrequired":
											showErrorMessage(16, "<?php echo _('Il login è richiesto per poter effettuare scansioni.'); ?>");
											errore = true;
										break;
										
										case "error_license":
											showErrorMessage(17, "<?php echo _('La licenza è scaduta.'); ?>");
											errore = true;
										break;

										// Default

										default:
											showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
											errore = true;
										break;
									}
									if(errore) {
										$('#r3').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
									}
								},
								error: function(risposta) {
									if(risposta == "[object Object]") {
										showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
									} else {
										showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
									}
									errore = true;
									$('#r3').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
								},
								complete: function(risposta) {
									completati++;
									$("#file3").prop("disabled", false);
									$("#file3").val("");
									$("#button3").prop("disabled", false);
									$("#button3").html("<?php echo _('Inserisci'); ?> MsMpEng.exe <small><?php echo _('o Log Antivirus'); ?></small>");
									if(completati == fileselezionati) {
										$("#invio").prop("disabled", false);
										$(document).prop('title', '<?php echo _("Scansione completata!"); ?> | <?php echo $solutionname; ?>');
										$("#audio")[0].play();
										scansione = false;
										
										<?php 
										if($otp) {
											?>
											if(completati == 4) {
												endOTPScans();
											}
											<?php
										} 
										?>
										
										generaInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito3);
										<?php
										if(getPreference($id, "PopupScansione") == 1) {
											?>
											if(risposta1 != null || risposta2 != null || risposta3 != null || risposta4 != null) {
												moreInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito3);
											}
											<?php
										}
										?>
									}
								},
								xhr: function() {
									var myXhr = $.ajaxSettings.xhr();
									var percentuale;
									if (myXhr.upload) {
										var iniziato_a = new Date();
										// For handling the progress of the upload
										myXhr.upload.addEventListener('progress', function(e) {
											if (e.lengthComputable) {
												// Calcolo tempo stimato
												var dati_caricati = e.loaded;
												var dati_totali = e.total;
												var secondi_passati = (new Date().getTime() - iniziato_a.getTime())/1000;
												var byte_al_secondo = secondi_passati ? dati_caricati / secondi_passati : 0 ;
												var byte_rimasti = dati_totali - dati_caricati;
												var secondi_rimasti = secondi_passati ? Math.round(byte_rimasti / byte_al_secondo) : "<?php echo _('Calcolo...'); ?>";
												if(secondi_rimasti == 1) {
													$("#time3").html("<small>"+secondi_rimasti+" <?php echo _('secondo rimasto'); ?></small>");
												} else {
													$("#time3").html("<small>"+secondi_rimasti+" <?php echo _('secondi rimasti'); ?></small>");
												}
												
												percentuale = (100 * e.loaded) / e.total;
												percentuale = Math.round(percentuale);
												if(percentuale != 100) {
													console.log("<?php echo _('Progresso upload'); ?> (3): "+percentuale+"%");
													$("#r3").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Carico...'); ?>'><br><?php echo _('Carico...'); ?>");
												} else {
													console.log("<?php echo _('Upload completato!'); ?> (3)");
													$("#r3").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Scansiono...'); ?>'><br><?php echo _('Scansiono...'); ?>");
													$("#barraupload3").removeClass("progress-bar-striped progress-bar-animated");
													$("#time3").html("<br>");
													
													$.post('Utils/getApproximateTime.php', {
														pesofile: e.total,
														tipocontrollo: 3
													},
													function(result) {
														$('#popover_check3').attr('title', "<?php echo _("Durata scansione stimata"); ?>: "+result)
														$("#popover_check3").tooltip('show');
													});
												}
												$('#barraupload3').css('width', percentuale+'%');
												$('#barraupload3').attr('aria-valuenow', percentuale);
												$('#barraupload3').text(percentuale+"%");
											}
										} , false);
									}
									return myXhr;
								}
								});
							} else {
								$('#r3').html("<img src='CSS/Images/result_skipped.png' style='width: 32px'><br>(<?php echo _('Saltato'); ?>)");
							}
							//---------- FINE 3° FILE

							//---------- 4° FILE
							if(typeof file4 !== 'undefined') {
								form_data = new FormData();
								form_data.append("file", file4);
								form_data.append("controllo", 4);
								form_data.append("idutente", <?php echo $id; ?>);
								$.ajax({
								// Your server script to process the upload
								url: 'analysis.php',
								data: form_data,
								type: 'POST',
								cache: false,
								contentType: false,
								processData: false,

								beforeSend: function(risposta) {
									$("#file4").prop("disabled", true);
									$("#button4").prop("disabled", true);
									scansione = true;
								},
								success: function(risposta) {
									$("#popover_check4").tooltip('hide');
									$('#popover_check4').attr('title', "<?php echo _("Durata scansione stimata"); ?>")
									scansione = false;
									errore = false;
									risposta = risposta.split(":");
									stringa_risposta = risposta[2];
									id_risposta = risposta[1];
									risposta = risposta[0];
									esito4 = risposta;
									switch(risposta) {
										// Esiti
										//--------- CHEAT
										case "result_cheat":
											risposta4 = stringa_risposta;
											id4 = id_risposta;
											$('#r4').html("<img src='CSS/Images/result_cheat.png' style='width: 32px'><br><?php echo _('Cheat Rilevati'); ?>");
											$("#barraupload4").removeClass("bg-info");
											$("#barraupload4").addClass("bg-danger");
										break;

										//--------- SUSPECT
										case "result_suspect":
											risposta4 = stringa_risposta;
											id4 = id_risposta;
											$('#r4').html("<img src='CSS/Images/result_suspect.png' style='width: 32px'><br><?php echo _('Sospetto'); ?>");
											$("#barraupload4").removeClass("bg-info");
											$("#barraupload4").addClass("bg-warning");
										break;

										//--------- CLEAN
										case "result_clean":
											$('#r4').html("<img src='CSS/Images/result_clean.png' style='width: 32px'><br><?php echo _('Pulito'); ?>");
											$("#barraupload4").removeClass("bg-info");
											$("#barraupload4").addClass("bg-success");
										break;

										// Errori
										
										// --- di sistema
										case "error_remove":
											showErrorMessage(1, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unset":
											showErrorMessage(2, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_unzip":
											showErrorMessage(3, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_extract":
											showErrorMessage(4, "<?php echo _('Il file compresso deve avere lo stesso nome del file .txt!'); ?>");
											errore = true;
										break;

										case "error_rename":
											showErrorMessage(5, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_open":
											showErrorMessage(6, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_getstring":
											showErrorMessage(7, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historycheat":
											showErrorMessage(8, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										case "error_historyanalyzer":
											showErrorMessage(9, "<?php echo _('Si è verificato un errore interno. Ci scusiamo per il disagio.'); ?>");
											errore = true;
										break;

										// --- utente

										case "error_upload":
											showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
											errore = true;
										break;

										case "error_size":
											showErrorMessage(11, "<?php echo _('Il file che hai caricato è troppo grande.'); ?>");
											errore = true;
										break;

										case "error_extension":
											showErrorMessage(12, "<?php echo _('L’estensione del file caricato non è valida.'); ?>");
											errore = true;
										break;
										
										case "error_invalid":
											showErrorMessage(13, "<?php echo _('Il file che hai caricato non è valido.'); ?>");
											errore = true;
										break;
										
										case "error_max_otp":
											showErrorMessage(14, "<?php echo _('Il limite di scansioni per account OTP è stato raggiunto.'); ?>");
											errore = true;
										break;
										
										// ---

										case "error_unavailable":
											showErrorMessage(15, "<?php echo _('Il servizio non è al momento disponibile, riprova più tardi.'); ?>");
											errore = true;
										break;
										

										case "error_loginrequired":
											showErrorMessage(16, "<?php echo _('Il login è richiesto per poter effettuare scansioni.'); ?>");
											errore = true;
										break;
										
										case "error_license":
											showErrorMessage(17, "<?php echo _('La licenza è scaduta.'); ?>");
											errore = true;
										break;

										// Default

										default:
											showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
											errore = true;
										break;
									}
									if(errore) {
										$('#r4').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
									}
								},
								error: function(risposta) {
									if(risposta == "[object Object]") {
										showErrorMessage(10, "<?php echo _('Si è verificato un errore durante l’upload, riprova.'); ?>");
									} else {
										showErrorMessage("???", "<?php echo _('Caso imprevisto:'); ?>"+risposta);
									}
									errore = true;
									$('#r4').html("<img src='CSS/Images/result_error.png' style='width: 32px'><br>(<?php echo _('Errore'); ?>)");
								},
								complete: function(risposta) {
									completati++;
									$("#file4").prop("disabled", false);
									$("#file4").val("");
									$("#button4").prop("disabled", false);
									$("#button4").html("<?php echo _('Inserisci'); ?> lsass.exe");
									if(completati == fileselezionati) {
										$("#invio").prop("disabled", false);
										$(document).prop('title', '<?php echo _("Scansione completata!"); ?> | <?php echo $solutionname; ?>');
										$("#audio")[0].play();
										scansione = false;
										
										<?php 
										if($otp) {
											?>
											if(completati == 4) {
												endOTPScans();
											}
											<?php
										} 
										?>
										
										generaInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito4);
										<?php
										if(getPreference($id, "PopupScansione") == 1) {
											?>
											if(risposta1 != null || risposta2 != null || risposta3 != null || risposta4 != null) {
												moreInfo(id1, id2, id3, id4, risposta1, risposta2, risposta3, risposta4, esito4);
											}
											<?php
										}
										?>
									}
								},
								xhr: function() {
									var myXhr = $.ajaxSettings.xhr();
									var percentuale;
									if (myXhr.upload) {
										var iniziato_a = new Date();
										// For handling the progress of the upload
										myXhr.upload.addEventListener('progress', function(e) {
											if (e.lengthComputable) {
												// Calcolo tempo stimato
												var dati_caricati = e.loaded;
												var dati_totali = e.total;
												var secondi_passati = (new Date().getTime() - iniziato_a.getTime())/1000;
												var byte_al_secondo = secondi_passati ? dati_caricati / secondi_passati : 0 ;
												var byte_rimasti = dati_totali - dati_caricati;
												var secondi_rimasti = secondi_passati ? Math.round(byte_rimasti / byte_al_secondo) : "<?php echo _('Calcolo...'); ?>";
												if(secondi_rimasti == 1) {
													$("#time4").html("<small>"+secondi_rimasti+" <?php echo _('secondo rimasto'); ?></small>");
												} else {
													$("#time4").html("<small>"+secondi_rimasti+" <?php echo _('secondi rimasti'); ?></small>");
												}
												
												percentuale = (100 * e.loaded) / e.total;
												percentuale = Math.round(percentuale);
												if(percentuale != 100) {
													console.log("<?php echo _('Progresso upload'); ?> (4): "+percentuale+"%");
													$("#r4").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Carico...'); ?>'><br><?php echo _('Carico...'); ?>");
												} else {
													console.log("<?php echo _('Upload completato!'); ?> (4)");
													$("#r4").html("<img src='CSS/Images/scanning.gif' style='width: 32px' alt='<?php echo _('Scansiono...'); ?>'><br><?php echo _('Scansiono...'); ?>");
													$("#barraupload4").removeClass("progress-bar-striped progress-bar-animated");
													$("#time4").html("<br>");
													
													$.post('Utils/getApproximateTime.php', {
														pesofile: e.total,
														tipocontrollo: 4
													},
													function(result) {
														$('#popover_check4').attr('title', "<?php echo _("Durata scansione stimata"); ?>: "+result)
														$("#popover_check4").tooltip('show');
													});
												}
												$('#barraupload4').css('width', percentuale+'%');
												$('#barraupload4').attr('aria-valuenow', percentuale);
												$('#barraupload4').text(percentuale+"%");
											}
										} , false);
									}
									return myXhr;
								}
								});
							} else {
								$('#r4').html("<img src='CSS/Images/result_skipped.png' style='width: 32px'><br>(<?php echo _('Saltato'); ?>)");
							}
							//---------- FINE 4° FILE
						} else {
							swal({
							  title: "<?php echo _('File troppo pesanti'); ?>",
							  text: "<?php echo _('Tutti i file devono pesare meno di '); echo $pesomax / 1000000; echo 'MB.'; ?>",
							  type: "error"
							})
						}
					} else {
						swal({
						  title: "<?php echo _('Estensioni non valide'); ?>",
						  text: "<?php echo _('Tutti i file devono essere .zip'); ?>",
						  type: "error"
						})
					}
					// --- FINE CONTROLLO ESTENSIONE .ZIP
				} else {
					swal({
					  title: "<?php echo _('È richiesto un file'); ?>",
					  text: "<?php echo _('Inserisci un file per iniziare la scansione'); ?>",
					  type: "info"
					})
				}
			});
		});

		/* INIZIO OPERAZIONI SUI BUTTONS */

		$("#button1").on("click", function() {
			$("#file1").click();
		});

		$("#button2").on("click", function() {
			$("#file2").click();
		});

		$("#button3").on("click", function() {
			$("#file3").click();
		});

		$("#button4").on("click", function() {
			$("#file4").click();
		});

		$("#file1").change(function() {
			if($("#file1").val()) { // Controllo valore
				if($("#file1").get(0).files.length != 0 && $("#file1").val().split(".").pop() == "zip") { // Controllo estensione
					if((document.getElementById("file1").files[0].size / 1000000) <= <?php echo $pesomax / 1000000; ?>) { // Controllo peso troppo alto
						var nomefile = $("#file1").val().split('\\').pop();
						$("#button1").html(nomefile+" ("+Math.round(this.files[0].size/1000000)+"MB)");
						if((this.files[0].size/1000000) > 50) { // Avviso file pesante
							bigFileWarning(Math.round(this.files[0].size/1000000));
						}
					} else {
						$("#file1").val("");
						swal({
						  title: "<?php echo _('File troppo pesante'); ?>",
						  text: "<?php echo _('Questo file supera il limite di '); echo $pesomax / 1000000; echo 'MB.'; ?>",
						  type: "error"
						})
					}
				} else {
					$("#file1").val("");
					swal({
					  title: "<?php echo _('Estensione non valida'); ?>",
					  text: "<?php echo _('Ogni file deve terminare con l’estensione .zip'); ?>",
					  type: "error"
					})
				}
			} else {
				$("#button1").html("<?php echo _('Inserisci'); ?> Javaw.exe");
			}
		});

		$("#file2").change(function() {
			if($("#file2").val()) { // Controllo valore
				if($("#file2").get(0).files.length != 0 && $("#file2").val().split(".").pop() == "zip") { // Controllo estensione
					if((document.getElementById("file2").files[0].size / 1000000) <= <?php echo $pesomax / 1000000; ?>) { // Controllo peso troppo alto
						var nomefile = $("#file2").val().split('\\').pop();
						$("#button2").html(nomefile+" ("+Math.round(this.files[0].size/1000000)+"MB)");
						if((this.files[0].size/1000000) > 50) { // Avviso file pesante
							bigFileWarning(Math.round(this.files[0].size/1000000));
						}
					} else {
						$("#file2").val("");
						swal({
						  title: "<?php echo _('File troppo pesante'); ?>",
						  text: "<?php echo _('Questo file supera il limite di '); echo $pesomax / 1000000; echo 'MB.'; ?>",
						  type: "error"
						})
					}
				} else {
					$("#file2").val("");
					swal({
					  title: "<?php echo _('Estensione non valida'); ?>",
					  text: "<?php echo _('Ogni file deve terminare con l’estensione .zip'); ?>",
					  type: "error"
					})
				}
			} else {
				$("#button2").html("<?php echo _('Inserisci'); ?> Dwm.exe");
			}
		});

		$("#file3").change(function() {
			if($("#file3").val()) { // Controllo valore
				if($("#file3").get(0).files.length != 0 && $("#file3").val().split(".").pop() == "zip") { // Controllo estensione
					if((document.getElementById("file3").files[0].size / 1000000) <= <?php echo $pesomax / 1000000; ?>) { // Controllo peso troppo alto
						var nomefile = $("#file3").val().split('\\').pop();
						$("#button3").html(nomefile+" ("+Math.round(this.files[0].size/1000000)+"MB)");
						if((this.files[0].size/1000000) > 50) { // Avviso file pesante
							bigFileWarning(Math.round(this.files[0].size/1000000));
						}
					} else {
						$("#file3").val("");
						swal({
						  title: "<?php echo _('File troppo pesante'); ?>",
						  text: "<?php echo _('Questo file supera il limite di '); echo $pesomax / 1000000; echo 'MB.'; ?>",
						  type: "error"
						})
					}
				} else {
					$("#file3").val("");
					swal({
					  title: "<?php echo _('Estensione non valida'); ?>",
					  text: "<?php echo _('Ogni file deve terminare con l’estensione .zip'); ?>",
					  type: "error"
					})
				}
			} else {
				$("#button3").html("<?php echo _('Inserisci'); ?> MsMpEng <small><?php echo _('o Log Antivirus'); ?></small>");
			}
		});

		$("#file4").change(function() {
			if($("#file4").val()) { // Controllo valore
				if($("#file4").get(0).files.length != 0 && $("#file4").val().split(".").pop() == "zip") { // Controllo estensione
					if((document.getElementById("file4").files[0].size / 1000000) <= <?php echo $pesomax / 1000000; ?>) { // Controllo peso troppo alto
						var nomefile = $("#file4").val().split('\\').pop();
						$("#button4").html(nomefile+" ("+Math.round(this.files[0].size/1000000)+"MB)");
						if((this.files[0].size/1000000) > 50) { // Avviso file pesante
							bigFileWarning(Math.round(this.files[0].size/1000000));
						}
					} else {
						$("#file4").val("");
						swal({
						  title: "<?php echo _('File troppo pesante'); ?>",
						  text: "<?php echo _('Questo file supera il limite di '); echo $pesomax / 1000000; echo 'MB.'; ?>",
						  type: "error"
						})
					}
				} else {
					$("#file4").val("");
					swal({
					  title: "<?php echo _('Estensione non valida'); ?>",
					  text: "<?php echo _('Ogni file deve terminare con l’estensione .zip'); ?>",
					  type: "error"
					})
				}
			} else {
				$("#button4").html("<?php echo _('Inserisci'); ?> lsass.exe");
			}
		});
		/* FINE OPERAZIONI SUI BUTTONS */
	});
</script>
<?php }  ?>