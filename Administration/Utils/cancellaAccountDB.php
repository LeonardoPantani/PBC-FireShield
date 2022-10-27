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

if (!getPermission($id, "Account_Delete")) {
	echo "no_permission";
    exit;
}

if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
    if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
        if (isset($_POST["id_utente"]) && isset($_POST["cancella_dati_pagamento"])) {
			$id_utente = $_POST["id_utente"];

			// --- CONTROLLO SE L'ID ESISTE
			$stmt = $connection->prepare("SELECT ID, Tipo FROM $table_users WHERE ID = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			$esito = $stmt->get_result();
			$nrighe = $esito->num_rows;
			if($nrighe == 0) {
				echo "invalid_id";
				exit;
			}
			$riga = $esito->fetch_assoc();
			if($riga["Tipo"] == 1) {
				echo "failure_admin";
				exit;
			}
			
			$username_utente = getUsernameFromID($id_utente);
			
			// --- RIMOZIONE LICENZA
			$stmt = $connection -> prepare("DELETE FROM $table_licenses WHERE IDCliente = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			
			// --- RIMOZIONE PAGAMENTI (SE OPZIONE E' STATA SPUNTATA)
			if($_POST["cancella_dati_pagamento"] == "true") {
				$stmt = $connection -> prepare("DELETE FROM $table_payments WHERE IDUtente = ?");
				$stmt->bind_param("i", $id_utente);
				$stmt->execute();
			}
			
			// --- RIMOZIONE PREFERENZE
			$stmt = $connection -> prepare("DELETE FROM $table_preferences WHERE IDUtente = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			
			// --- RIMOZIONE PROPOSTE STRINGHE
			$stmt = $connection -> prepare("DELETE FROM $table_suggestions WHERE IDUtente = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			
			// --- RIMOZIONE TICKETS
			$stmt = $connection -> prepare("DELETE FROM $table_tickets WHERE IDUtente = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			
			// --- RIMOZIONE SESSIONI OTP
			$stmt = $connection -> prepare("DELETE FROM $table_otpsessions WHERE IDUtente = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			
			// --- RIMOZIONE UTENTE
			$stmt = $connection -> prepare("DELETE FROM $table_users WHERE ID = ?");
			$stmt->bind_param("i", $id_utente);
			$stmt->execute();
			
			// SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE
			$dataoggi = date("Y-m-d H:i:s");
			if (!fopen("../Logs/Log_Cancellazioni.txt", "r")) {
				file_put_contents("../Logs/Log_Cancellazioni.txt", "INIZIO LOG_CANCELLAZIONI:\n\n");
			}
			
			file_put_contents("../Logs/Log_Cancellazioni.txt", "\n[" . $dataoggi . "] L'ADMIN " . $username . " (ID: " . $id . ") ha eliminato l'account di " . $username_utente . " (ID: " . $id_utente . ")", FILE_APPEND);
			// FINE SALVATAGGIO MODIFICHE DELL'AMMINISTRATORE
			
			echo "success";
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
		<br>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
		</div>
	</body>
	";
}
