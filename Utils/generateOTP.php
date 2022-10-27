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
	$univoco = false;
	
	while(!$univoco) {
		$codicegenerato = bin2hex(random_bytes(5)); // 10 caratteri
		
		$stmt = $connection->prepare("SELECT CodiceOTP FROM $table_otpsessions WHERE CodiceOTP = ?");
		$stmt->bind_param("s", $codicegenerato);
		$stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
		if($nrighe == 0) {
			$univoco = true;
		}
	}
	$stmt = $connection->prepare("UPDATE $table_otpsessions SET CodiceOTP = ?, DataInizioSessione = NULL, DataFineSessione = NULL, StatoSessione = 1, ScansioniEseguite = 0 WHERE IDUtente = ?");
	$stmt->bind_param("si", $codicegenerato, $id);
	$esito = $stmt->execute();
	if ($esito) {
		echo "ok";
	} else {
		echo "failure";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>