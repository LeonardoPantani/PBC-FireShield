<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();
// -----------------------------

if ($tickets === true) {
    if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
        if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
            if (isset($_POST["spiegazione"], $_POST["tiporichiesta"]) && !empty($_POST["spiegazione"])) {
				if($_POST["tiporichiesta"] == "bug" || $_POST["tiporichiesta"] == "falseflag") {
					// pulizia input
					$spiegazione = filter_var(htmlspecialchars($_POST["spiegazione"]), FILTER_SANITIZE_STRING);
					$telegramusername = str_replace("@", "", filter_var(htmlspecialchars($_POST["telegramusername"]), FILTER_SANITIZE_STRING));
					$tiporichiesta = $_POST["tiporichiesta"];

					$data = new DateTime("now");
					$data = $data->format("Y-m-d H:i:s");

					$stmt = $connection->prepare("SELECT IDUtente FROM $table_tickets WHERE IDUtente = ? AND Stato = 1");
					$stmt->bind_param("i", $id);
					$stmt->execute();
					$esito = $stmt->get_result();
					$nrighe = $esito->num_rows;
					if ($nrighe > 0) {
						header("Location:ticket.php?e=3");
					} else {
						$stmt = $connection->prepare("INSERT INTO $table_tickets (IDUtente, Spiegazione, Data, TelegramUsername, TipoRichiesta) VALUES (?, ?, ?, ?, ?)");
						$stmt->bind_param("issss", $id, $spiegazione, $data, $telegramusername, $tiporichiesta);
						$esito = $stmt->execute();
						$ultimoid = $connection->insert_id;
						if ($esito) {
							telegramMSG();
							header("Location:ticket.php?e=7"); // OK
						} else {
							echo $connection->error;
							exit;
						}
					}
				} else {
					header("Location:ticket.php?e=6");
				}
            } else {
                header("Location:ticket.php?e=2");
            }
        } else {
            header("Location:ticket.php?e=5");
        }
    } else {
        header("Location:ticket.php?e=2");
    }
} else {
    header("Location:ticket.php?e=1");
}
exit;

function telegramMSG()
{
	sendTelegramMessage("
	🎫 <b>NEW TICKET</b> 🎫
	______________________________________

	Ticket ID: <b>".$GLOBALS["ultimoid"]."</b>
	Username: <b>".$GLOBALS["username"]."</b>
	Request type: <b>".$GLOBALS["tiporichiesta"]."</b>
	Telegram Username: <b>@".$GLOBALS["telegramusername"]."</b>
	
	Text:
	<i>".$GLOBALS["spiegazione"]."</i>
	
	<a href='https://www.fireshield.it/Administration/visualizzaTicket.php?id=".$GLOBALS["ultimoid"]."'>View the ticket</a>

	Date: <b>".date("d/m/Y H:i:s")."</b>", "-395061512");
}
