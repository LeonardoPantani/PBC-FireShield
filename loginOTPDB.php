<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
if (isLogged()) {
    header("Location:/");
    exit;
}

if ($loginotp) {

    // ----------- CONTROLLO TOKEN DI SICUREZZA
    if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
        if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
            if (isset($_POST["otp"])) {
                $otp = str_replace(' ', '', $_POST["otp"]);

                // ----------- OTTENIMENTO DATI UTENTE
                $stmt = $connection->prepare("SELECT IDUtente FROM $table_otpsessions WHERE CodiceOTP = ? AND StatoSessione = 1");
                $stmt->bind_param("s", $otp);
                $stmt->execute();
                $esito = $stmt->get_result();
                $datiOTP = $esito->fetch_assoc();
				$nrighe = $esito->num_rows;

                if ($nrighe === 0) {
                    header("Location:loginOTP.php?e=3");
                } else {
					$stmt = $connection->prepare("SELECT * FROM $table_users WHERE ID = ?");
					$stmt->bind_param("i", $datiOTP["IDUtente"]);
					$stmt->execute();
					$esito = $stmt->get_result();
					$datiUtente = $esito->fetch_assoc();

                    // ----------- VERIFICA SE L'ACCOUNT E' ABILITATO
                    if ($datiUtente["Abilitato"] === 0) { //se l'utente è stato creato da un admin ma non è abilitato dagli amministratori
                        header("Location:loginOTP.php?e=6"); // Errore: L'account è in attesa di approvazione (abilitato = 0)
                        exit;
                    }
					
					if (!hasTwoFactorAuthentication($datiOTP["IDUtente"])) { //se lo staffer non ha la autenticazione a 2 fattori
						header("Location:loginOTP.php?e=7"); // Errore: L'account principale non ha l'autenticazione a 2 fattori abilitata
						exit;
					}
					
                    $esito = setUserData($datiUtente["ID"], "[Protected]", $datiUtente["Username"], $datiUtente["Tipo"], 0, true);

                    // ----------- IMPOSTAZIONE DELLE PREFERENZE
                    if ($esito) {
                        session_write_close();
						session_name('__Secure-FSCDSESSION');
                        session_start();
						
						$data = date("Y-m-d H:i:s", strtotime("now"));
						$datatermine = date("Y-m-d H:i:s", strtotime("+".$otpsessionmaxduration." minutes"));

                        // ----------- ELIMINAZIONE OTP ATTUALE
                        $stmt = $connection -> prepare("UPDATE $table_otpsessions SET DataInizioSessione = ?, DataFineSessione = ?, StatoSessione = 2, ScansioniEseguite = 0 WHERE IDUtente = ?");
                        $stmt->bind_param("ssi", $data, $datatermine, $datiUtente["ID"]);
                        $stmt->execute();

                        $stmt = $connection->prepare("SELECT Lingua FROM $table_preferences WHERE IDUtente = ?");
                        $stmt->bind_param("i", $datiUtente["ID"]);
                        $stmt->execute();
                        $esito = $stmt->get_result();
                        $datiPreferenze = $esito->fetch_assoc();

                        $_SESSION["lang"] = $datiPreferenze["Lingua"];
                        $_SESSION["scansioni_effettuate"] = 0;
						$_SESSION["codiceotp"] = $_POST["otp"];

                        // ----------- REDIRECT DOPO LOGIN EFFETTUATO CON SUCCESSO
                        header("Location:/");
                    } else {
                        header("Location:loginOTP.php?e=5"); // Errore: La sessione non è stata impostata
                    }
                }
            } else {
                header("Location:loginOTP.php?e=2"); // Errore: Non tutti i parametri sono stati impostati (OTP)
            }
        } else {
            header("Location:loginOTP.php?e=4"); // Errore: Il token di sicurezza non corrisponde
        }
    } else {
        header("Location:loginOTP.php?e=2"); // Errore: Non tutti i parametri sono impostati (CSRF TOKEN)
    }
} else {
    header("Location:loginOTP.php?e=1"); // Errore: I login sono disabilitati
}
exit;