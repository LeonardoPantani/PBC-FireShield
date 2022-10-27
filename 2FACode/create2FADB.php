<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();
// -----------------------------

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["secret"], $_POST["secret_generated"])) {
        $stmt = $connection->prepare("SELECT * FROM Users WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $esito = $stmt->get_result();
        $nrighe = $esito->num_rows;

        if ($nrighe == 0) {
            echo "noid";
        } else {
			$_POST["secret"] = str_replace(" ", "", $_POST["secret"]);
            require_once 'GoogleAuthenticator.php';
            $ga = new PHPGangsta_GoogleAuthenticator();
            $esito2fa = $ga->verifyCode($_POST["secret_generated"], $_POST["secret"], 3);
            if ($esito2fa) {
                $stmt = $connection->prepare("UPDATE $table_users SET Secret = ? WHERE ID = ?");
                $stmt->bind_param("si", $_POST["secret_generated"], $id);
                $esito = $stmt->execute();
                if (!$esito) {
                    echo "failed_2";
                } else {
					if (generate2FARecoveryCodes($id)) {
						echo "success";
					} else {
						echo "failed_3";
					}
                }
            } else {
                echo "failed";
            }
        }
    } else {
        echo "unset";
    }
    exit;
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>