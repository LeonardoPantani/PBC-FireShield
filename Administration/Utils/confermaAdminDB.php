<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	// ----------- CONTROLLO TOKEN DI SICUREZZA
	if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
		if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
		
			// ----------- OTTENIMENTO DATI UTENTE
			$stmt = $connection->prepare("SELECT * FROM $table_users WHERE Username = ?");
			$stmt->bind_param("s", $username);
			$stmt->execute();
			$esito = $stmt->get_result();
			$datiUtente = $esito->fetch_assoc();
			
			// ----------- CONTROLLO 2FA
			require_once '../../2FACode/GoogleAuthenticator.php';
			$ga = new PHPGangsta_GoogleAuthenticator();
			$secretcode = str_replace(' ', '', $_POST["secretcode"]);
			$esito2fa = $ga->verifyCode($datiUtente["Secret"], $secretcode, 3);
			if ($esito2fa) {
				$_SESSION["ConfermaAdmin"] = 1;
				echo "success";
			} else {
				echo "failure"; // Errore: Il codice OTP non è valido
			}
		} else {
			echo "token_error"; // Errore: Il token di sicurezza non corrisponde
		}
	} else {
		echo "unset"; // Errore: Non tutti i parametri sono impostati (CSRF TOKEN)
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
exit;