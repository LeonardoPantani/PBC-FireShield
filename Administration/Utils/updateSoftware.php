<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
		$cartella = "../../Files/Software/";
		$nomefile = basename($_FILES["file"]["name"]);
		$nome_completo = $cartella . "FireShield Cheat Detector.rar";
		$estensione = strtolower(pathinfo($nomefile,PATHINFO_EXTENSION));

		if($estensione != "rar") {
			echo "error_extension";
			exit;
		}
		
		if (move_uploaded_file($_FILES["file"]["tmp_name"], $nome_completo)) {
			echo "success";
		} else {
			echo "error_generic";
		}
	} else {
		echo "error_upload_fail";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>