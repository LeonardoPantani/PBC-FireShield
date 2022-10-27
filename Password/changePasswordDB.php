<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

if (!isLogged()) {
	echo "invalid_session";
	exit;
}

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();
// -----------------------------

if (isset($_POST["passwordvecchia"]) && isset($_POST["passwordnuova1"]) && isset($_POST["passwordnuova2"])) {
	if(!empty($_POST["passwordvecchia"]) && !empty($_POST["passwordnuova1"]) && !empty($_POST["passwordnuova2"])) {
		if($_POST["passwordnuova1"] === $_POST["passwordnuova2"]) {
			$stmt = $connection -> prepare("SELECT Password FROM $table_users WHERE ID = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();
			$nrighe = $result -> num_rows;
			if($nrighe > 0) {
				$riga = $result -> fetch_assoc();
				if(password_verify($_POST["passwordvecchia"], $riga["Password"])) {
					if(!password_verify($_POST["passwordvecchia"], $_POST["passwordnuova1"])) {
						$passwordhash = password_hash($_POST["passwordnuova1"], PASSWORD_DEFAULT);
						
						$stmt = $connection -> prepare("UPDATE $table_users SET Password = ? WHERE ID = ?");
						$stmt -> bind_param("si", $passwordhash, $id);
						$esito = $stmt -> execute();
						if($esito) { // password aggiornata
							preparazioneMail($titolomail, $testomail);
							sendMail("info", "FireShield", $email, $titolomail, $testomail);
							
							echo "success";
						} else { // non è stata aggiornata la password
							echo "failed";
						}
					} else { // la password vecchia e la nuova sono uguali
						echo "same_password";
					}
				} else { // la password vecchia inserita e quella attuale vecchia nel db non coincidono
					echo "different_2";
				}
			} else { // non è stato trovato l'id per la verifica vecchia password
				echo "invalid_id";
			}
		} else { // le due password nuove non corrispondono
			echo "different_1";
		}
	} else { // non tutti i campi sono pieni
		echo "empty";
	}
} else { // non sono settati tutti i post
	echo "unset";
	exit;
}

function preparazioneMail(&$titolomail, &$testomail)
{
    $titolomail = _("Notifica di Cambio Password") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai cambiato la tua password.") . " <b>" . _("Sei stato tu, giusto?") . "</b>
		<br><br>
		" . sprintf(_("Se non hai effettuato questa modifica, %sreimposta la tua password%s adesso."), "<a href='https://www.fireshield.it/Password/passwordRecovery.php'>", "</a>") . "
		<br>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}
