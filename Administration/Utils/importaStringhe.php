<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ImportazioneStringhe")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["tipoimport"]) && isset($_POST["tabella"])) {
		if(is_uploaded_file($_FILES['fileimport']['tmp_name'])) {
			if ($_POST["tipoimport"] == 1) { // SVUOTA ANCHE IL CONTENUTO DELLA TABELLA PRIMA
				$stmt = $connection->prepare("TRUNCATE TABLE ".$_POST["tabella"]);
				$esito = $stmt->execute();
				if(!$esito) {
					echo "error_truncate";
				}
			}
			
			$contenuto = file_get_contents($_FILES['fileimport']['tmp_name']);
			$array = explode("\n", $contenuto);
			foreach($array as $valore) {
				$stringa = explode("--CLIENT_DIV--", $valore);
				$stmt = $connection->prepare("INSERT INTO ".$_POST["tabella"]." (Stringa, Client) VALUES (?, ?)");
				$stmt->bind_param("ss", $stringa[0], $stringa[1]);
				$stmt->execute();
			}
			
			$stmt = $connection -> prepare("DELETE FROM ".$_POST["tabella"]." WHERE Stringa = ' '");
			$stmt->execute();
			
			$stmt = $connection->prepare("ALTER TABLE ".$_POST["tabella"]." AUTO_INCREMENT = 1");
			$stmt->execute();
			echo "success";
		} else {
			echo "unset_file";
		}
    } else {
        echo "unset";
        exit;
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>