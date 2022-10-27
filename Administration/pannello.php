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

adminCheck();

networkInformation($infonetwork);

$lineefile = file("../Functions/Task/esito.txt");

$risposta_news = getNewsText();

$stmt = $connection->prepare("SELECT IDNetwork, NomeNetwork FROM $table_networks");
$stmt->execute();
$result = $stmt->get_result();
$numeroNetwork = $result->num_rows;

$stmt = $connection->prepare("SELECT ID FROM $table_tickets WHERE Stato = 1");
$stmt->execute();
$result2 = $stmt->get_result();
$numeroTicketAperti = $result2->num_rows;

$stmt = $connection->prepare("SELECT IDCandidatura FROM $table_applications WHERE Stato = 0");
$stmt->execute();
$result3 = $stmt->get_result();
$numeroCandidatureAperte = $result3->num_rows;
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Amministrazione"); ?> <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<style> .titolo { color:white; } </style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body onload="avvioInterval()" <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white" style="width: 90%">
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<h3><?php echo _("Pannello di Amministrazione"); ?></h3>
			<?php echo _("Tuo IP:")." "; echo getClientIP(); ?>
			<?php if(getPermission($id, "Account_Permissions")) { ?><br><a href="listaPermessi.php"><?php echo _("Modifica Permessi Amministratore"); ?></a><?php } ?>
			<hr>
			<div class="container-fluid text-center text-dark">
				<div class="row"> <!-- Riga 1 -->
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-cog"></span>
							</div>
							<h5 class="card-title"><?php echo _("Stato Conn. Database:"); ?></h5>
							<p class="card-text">
								<p id="checkconnection" style="color:lightgray;"><?php echo _("Verificando connessione..."); ?></p>
							</p>
						</div>
					</div>
					
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-folder"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo sprintf(_("File nella cartella %s:"), $folderanalysis); ?></h5>
							<p class="card-text">
								<span style="color:lightgray;" id="numerofiles"><?php echo _("Verificando presenza file..."); ?></span></i>
							</p>
						</div>
					</div>
					
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-bug"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Log Errori:"); ?></h5>
							<p class="card-text">
								<button class="btn btn-secondary" type="button" name="visualizzaLogs" onClick="location.href='visualizzaLogs.php'" <?php if (!getPermission($id, "ModificaConfig")) {echo "disabled";}?>><?php echo _("Visualizza log"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-person"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Utenti disabilitati:"); ?></h5>
							<p class="card-text">
								<span style="color:lightgray;" id="checkactive"><?php echo _("Verificando stato utenti..."); ?></span>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "Account_See") && !getPermission($id, "Account_Create") && !getPermission($id, "Account_Delete")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-command"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Modifica Account"); ?></h5>
							<p class="card-text">
								<button class="btn btn-secondary" type="button" name="viewAccount" onClick="location.href='listaUtenti.php'" <?php if (!getPermission($id, "Account_See")) {echo "disabled";}?>><?php echo _("Vedi"); ?></button>
								<button class="btn btn-secondary" type="button" name="addAccount" onClick="location.href='registrazione.php'" <?php if (!getPermission($id, "Account_Create")) {echo "disabled";}?>><?php echo _("Crea"); ?></button>
								<button class="btn btn-secondary" type="button" name="addAccount" onClick="location.href='cancellaAccount.php'" <?php if (!getPermission($id, "Account_Delete")) {echo "disabled";}?>><?php echo _("Elimina"); ?></button>
							</p>
						</div>
					</div>
				</div>
				
				<!-- DIVISIONE TRA 1 e 2 -->
				
				<hr>
				
				<div class="row"> <!-- Riga 2 -->
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-excerpt"></span>
							</div>
							<h5 class="card-title"><?php echo _("Accesso alle scansioni:"); ?></h5>
							<p class="card-text">
								<?php
								if($loginrequired) {
									echo _("Solo utenti registrati");
								} else {
									echo _("Tutti gli utenti");
								} ?>
								<br>
							</p>
						</div>
					</div>
					
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-fork"></span>
							</div>
							<h5 class="card-title"><?php echo _("Stato esecuzione aggiornam.:"); ?></h5>
							<p class="card-text">
								<?php
									echo _("Ultimo task eseguito")." ";
									if(date("Y-m-d") == date("Y-m-d", strtotime($lineefile[1]))) { 
										echo "<b>"._("oggi")."</b>"." ";
										$parti_data = explode(" ", $lineefile[1]);
										echo _("alle ore")." <b>".$parti_data[1]."</b>";
									} else { 
										echo _("in data:")." <b>".$lineefile[1]."</b>";
									}
								 ?>
								<br>
							</p>
						</div>
					</div>
					
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-people"></span>
							</div>
							<h5 class="card-title"><?php echo _("Media CPS degli utenti:"); ?></h5>
							<p id="checkcpsaverage" style="color:lightgray;"><?php echo _("Ottenimento risultati..."); ?></p>
						</div>
					</div>
					
					<div class="card bg-light" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-graph"></span>
							</div>
							<h5 class="card-title"><?php echo _("Google Analytics:"); ?></h5>
							<br>
							<p>
							<button class="btn btn-info" onClick="location.href='https://analytics.google.com/analytics/web/?authuser=1#/report-home/a122642131w180773522p178812952'" name="accessoanalytics"><?php echo _("Visualizza statistiche"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card  <?php if (!getPermission($id, "ListaPagamenti")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 14rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-dollar"></span>
							</div>
							<h5 class="card-title"><?php echo _("Lista dei Pagamenti:"); ?></h5>
							<br>
							<p>
								<button class="btn btn-info" onClick="location.href='listaPagamenti.php'" name="accessolistapagamenti" <?php if (!getPermission($id, "ListaPagamenti")) { echo "disabled"; } ?>><?php echo _("Visualizza pagamenti"); ?></button>
							</p>
						</div>
					</div>
				</div>
				
				<?php if ($risposta_news) { ?>
				<hr>
				
				<div class="card bg-light mb-3"> <!-- BROADCAST -->
					<div class="card-body">
						<h6 align="left"><?php echo _("Notizia in Broadcast"); ?>  <?php if (getPermission($id, "ModificaNews")) { ?><small>(<a href="modificaConfig.php"><?php echo _("Clicca per modificare"); ?></a>)</small><?php } else { ?><small>(<?php echo _("Nessun permesso per modificare"); ?></small><?php } ?></h6>
						<marquee behavior="scroll" direction="left" scrollamount="8"><b><?php echo $risposta_news[0]; echo ":</b> "; echo $risposta_news[1]; ?></marquee>
					</div>
				</div>
				<?php } else {
					?><br><?php
				}?>
				
				<?php if($numeroTicketAperti > 0) { ?><div align="left"><font color="lightcoral"><?php echo _("Tickets:"); ?></font> <font color="white"><?php if($numeroTicketAperti == 1) { echo _("C'è"); } else { echo _("Ci sono"); } ?> <b><?php echo $numeroTicketAperti; ?></b> <?php echo _("ticket")." "; if($numeroTicketAperti == 1) { echo _("aperto"); } else { echo _("aperti"); } ?> <?php echo _("in attesa di una risposta"); if(getPermission($id, "ModificaTickets")) { ?> <small>(<a href="listaTickets.php"><?php echo _("Clicca per")." "; if($numeroTicketAperti == 1) { echo _("controllarlo"); } else { echo _("controllarli"); } ?></a>)</small><?php } ?>.</font></div><?php } ?>
				
				<?php if($numeroCandidatureAperte > 0) { ?><div align="left"><font color="lightcoral"><?php echo _("Candidature:"); ?></font> <font color="white"><?php if($numeroCandidatureAperte == 1) { echo _("C'è"); } else { echo _("Ci sono"); } ?> <b><?php echo $numeroCandidatureAperte; ?></b> <?php if($numeroCandidatureAperte == 1) { echo _("candidatura"); } else { echo _("candidature"); }  echo " "._("in attesa di valutazione"); if(getPermission($id, "ViewApplication")) { ?> <small>(<a href="listaCandidature.php"><?php echo _("Clicca per")." "; if($numeroCandidatureAperte == 1) { echo _("vederla"); } else { echo _("vederle"); } ?></a>)</small><?php } ?>.</font></div><?php } ?>
				
				<hr> <!-- DIVISIONE TRA 2 e 3 -->
				
				<h5 class="titolo"><?php echo _("Modifica Stringhe"); if (getPermission($id, "ListaStringhe")) { ?> (<b><?php echo getStringNumber(); ?></b> <?php echo _("totali"); ?>)<?php } ?></h5>
				<br>
				
				<div class="row"> <!-- Riga 3 -->
					<div class="card <?php if (!getPermission($id, "ModificaStringhe")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 25rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-plus"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Aggiungi stringa al Database"); ?></h5>
							<p class="card-text">
								<form id="aggiungiStringa" name="aggiungiStringa" action="Utils/aggiungiStringa.php" method="POST">
									<div class="input-group mb-3">
										<div class="input-group-prepend">
										<div class="input-group-text">
											<span class="oi oi-signpost"></span>
										</div>
										</div>
										<input class="form-control" type="text" name="stringa" id="stringa" placeholder="<?php echo _("Stringa da aggiungere"); ?>" autocomplete="off" required <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>></input>
									</div>
									<div class="input-group mb-3">
										<div class="input-group-prepend">
										<div class="input-group-text">
											<span class="oi oi-info"></span>
										</div>
										</div>
										<input class="form-control" type="text" name="nomeclient" id="nomeclient" placeholder="<?php echo _("Nome del Client"); ?>" autocomplete="off" required <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>></input>
									</div>
									<select class="form-control" id="selectstringa" <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>>
										<option value="<?php echo $table_cheatjava; ?>"><?php echo $table_cheatjava; ?></option>
										<option value="<?php echo $table_cheatdwm; ?>"><?php echo $table_cheatdwm; ?></option>
										<option value="<?php echo $table_cheatmsmpeng; ?>"><?php echo $table_cheatmsmpeng; ?></option>
										<option value="<?php echo $table_cheatlsass; ?>"><?php echo $table_cheatlsass; ?></option>
										<option value="<?php echo $table_suspectjava; ?>"><?php echo $table_suspectjava; ?></option>
										<option value="<?php echo $table_suspectdwm; ?>"><?php echo $table_suspectdwm; ?></option>
										<option value="<?php echo $table_suspectmsmpeng; ?>"><?php echo $table_suspectmsmpeng; ?></option>
										<option value="<?php echo $table_suspectlsass; ?>"><?php echo $table_suspectlsass; ?></option>
									</select>
									<br>
									<button class="btn btn-success" type="submit" name="inviaaggiunta" <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>><?php echo _("Aggiungi"); ?></button>
								</form>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "ListaStringhe")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 25rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-book"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Lista delle Stringhe"); ?></h5>
							<p class="card-text">
								<br>
								<button class="btn btn-secondary" type="button" name="listastringhe" onClick="location.href='listaStringhe.php'" <?php if (!getPermission($id, "ListaStringhe")) {echo "disabled";}?>><?php echo _("Visualizza Lista Stringhe"); ?></button>
								<br><br>
								<button class="btn btn-secondary" type="button" name="analisisimilarita" onClick="location.href='../BETA/checkSimilar.php'" <?php if (!getPermission($id, "ListaStringhe")) {echo "disabled";}?>><?php echo _("Analisi Similarità"); ?> [BETA]</button>
								<br><br>
								<button class="btn btn-secondary" type="button" name="listaproposte" onClick="location.href='listaProposte.php'" <?php if (!getPermission($id, "ListaStringhe")) {echo "disabled";}?>><?php echo _("Lista Proposte Stringhe"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "ModificaStringhe")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 25rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-minus"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Rimuovi stringa dal Database"); ?></h5>
							<p class="card-text">
								<form id="rimuoviStringa" name="rimuoviStringa" action="Utils/eliminaStringa.php" method="POST">
									<div class="input-group mb-3">
										<div class="input-group-prepend">
										<div class="input-group-text">
											<span class="oi oi-signpost"></span>
										</div>
										</div>
										<input class="form-control" type="text" name="stringa2" id="stringa2" placeholder="<?php echo _("Stringa da rimuovere"); ?>" autocomplete="off" required <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>></input>
									</div>
									<select class="form-control" id="selectstringa2" <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>>
										<option value="<?php echo $table_cheatjava; ?>"><?php echo $table_cheatjava; ?></option>
										<option value="<?php echo $table_cheatdwm; ?>"><?php echo $table_cheatdwm; ?></option>
										<option value="<?php echo $table_cheatmsmpeng; ?>"><?php echo $table_cheatmsmpeng; ?></option>
										<option value="<?php echo $table_cheatlsass; ?>"><?php echo $table_cheatlsass; ?></option>
										<option value="<?php echo $table_suspectjava; ?>"><?php echo $table_suspectjava; ?></option>
										<option value="<?php echo $table_suspectdwm; ?>"><?php echo $table_suspectdwm; ?></option>
										<option value="<?php echo $table_suspectmsmpeng; ?>"><?php echo $table_suspectmsmpeng; ?></option>
										<option value="<?php echo $table_suspectlsass; ?>"><?php echo $table_suspectlsass; ?></option>
									</select>
									<br><br><br>
									<button class="btn btn-danger" type="submit" name="inviarimozione" <?php if (!getPermission($id, "ModificaStringhe")) {echo "disabled";}?>><?php echo _("Rimuovi"); ?></button>
								</form>
							</p>
						</div>
					</div>
				</div>
				
				<hr> <!-- DIVISIONE TRA 3 e 4 -->
				
				<h5 class="titolo"><?php echo _("Cronologia & Software"); ?></h5>
				<br>
				
				<div class="row"> <!-- Riga 4 -->
					<div class="card <?php if (!getPermission($id, "CronologiaEventi")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 25rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-box"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Cronologia Stringhe"); ?></h5>
							<p class="card-text">
								<br><br><br><br><br><br><br>
								<button class="btn btn-secondary" type="button" name="lista" onClick="location.href='listaCronologiaCheat.php'" <?php if (!getPermission($id, "CronologiaEventi")) {echo "disabled";}?>><?php echo _("Cronologia eventi (cheat)"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "ModificaSoftware")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 25rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-code"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Aggiornamento Software"); ?></h5>
							<p class="card-text">
								<?php if(!$softwaredownload) { ?><font color="red"><?php echo _("Download software disabilitato."); ?></font><?php } ?>
								<form id="formuploadsoftware" name="formuploadsoftware" action="Utils/updateSoftware.php" method="POST">
									<input style="display:none;" type="file" name="updatesoftware" id="updatesoftware" accept=".rar" required></input>
									<button class="btn btn-secondary" type="button" name="buttonupdatesoftware" id="buttonupdatesoftware" <?php if (!getPermission($id, "ModificaSoftware")) {echo "disabled";}?>><?php echo _("File di aggiornamento"); ?></button>
									<br><br><br>
									<button class="btn btn-secondary" type="button" name="modificaVersioni" onClick="location.href='modificaVersione.php'" <?php if (!getPermission($id, "ModificaSoftware")) {echo "disabled";}?>><?php echo _("Modifica Versioni"); ?></button>
									<br>
									<button class="btn btn-secondary" type="button" name="modificaSoftware" onClick="location.href='modificaSoftware.php'" <?php if (!getPermission($id, "ModificaSoftware")) {echo "disabled";}?>><?php echo _("Modifica File Software (DB)"); ?></button>
									<br>
									<button class="btn btn-secondary" type="button" name="modificaApplications" onClick="location.href='modificaApplications.php'" <?php if (!getPermission($id, "ModificaSoftware")) {echo "disabled";}?>><?php echo _("Modifica Pacchetto Applicazioni"); ?></button>
									<br><br>
									<button class="btn btn-success" type="submit" name="invioupdate" id="invioupdate"<?php if (!getPermission($id, "ModificaSoftware")) {echo "disabled";}?>><?php echo _("Carica file e aggiorna Software"); ?></button>
								</form>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "CronologiaEventi")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 25rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-box"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Cronologia Analyzer"); ?></h5>
							<p class="card-text">
								<br><br><br><br><br><br><br>
								<button class="btn btn-secondary" type="button" name="lista2" onClick="location.href='listaCronologiaAnalyzer.php'" <?php if (!getPermission($id, "CronologiaEventi")) {echo "disabled";}?>><?php echo _("Cronologia eventi (analyzer)"); ?></button>
							</p>
						</div>
					</div>
				</div>
				
				<hr> <!-- DIVISIONE TRA 4 e 5 -->
				
				<h5 class="titolo"><?php echo _("Altre impostazioni"); ?></h5>
				<br>
				
				<div class="row"> <!-- Riga 5 -->
					<div class="card <?php if (!getPermission($id, "ModificaTickets")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 15rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-clipboard"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Visualizzazione Tickets"); ?></h5>
							<p class="card-text">
								<?php echo _("Ticket aperti:"); ?> <b><?php echo $numeroTicketAperti; ?></b><br><br><br>
								<button class="btn btn-secondary" type="button" name="lista3" onClick="location.href='listaTickets.php'" <?php if (!getPermission($id, "ModificaTickets")) {echo "disabled";}?>><?php echo _("Visualizza i tickets"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "ViewApplication")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 16rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-document"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Visualizzazione Candidature"); ?></h5>
							<p class="card-text">
								<?php echo _("Candidature in attesa:"); ?> <b><?php echo $numeroCandidatureAperte; ?></b><br><br><br>
								<button class="btn btn-secondary" type="button" name="lista3" onClick="location.href='listaCandidature.php'" <?php if (!getPermission($id, "ViewApplication")) {echo "disabled";}?>><?php echo _("Visualizza le candidature"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "ModificaNews") && !getPermission($id, "ModificaNewsLetter")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 16rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-calendar"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Aggiunta News e Invio NewsLetter"); ?></h5>
							<p class="card-text">
								<button class="btn btn-secondary" type="button" name="news" onClick="location.href='aggiungiNews.php'" <?php if (!getPermission($id, "ModificaNews")) {echo "disabled";}?>><?php echo _("Gestione delle News"); ?></button>
								<br><br>
								<button class="btn btn-secondary" type="button" name="news" onClick="location.href='inviaNewsLetter.php'" <?php if (!getPermission($id, "ModificaNewsLetter")) {echo "disabled";}?>><?php echo _("Invia una NewsLetter"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "CronologiaEventi")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 16rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-document"></span>
							</div>
							<h5 class="card-title"><?php echo _("Cronologia file di scansione:"); ?></h5>
							<p class="card-text">
								<br><br>
								<button class="btn btn-secondary" onClick="location.href='scaricaHistory.php'" name="cronologiafile" <?php if (!getPermission($id, "CronologiaEventi")) {echo "disabled";}?>><?php echo _("Visualizza file"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "VisualizzaLogs")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 16rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-document"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Network Log"); ?></h5>
							<p class="card-text">
								<form id="formnetworklog" name="formnetworklog" action="networkLogs.php" method="POST">
									<select class="form-control" name="selectnetworklog" id="selectnetworklog" <?php if (!getPermission($id, "VisualizzaLogs")) {echo "disabled";}?>>
										<?php
										if ($numeroNetwork == 0) {
											?><option value="0"><?php echo _("Nessun network..."); ?></option><?php
										} else {
											while ($riga = $result->fetch_assoc()) {
												?>
												<option value="-1"><?php echo _("-- Seleziona --"); ?></option>
												<option value="<?php echo $riga["IDNetwork"]; ?>"><?php echo $riga["NomeNetwork"]; ?></option><?php
											}
										}
										?>
									</select>
									<br><br>
									<button class="btn btn-secondary" type="submit" name="inviaselezionanetwork" <?php if ($numeroNetwork == 0) { echo "disabled"; } else { if (!getPermission($id, "VisualizzaLogs")) {echo "disabled";} } ?>><?php echo _("Visualizza Network Log"); ?></button>
								</form>
							</p>
						</div>
					</div>
				</div>
				
				<hr> <!-- DIVISIONE TRA 5 e 6 -->
				
				<h5 style="color:lightcoral;"><?php echo _("Amministrazione & Modifiche Generali"); ?></h5>
				<br>
				
				<div class="row"> <!-- Riga 6 -->
					<div class="card <?php if (!getPermission($id, "ModificaConfig")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 18rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-pencil"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Modifica Config"); ?></h5>
							<p class="card-text">
								<br><br><br><br><br>
								<button class="btn btn-secondary" type="button" name="config" onClick="location.href='modificaConfig.php'" <?php if (!getPermission($id, "ModificaConfig")) {echo "disabled";}?>><?php echo _("Modifica il Config"); ?></button>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "EsportazioneStringhe")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 18rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-data-transfer-download"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Esportazione Stringhe"); ?></h5>
							<p class="card-text">
								<form id="exportStringa" name="exportStringa" action="Utils/esportaStringaDB.php" method="POST">
									<select class="form-control" name="tabella" id="tabellaexport" <?php if (!getPermission($id, "EsportazioneStringhe")) {echo "disabled";}?>>
										<option value="-1"><?php echo _("-- Seleziona --"); ?></option>
										<option value="<?php echo $table_cheatjava; ?>"><?php echo $table_cheatjava; ?></option>
										<option value="<?php echo $table_cheatdwm; ?>"><?php echo $table_cheatdwm; ?></option>
										<option value="<?php echo $table_cheatmsmpeng; ?>"><?php echo $table_cheatmsmpeng; ?></option>
										<option value="<?php echo $table_cheatlsass; ?>"><?php echo $table_cheatlsass; ?></option>
										<option value="<?php echo $table_suspectjava; ?>"><?php echo $table_suspectjava; ?></option>
										<option value="<?php echo $table_suspectdwm; ?>"><?php echo $table_suspectdwm; ?></option>
										<option value="<?php echo $table_suspectmsmpeng; ?>"><?php echo $table_suspectmsmpeng; ?></option>
										<option value="<?php echo $table_suspectlsass; ?>"><?php echo $table_suspectlsass; ?></option>
									</select>
									<br><br><br>
									<button class="btn btn-success" type="submit" name="inviaexport" <?php if (!getPermission($id, "EsportazioneStringhe")) {echo "disabled";}?>><?php echo _("Esporta"); ?></button>
								</form>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "ImportazioneStringhe")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 18rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-data-transfer-upload"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Importazione Stringhe"); ?> <a title="<?php echo _("Spiegazione tipi di importazione:&#013;&#013;Tipo 1: Elimina le stringhe già presenti nella tabella e poi importa quelle nuove.&#013;Tipo 2: Importa le stringhe nuove lasciando anche quelle vecchie.&#013;&#013;Sono accettati solo file di testo .txt"); ?>"><span class="oi oi-info" style="color:red;"></span></a></h5>
							<p class="card-text">
								<form id="formimport" name="formimport" action="Utils/importaStringhe.php" method="POST">
									<input type="radio" name="tipoimport" value="1" checked <?php if (!getPermission($id, "ImportazioneStringhe")) {echo "disabled";}?>></input> 1 | <input type="radio" name="tipoimport" value="2" <?php if (!getPermission($id, "ImportazioneStringhe")) {echo "disabled";}?>> 2 | <button type="button" id="buttonfileimport" <?php if (!getPermission($id, "ImportazioneStringhe")) {echo "disabled";}?>><?php echo _("File import."); ?></button>
									<input accept=".txt" style="display:none;" type="file" id="fileimport" name="fileimport"></input>
									<br><br>
									<select class="form-control" id="tabellaimport" <?php if (!getPermission($id, "ImportazioneStringhe")) {echo "disabled";}?>>
										<option value="-1"><?php echo _("-- Seleziona --"); ?></option>
										<option value="<?php echo $table_cheatjava; ?>"><?php echo $table_cheatjava; ?></option>
										<option value="<?php echo $table_cheatdwm; ?>"><?php echo $table_cheatdwm; ?></option>
										<option value="<?php echo $table_cheatmsmpeng; ?>"><?php echo $table_cheatmsmpeng; ?></option>
										<option value="<?php echo $table_cheatlsass; ?>"><?php echo $table_cheatlsass; ?></option>
										<option value="<?php echo $table_suspectjava; ?>"><?php echo $table_suspectjava; ?></option>
										<option value="<?php echo $table_suspectdwm; ?>"><?php echo $table_suspectdwm; ?></option>
										<option value="<?php echo $table_suspectmsmpeng; ?>"><?php echo $table_suspectmsmpeng; ?></option>
										<option value="<?php echo $table_suspectlsass; ?>"><?php echo $table_suspectlsass; ?></option>
									</select>
									<br>
									<button class="btn btn-danger" id="buttonimportfile" type="submit" <?php if (!getPermission($id, "ImportazioneStringhe")) {echo "disabled";} ?>><?php echo _("Importa da file"); ?></button>
								</form>
							</p>
						</div>
					</div>
					
					<div class="card <?php if (!getPermission($id, "FormattazioneStringhe")) { echo 'border-danger bg-dark text-danger" title="'._("Non hai accesso a questo contenuto.").'"'; } else { echo "bg-light"; } ?>" style="width: 18rem; float: none; margin: 0 auto;">
						<div class="card-body">
							<div align="center">
								<span class="oi oi-delete"></span>
								<br>
							</div>
							<h5 class="card-title"><?php echo _("Format. Tabelle"); ?></h5>
							<p class="card-text">
								<form id="formtruncate" name="formtruncate" action="Utils/svuotaTabella.php" method="POST">
									<select class="form-control" id="tabellatruncate" <?php if (!getPermission($id, "FormattazioneStringhe")) {echo "disabled";}?>>
										<option value="-1"><?php echo _("-- Seleziona --"); ?></option>
										<option value="Tutte"><?php echo _("Tutte (Stringhe...)"); ?></option>
										<option value="<?php echo $table_cheatjava; ?>"><?php echo $table_cheatjava; ?></option>
										<option value="<?php echo $table_cheatdwm; ?>"><?php echo $table_cheatdwm; ?></option>
										<option value="<?php echo $table_cheatmsmpeng; ?>"><?php echo $table_cheatmsmpeng; ?></option>
										<option value="<?php echo $table_cheatlsass; ?>"><?php echo $table_cheatlsass; ?></option>
										<option value="<?php echo $table_suspectjava; ?>"><?php echo $table_suspectjava; ?></option>
										<option value="<?php echo $table_suspectdwm; ?>"><?php echo $table_suspectdwm; ?></option>
										<option value="<?php echo $table_suspectmsmpeng; ?>"><?php echo $table_suspectmsmpeng; ?></option>
										<option value="<?php echo $table_suspectlsass; ?>"><?php echo $table_suspectlsass; ?></option>
										<option value="<?php echo $table_historycheat; ?>"><?php echo $table_historycheat; ?></option>
										<option value="<?php echo $table_historyanalyzer; ?>"><?php echo $table_historyanalyzer; ?></option>
										<option value="<?php echo $table_tickets; ?>"><?php echo $table_tickets; ?></option>
										<option value="<?php echo $table_news; ?>"><?php echo $table_news; ?></option>
										<option value="<?php echo $table_networks; ?>"><?php echo $table_networks; ?></option>
										<option value="<?php echo $table_permissions; ?>"><?php echo $table_permissions; ?></option>
										<option value="<?php echo $table_licenses; ?>"><?php echo $table_licenses; ?></option>
										<option value="<?php echo $table_payments; ?>"><?php echo $table_payments; ?></option>
										<option value="<?php echo $table_preferences; ?>"><?php echo $table_preferences; ?></option>
										<option value="<?php echo $table_cpstest; ?>"><?php echo $table_cpstest; ?></option>
										<option value="<?php echo $table_otpsessions; ?>"><?php echo $table_otpsessions; ?></option>
										<option value="<?php echo $table_datarequests; ?>"><?php echo $table_datarequests; ?></option>
									</select>
									<br><br><br>
									<button class="btn btn-danger" type="submit" <?php if (!getPermission($id, "FormattazioneStringhe")) {echo "disabled";} ?>><?php echo _("Svuota tabella/e"); ?></button>
								</form>
							</p>
						</div>
					</div>
				</div>
				<br>
				<p style="color:lightgray;"><small><?php echo _("La diffusione del contenuto delle pagine di amministrazione è severamente vietata."); ?></small></p>
			</div>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php"; ?>
