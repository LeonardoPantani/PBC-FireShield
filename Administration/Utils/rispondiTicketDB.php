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

if (!getPermission($id, "ModificaTickets")) {
	header("Location:../pannello.php");
	exit;
}

if (isset($_POST["idticket"]) && isset($_POST["risposta"]) && isset($_POST["idutente"])) {
    $idticket = $_POST["idticket"];
    $risposta = $_POST["risposta"];
	$idutente = $_POST["idutente"];
} else {
    header("Location:../listaTickets.php?e=1");
    exit;
}

$stmt = $connection->prepare("SELECT Email FROM $table_users WHERE ID = ?");
$stmt->bind_param("i", $idutente);
$stmt->execute();
$esito = $stmt->get_result();
$result = $esito->fetch_assoc();

$stmt = $connection->prepare("UPDATE $table_tickets SET Stato = 2, IDStaffer = ?, Risposta = ? WHERE ID = ?");
$stmt->bind_param("isi", $id, $risposta, $idticket);
$esito = $stmt->execute();
if ($esito) {
	$linguautente = getPreference($idutente, "Lingua");
	
	telegramMSG();
	
	preparazioneMail($titolomail, $testomail);
	sendMail("info", "FireShield", $result["Email"], $titolomail, $testomail);
	
    header("Location:../listaTickets.php");
} else {
    header("Location:../listaTickets.php?e=2");
}
exit;

function preparazioneMail(&$titolomail, &$testomail)
{
	setlocale(LC_MESSAGES, $GLOBALS["linguautente"]);
	bindtextdomain("rispondiTicketDB", "../../Translations");
	bind_textdomain_codeset("rispondiTicketDB", 'UTF-8');
	textdomain("rispondiTicketDB");
	
    $titolomail = _("Aggiornamento sul tuo ticket") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, ti abbiamo inviato questa mail perché un nostro membro dello staff ha risposto al tuo ticket!") . "
		<br><br>
		<a href='https://www.fireshield.it/ticket.php'>" . _("Clicca qui per visualizzarlo") . "</a>
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
	🎫 <b>TICKET CLOSED</b> 🎫
	______________________________________

	Ticket ID: <b>".$GLOBALS["idticket"]."</b>
	Staff member: <b>".$GLOBALS["username"]."</b>
	
	Answer:
	<i>".$GLOBALS["risposta"]."</i>
	
	<a href='https://www.fireshield.it/Administration/visualizzaTicket.php?id=".$GLOBALS["idticket"]."'>View the ticket</a>

	Date: <b>".date("d/m/Y H:i:s")."</b>", "-395061512");
}