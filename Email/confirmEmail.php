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

if (isset($_GET["token"]) && isset($_GET["email"])) {
    if(!empty($_GET["token"]) && !empty($_GET["email"])) {
		$stmt = $connection->prepare("SELECT Token, Email FROM $table_users WHERE Token = ?");
		$stmt->bind_param("s", $_GET["token"]);
		$stmt->execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		if($nrighe > 0) {
			$result = $esito->fetch_assoc();
			$vecchiaemail = $result["Email"];
			
			$stmt = $connection->prepare("SELECT Email FROM $table_users WHERE Email = ?");
			$stmt->bind_param("s", $_GET["email"]);
			$stmt->execute();
			$esito = $stmt->get_result();
			$nrighe = $esito->num_rows;
			if ($nrighe == 0) {
				$stmt = $connection->prepare("UPDATE $table_users SET Email = ?, Token = NULL WHERE Token = ?");
				$stmt->bind_param("ss", $_GET["email"], $_GET["token"]);
				$esito = $stmt->execute();
				if($esito) {
					preparazioneMail($titolomail, $testomail);
					sendMail("info", "FireShield", $vecchiaemail, $titolomail, $testomail);
					
					header("Location:../Utils/deleteSession.php?r=../Email/emailChangeResult.php?e=5");
					exit;
				} else {
					header("Location:emailChangeResult.php?e=4");
					exit;
				}
			} else {
				header("Location:emailChangeResult.php?e=3");
				exit;
			}
		} else {
			header("Location:emailChangeResult.php?e=2");
			exit;
		}
    } else {
        header("Location:emailChangeResult.php?e=1");
        exit;
    }
} else {
    header("Location:emailChangeResult.php?e=1");
    exit;
}

function preparazioneMail(&$titolomail, &$testomail)
{
    $titolomail = _("Notifica di Cambio Email") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché hai cambiato la casella di posta associata al tuo account.") . " <b>" . _("Sei stato tu, giusto?") . "</b>
		<br><br>
		" . sprintf(_("Se non hai effettuato questa modifica, %sreimposta la tua password e indirizzo email%s adesso."), "<a href='https://www.fireshield.it/settings.php'>", "</a>") . "
		<br>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}
