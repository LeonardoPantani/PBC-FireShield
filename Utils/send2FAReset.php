<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if(isset($_SESSION["2fareset"]) && $_SESSION["2fareset"] == 1) {
		echo "failure";
		exit;
	} else {
		preparazioneMail($titolomail, $testomail);

		sendMail("info", "FireShield", $email, $titolomail, $testomail);
		$_SESSION["2fareset"] = 1;
		sendTelegramMessage("🔑 <b>2FA RESET REQUEST</b> 🔑\n__________________________________\n\nUser: <b>".$username."</b>\n\n<a href='https://fireshield.it/Administration/Utils/resettaOTP.php?id=".$id."'>Reset OTP Code</a>\n\n<i>Check before if the request is valid.\nDO NOT click on the link if the request is not legitimate!</i>\n\nRequest date: <b>".date("d/m/Y H:i:s")."</b>", -339570880);
		echo "success";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}

function preparazioneMail(&$titolomail, &$testomail)
{
    $titolomail = _("Richiesta di reset 2FA inviata!") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai richiesto un <b>Reset del codice 2FA</b>.") . "
		<br>
		" . _("Ti aggiorneremo su questa casella di posta riguardo lo stato della tua richiesta, pertanto dai un'occhiata ogni tanto!") . "
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}
?>