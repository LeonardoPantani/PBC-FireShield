<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "FormattazioneStringhe")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (!isset($_POST["tabella"])) {
        echo "unset";
        exit;
    }
    if ($_POST["tabella"] == "Tutte") {
        $stmt = $connection->prepare("TRUNCATE $table_cheatjava");
        $stmt->execute();

        $stmt = $connection->prepare("TRUNCATE $table_cheatdwm");
        $stmt->execute();

        $stmt = $connection->prepare("TRUNCATE $table_cheatmsmpeng");
        $stmt->execute();

        $stmt = $connection->prepare("TRUNCATE $table_cheatlsass");
        $stmt->execute();

        $stmt = $connection->prepare("TRUNCATE $table_suspectjava");
        $stmt->execute();
		
        $stmt = $connection->prepare("TRUNCATE $table_suspectdwm");
        $stmt->execute();
		echo "success";
    } else {
        $stmt = $connection->prepare("TRUNCATE " . $_POST["tabella"]);
        $esito = $stmt->execute();
		if($esito) {
			echo "success";
		} else {
			echo "failure";
		}
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>