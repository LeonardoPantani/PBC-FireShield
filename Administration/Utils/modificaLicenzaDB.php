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
        if (isset($_POST["idutente"]) && isset($_POST["tipolicenza"]) && isset($_POST["duratalicenza"])) {
            $idutente = $_POST["idutente"];
            $tipolicenza = $_POST["tipolicenza"];
            $duratalicenza = $_POST["duratalicenza"];
            $scadenzalicenza = $_POST["scadenzalicenza"];

            if ($tipolicenza == 0) {
                $tipolicenza = "single";
            } elseif ($tipolicenza == 1) {
                $tipolicenza = "network";
            } else {
                $tipolicenza = "single"; // in caso di problemi
            }

            if ($duratalicenza == 0) {
                $duratalicenza = "monthly";
            } elseif ($duratalicenza == 1) {
                $duratalicenza = "permanent";
                $scadenzalicenza = NULL;
            } else {
                $duratalicenza = "monthly";
            }
			
            $stmt = $connection->prepare("SELECT * FROM $table_licenses, $table_users WHERE IDCliente = ?");
            $stmt->bind_param("i", $idutente);
            $stmt->execute();
            $esito = $stmt->get_result();
            $precedente = $esito->fetch_assoc();

            $stmt = $connection->prepare("UPDATE $table_licenses SET DurataLicenza = ?, TipoLicenza = ?, Scadenza = ? WHERE IDCliente = ?");
            $stmt->bind_param("sssi", $duratalicenza, $tipolicenza, $scadenzalicenza, $idutente);
            $esito = $stmt->execute();
            if ($esito) {
                // SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE
                $dataoggi = date("Y-m-d H:i:s");
                if (!fopen("../Logs/Log_Licenze.txt", "r")) {
                    file_put_contents("../Logs/Log_Licenze.txt", "INIZIO LOG_LICENZE:\n\n");
                }
				if($scadenzalicenza == "") {
					$scadenzalicenza = "NO SCADENZA";
				}
				if($precedente["Scadenza"] == "") {
					$precedente["Scadenza"] = "NO SCADENZA";
				}
                file_put_contents("../Logs/Log_Licenze.txt", "\n[".$dataoggi."] L'ADMIN " . $username . " (ID: " . $id . ") ha modificato la licenza di " . $precedente["Username"] . " (ID: " . $precedente["ID"] . "). Nuova licenza: " . $tipolicenza . " (" . $duratalicenza . " - " . $scadenzalicenza . "). Precedente: " . $precedente["TipoLicenza"] . " (" . $precedente["DurataLicenza"] . " - " . $precedente["Scadenza"] . ").\n", FILE_APPEND);
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
