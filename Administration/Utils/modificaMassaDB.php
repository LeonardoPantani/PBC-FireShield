<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "Account_License")) {
    echo "no_permission";
    exit;
}


if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
    if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
        if (isset($_POST["segno"]) && isset($_POST["giorni"]) && isset($_POST["modificaLicenzeScadute"])) {
			if(!is_numeric($_POST["giorni"])) {
				echo "not_numeric";
				exit;
			}
            $segno = $_POST["segno"];
            $giorni = $_POST["giorni"];
            $modificalicenzescadute = $_POST["modificalicenzescadute"];

            // SEGNO +
			if($segno == "+") {
				if($modificalicenzescadute) {
					$stmt = $connection->prepare("UPDATE $table_licenses SET Scadenza = DATE_ADD(Scadenza, INTERVAL +? DAY) WHERE Scadenza != '' AND DurataLicenza = 'monthly'");
					$stmt->bind_param("i", $giorni);
					$esito = $stmt->execute();
					$modificalicenzescadute = "(Licenze scadute modificate)";
				} else {
					$stmt = $connection->prepare("UPDATE $table_licenses SET Scadenza = DATE_ADD(Scadenza, INTERVAL +? DAY) WHERE Scadenza != '' AND DurataLicenza = 'monthly' AND Scadenza > CURDATE()");
					$stmt->bind_param("i", $giorni);
					$esito = $stmt->execute();
					$modificalicenzescadute = "(Licenze scadute NON modificate)";
				}
			} elseif($segno == "-") {
				if($modificalicenzescadute) {
					$stmt = $connection->prepare("UPDATE $table_licenses SET Scadenza = DATE_ADD(Scadenza, INTERVAL -? DAY) WHERE Scadenza != '' AND DurataLicenza = 'monthly'");
					$stmt->bind_param("i", $giorni);
					$esito = $stmt->execute();
					$modificalicenzescadute = "(Licenze scadute modificate)";
				} else {
					$stmt = $connection->prepare("UPDATE $table_licenses SET Scadenza = DATE_ADD(Scadenza, INTERVAL -? DAY) WHERE Scadenza != '' AND DurataLicenza = 'monthly' AND Scadenza > CURDATE()");
					$stmt->bind_param("i", $giorni);
					$esito = $stmt->execute();
					$modificalicenzescadute = "(Licenze scadute NON modificate)";
				}
			} else {
				echo "sign_error";
				exit;
			}

			$stmt = $connection->prepare("SELECT ROW_COUNT() AS RigheModificate");
			$stmt->execute();
			$esito = $stmt->get_result();
			$risultato = $esito->fetch_assoc();
			$righemodificate = $risultato["RigheModificate"];

            if ($esito) {
                // SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE
                $dataoggi = date("Y-m-d H:i:s");
                if (!fopen("../Logs/Log_Licenze.txt", "r")) {
                    file_put_contents("../Logs/Log_Licenze.txt", "INIZIO LOG_LICENZE:\n\n");
                }
                file_put_contents("../Logs/Log_Licenze.txt", "\n[".$dataoggi."] L'ADMIN " . $username . " (ID: " . $id . ") ha modificato ".$righemodificate." licenze/a in massa effettuando la seguente modifica a tutte le licenze: ".$segno." ".$giorni." giorni/o ".$modificalicenzescadute."\n", FILE_APPEND);
                // FINE SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE

                echo "success";
            } else {
                echo "failure";
            }
        } else {
            echo "unset";
        }
    } else {
        echo "token_error";
    }
} else {
    echo "unset";
}