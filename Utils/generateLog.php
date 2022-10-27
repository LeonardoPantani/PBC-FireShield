<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

$errore = false;
if(isset($_GET["id"])) {
	if(is_numeric($_GET["id"])) {
		$stmt = $connection -> prepare("SELECT * FROM $table_historyanalyzer WHERE ID = ?");
		$stmt -> bind_param("i", $_GET["id"]);
		$stmt -> execute();
		$result = $stmt -> get_result();
		$nrighe = $result -> num_rows;
		if($nrighe > 0) {
			$riga = $result -> fetch_assoc();
			
			$stmt = $connection -> prepare("SELECT Username FROM $table_users WHERE ID = ?");
			$stmt -> bind_param("i", $riga["IDUtente"]);
			$stmt -> execute();
			$result2 = $stmt -> get_result();
			$nrighe2 = $result2 -> num_rows;
			if($nrighe2 > 0) {
				$riga2 = $result2 -> fetch_assoc();
			}
			if($riga["Controllo"] == 1) { 
				$stmt = $connection -> prepare("SELECT * FROM $table_cheatjava WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 2) {
				$stmt = $connection -> prepare("SELECT * FROM $table_cheatdwm WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 3) {
				$stmt = $connection -> prepare("SELECT * FROM $table_cheatmsmpeng WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 4) {
				$stmt = $connection -> prepare("SELECT * FROM $table_cheatlsass WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 5) {
				$stmt = $connection -> prepare("SELECT * FROM $table_suspectjava WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 6) {
				$stmt = $connection -> prepare("SELECT * FROM $table_suspectdwm WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 7) {
				$stmt = $connection -> prepare("SELECT * FROM $table_suspectmsmpeng WHERE Stringa = ?");
			} elseif($riga["Controllo"] == 8) {
				$stmt = $connection -> prepare("SELECT * FROM $table_suspectlsass WHERE Stringa = ?");
			}
			$stmt -> bind_param("s", $riga["StringaTrovata"]);
			$stmt -> execute();
			$result = $stmt -> get_result();
			$nrighe = $result -> num_rows;
			if($nrighe > 0) {
				$riga3 = $result -> fetch_assoc();
			}

			if($riga["Esito"] == 0) { 
				$esitoscansione = _("Errore");
			} elseif($riga["Esito"] == 1) {
				$esitoscansione = _("Pulito");
			} elseif($riga["Esito"] == 2) {
				$esitoscansione = _("Sospetto");
			} elseif($riga["Esito"] == 3) {
				$esitoscansione = _("Cheat");
			}

			if($riga["Controllo"] == 1 || $riga["Controllo"] == 5) { 
				$tiposcansione = "Javaw";
			} elseif($riga["Controllo"] == 2 || $riga["Controllo"] == 6) {
				$tiposcansione = "Dwm";
			} elseif($riga["Controllo"] == 3 || $riga["Controllo"] == 7) {
				$tiposcansione = "Msmpeng";
			} elseif($riga["Controllo"] == 4 || $riga["Controllo"] == 8) {
				$tiposcansione = "lsass";
			} else {
				$tiposcansione = _("Sconosciuto");
			}
			
			header('Content-disposition: attachment; filename='._("Log scansione").' n°'.$_GET["id"].'.txt');
			header('Content-type: text/plain');

			$contenuto = "--------------------------------------------------\n";
			$contenuto .= $solutionname." "._("- File di log scansione");
			$contenuto .= "\n";
			$contenuto .= "--------------------------------------------------\n";
			$contenuto .= "\n\n";
			$contenuto .= _("Esito scansione").": ".$esitoscansione;
			$contenuto .= "\n";
			$contenuto .= _("Iniziata il").": ".formatDateComplete($riga["Data"], true);
			$contenuto .= "\n";
			$contenuto .= _("Durata").": ".$riga["Durata"];
			$contenuto .= "\n";
			$contenuto .= _("Tipo scansione").": ".$tiposcansione;
			$contenuto .= "\n";
			if($riga["Esito"] == 3) {
				$contenuto .= _("Risultato").": ".$riga3["Client"];
				$contenuto .= "\n";
			}
			if($riga2["Username"] != "") {
				$contenuto .= _("Utente").": ".$riga2["Username"];
				$contenuto .= "\n";
			}
			$contenuto .= "\n--------- "._("FINE DEL FILE DI LOG SCANSIONE")." ---------";
			echo $contenuto;
		} else {
			echo "invalid";
		}
	} else {
		echo "invalid";
	}
} else {
	echo "invalid";
}