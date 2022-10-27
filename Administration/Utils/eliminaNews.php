<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaNews")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_GET["id"])) {
		$id_notizia = $_GET["id"];
	} else {
		echo "unset";
		exit;
	}

	$stmt = $connection->prepare("DELETE FROM $table_news WHERE ID = ?");
	$stmt->bind_param("i", $id_notizia);
	$esito = $stmt->execute();

	$stmt = $connection -> prepare("ALTER TABLE $table_news AUTO_INCREMENT = 1");
	$stmt->execute();
	
	if($esito) {
		echo "success";
	} else {
		echo "failure";
	}
}
exit;
