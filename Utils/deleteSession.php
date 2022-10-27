<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

if (isset($_SESSION["lang"])) {
	$linguaprecedente = $_SESSION["lang"];
}
if($otp) {
	$stmt = $connection->prepare("UPDATE $table_otpsessions SET CodiceOTP = NULL, DataInizioSessione = NULL, DataFineSessione = NULL, StatoSessione = 0, ScansioniEseguite = 0 WHERE IDUtente = ? AND CodiceOTP = ?");
	$stmt->bind_param("is", $id, $_SESSION["codiceotp"]);
	$stmt->execute();
}
session_destroy();
session_name('__Secure-FSCDSESSION');
session_start();
if (!empty($linguaprecedente)) {
	$_SESSION["lang"] = $linguaprecedente;
}
if(isset($_GET["m"])) {
	header("Location:/login.php?e=".$_GET["m"]);
	exit;
}
if(isset($_GET["r"])) {
	header("Location:".$_GET["r"]);
	exit;
}
header("Location:/");
exit;
