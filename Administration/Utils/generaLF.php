<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

adminCheck();

networkInformation($infonetwork);

if (!getPermission($id, "Account_LF")) {
	echo "no_permission";
	exit;
}

if (isset($_POST["idutente"])) {
    $idutente = $_POST["idutente"];
} else {
    echo "unset";
    exit;
}

if(getPermission($idutente, "Account_LF") == 1) {
	echo "cant_login";
	exit;
}

$stringa_accesso = bin2hex(random_bytes(25));

$stmt = $connection->prepare("UPDATE $table_users SET LF = ? WHERE ID = ?");
$stmt->bind_param("si", $stringa_accesso, $idutente);
$esito = $stmt->execute();
if ($esito) {
    echo $stringa_accesso;
} else {
    echo "error_update";
}
exit;