<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaStringhe")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_POST["id_stringa"]) && isset($_POST["nome_tabella"]) && isset($_POST["stringa"]) && isset($_POST["client"])) {
		$stringa = $_POST["stringa"];
		$client = $_POST["client"];
		$id_stringa = $_POST["id_stringa"];
		$nome_tabella = $_POST["nome_tabella"];
	} else {
		echo "unset";
	}
	
	$stmt = $connection->prepare("SELECT Stringa, Client FROM " . $_POST["nome_tabella"] . " WHERE ID = ?");
	$stmt->bind_param("i", $_POST["id_stringa"]);
	$stmt->execute();
	$result = $stmt->get_result();
	$riga = $result->fetch_assoc();

	$stmt = $connection->prepare("UPDATE $nome_tabella SET Stringa = ?, Client = ? WHERE ID = ?");
	$stmt->bind_param("ssi", $stringa, $client, $id_stringa);
	$esito = $stmt->execute();
	if ($esito) {
		sendTelegramMessage("📝 <b>STRINGA MODIFICATA</b> 📝\n__________________________________\n\nStringa (Prima): <b>".$riga["Stringa"]."</b>\nStringa (Dopo): <b>".$_POST["stringa"]."</b>\n\nClient (Prima): <b>".$riga["Client"]."</b>\nClient (Dopo): <b>".$_POST["client"]."</b>\n\nTabella: <b>".$_POST["nome_tabella"]."</b>\n\nStringhe nella tabella: <b>".getStringNumber($_POST["nome_tabella"])."</b>\n\nModificata da <i>".getUsernameFromID($id)."</i> in data: <b>".date("d/m/Y H:i:s")."</b>");
		echo "success";
	} else {
		echo "failure";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}