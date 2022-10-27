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

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	$stmt = $connection -> prepare("UPDATE $table_otpsessions SET CodiceOTP = NULL, DataInizioSessione = NULL, DataFineSessione = NULL, StatoSessione = 0, ScansioniEseguite = 0 WHERE IDUtente = ?");
	$stmt -> bind_param("i", $id);
	$esito = $stmt -> execute();
	if ($esito) {
		echo "ok";
	} else {
		echo "failure";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>