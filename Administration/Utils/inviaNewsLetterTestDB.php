<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaNewsLetter")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["titolo_italiano"]) && isset($_POST["titolo_inglese"]) && isset($_POST["titolo_francese"]) && isset($_POST["testo_italiano"]) && isset($_POST["testo_inglese"]) && isset($_POST["testo_francese"]) && isset($_POST["mail_test"])) {
		$titolo_italiano = $_POST["titolo_italiano"];
		$titolo_inglese = $_POST["titolo_inglese"];
		$titolo_francese = $_POST["titolo_francese"];
		$testo_italiano = nl2br($_POST["testo_italiano"]);
		$testo_inglese = nl2br($_POST["testo_inglese"]);
		$testo_francese = nl2br($_POST["testo_francese"]);
		$mail_test = $_POST["mail_test"];
        if (empty($titolo_italiano) || empty($titolo_inglese) || empty($titolo_francese) || empty($testo_italiano) || empty($testo_inglese) || empty($testo_francese) || empty($mail_test)) {
            echo "unset";
        } else {
			$username_utente = getUsernameFromEmail($mail_test);
			preparazioneMail($titolomail, $testomail, "it_IT", $mail_test);
			sendMail("info", "FireShield", $mail_test, str_replace("[username]", $username_utente, $titolomail), str_replace("[username]", $username_utente, $testomail), array("List-Unsubscribe" => "<".$issuesmail.">, <https://fireshield.it/unsubscribe.php?m=".$mail_test.">"));
			
			preparazioneMail($titolomail, $testomail, "en_US", $mail_test);
			sendMail("info", "FireShield", $mail_test, str_replace("[username]", $username_utente, $titolomail), str_replace("[username]", $username_utente, $testomail), array("List-Unsubscribe" => "<".$issuesmail.">, <https://fireshield.it/unsubscribe.php?m=".$mail_test.">"));
			
			preparazioneMail($titolomail, $testomail, "fr_FR", $mail_test);
			sendMail("info", "FireShield", $mail_test, str_replace("[username]", $username_utente, $titolomail), str_replace("[username]", $username_utente, $testomail), array("List-Unsubscribe" => "<".$issuesmail.">, <https://fireshield.it/unsubscribe.php?m=".$mail_test.">"));
			echo "success";
			exit;
        }
    } else {
        echo "unset";
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}

function preparazioneMail(&$titolomail, &$testomail, $lingua, $mail)
{
	if($lingua == "it_IT") {
		$titolo = $GLOBALS["titolo_italiano"];
		$testo = $GLOBALS["testo_italiano"];
	} elseif($lingua == "fr_FR") {
		$titolo = $GLOBALS["titolo_francese"];
		$testo = $GLOBALS["testo_francese"];
	} else {
		$titolo = $GLOBALS["titolo_inglese"];
		$testo = $GLOBALS["testo_inglese"];	
	}
	
	
	if($lingua != "it_IT" && $lingua != "fr_FR" && $lingua != "en_US") { // Per evitare errori dal database
		$lingua = "en_US";
	}
	setlocale(LC_MESSAGES, $lingua);
	bindtextdomain("inviaNewsLetterDB", "../../Translations");
	bind_textdomain_codeset("inviaNewsLetterDB", 'UTF-8');
	textdomain("inviaNewsLetterDB");
	
    $titolomail = $titolo . " - " . $GLOBALS['solutionname'];
    $testomail = "
	<body>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		".$testo."
		<br><br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
		<br><br><br>
		<hr>
		<small>"._("Se non desideri più ricevere questi aggiornamenti, puoi annullare l'iscrizione")." <a href='https://fireshield.it/unsubscribe.php?m=".$mail."'>"._("qui")."</a>.</small>
	</body>
	";
}

?>