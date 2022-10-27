<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

kickLoggedUser();
// -----------------------------

if (isset($_POST["password1"]) && isset($_POST["password2"]) && isset($_POST["token"]) && $_POST["token"] != "") {
    if(($_POST["password1"] == $_POST["password2"]) && (!empty($_POST["password1"]) && !empty($_POST["password2"]))) {
        $passwordhash = password_hash($_POST["password1"], PASSWORD_DEFAULT);
        $stmt = $connection->prepare("UPDATE $table_users SET Password = ?, Token = NULL WHERE Token = ?");
        $stmt->bind_param("ss", $passwordhash, $_POST["token"]);
        $esito = $stmt->execute();
        if($esito) {
			preparazioneMail($titolomail, $testomail);
			sendMail("info", "FireShield", $email, $titolomail, $testomail);
			
            header("Location:../login.php"); // successo
            exit;
        } else {
            header("Location:reset.php?token=".$_POST["token"]."&e=3"); // errore update
            exit;
        }
    } else {
        header("Location:reset.php?token=".$_POST["token"]."&e=2"); // le password non corrispondono e non devono essere vuote
        exit;
    }
} else {
    header("Location:reset.php?token=".$_POST["token"]."&e=1"); // post non ricevuto 
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