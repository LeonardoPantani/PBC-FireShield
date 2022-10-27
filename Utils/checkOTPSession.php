<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if(!isLogged()) {
	echo "not_logged";
	exit;
}

readUserData($id, $email, $username, $tipo, $network, $otp);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	$stmt = $connection -> prepare("SELECT DataFineSessione FROM $table_otpsessions WHERE IDUtente = ? AND CodiceOTP = ?");
	$stmt->bind_param("is", $id, $_SESSION["codiceotp"]);
	$stmt->execute();
	$esito = $stmt->get_result();
	$nrighe = $esito->num_rows;
	if($nrighe > 0) {
		$riga = $esito->fetch_assoc();
		if(strtotime($riga["DataFineSessione"]) > strtotime("now")) {
			echo "ok";
		} else {
			updateStatus();
			echo "expired";
		}
	} else {
		echo "not_found";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}

function updateStatus() {
	$tabella = $GLOBALS["table_otpsessions"];
	$stmt = $GLOBALS["connection"]->prepare("UPDATE $tabella SET StatoSessione = 3 WHERE IDUtente = ? AND CodiceOTP = ?");
	$stmt->bind_param("is", $GLOBALS["id"], $_SESSION["codiceotp"]);
	$stmt->execute();
}
?>