<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["idscelto"])) {
        $idscelto = $_POST["idscelto"];
        if (empty($idscelto)) {
            echo "unset";
        } else {
            $stmt = $connection->prepare("SELECT $table_users.ID FROM $table_users WHERE $table_users.ID = ?");
            $stmt->bind_param("i", $idscelto);
			$stmt->execute();
            $esito = $stmt->get_result();
            $nrighe = $esito->num_rows;
			if($nrighe > 0) {
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