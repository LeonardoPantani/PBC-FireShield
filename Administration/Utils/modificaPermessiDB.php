<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "Account_Permissions")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_POST["id_utente"]) && isset($_POST["nome_permesso"]) && isset($_POST["valore_permesso"])) {
		$id_utente = $_POST["id_utente"];
		$nome_permesso = $_POST["nome_permesso"];
		$valore_permesso = $_POST["valore_permesso"];
	} else {
		echo "unset";
	}
	
	if(getPermission($id_utente, "Account_Permissions") && $id != $id_utente) { // non si può modificare i permessi di un utente che può modificare altri permessi
		echo "invalid_action";
	} else {
		if($valore_permesso != "1" && $valore_permesso != "0") {
			echo "invalid_value";
		} else {
			$stmt = $connection->prepare("UPDATE $table_permissions SET $nome_permesso = ? WHERE IDUtente = ?");
			$stmt->bind_param("si", $valore_permesso, $id_utente);
			$esito = $stmt->execute();
			if ($esito) {
				echo "success";
			} else {
				echo "failure";
			}	
		}
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}