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

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	$data_attuale = date("Y-m-d H:i");
	
	$stmt = $connection -> prepare("DELETE FROM $table_licenses WHERE IDCliente = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	
	$stmt = $connection -> prepare("DELETE FROM $table_preferences WHERE IDUtente = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	
	$stmt = $connection -> prepare("DELETE FROM $table_suggestions WHERE IDUtente = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	
	$stmt = $connection -> prepare("DELETE FROM $table_tickets WHERE IDUtente = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	
	$stmt = $connection -> prepare("DELETE FROM $table_otpsessions WHERE IDUtente = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	
	$stmt = $connection -> prepare("DELETE FROM $table_users WHERE ID = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	echo "success";
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}

function preparazioneMail(&$titolomail, &$testomail, $username_eliminato)
{
    $titolomail = _("Avviso rimozione account") . " - FireShield";
    $testomail = "
	<body align='center'>
		<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		<h1>FireShield Cheat Detector</h1>
		" . _("Il tuo account")." (".$username_eliminato.")"._("è stato rimosso dalla piattaforma come da te richiesto").".
		<br><br>
		<hr>
		<div style='text-align:left;'>
		<h5>" . _("Data cancellazione account") . ":</h5>
		<p>
		" . $GLOBALS['data_attuale'] . "
		</p>
		</div>
		<br><br>
		<p style='text-align:right;'>" . _("Il team del")." ".$solutionname."</p>
	</body>
	";
}
?>