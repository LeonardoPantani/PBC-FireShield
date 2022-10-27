<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if (!isLogged()) {
	echo "invalid_session";
	exit;
}

readUserData($id, $email, $username, $tipo, $network, $otp);

if(isset($_GET["preferenza_nome"]) && isset($_GET["preferenza_valore"])) {
	if(empty($_GET["preferenza_nome"])) {
		echo "unset";
	} else {
		$nomepreferenza = filter_var($_GET["preferenza_nome"], FILTER_SANITIZE_STRING);
		$valorepreferenza = filter_var($_GET["preferenza_valore"], FILTER_SANITIZE_STRING);
		if($nomepreferenza == "2FARichiesto" && $tipo != 1) { // temporaneo
			echo "failure";
		} else {
			if($nomepreferenza == "Lingua") {
				if(!in_array($valorepreferenza, $language_list)) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "NewsLetter") {
				if($valorepreferenza != 0 && $valorepreferenza != 1) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "LoginRedirect") {
				if($valorepreferenza != 0 && $valorepreferenza != 1 && $valorepreferenza != 2) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "PopupScansione") {
				if($valorepreferenza != 0 && $valorepreferenza != 1) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "Intervallo") {
				if(!in_array($valorepreferenza, $interval_list)) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "2FARichiesto") {
				if(($valorepreferenza != 0 && $valorepreferenza != 1) || $tipo != 1) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "FormatoData") {
				if($valorepreferenza != 0 && $valorepreferenza != 1 && $valorepreferenza != 2) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "RiepilogoScansioni") {
				if(!in_array($valorepreferenza, $scans_summary_numbers)) {
					echo "failure";
					exit;
				}
			} elseif($nomepreferenza == "ReloadAuto") {
				if($valorepreferenza != 0 && $valorepreferenza != 1) {
					echo "failure";
					exit;
				}
			} else {
				echo "failure";
				exit;
			}
			
			// CONTROLLI EFFETTUATI (Eccetto quello sul tipo)
			$esito = updatePreference($id, $nomepreferenza, $valorepreferenza);
			if($esito == 1) {
				echo "success";
			} else {
				echo "failure";
			}
		}
	}
} else {
	echo "unset";
}
?>