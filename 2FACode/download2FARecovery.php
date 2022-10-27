<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

$contenuto = "";

$stmt = $connection->prepare("SELECT * FROM $table_2fa WHERE IDUtente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$esito = $stmt->get_result();
$nrighe = $esito->num_rows;
if($nrighe > 0) {
	$riga = $esito -> fetch_assoc();
	$array_codici = unserialize($riga["RecoveryCodes"]);
	foreach($array_codici as $codice) {
		$contenuto .= $codice."\n";
	}
	header('Content-disposition: attachment; filename='.$solutionname_short.'_recovery_codes.txt');
	header('Content-type: text/plain');
	echo $contenuto;
} else {
	echo "invalid";
}