<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

kickLoggedUser();
// -----------------------------

if (isset($_POST["username"])) {
    $username = $_POST["username"];
    if (!empty($username) && $username != "AdminFireShield") {
		$recaptcharesponse = $_POST["g-recaptcha-response"];
		$verifica = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret2 . "&response=" . $recaptcharesponse);
		$esitoRecaptcha = json_decode($verifica);
		if ($esitoRecaptcha->success == false) {
			header("Location:passwordRecovery.php?e=7");
			exit;
		}
		
        $stmt = $connection->prepare("SELECT ID, Email FROM $table_users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $esito = $stmt->get_result();
        $nrighe = $esito->num_rows;
        if ($nrighe > 0) {
            $riga = $esito->fetch_assoc();
			if($riga["Email"] != "") {
				$codicegenerato = bin2hex(random_bytes(25)); //genero il token
				$stmt = $connection->prepare("UPDATE $table_users SET Token = ? WHERE ID = ?");
				$stmt->bind_param("ss", $codicegenerato, $riga["ID"]);
				$esito_inserimento = $stmt->execute();
				if ($esito_inserimento) {
					preparazioneMail($titolomail, $testomail);

					$esito_invio = sendMail("info", "FireShield", $riga["Email"], $titolomail, $testomail);
					if ($esito_invio) {
						header("Location:passwordRecovery.php?e=8"); // la mail è stata mandata
					} else {
						header("Location:passwordRecovery.php?e=6"); // la mail non è stata mandata
					}
					exit;
				} else {
					header("Location:passwordRecovery.php?e=5"); // l'inserimento del token è fallito
					exit;
				}
			} else {
				header("Location:passwordRecovery.php?e=4"); // la mail non è nel database
				exit;
			}
        } else {
            header("Location:passwordRecovery.php?e=3"); // righe select vuote
            exit;
        }
    } else {
        header("Location:passwordRecovery.php?e=2"); //se l'username è vuoto
        exit;
    }
} else {
    header("Location:passwordRecovery.php?e=1"); // post non ricevuto 
    exit;
}

function preparazioneMail(&$titolomail, &$testomail)
{
    $titolomail = _("Richiesta di Recupero Password") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai richiesto un <b>Recupero Password</b>.") . "
		<br>
		<a href='https://www.fireshield.it/Password/reset.php?token=" . $GLOBALS["codicegenerato"] . "'>" . _("Clicca qui per recuperare la Password") . "</a>
		<br>
		<hr>
		<p>" . _("Se non sei stato tu a richiedere questa operazione, puoi ignorare questa email.") . "</p>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}