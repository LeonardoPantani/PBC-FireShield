<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaNews")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_POST["titolo"]) && isset($_POST["testo"])) {
		$titolo = $_POST["titolo"];
		$testo = nl2br($_POST["testo"]);
		$id_notizia = $_POST["id_notizia"];
	} else {
		echo "unset";
	}
	$data = new DateTime("now");
	$data = $data->format("Y-m-d H:i");

	$stmt = $connection->prepare("UPDATE $table_news SET Titolo = ?, Testo = ? WHERE ID = ?");
	$stmt->bind_param("ssi", $titolo, $testo, $id_notizia);
	$esito = $stmt->execute();
	if ($esito) {
		echo "success";
	} else {
		echo "failure";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}