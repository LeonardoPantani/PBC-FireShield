<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["emailscelta"])) {
        $email = $_POST["emailscelta"];
        if (empty($email)) {
            echo "unset";
        } else {
            $stmt = $connection->prepare("SELECT $table_users.Email FROM $table_users WHERE $table_users.Email = ?");
            $stmt->bind_param("s", $email);
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