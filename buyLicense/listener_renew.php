<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

// PAGAMENTO
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location:/');
    exit();
}

if ($payments_live) {
    $url_pagamenti = "https://ipnpb.paypal.com/cgi-bin/webscr";
} else {
    $url_pagamenti = "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr";
}

$valore = "cmd=_notify-validate&" . http_build_query($_POST);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_pagamenti);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $valore);
$risposta = curl_exec($ch);
curl_close($ch);

if ($risposta == "VERIFIED") { //la risposta è verificata
    if (isset($_POST["custom"])) { // IL CAMPO CUSTOM CHE CONTIENE L'ID NON E' VUOTO
        $paypalid = $_POST["payer_id"];
        $email_acquirente = $_POST['payer_email'];
        $nome = $_POST["first_name"];
        $cognome = $_POST["last_name"];
        $anagrafica_acquirente = $nome . " " . $cognome;

        $residenza_paese = $_POST["address_country"];
        $residenza_codicepaese = $_POST["address_country_code"];
        $residenza_zip = $_POST["address_zip"];
        $residenza_regione = $_POST["address_state"];
        $residenza_citta = $_POST["address_city"];
        $residenza_indirizzo = $_POST["address_street"];

        $prezzo = $_POST['mc_gross'];
        $tasse = $_POST['mc_fee'];
        $moneta = $_POST['mc_currency'];
        $oggetto = $_POST['item_number'];
        $statopagamento = $_POST['payment_status'];
		
		// PER DEBUG
		if(!$payments_live) {
			$email_acquirente = "leonardo.pantani@gmail.com";
		}

        $id = $_POST["custom"]; //OTTENGO L'ID DI CHI VUOLE IL RINNOVO DAL POST
        $id = intval($id);
        $infolicenza = getLicense($id);

        if ($statopagamento == "Completed") {
            $stmt = $connection->prepare("SELECT $table_licenses.DurataLicenza, $table_licenses.TipoLicenza, $table_users.Username, $table_users.ID FROM $table_licenses, $table_users WHERE $table_licenses.DurataLicenza = 'monthly' AND $table_licenses.TipoLicenza = 'single' AND $table_licenses.IDCliente = ? AND $table_users.ID = ?");
            $stmt->bind_param("ii", $id, $id);
            $esito = $stmt->execute();
            $result = $stmt->get_result();
            $nrighe = $result->num_rows;

            if ($nrighe > 0) {
                $riga = $result->fetch_assoc();
                $username_utente = $riga["Username"];
                $scadenza = date("Y-m-d", strtotime("+1 Month"));
                // --------- AGGIORNO LA LICENZA
                $stmt = $connection->prepare("UPDATE $table_licenses SET Scadenza = ? WHERE IDCliente = ?");
                $stmt->bind_param("si", $scadenza, $id);
                $esito = $stmt->execute();
                if (!$esito) {
                    // MAIL ALLO STAFF
                    paymentError($titolomail, $testomail, "La licenza rinnovata non è stata assegnata all'utente.");
                    sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

                    // MAIL ALL'UTENTE
                    paymentFailed($titolomail, $testomail);
                    sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
                    exit;
                }

                // --------- AGGIORNO LA MAIL UTENTE
                $stmt = $connection->prepare("UPDATE $table_users SET Email = ? WHERE ID = ?");
                $stmt->bind_param("si", $email_acquirente, $id);
                $esito = $stmt->execute();
                if (!$esito) {
                    // MAIL ALLO STAFF
                    paymentError($titolomail, $testomail, "La licenza aggiornata non è stata modificata nel Database.");
                    sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

                    // MAIL ALL'UTENTE
                    paymentFailed($titolomail, $testomail);
                    sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
                    exit;
                }

                // ----------- SALVO I DATI DI PAGAMENTO NEL DATABASE
                $data_attuale = date("Y-m-d H:i:s");
                $stmt = $connection->prepare("INSERT INTO $table_payments (IDUtente, IDLicenza, PaypalID, TipoLicenza, DurataLicenza, Email, Nome, Cognome, Residenza_Paese, Residenza_CodicePaese, Residenza_ZIP, Residenza_Regione, Residenza_Citta, Residenza_Indirizzo, Prezzo, Tasse, Moneta, DataPagamento, StatoPagamento) VALUES (?, ?, ?, 'single', 'monthly', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssssisssddsss", $id, $infolicenza[0], $paypalid, $email_acquirente, $nome, $cognome, $residenza_paese, $residenza_codicepaese, $residenza_zip, $residenza_regione, $residenza_citta, $residenza_indirizzo, $prezzo, $tasse, $moneta, $data_attuale, $statopagamento);
                $esito = $stmt->execute();
                if (!$esito) {
                    // MAIL ALLO STAFF
                    paymentError($titolomail, $testomail, "I dati di pagamento non sono stati salvati.");
                    sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

                    // MAIL ALL'UTENTE
                    paymentFailed($titolomail, $testomail);
                    sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
                    exit;
                }

                // ----------- SUCCESSO RINNOVO
                $tipolicenza = "Single";
                $duratalicenza = "Monthly";
                paymentComplete($titolomail, $testomail);
                sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
				
				// --------- INVIO DELLA NOTIFICA DI PAGAMENTO ALLA MAIL PRINCIPALE
				paymentStaffNotification($titolomail, $testomail);
				sendMail("payments", "FireShield", "fireshieldcheatdetector@gmail.com", $titolomail, $testomail);
				
				sendTelegramMessage("
				💸 <b>NUOVO RINNOVO RICEVUTO</b> 💸
				______________________________________

				Tipo Licenza: <i>".$tipolicenza." (".$duratalicenza.")</i>
				Username & ID: <i>".$riga["Username"]." (".$id.")</i>
				Anagrafica: <i>".$anagrafica_acquirente." (".$residenza_codicepaese.")</i>
				Email: <i>".$email_acquirente."</i>
				ID Paypal: <i>".$paypalid."</i>
				Prezzo: <i>".$prezzo." ".$moneta."</i>

				<a href='https://www.fireshield.it/Administration/listaPagamenti'>Visualizza pagamento</a>

				Data: <b>".date("d/m/Y H:i:s")."</b>", "-1001312169410");
				
				// --------- CONFERMA A PAYPAL
				header("HTTP/1.1 200 OK");
                exit;
            } else { // ----------- FINE CONTROLLO SE PAGAMENTO E' EFFETTUATO SU LICENZA MENSILE SINGOLA
                // MAIL ALLO STAFF
                paymentError($titolomail, $testomail, "La licenza rinnovata non era Singola (monthly).");
                sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

                // MAIL ALL'UTENTE
                paymentFailed($titolomail, $testomail);
                sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
				
				sendTelegramMessage("
				❌ <b>ERRORE PAGAMENTO</b> ❌
				______________________________________
				
				La licenza rinnovata non era di tipo Single Mensile.

				Tipo Licenza: <i>".$tipolicenza." (".$duratascelta.")</i>
				Email: <i>".email_acquirente."</i>
				ID Paypal: <i>".$paypalid."</i>

				Data: <b>".date("d/m/Y H:i:s")."</b>", "-1001312169410");
                exit;
            }
        } else { // ----------- FINE IF STATOPAGAMENTO COMPLETED
            paymentFailed($titolomail, $testomail);
            sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
				
			sendTelegramMessage("
			❌ <b>ERRORE PAGAMENTO</b> ❌
			______________________________________

			Tipo Licenza: <i>".$tipolicenza." (".$duratascelta.")</i>
			Email: <i>".email_acquirente."</i>
			ID Paypal: <i>".$paypalid."</i>

			Data: <b>".date("d/m/Y H:i:s")."</b>", "-1001312169410");
            exit;
        }
    } else { // ----------- FINE IF ISSET CUSTOM FIELD
        // MAIL ALLO STAFF
        paymentError($titolomail, $testomail, "Il campo ID utente nel button PayPal era vuoto.");
        sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

        // MAIL ALL'UTENTE
        paymentFailed($titolomail, $testomail);
        sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
		
		sendTelegramMessage("
		❌ <b>ERRORE PAGAMENTO</b> ❌
		______________________________________
		
		Il campo ID utente nel button Paypal era vuoto.

		Tipo Licenza: <i>".$tipolicenza." (".$duratascelta.")</i>
		Email: <i>".email_acquirente."</i>
		ID Paypal: <i>".$paypalid."</i>

		Data: <b>".date("d/m/Y H:i:s")."</b>", "-1001312169410");
        exit;
    }
} else { // ----------- RISPOSTA NON VERIFICATA
    // MAIL ALLO STAFF
    paymentError($titolomail, $testomail, "La richiesta PayPal ha restituito un errore");
    sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);
	
	sendTelegramMessage("
	❌ <b>ERRORE RICHIESTA PAYPAL</b> ❌
	______________________________________

	Non sono disponibili altre informazioni.

	Data: <b>".date("d/m/Y H:i:s")."</b>", "-1001312169410");
    exit;
}



function paymentComplete(&$titolomail, &$testomail)
{
    $titolomail = _("License renewal received!") . " - " . $GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		" . _("Thank you for choosing our tool for quick and easy Anti-Cheat Minecraft screenshares.") . "
		<br>
		" . _("This is the payment receipt for the") . " <b>" . $GLOBALS['tipolicenza'] . "</b> " . _("license") . " (<i>" . $GLOBALS['duratalicenza'] . "</i>).
		<br><br>
		" . _("License renewed for") . ": <b>" . $GLOBALS['username_utente'] . "</b>
		<br>
		" . _("Next license expiration") . ": <b>" . $GLOBALS['scadenza'] . "</b>
		<hr>
		<div style='text-align:left;'>
		<h5>" . _("Data we received from PayPal") . ":</h5>
		<p>
		" . _("Email") . ": " . $GLOBALS['email_acquirente'] . "
		<br>
		" . _("First Name") . ": " . $GLOBALS['nome'] . "
		<br>
		" . _("Last Name") . ": " . $GLOBALS['cognome'] . "
		<br>
		" . _("Address") . ": " . $GLOBALS['residenza_indirizzo'] . ", " . $GLOBALS['residenza_citta'] . ", " . $GLOBALS['residenza_regione'] . ", " . $GLOBALS['residenza_paese'] . "
		</p>
		</div>
		<p style='text-align:right;'>".sprintf(_("The %s team"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}

function paymentFailed(&$titolomail, &$testomail)
{
    $titolomail = _("License renewal failed") . " - " . $GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		" . _("Hello, the renewal of the license was not successful") . " (" . _("payment status") . ": <b>" . $GLOBALS['statopagamento'] . "</b>).
		<br>
		" . _("Ci scusiamo per il disagio.") . "
		<br><br>
		" . _("If you think this is an error please tell us at") . " <a href='mailto:" . $GLOBALS['feedbackmail'] . "'>" . _("this email address") . "</a>.
		<hr>
		<div style='text-align:left;'>
		<h5>" . _("Data we received from PayPal") . ":</h5>
		<p>
		" . _("Email") . ": " . $GLOBALS['email_acquirente'] . "
		<br>
		" . _("First Name") . ": " . $GLOBALS['nome'] . "
		<br>
		" . _("Last Name") . ": " . $GLOBALS['cognome'] . "
		<br>
		" . _("Address") . ": " . $GLOBALS['residenza_indirizzo'] . ", " . $GLOBALS['residenza_citta'] . ", " . $GLOBALS['residenza_regione'] . ", " . $GLOBALS['residenza_paese'] . "
		</p>
		</div>
		<p style='text-align:right;'>".sprintf(_("The %s team"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}

function paymentError(&$titolomail, &$testomail, $spiegazione)
{
    $titolomail = "[!] Errore per il rinnovo [!] - ".$GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		Si è verificato un errore grave per il rinnovo della licenza " . $GLOBALS['infolicenza'][2] . " (" . $GLOBALS['infolicenza'][1] . ") all'utente con Username " . $GLOBALS['username_utente'] . "!! Motivo: " . $spiegazione . "
		<br><br>
		<p style='text-align:right;'>Il team del FireShield Cheat Detector</p>
	</body>
	";
}

function paymentStaffNotification(&$titolomail, &$testomail)
{
    $titolomail = "Rinnovo ricevuto! - ".$GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		Ricevuto con successo il pagamento da " . $GLOBALS['username_utente'] . ". La licenza rinnovata è la seguente: " . $GLOBALS['infolicenza'][2] . " (" . $GLOBALS['infolicenza'][1] . ")!
		<br>
		<hr>
		<div style='text-align:left;'>
		<h5>Dati ricevuti:</h5>
		<p>
		ID Paypal " . $GLOBALS['paypalid'] . "
		<br>
		Email: " . $GLOBALS['email_acquirente'] . "
		<br>
		Nome: " . $GLOBALS['nome'] . "
		<br>
		Cognome: " . $GLOBALS['nome'] . "
		<br>
		Prezzo: " . $GLOBALS['prezzo'] . "
		<br>
		Tasse: " . $GLOBALS['tasse'] . "
		<br>
		Moneta: " . $GLOBALS['moneta'] . "
		<br><br>
		Data pagamento: " . $GLOBALS['data_attuale'] . "
		<br>
		Residenza: " . $GLOBALS['residenza_paese'] . " (" . $GLOBALS['residenza_codicepaese'] . ") " . $GLOBALS['residenza_regione'] . ", " . $GLOBALS['residenza_citta'] . ", " . $GLOBALS['residenza_indirizzo'] . "
		</p>
		</div>
		<br><br>
		<p style='text-align:right;'>Il team del FireShield Cheat Detector</p>
	</body>
	";
}