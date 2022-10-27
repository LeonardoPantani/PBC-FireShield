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

if ($stringsuggestions === true) {
    if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
        if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
            if (isset($_POST["tipostringa"], $_POST["stringa"], $_POST["client"])) {
				if($_POST["tipostringa"] == "javaw" || $_POST["tipostringa"] == "dwm" || $_POST["tipostringa"] == "msmpeng" || $_POST["tipostringa"] == "lsass" || $_POST["tipostringa"] == "other") {
					$tipostringa = $_POST["tipostringa"];
					$stringa = $_POST["stringa"];
					$client = $_POST["client"];
					// pulizia input
					$note = filter_var(htmlspecialchars($_POST["note"]), FILTER_SANITIZE_STRING);

					$data = new DateTime("now");
					$data = $data->format("Y-m-d H:i:s");

					$stmt = $connection->prepare("INSERT INTO $table_suggestions (IDUtente, Data, TipoStringa, Stringa, Client, Note, Stato) VALUES (?, ?, ?, ?, ?, ?, 0)");
					$stmt->bind_param("isssss", $id, $data, $tipostringa, $stringa, $client, $note);
					$esito = $stmt->execute();
					if ($esito) {
						header("Location:suggestString.php?e=7"); // OK
						exit;
					} else {
						header("Location:suggestString.php?e=6");
						exit;
					}
				} else {
					header("Location:suggestString.php?e=5");
				}
            } else {
                header("Location:suggestString.php?e=4");
            }
        } else {
            header("Location:suggestString.php?e=3");
        }
    } else {
        header("Location:suggestString.php?e=2");
    }
} else {
    header("Location:suggestString.php?e=1");
}
exit;
