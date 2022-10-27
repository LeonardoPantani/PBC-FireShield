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

if (!getPermission($id, "CronologiaEventi")) {
	header("Location:pannello.php");
	exit;
}

$max_rows_shown = 300;

if (isset($_GET["order"])) {
    if ($_GET["order"] == "date") {
        $stmt = $connection->prepare("SELECT $table_historyanalyzer.*, $table_users.Username FROM $table_historyanalyzer, $table_users WHERE $table_historyanalyzer.IDUtente = $table_users.ID ORDER BY Data DESC LIMIT ".$max_rows_shown);
    } else if ($_GET["order"] == "duration") {
        $stmt = $connection->prepare("SELECT $table_historyanalyzer.*, $table_users.Username FROM $table_historyanalyzer, $table_users WHERE $table_historyanalyzer.Data > '2018-12-28' AND $table_historyanalyzer.IDUtente = $table_users.ID ORDER BY Durata DESC LIMIT ".$max_rows_shown);
    } else if ($_GET["order"] == "weight") {
        $stmt = $connection->prepare("SELECT $table_historyanalyzer.*, $table_users.Username FROM $table_historyanalyzer, $table_users WHERE $table_historyanalyzer.Data > '2018-12-28' AND $table_historyanalyzer.IDUtente = $table_users.ID ORDER BY PesoFile DESC LIMIT ".$max_rows_shown);
    } else {
        $stmt = $connection->prepare("SELECT $table_historyanalyzer.*, $table_users.Username FROM $table_historyanalyzer, $table_users WHERE $table_historyanalyzer.IDUtente = $table_users.ID ORDER BY Data DESC LIMIT ".$max_rows_shown);
    }
} else {
    $stmt = $connection->prepare("SELECT $table_historyanalyzer.*, $table_users.Username FROM $table_historyanalyzer, $table_users WHERE $table_historyanalyzer.IDUtente = $table_users.ID ORDER BY Data DESC LIMIT ".$max_rows_shown);
}
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;

$scansioni_giorno = 0;
$scansioni_settimana = 0;
$data_oggi = new DateTime();
while ($riga = $result->fetch_assoc()) {
	$data_scansione = new DateTime($riga["Data"]);
	
	if($data_oggi->diff($data_scansione)->days === 0) {
		$scansioni_giorno++;
	}
	if($data_oggi->diff($data_scansione)->days <= 7) {
		$scansioni_settimana++;
	}
}

$stmt = $connection->prepare("SELECT ID FROM $table_historyanalyzer");
$stmt->execute();
$result_contatore = $stmt->get_result();
$righe_totali = $result_contatore->num_rows;

