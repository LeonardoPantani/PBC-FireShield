<?php
// COPYRIGHT FIRESHIELD 2018
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

/*
	TASK AUTOMATICI PROGRAMMATI
	
	Leonardo Pantani, Copyright FireShield 2018, Tutti i diritti riservati
*/

try {
	// ------------- ELIMINAZIONE FILE DI HISTORY SCANSIONI OBSOLETI
	$filerimossi_history = 0;
	$nomifile = array_diff(scandir($droot."/".$folderhistory), array('.', '..'));
	$dataoggi = new DateTime();

	foreach($nomifile as $valore) {
		$divisione = explode("_", $valore);
		$data = explode("-", $divisione[1]);
		$data[5] = str_replace(".txt", "", $data[5]);

		$datafile = $data[0]."-".$data[1]."-".$data[2]." ".$data[3].":".$data[4].":".$data[5];
		$datafile = date_create_from_format("Y-m-d H:i:s", $datafile);
		$differenza = intval(date_diff($dataoggi, $datafile)->format("%d"));
		
		if($differenza >= $daysbeforedelete) {
			unlink($droot.$folderhistory."/".$valore);
			$filerimossi_history++;
		}
	}

	$stmt = $connection->prepare("SELECT ID FROM $table_historyanalyzer");
	$stmt->execute();
	$esito = $stmt->get_result();
	$numero_completo_scansioni = $esito->num_rows;

	$stmt = $connection->prepare("SELECT Media FROM $table_cpstest");
	$stmt->execute();
	$esito = $stmt->get_result();
	$nrighe = $esito->num_rows;
	$cpstotali = 0;
	if($nrighe > 0) {
		while($riga = $esito -> fetch_assoc()) {
			$cpstotali = $cpstotali + $riga["Media"];
		}
		$mediacps = round($cpstotali/$nrighe, 2);
	}

	$filerimossi_datarequest = 0;
	$datarequests_presenti = new FilesystemIterator($droot.$folderdatarequests."/", FilesystemIterator::SKIP_DOTS);
	if(iterator_count($datarequests_presenti) > 0) {
		$stmt = $connection->prepare("SELECT IDRichiesta, Data FROM $table_datarequests");
		$stmt->execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		$dataoggi2 = date("Y-m-d H:i:s");
		while($riga = $esito->fetch_assoc()) {
			$differenza = abs(strtotime($dataoggi2) - strtotime($riga["Data"]));
			$years = floor($differenza / (365*60*60*24));
			$months = floor(($differenza - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($differenza - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			
			if($days >= $daysbeforedeletedatarequests) {
				if(file_exists($droot.$folderdatarequests."/"."DataRequest_".$riga["IDRichiesta"].".txt")) {
					unlink($droot.$folderdatarequests."/"."DataRequest_".$riga["IDRichiesta"].".txt");
					$filerimossi_datarequest++;	
				}
			}
		}
	}
	// telegramMSG(true);
	file_put_contents("esito.txt",$numero_completo_scansioni."\n".date_format($dataoggi, "Y-m-d H:i:s")."\n"."OK"."\n".$mediacps);
	// ------------- (SPAZIO VUOTO)
} catch(Exception $e) {
	// telegramMSG(false);
	file_put_contents("esito.txt", $numero_completo_scansioni."\n".date_format($dataoggi, "Y-m-d H:i:s")."\n"."ERROR ".$e->getMessage()."\n".$mediacps);
}

function telegramMSG($stato) {
	if($stato) {
		sendTelegramMessage("
		💾 <b>PULIZIA & UPDATE AUTOMATICI</b> 💾
		______________________________________

		Scansioni totali: <b>".$GLOBALS["numero_completo_scansioni"]."</b>
		CPS medi: <b>".$GLOBALS["mediacps"]."</b>
		File history rimossi: <b>".$GLOBALS["filerimossi_history"]."</b>
		File datarequest rimossi: <b>".$GLOBALS["filerimossi_datarequest"]."</b>
		Esito: ✅

		Data: <b>".date("d/m/Y H:i:s")."</b>");
	} else {
		sendTelegramMessage("
		💾 <b>PULIZIA & UPDATE AUTOMATICI</b> 💾
		______________________________________

		Scansioni totali: <b>".$GLOBALS["numero_completo_scansioni"]."</b>
		CPS medi: <b>".$GLOBALS["mediacps"]."</b>
		File history rimossi: <b>".$GLOBALS["filerimossi_history"]."</b>
		File datarequest rimossi: <b>".$GLOBALS["filerimossi_datarequest"]."</b>
		Esito: ❌

		Data: <b>".date("d/m/Y H:i:s")."</b>");
	}
}
?>