<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

if (!getPermission($id, "Account_Password")) {
	header("Location:pannello.php");
	exit;
}

if (isset($_GET["id"], $_GET["value"])) {
    $stmt = $connection->prepare("UPDATE $table_users SET AggiornaPassword = ? WHERE ID = ?");
    $stmt->bind_param("ii", $_GET["value"], $_GET["id"]);
    $esito = $stmt->execute();
    header("Location:../listaUtenti.php");
    exit;
}
