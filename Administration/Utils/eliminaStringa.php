<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaStringhe")) {
    echo "no_permission";
    exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["stringa"]) && isset($_POST["tabella"])) {
        $stringa = $_POST["stringa"];
        if (empty($stringa)) {
            echo "unset";
        } else {
            $stmt = $connection->prepare("DELETE FROM " . $_POST["tabella"] . " WHERE Stringa = ?");
            $stmt->bind_param("s", $stringa);
            $stmt->execute();
			$righerimosse = $stmt->affected_rows;
			
            if ($righerimosse > 0) {
				$stmt = $connection->prepare("DELETE FROM $table_historycheat WHERE Stringa = ?");
				$stmt->bind_param("s", $stringa);
				$stmt->execute();
				
				sendTelegramMessage("❌ <b>STRINGA RIMOSSA</b> ❌\n__________________________________\n\nStringa: <b>".$_POST["stringa"]."</b>\n\nTabella: <b>".$_POST["tabella"]."</b>\n\nStringhe nella tabella: <b>".getStringNumber($_POST["tabella"])."</b>\n\nRimossa da <i>".getUsernameFromID($id)."</i> in data: <b>".date("d/m/Y H:i:s")."</b>");
				echo "success";
            } else {
                echo "notfound";
			}
        }
    } else {
        echo "unset";
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>