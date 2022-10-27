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
	$stmt = $connection->prepare("SELECT CodiceOTP, DataInizioSessione, DataFineSessione, StatoSessione, ScansioniEseguite FROM $table_otpsessions WHERE IDUtente = ?");
	$stmt->bind_param("i", $id);
	$esito = $stmt->execute();
	$result = $stmt -> get_result();
	$nrighe = $result -> num_rows;
	if ($nrighe > 0) {
		$riga = $result -> fetch_assoc();
		if($riga["CodiceOTP"] == "") {
			echo "empty";
		} else {
			if($riga["DataInizioSessione"] == "") {
				$riga["DataInizioSessione"] = "NULL";
			}
			if($riga["DataFineSessione"] == "") {
				$riga["DataFineSessione"] = "NULL";
			}
			echo $riga["CodiceOTP"]."_".$riga["DataInizioSessione"]."_".$riga["DataFineSessione"]."_".$riga["StatoSessione"]."_".$riga["ScansioniEseguite"]."_".formatDate($riga["DataInizioSessione"], true)."_".formatDate($riga["DataFineSessione"], true);
		}
	} else {
		echo "failure";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>