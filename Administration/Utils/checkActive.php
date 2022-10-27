<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser();

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    $connection = new mysqli($host, $usernamedb, $password, $dbname);
    if (!$connection->connect_error) {
        $stmt = $connection->prepare("SELECT ID FROM $table_users WHERE Abilitato = 0");
        $esito = $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        echo $nrighe;
    }
    exit;
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>
