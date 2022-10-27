<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if (!isLogged()) {
	echo "invalid_session";
	exit;
}

readUserData($id, $email, $username, $tipo, $network, $otp);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if(isset($_POST["password"])) {
		$stmt = $connection -> prepare("SELECT Password FROM $table_users WHERE ID = ?");
		$stmt -> bind_param("i", $id);
		$stmt -> execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		
		if ($nrighe > 0) {
			$riga = $esito->fetch_assoc();
			$esitopassword = password_verify($_POST["password"], $riga["Password"]);
			if($esitopassword) {
				echo "success";
			} else {
				echo "failure";
			}
		} else {
			echo "id_not_found";
		}
	} else {
		echo "unset";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>