$stmt = $connection->prepare("SELECT Durata, PesoFile FROM $table_historyanalyzer WHERE PesoFile != 0 AND Esito = 1");
$stmt->execute();
$result_tempo_approssimativo = $stmt->get_result();
$righe_tempo_approssimativo = $result_tempo_approssimativo->num_rows;
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title>Amministrazione <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<style>
.loader {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background: url('../CSS/Images/scanning.gif') 50% 50% no-repeat rgb(0,0,0);
	background-size: 100px 100px;
    opacity: .9;
}
</style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
	<div class="loader"></div>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-1 mb-1 bg-dark text-white rounded">
            <h3 id="cima">Cronologia: <?php echo $righe_totali . " ";if ($righe_totali == 1) {echo "evento";} else {echo "eventi";} echo " analyzer"; ?></h3>
            <?php if ($righe_totali > 0) {?>
			<h5><?php if((isset($_GET["order"]) && $_GET["order"] != "duration") || !isset($_GET["order"])) { ?>Scansioni nelle ultime 24 ore: <b><?php echo $scansioni_giorno; ?></b> | Scansioni nell'ultima settimana: <b><?php echo $scansioni_settimana; ?></b><?php } else { echo "Impossibile visualizzare le quantità di scansioni se la tabella è ordinata in base alla durata"; } ?></h5>
			<small><font color="lightgray">Calcolo tempo approssimativo basato su <b><?php echo $righe_tempo_approssimativo; ?></b> scansioni.</font></small>
			<br>
			<table class="table table-dark table-striped">
                <tr>
                    <th>ID</th>
                    <th>Esito</th>
                    <th><a href="listaCronologiaAnalyzer.php?order=date">Data inizio <?php if (isset($_GET["order"])) {if ($_GET["order"] == "date") {echo "<b>*</b>";}} else {echo "<b>*</b>";}?></a></th>
                    <th><a href="listaCronologiaAnalyzer.php?order=duration">Durata analisi <?php if (isset($_GET["order"])) {if ($_GET["order"] == "duration") {echo "<b>*</b>";}}?></a></th>
					<th>Tipo check</th>
					<th>ID Utente</th>
					<th>Stringa trovata</th>
					<th>Client trovato</th>
					<th>OTP?</th>
					<th>IP</th>
					<th><a href="listaCronologiaAnalyzer.php?order=weight">Peso<?php if (isset($_GET["order"])) {if ($_GET["order"] == "weight") {echo "<b>*</b>";}}?></a></th>
                </tr>
                <tbody class="table-striped">
                    <?php
						mysqli_data_seek($result, 0);
						while ($riga = $result->fetch_assoc()) {
							echo "<tr>";
							
							
							echo "<td>".$riga["ID"];
							$dataoggi = strtotime(date("Y-m-d H:i:s"));
							$scadenza = strtotime(date($riga["Data"])." +14 days");
							if($dataoggi < $scadenza) {
								echo " <a title='Clicca per scaricare il file di history, potrebbe volerci qualche minuto' href='Utils/scaricaHistoryDB.php?idscansione=".$riga["ID"]."&mode=NORMAL'><small><span class='oi oi-data-transfer-download'></span></small></a>";
							} else {
								echo " <a title='Il file relativo a questa scansione è scaduto e quindi non si può scaricare'><small><span class='oi oi-data-transfer-download'></span></small></a>";
							}
							echo "</td>";
							
							echo "<td><b>";
							switch ($riga["Esito"]) {
								case 0:
									echo "<font color='lightcoral'>Errore</font></b> ";if ($riga['CodiceErrore'] != 0) {echo "(CE: <a href='#errore" . $riga['CodiceErrore'] . "'>" . $riga['CodiceErrore'] . "</a>)";}
									break;
								case 1:
									echo "<font color='lightgreen'>Pulito</font>";
									break;
								case 2:
									echo "<font color='orange'>Sospetto</font>";
									break;
								case 3:
									echo "<font color='lightcoral'>Cheat</font>";
									break;
								default:
									echo "(Sconosciuto)</font>";
									break;
							}
							echo "</b></td>";
							
							echo "<td>" . formatDate($riga["Data"], true) . "</td>";
							
							echo "<td>" . $riga["Durata"] . "</td>";
							
							echo "<td>";
							switch ($riga["Controllo"]) {
								case 1:
									echo $table_cheatjava;
									$controllo = $table_cheatjava;
									break;
								case 2:
									echo $table_cheatdwm;
									$controllo = $table_cheatdwm;
									break;
								case 3:
									echo $table_cheatmsmpeng;
									$controllo = $table_cheatmsmpeng;
									break;
								case 4:
									echo $table_cheatlsass;
									$controllo = $table_cheatlsass;
									break;
								case 5:
									echo $table_suspectjava;
									$controllo = $table_suspectjava;
									break;
								case 6:
									echo $table_suspectdwm;
									$controllo = $table_suspectdwm;
									break;
								case 7:
									echo $table_suspectmsmpeng;
									$controllo = $table_suspectmsmpeng;
									exit;
								case 8:
									echo $table_suspectlsass;
									$controllo = $table_suspectlsass;
									exit;
								default:
									break;
							}
							echo "</td>";
							
							echo "<td>";if ($riga["IDUtente"] != 0) {echo "<a title='".$riga["Username"]."'>".$riga["IDUtente"]."</a>";} else {echo "<i>Ospite</i>";} echo "</td>";
							if($riga["StringaTrovata"] != "") {
								$stmt2 = $connection -> prepare("SELECT Client FROM $controllo WHERE Stringa = ?");
								$stmt2->bind_param("s", $riga["StringaTrovata"]);
								$stmt2->execute();
								$result_client = $stmt2->get_result();
								$result_client = $result_client->fetch_assoc();
								if(strlen($riga["StringaTrovata"]) > 20) {
									echo "<td><a title='".$riga["StringaTrovata"]."'>" . substr($riga["StringaTrovata"], 0, 20) . " (...)</a></td>";
								} else {
									echo "<td>" . $riga["StringaTrovata"] . "</td>";
								}
							} else {
								echo "<td></td>";
							}
							
							if($riga["StringaTrovata"] != "") {
								if($result_client["Client"] == "") {
									echo "<td><a title='La stringa non è più presente nel database' style='color:lightcoral;'>???</a></td>";
								} else {
									if(strlen($result_client["Client"]) > 30) {
										echo "<td><a title='".$result_client["Client"]."'>" . substr($result_client["Client"], 0, 30) . " (...)</a></td>";
									} else {
										echo "<td>" . $result_client["Client"] . "</td>";
									}
								}
							} else {
								echo "<td></td>";
							}
							
							echo "<td>"; if($riga["ID"] > 2965) { if($riga["OTPUsato"] == 0) { echo "No"; } else { echo "Sì"; } } else { echo "//"; } echo "</td>";
							
							echo "<td>"; if($riga["IndirizzoIP"] == "") { echo "//"; } else { echo "<small>".$riga["IndirizzoIP"]."</small>"; } echo "</td>";
							
							echo "<td>"; if($riga["PesoFile"] == 0) { echo "//"; } else { echo round($riga["PesoFile"]/1000000)."MB"; } echo "</td>";

							
							echo "</tr>";
						}
						?>
                </tbody>
            </table>
			<hr>
			<p>Sono mostrate le ultime <b><?php echo $max_rows_shown; ?></b> righe della tabella.</p>
			<hr>
			<h5>Spiegazione codici errore:</h5>
			<ul align="left">
				<li id="errore0">Errore 0: Errore imprevisto durante la procedura del salvataggio</li>
				<li id="errore1">Errore 1: Non è stato possibile rimuovere il file salvato dopo la scansione.</li>
				<li id="errore2">Errore 2: I valori che la homepage dovrebbe passare alla scansione sono stati manomessi o errati.</li>
				<li id="errore3">Errore 3: Non è stato possibile prendere i dati dalla cartella compressa.</li>
				<li id="errore4">Errore 4: Non è stato possibile prendere il file dalla cartella compressa. (Archivio e file con nomi diversi)</li>
				<li id="errore5">Errore 5: Non è stato possibile rinominare il file estratto dalla cartella compressa.</li>
				<li id="errore6">Errore 6: Non è stato possibile aprire il file.</li>
				<li id="errore7">Errore 7: Non è stato possibile ottenere le stringhe dal database.</li>
				<li id="errore8">Errore 8: Non è stato possibile salvare la stringa cheat nel database.</li>
				<li id="errore9">Errore 9: Non è stato possibile salvare l'evento nella cronologia.</li>
				<li id="errore10">Errore 10: L'upload del file è fallito (da parte dell'utente).</li>
				<li id="errore11">Errore 11: Il file caricato dall'utente è troppo grande e supera il massimo di <?php echo $pesomax / 1000000; ?>MB.</li>
				<li id="errore12">Errore 12: L'estensione del file caricato dall'utente non è .zip</li>
				<li id="errore13">Errore 13: Il file caricato non è valido. (Non è di Process Hacker)</li>
				<li id="errore14">Errore 14: L'utente connesso tramite OTP ha provato ad eseguire più di 4 scansioni (limite).</li>
			</ul>
			<a href="#cima">Clicca qui per tornare in cima</a>
			<br><br>
            <?php } else {
				echo "No cronologia analyzer.";
			}?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
document.getElementById('jquery').addEventListener('load', function() {	
	$(window).on('load', function(){
		$(".loader").fadeOut("slow");
	});
});
</script>