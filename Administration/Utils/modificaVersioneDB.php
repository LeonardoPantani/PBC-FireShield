<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaSoftware")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_POST["versione1"]) && isset($_POST["versione2"]) && isset($_POST["versione3"])) {
		$versione1 = $_POST["versione1"];
		$versione2 = $_POST["versione2"];
		$versione3 = $_POST["versione3"];
		if ($versione1 == "" && $versione2 == "" && $versione3 == "") {
			echo "string_empty";
		} else {
			$file1 = @fopen("../../Files/Software/versioni.txt", "r");
			if ($file1) {
				file_put_contents("../../Files/Software/versioni.txt", $versione1);
			} else {
				echo "not_found";
				exit;
			}

			$file2 = @fopen("../../Files/Software/versionisoftware.txt", "r");
			if ($file2) {
				file_put_contents("../../Files/Software/versionisoftware.txt", $versione2);
			} else {
				echo "not_found";
				exit;
			}

			$file3 = @fopen("../../Files/Software/versionecorrentesoftware.txt", "r");
			if ($file3) {
				file_put_contents("../../Files/Software/versionecorrentesoftware.txt", $versione3);
			} else {
				echo "not_found";
				exit;
			}

			echo "success";
		}
	} else {
		echo "unset";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
