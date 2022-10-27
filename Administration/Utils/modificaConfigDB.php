<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaConfig")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_POST["configmodificato"])) {
		$configmodificato = $_POST["configmodificato"];
		if ($configmodificato == "") {
			echo "file_empty";
		} else {
			$file = @fopen("../../Config/config.php", "r");
			if ($file) {
				$data = date("Y-m-d H-i-s");
				$nuovonome = "../../Config/" . $folderconfigbackup . "/config_" . $data . ".php";
				rename("../../Config/config.php", $nuovonome);
				file_put_contents("../../Config/config.php", $configmodificato);
				echo "success";
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
