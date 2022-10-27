<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

adminCheck();

if (!getPermission($id, "Account_Create")) {
	echo "no_permission";
    exit;
}

if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
    if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]) && isset($_POST["tipoutente"]) && isset($_POST["abilitato"]) && isset($_POST["lingua"]) && isset($_POST["tipolicenza"]) && isset($_POST["duratalicenza"]) && isset($_POST["scadenzalicenza"]) && isset($_POST["inviamail"])) {
			if (validateString($_POST["username"])) {
				$username_utente = $_POST["username"];
                $password_utente = $_POST["password"];
                $email_utente = $_POST["email"];
				$tipo_utente = $_POST["tipoutente"];
				$abilitato = $_POST["abilitato"];
				$lingua = $_POST["lingua"];
				$tipolicenza = $_POST["tipolicenza"];
				$duratalicenza = $_POST["duratalicenza"];
				$scadenzalicenza = $_POST["scadenzalicenza"];

                $password_utente_hash = password_hash($password_utente, PASSWORD_DEFAULT); //hash password

				// --- CONTROLLO SE ESISTE GIA' UN USERNAME O MAIL UGUALE
                $stmt = $connection->prepare("SELECT Username FROM $table_users WHERE Username = ? OR Email = ?");
                $stmt->bind_param("ss", $username_utente, $email_utente);
                $stmt->execute();
                $esito = $stmt->get_result();
                $nrighe = $esito->num_rows;
				if($nrighe > 0) {
					echo "already_exists";
					exit;
				}
				
				if($tipo_utente != 0 && $tipo_utente != 2) {
					echo "invalid_user_type";
					exit;
				}
				
				if($abilitato != 0 && $abilitato != 1) {
					echo "invalid_user_status";
					exit;
				}
				
				// --- INSERISCO L'UTENTE
				$stmt = $connection->prepare("INSERT INTO $table_users (Email, Username, Password, Tipo, Abilitato) VALUES (?, ?, ?, ?, ?)");
				$stmt->bind_param("sssii", $email_utente, $username_utente, $password_utente_hash, $tipo_utente, $abilitato);
				$esito = $stmt->execute();
				$id_utente = $connection->insert_id;
				if(!$esito) {
					echo "failure";
					exit;
				}

				// --- INSERISCO LE PREFERENZE
				$stmt = $connection->prepare("INSERT INTO $table_preferences (IDUtente, Lingua) VALUES (?, ?)");
				$stmt->bind_param("is", $id_utente, $lingua);
				$esito = $stmt->execute();
				if(!$esito) {
					echo "failure";
					exit;
				}
				
				// --- INSERISCO IL CAMPO SESSIONE OTP
				$stmt = $connection->prepare("INSERT INTO $table_otpsessions (IDUtente, CodiceOTP, DataInizioSessione, DataFineSessione, StatoSessione, ScansioniEseguite) VALUES (?, '', NULL, NULL, 0, 0)");
				$stmt->bind_param("i", $id_utente);
				$esito = $stmt->execute();
				if(!$esito) {
					echo "failure";
					exit;
				}

				// --- INSERISCO LA LICENZA (SE PRESENTE - SE IL CAMPO TIPOLICENZA O CAMPO DURATALICENZA SONO VUOTI)
				if ($tipolicenza != -1 || $duratalicenza != -1) {
					if(($tipolicenza == 1 && $duratalicenza == 0) || ($scadenzalicenza == "" && $duratalicenza == 0)) {
						echo "license_assignment_error";
						exit;
					}
					// MODIFICA VALORI PER INSERIMENTO IN DB
					if ($tipolicenza == 0) {
						$tipolicenza = "single";
					} else {
						$tipolicenza = "network";
					}
					if ($duratalicenza == 0) {
						$duratalicenza = "monthly";
					} else {
						$duratalicenza = "permanent";
					}
					
					$stmt = $connection->prepare("INSERT INTO $table_licenses (DurataLicenza, TipoLicenza, Scadenza, IDCliente) VALUES (?, ?, ?, ?)");
					$stmt->bind_param("sssi", $duratalicenza, $tipolicenza, $scadenzalicenza, $id_utente);
					$esito = $stmt->execute();
					if(!$esito) {
						echo "failure_license";
						exit;
					}

					// SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE
					$dataoggi = date("Y-m-d H:i:s");
					if (!fopen("../Logs/Log_Registrazioni.txt", "r")) {
						file_put_contents("../Logs/Log_Registrazioni.txt", "INIZIO LOG_REGISTRAZIONI:\n\n");
					}
					if ($scadenza == "") {
						$scadenza = "NO SCADENZA";
					}
					file_put_contents("../Logs/Log_Registrazioni.txt", "\n[" . $dataoggi . "] L'ADMIN " . $username . " (ID: " . $id . ") ha creato un nuovo account. ID: " . $id_utente . " | Username: " . $username_utente . " | Email: " . $email_utente . ". LICENZA ASSEGNATA: " . $tipolicenza . " (" . $duratalicenza . " - " . $scadenza . ").\n", FILE_APPEND);
					// FINE SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE

					if($_POST["inviamail"] == "true") {
						signUpMail($titolomail, $testomail, $lingua);
						sendMail("info", "FireShield", $email_utente, $titolomail, $testomail);
					}
					echo "success";
					exit;
				} else {
					// SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE
					$dataoggi = date("Y-m-d H:i:s");
					if (!fopen("../Logs/Log_Registrazioni.txt", "r")) {
						file_put_contents("../Logs/Log_Registrazioni.txt", "INIZIO LOG_REGISTRAZIONI:\n\n");
					}
					file_put_contents("../Logs/Log_Registrazioni.txt", "\n[" . $dataoggi . "] L'ADMIN " . $username . " (ID: " . $id . ") ha creato un nuovo account. ID: " . $id_utente . " | Username: " . $username_utente . " | Email: " . $email_utente . ". NESSUNA LICENZA ASSEGNATA.\n", FILE_APPEND);
					// FINE SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE

					if($_POST["inviamail"] == "true") {
						signUpMail($titolomail, $testomail, $lingua);
						sendMail("info", "FireShield", $email_utente, $titolomail, $testomail);
					}
					echo "success";
					exit;
				}
            } else {
                echo "invalid_username";
				exit;
            }
        } else {
            echo "unset";
			exit;
        }
    } else {
        echo "csrf_invalid";
		exit;
    }
} else {
    echo "token_error";
}

function signUpMail(&$titolomail, &$testomail, $lingua)
{
	setlocale(LC_MESSAGES, $lingua);
	bindtextdomain("registrazioneDB", "../../Translations");
	bind_textdomain_codeset("registrazioneDB", 'UTF-8');
	textdomain("registrazioneDB");

	$titolomail = _("Account Creato")." - ".$GLOBALS["solutionname"];
	$testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		<div style='text-align: left;'>
		".sprintf(_("Benvenuto su <b>%s</b>! I nostri amministratori hanno creato un account per te. Ecco le tue credenziali di accesso"), $GLOBALS["solutionname_short"]).":
		<br>
		<br>
		<b>Username:</b> " . $GLOBALS['username_utente'] . "
		<br>
		<b>Password:</b> " . $GLOBALS['password_utente'] . " ("._("Puoi cambiarla dalle Impostazioni").")
		<br>
		<b>Email:</b> " . $GLOBALS['email_utente'] . "
		<br>
		<a href='https://www.fireshield.it/login.php?fl=1'>"._("LOG-IN")."</a>
		<br>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
		</div>
	</body>
	";
}
