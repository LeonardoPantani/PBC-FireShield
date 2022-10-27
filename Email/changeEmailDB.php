<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();
// -----------------------------

if (isset($_POST["emailnuova"])) {
    $emailnuova = $_POST["emailnuova"];
    if (!empty($emailnuova)) {
        $stmt = $connection->prepare("SELECT Email FROM $table_users WHERE Email = ?");
        $stmt->bind_param("s", $emailnuova);
        $stmt->execute();
        $esito = $stmt->get_result();
        $nrighe = $esito->num_rows;
        if ($nrighe == 0) {
			$codicegenerato = bin2hex(random_bytes(25)); //genero il token
			$stmt = $connection->prepare("UPDATE $table_users SET Token = ? WHERE ID = ?");
			$stmt->bind_param("ss", $codicegenerato, $id);
			$esito_inserimento = $stmt->execute();
			if ($esito_inserimento) {
				preparazioneMail1($titolomail, $testomail);

				$esito_invio = sendMail("info", "FireShield", $emailnuova, $titolomail, $testomail);
				if ($esito_invio) {
					echo "success";
				} else {
					echo "failure";
				}
				exit;
			} else {
				echo "failure_token";
			}
		} else {
			echo "already_exists";
		}
	} else {
		echo "unset";
	}
} else {
	echo "unset";
}

function preparazioneMail1(&$titolomail, &$testomail)
{
    $titolomail = _("Cambio indirizzo Email") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai richiesto un <b>Cambio indirizzo Email</b>.") . "
		<br>
		<a href='https://www.fireshield.it/Email/confirmEmail.php?token=" . $GLOBALS["codicegenerato"] . "&email=" . $GLOBALS["emailnuova"] . "'>" . _("Clicca qui per confermare questo indirizzo Email") . "</a>
		<br>
		<hr>
		<p>" . _("Se non sei stato tu a richiedere questa operazione, puoi ignorare questa email.") . "</p>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}

function preparazioneMail2(&$titolomail, &$testomail)
{
    $titolomail = _("Cambio Email") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai cambiato l'indirizzo email assegnato al tuo account.") . " <b>" . _("Sei stato tu, giusto?") . "</b>
		<br><br>
		" . sprintf(_("Se non hai effettuato questa modifica, %sreimposta la tua password e indirizzo email%s adesso."), "<a href='https://www.fireshield.it/settings.php'>", "</a>") . "
		<br>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}