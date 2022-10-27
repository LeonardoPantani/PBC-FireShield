<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["nomenetworkscelto"])) {
        $nomenetworkscelto = $_POST["nomenetworkscelto"];
        if (empty($nomenetworkscelto)) {
            echo "unset";
        } else {
            $stmt = $connection->prepare("SELECT NomeNetwork FROM $table_networks WHERE NomeNetwork = ?");
            $stmt->bind_param("s", $nomenetworkscelto);
			$stmt->execute();
            $esito = $stmt->get_result();
            $nrighe = $esito->num_rows;
            if ($nrighe > 0) {
				$riga = $esito->fetch_assoc();
				echo "found";
            } else {
                echo "not_found";
            }
        }
    } else {
        echo "unset";
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>