<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "EsportazioneStringhe")) {
	echo "no_permission";
	exit;
}

if (isset($_POST["tabella"])) {
	$tabella = $_POST["tabella"];
	header('Content-disposition: attachment; filename=export_'.$tabella.'.txt');
	header('Content-type: text/plain');
	
	$contenuto = "";
	$stmt = $connection->prepare("SELECT Stringa FROM $tabella");
	$stmt->execute();
	$result = $stmt->get_result();

	while ($riga = $result->fetch_assoc()) {
		$contenuto .= $riga["Stringa"]."\n";
	}
	echo $contenuto;
} else {
	echo "unset";
}
?>