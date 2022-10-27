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
    if (isset($_POST["stringa"]) && isset($_POST["tabella"]) && isset($_POST["nomeclient"])) {
        $stringa = $_POST["stringa"];
		$client = $_POST["nomeclient"];
        if (empty($stringa)) {
            echo "unset";
        } else {
            $stmt = $connection->prepare("SELECT Stringa FROM " . $_POST["tabella"] . " WHERE Stringa = ?");
            $stmt->bind_param("s", $stringa);
            $stmt->execute();
            $esito = $stmt->get_result();
            $nrighe = $esito->num_rows;
            if ($nrighe > 0) {
                echo "alreadyset";
            } else {
                $stmt = $connection->prepare("INSERT INTO " . $_POST["tabella"] . " (Stringa, Client) VALUES (?, ?)");
                $stmt->bind_param("ss", $stringa, $client);
                $esito = $stmt->execute();
                if ($esito) {
					sendTelegramMessage("✅ <b>NUOVA STRINGA</b> ✅\n__________________________________\n\nStringa: <b>".$_POST["stringa"]."</b>\n\nClient: <b>".$_POST["nomeclient"]."</b>\n\nTabella: <b>".$_POST["tabella"]."</b>\n\nStringhe nella tabella: <b>".getStringNumber($_POST["tabella"])."</b>\n\nAggiunta da <i>".getUsernameFromID($id)."</i> in data: <b>".date("d/m/Y H:i:s")."</b>");
                    echo "success";
                } else {
                    echo "failure";
                }
            }
        }
    } else {
        echo "unset";
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>