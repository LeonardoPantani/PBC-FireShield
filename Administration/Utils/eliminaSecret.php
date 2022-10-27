<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "Account_Secret")) {
	header("Location:pannello.php");
	exit;
}

if (isset($_GET["id"])) {
    $stmt = $connection->prepare("UPDATE $table_users SET Secret = NULL WHERE ID = ?");
    $stmt->bind_param("i", $_GET["id"]);
    $esito = $stmt->execute();
    header("Location:../listaUtenti.php");
    exit;
}
