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

if (!getPermission($id, "ListaStringhe")) {
	header("Location:pannello.php");
	exit;
}

if (isset($_POST["idproposta"]) && isset($_POST["stato"])) {
    $idproposta = $_POST["idproposta"];
    $stato = $_POST["stato"];
} else {
    header("Location:../listaProposte.php?e=1");
    exit;
}

$stmt = $connection->prepare("UPDATE $table_suggestions SET Stato = ?, IDStaffer = ? WHERE IDProposta = ?");
$stmt->bind_param("iii", $stato, $id, $idproposta);
$esito = $stmt->execute();
if ($esito) {
    header("Location:../listaProposte.php");
} else {
    header("Location:../listaProposte.php?e=2");
}
exit;