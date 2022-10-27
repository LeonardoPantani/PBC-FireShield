<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

adminCheck();

networkInformation($infonetwork);

if (!getPermission($id, "EditApplication")) {
	header("Location:../pannello.php");
	exit;
}

if (isset($_POST["idcandidatura"]) && isset($_POST["statocandidatura"])) {
    $idcandidatura = $_POST["idcandidatura"];
    $statocandidatura = $_POST["statocandidatura"];
} else {
    header("Location:../listaCandidature.php?e=1");
    exit;
}
$stmt = $connection->prepare("SELECT Nome, Cognome, Email, Lingua, Token FROM $table_applications WHERE IDCandidatura = ?");
$stmt->bind_param("i", $idcandidatura);
$stmt->execute();
$esito = $stmt->get_result();
$result = $esito->fetch_assoc();
$nome = $result["Nome"];
$cognome = $result["Cognome"];
$token = $result["Token"];

if($statocandidatura == 0) {
	$statocandidaturatesto = "Waiting";
} elseif($statocandidatura == 1) {
	$statocandidaturatesto = "Accepted ✅";
} elseif($statocandidatura == 2) {
	$statocandidaturatesto = "Rejected ❌";
} else {
	$statocandidaturatesto = "Unknown";
}

$dataaggiornamento = date("Y-m-d H:i:s");

$stmt = $connection->prepare("UPDATE $table_applications SET Stato = ?, DataAggiornamento = ? WHERE IDCandidatura = ?");
$stmt->bind_param("isi", $statocandidatura, $dataaggiornamento, $idcandidatura);
$esito = $stmt->execute();
if ($esito) {
	telegramMSG();
	
	preparazioneMail($titolomail, $testomail);
	sendMail("info", "FireShield", $result["Email"], $titolomail, $testomail);
	
    header("Location:../listaCandidature.php");
} else {
    header("Location:../listaCandidature.php?e=2");
}
exit;

function preparazioneMail(&$titolomail, &$testomail)
{
	setlocale(LC_MESSAGES, $GLOBALS["lingua"]);
	bindtextdomain("rispondiCandidaturaDB", "../../Translations");
	bind_textdomain_codeset("rispondiCandidaturaDB", 'UTF-8');
	textdomain("rispondiCandidaturaDB");
	
    $titolomail = _("Aggiornamento sulla tua candidatura") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché la tua candidatura è stata valutata!") . "
		<br><br>
		<a href='https://www.fireshield.it/application.php?t=". $GLOBALS["token"] . "'>" . _("Clicca qui per visualizzarla") . "</a>
		<br>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}

function telegramMSG()
{
	sendTelegramMessage("
	📄 <b>STAFF APPLICATION CLOSED</b> 📄
	______________________________________

	Candidate: <b>".$GLOBALS["nome"]." ".$GLOBALS["cognome"]."</b>
	Application ID: <b>".$GLOBALS["idcandidatura"]."</b>
	Application status: <b>".$GLOBALS["statocandidaturatesto"]."</b>
	
	Staff member: <b>".$GLOBALS["username"]."</b>
	
	<a href='https://www.fireshield.it/Administration/visualizzaCandidatura.php?id=".$GLOBALS["idcandidatura"]."'>View application</a>

	Date: <b>".date("d/m/Y H:i:s")."</b>", -339570880);
}