<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "Account_Secret")) {
	header("Location:pannello.php");
	exit;
}

if (isset($_GET["id"])) {
    $stmt = $connection->prepare("UPDATE $table_users SET Secret = NULL WHERE ID = ?");
    $stmt->bind_param("i", $_GET["id"]);
    $esito = $stmt->execute();
	sendTelegramMessage("🔑 <b>2FA RESET REQUEST ACCEPTED</b> 🔑\n__________________________________\n\nUser: <b>".getUsernameFromID($id)."</b>\nAccepted by: <b>".$username."</b>\n\nCompletion date: <b>".date("d/m/Y H:i:s")."</b>", -339570880);
	
	$stmt = $connection->prepare("SELECT Email FROM $table_users WHERE ID = ?");
	$stmt->bind_param("i", $_GET["id"]);
	$stmt->execute();
	$esito = $stmt->get_result();
	$result = $esito->fetch_assoc();

	$linguautente = getPreference($_GET["id"], "Lingua");
	preparazioneMail($titolomail, $testomail);
	$esito_invio = sendMail("info", "FireShield", $result["Email"], $titolomail, $testomail);
	if ($esito_invio) {
		echo "✔";
	} else {
		echo "❌";
	}
    
    exit;
}

function preparazioneMail(&$titolomail, &$testomail)
{
	setlocale(LC_MESSAGES, $GLOBALS["linguautente"]);
	bindtextdomain("resettaOTP", "../../Translations");
	bind_textdomain_codeset("resettaOTP", 'UTF-8');
	textdomain("resettaOTP");
	
    $titolomail = _("Richiesta di reset 2FA approvata!") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai richiesto un <b>Reset del codice 2FA</b>.") . "
		<br>
		" . _("Un membro dello staff ha approvato la tua richiesta di reset del codice 2FA") . ".
		<br>
		<hr>
		<br>
		" . sprintf(_("%sAccedi al tuo account e reimposta il codice 2FA%s"), "<a href='https://fireshield.it/login.php'>", "</a>") . "
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}