</body>
</html>
<script>
    setInterval(function() {
        checkConnection();
    }, 7500);

	setInterval(function() {
		checkActive();
	}, 7500);

	setInterval(function() {
		checkFileNumber();
	}, 7500);
	
	function checkConnection() {
		$.ajax({
		type: "POST",
		url: "Utils/checkConnection.php",
		datatype: "html",
		success:function(result)
		{
			if(result == "success") {
				$("#checkconnection").css("color", "green");
				$("#checkconnection").html("✅");
			} else if (result == "error_config") {
				$("#checkconnection").css("color", "red");
				$("#checkconnection").html("<?php echo _("ERRORE CONFIG"); ?> ❌");
			} else if (result == "failed") {
				$("#checkconnection").css("color", "red");
				$("#checkconnection").html("<?php echo _("ERRORE DATABASE"); ?> ❌");
			} else if (result == "error_unlogged") {
				$("#checkconnection").css("color", "red");
				$("#checkconnection").html("<?php echo _("Sessione non valida: Rieffettua il login"); ?>");
			} else {
				$("#checkconnection").html(result);
			}
		}
		});
	}

	function checkFileNumber() {
		$.ajax({
		type: "POST",
		url: "Utils/checkFileAnalysis.php",
		datatype: "html",
		success:function(result)
		{
			if(result != "error_unlogged") {
				if(result == 0) {
					$("#numerofiles").css("color", "black");
					$("#numerofiles").html("<?php echo _("Nessuno"); ?>");
				} else {
					$("#numerofiles").css("color", "black");
					if(result == 1) {
						$("#numerofiles").html("<b>1</b> <?php echo _("scansione in corso"); ?>");
					} else {
						$("#numerofiles").html("<b>"+result+"</b> <?php echo _("scansioni in corso"); ?>");
					}
				}
			} else {
				$("#numerofiles").css("color", "red");
				$("#numerofiles").html("<?php echo _("Sessione non valida: Rieffettua il login"); ?>");
			}
		}
		});
	}

	function checkActive() {
		$.ajax({
		type: "POST",
		url: "Utils/checkActive.php",
		datatype: "html",
		success:function(result)
		{
			if(result != "error_unlogged") {
				if(result == 0) {
					$("#checkactive").css("color", "black");
					$("#checkactive").html("<?php echo _("Nessuno"); ?>");
				} else if (result > 0) {
					$("#checkactive").css("color", "black");
					$("#checkactive").html("<b>"+result+"</b> <?php echo _("utente/i è disabilitato e non può accedere."); ?>");
				} else {
					$("#checkactive").html(result);
				}
			} else {
				$("#checkactive").css("color", "red");
				$("#checkactive").html("<?php echo _("Sessione non valida: Rieffettua il login"); ?>");
			}
		}
		});
	}
	
	function checkRankingAverage() {
		$.ajax({
		type: "POST",
		url: "../Utils/checkCPSAverage.php",
		datatype: "html",
		success:function(result)
		{
			$("#checkcpsaverage").css("color", "black");
			$("#checkcpsaverage").html("<b>"+result+"</b> CPS");
		}
		});
	}

    document.getElementById('jquery').addEventListener('load', function() {	
        $("#aggiungiStringa").submit(function(e) {
            e.preventDefault();
            $.post('Utils/aggiungiStringa.php', {
                    stringa: $("#stringa").val(),
					nomeclient: $("#nomeclient").val(),
                    tabella: $("#selectstringa").val()
                },
                function(result) {
                    $("#stringa").val("");
					$("#nomeclient").val("");
                    if(result == "success") {
						swal({
						  title: "<?php echo _("Successo"); ?>",
						  text: "<?php echo _("Stringa aggiunta!"); ?>",
						  type: "success",
						  timer: 1000
						});
                    } else if(result == "failure") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Inserimento fallito."); ?>",
						  type: "error"
						});
                    } else if(result == "unset") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Inserisci una stringa."); ?>",
						  type: "error"
						});
                    } else if(result == "alreadyset") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Stringa già presente in quella tabella."); ?>",
						  type: "error"
						});
                    } else if(result == "no_permission") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Non hai i permessi."); ?>",
						  type: "error"
						});
					} else if(result == "error_unlogged") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
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

        $("#rimuoviStringa").submit(function(e) {
            e.preventDefault();
            $.post('Utils/eliminaStringa.php', {
                    stringa: $("#stringa2").val(),
                    tabella: $("#selectstringa2").val()
                },
                function(result) {
                    $("#stringa2").val("");
                    if(result == "success") {
						swal({
						  title: "<?php echo _("Successo"); ?>",
						  text: "<?php echo _("Stringa rimossa!"); ?>",
						  type: "success",
						  timer: 1000
						});
                    } else if(result == "failure") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Rimozione fallita."); ?>",
						  type: "error"
						});
                    } else if(result == "unset") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Inserisci una stringa."); ?>",
						  type: "error"
						});
                    } else if(result == "notfound") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Stringa non trovata in quella tabella."); ?>",
						  type: "error"
						});
                    } else if(result == "no_permission") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Non hai i permessi."); ?>",
						  type: "error"
						});
					} else if(result == "error_unlogged") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
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
		
		$("#buttonupdatesoftware").on("click", function() {
			$("#updatesoftware").click();
		});
		
		$("#invioupdate").on("click", function() {
			if($('#updatesoftware').get(0).files.length === 0) {
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Inserisci un file di aggiornamento."); ?>",
				  type: "error"
				});	
			}
		});
		
		$("#updatesoftware").change(function() {
			if($("#updatesoftware").val()) {
				var nomefile = $("#updatesoftware").val().split('\\').pop();
				if($("#updatesoftware").val().split(".").pop() == "rar") {
					$("#buttonupdatesoftware").html(nomefile);
					$( "#invioupdate" ).prop( "disabled", false );
				} else {
					$("#buttonupdatesoftware").html("<?php echo _(".rar richiesto"); ?>");
					$( "#invioupdate" ).prop( "disabled", true );
				}
			} else {
				$("#buttonupdatesoftware").html("<?php echo _("File di aggiornamento"); ?>");
			}
		});
		
        $("#formuploadsoftware").submit(function(e) {
            e.preventDefault();
			var file = document.getElementById("updatesoftware").files[0];
			form_data = new FormData();
			form_data.append("file", file);
			$.ajax({
				url: 'Utils/updateSoftware.php',
				type: 'POST',
				data: form_data,
				cache: false,
				contentType: false,
				processData: false,
				
				success: function(result) {
                    if(result == "success") {
						swal({
						  title: "<?php echo _("Successo"); ?>",
						  text: "<?php echo _("Aggiornamento software pubblicato!"); ?>",
						  type: "success"
						});
                    } else {
						if(result == "error_generic") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Errore generico."); ?>",
							  type: "error"
							});
						} else if(result == "error_extension") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("L'estensione deve essere .rar"); ?>",
							  type: "error"
							});
						} else if(result == "error_upload_fail") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Upload file fallito."); ?>",
							  type: "error"
							});
						} else if(result == "error_unlogged") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
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
				},
				error: function(result) {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: result,
					  type: "error"
					});
				},
				// Custom XMLHttpRequest
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					var percentuale;
					if (myXhr.upload) {
						// For handling the progress of the upload
						myXhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								percentuale = (100 * e.loaded) / e.total;
								percentuale = Math.round(percentuale);
								swal({
								  position: 'bottom-start',
								  title: "<?php echo _("Upload in corso"); ?>",
								  html: percentuale+"%",
								  type: "info",
								  showConfirmButton: false,
								  toast: true,
								  timer: 3000,
								  animation: false
								});	
							}
						} , false);
					}
					return myXhr;
				}
			});
        });

		$("#formnetworklog").submit(function(e) {
			if($("#selectnetworklog").val() == -1) {
				e.preventDefault();
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Seleziona un file di log."); ?>",
				  type: "error"
				});
			} else if($("#selectnetworklog").val() == 0) {
				e.preventDefault();
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Non ci sono Network di cui visualizzare i log."); ?>",
				  type: "error"
				});
			}
		});
		
		$("#exportStringa").submit(function(e) {
			if($("#tabellaexport").val() == -1) {
				e.preventDefault();
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Seleziona una tabella da esportare."); ?>",
				  type: "error"
				});
			} else {
				swal({
				  position: 'bottom-start',
				  title: "<?php echo _("Download..."); ?>",
				  html: "",
				  type: "info",
				  showConfirmButton: false,
				  toast: true,
				  timer: 3000
				});	
			}
		});
		
		$("#buttonfileimport").on("click", function() {
			$("#fileimport").click();
		});
		
		$("#fileimport").change(function() {
			if($("#fileimport").val()) {
				var nomefile = $("#fileimport").val().split('\\').pop();
				if($("#fileimport").val().split(".").pop() == "txt") {
					$("#buttonfileimport").html(nomefile);
					$( "#buttonimportfile" ).prop( "disabled", false );
				} else {
					$("#buttonfileimport").html("<?php echo _(".txt richiesto"); ?>");
					$( "#buttonimportfile" ).prop( "disabled", true );
				}
			} else {
				$("#buttonfileimport").html("<?php echo _("File import."); ?>");
			}
		});
		
        $("#formimport").submit(function(e) {
            e.preventDefault();
			if($("#tabellaimport").val() != -1) {
				if($("#fileimport").get(0).files.length != 0) {
					var file = document.getElementById("fileimport").files[0];
					var tipoimport = $('input[name=tipoimport]:checked', '#formimport').val()
					var tabella = $("#tabellaimport").val();
					form_data = new FormData();
					form_data.append("fileimport", file);
					form_data.append("tipoimport", tipoimport);
					form_data.append("tabella", tabella);
					
					swal({
					  title: "<?php echo _("Richiesta conferma"); ?>",
					  html: "<?php echo _("Cliccando su 'Conferma' la tabella"); ?> "+$("#tabellaimport").val()+" <?php echo _("sarà riempita di nuovi valori contenuti nel file di importazione."); ?>",
					  type: "warning",
					  showCancelButton: true,
					  confirmButtonText: '<?php echo _("Conferma"); ?>',
					  cancelButtonText: '<?php echo _("Annulla"); ?>'
					}).then((result) => {
					  if (result.value) {
						swal({
						  title: "<?php echo _("Attendi"); ?>",
						  text: "<?php echo _("Operazione in corso..."); ?>",
						  type: "info"
						});
						$.ajax({
							url: 'Utils/importaStringhe.php',
							type: 'POST',
							data: form_data,
							cache: false,
							contentType: false,
							processData: false,
							
							success: function(result) {
								if(result == "success") {
									swal({
									  title: "<?php echo _("Operazione completata"); ?>",
									  text: "<?php echo _("Importazione completata!"); ?>",
									  type: "success"
									});
								} else if(result == "unset") {
									swal({
									  title: "<?php echo _("Si è verificato un errore"); ?>",
									  text: "<?php echo _("Imposta tutti i campi."); ?>",
									  type: "error"
									});
								} else if(result == "unset_file") {
									swal({
									  title: "<?php echo _("Si è verificato un errore"); ?>",
									  text: "<?php echo _("File non valido."); ?>",
									  type: "error"
									});
								} else if(result == "error_truncate") {
									swal({
									  title: "<?php echo _("Si è verificato un errore"); ?>",
									  text: "<?php echo _("Impossibile svuotare la tabella."); ?>",
									  type: "error"
									});
								} else if(result == "error_unlogged") {
									swal({
									  title: "<?php echo _("Si è verificato un errore"); ?>",
									  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
									  type: "error"
									});
								} else {
									swal({
									  title: "<?php echo _("Caso imprevisto"); ?>",
									  text: result,
									  type: "error"
									});
								}
							},
							error: function(result) {
								swal({
								  title: "<?php echo _("Caso imprevisto"); ?>",
								  text: result,
								  type: "error"
								});
							}
						});
					  }
					});
				} else {
					swal({
					  title: "<?php echo _("Si è verificato un errore"); ?>",
					  text: "<?php echo _("Inserisci il file da importare."); ?>",
					  type: "error"
					});
				}
			} else {
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Seleziona una tabella in cui importare."); ?>",
				  type: "error"
				});
			}
        });
		
        $("#formtruncate").submit(function(e) {
            e.preventDefault();
			if($("#tabellatruncate").val() != -1) {
				swal({
				  title: "<?php echo _("Richiesta conferma"); ?>",
				  html: "<?php echo _("Cliccando su 'Conferma' la tabella"); ?> "+$("#tabellatruncate").val()+" <?php echo _("sarà svuotata e non si potrà recuperarla. Confermi l'operazione?"); ?>",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonText: '<?php echo _("Conferma"); ?>',
				  cancelButtonText: '<?php echo _("Annulla"); ?>'
				}).then((result) => {
				  if (result.value) {
					swal({
					  title: "<?php echo _("Attendi"); ?>",
					  text: "<?php echo _("Operazione in corso..."); ?>",
					  type: "info"
					});
					
					$.post('Utils/svuotaTabella.php', {
							tabella: $("#tabellatruncate").val()
						},
						function(result) {
							if(result == "success") {
								swal({
								  title: "<?php echo _("Operazione completata"); ?>",
								  text: "<?php echo _("Tabella svuotata con successo!"); ?>",
								  type: "success"
								});
							} else if(result == "failure") {
								swal({
								  title: "<?php echo _("Si è verificato un errore"); ?>",
								  text: "<?php echo _("Nome tabella errato."); ?>",
								  type: "error"
								});
							} else if(result == "unset") {
								swal({
								  title: "<?php echo _("Si è verificato un errore"); ?>",
								  text: "<?php echo _("Imposta tutti i valori."); ?>",
								  type: "error"
								});
							} else if(result == "error_unlogged") {
								swal({
								  title: "<?php echo _("Si è verificato un errore"); ?>",
								  text: "<?php echo _("Sessione non valida: Rieffettua il login"); ?>",
								  type: "error"
								});
							} else {
								swal({
								  title: "<?php echo _("Caso imprevisto"); ?>",
								  text: result,
								  type: "error"
								});
							}
						}
					);
				  }
				});
			} else {
				swal({
				  title: "<?php echo _("Si è verificato un errore"); ?>",
				  text: "<?php echo _("Seleziona una tabella da svuotare."); ?>",
				  type: "error"
				});
			}
		});
    });

    function avvioInterval() {
        checkConnection();
		checkActive();
		checkFileNumber();
		checkRankingAverage();
    }
</script>