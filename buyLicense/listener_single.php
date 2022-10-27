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

if ($risposta == "VERIFIED") { // la risposta è verificata
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

    $duratascelta = $_POST["option_selection1"];
    $usernamescelto = $_POST["option_selection2"];
	
	// PREPARAZIONE VARIABILI
	if ($duratascelta == "Monthly") {
		$duratascelta = "monthly";
		$scadenza = date("Y-m-d", strtotime("+1 Month"));
	} elseif ($duratascelta == "Permanent") {
		$duratascelta = "permanent";
		$scadenza = NULL;
	} else {
		$duratascelta = "monthly"; // Licenza monthly in caso di errori
	}
	$tipolicenza = "single";
	
	// PER DEBUG
	if(!$payments_live) {
		$email_acquirente = "leonardo.pantani@gmail.com";
	}

    // CONTROLLO SULL'USERNAME
	$usernamescelto = filter_var($usernamescelto, FILTER_SANITIZE_STRING);
	$usernamescelto = preg_replace($string_check_not_allowed, "", $usernamescelto);
	
	$stmt = $connection -> prepare("SELECT ID FROM $table_users WHERE Username = ?");
	$stmt -> bind_param("s", $usernamescelto);
	$stmt -> execute();
	$esito = $stmt->get_result();
	$nrighe = $stmt->num_rows;
	if($nrighe > 0) {
		$usernamescelto = randomPassword();
	} else {
		if ($usernamescelto == "" || $usernamescelto == "NA") { // perché il preg_replace sostituisce il "/"
			$usernamescelto = randomPassword();
            // MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "L'username scelto è vuoto ('') oppure è 'NA'.");
			sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);
		}
		if(checkBannedWords($usernamescelto)) {
			$usernamescelto = randomPassword();
			// MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "L'username scelto contiene una parola bandita.");
			sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);
		}
		if (strlen($usernamescelto) < $username_minlength) { // minimo x caratteri
			$stringaaggiuntiva = randomPassword(5);
			$usernamescelto = $usernamescelto . "_" . $stringaaggiuntiva;
			// MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "L'username scelto non raggiunge il limite minimo di caratteri.");
			sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);
		}
		if (strlen($usernamescelto) > $username_maxlength) { // massimo x caratteri
			$usernamescelto = substr($usernamescelto, 0, $username_maxlength - 1);
			// MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "L'username scelto supera limite di caratteri.");
			sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);
		}
	}
    // FINE CONTROLLO USERNAME

    if ($statopagamento == "Completed") {
        $password_acquirente = randomPassword();
        $passwordhash = password_hash($password_acquirente, PASSWORD_DEFAULT); //hash password
        $corretto = false;

        while ($corretto == false) {
            $stmt = $connection->prepare("SELECT * FROM $table_users WHERE Username = ?");
            $stmt->bind_param("s", $usernamescelto);
            $stmt->execute();
            $esito = $stmt->get_result();
            $nrighe = $esito->num_rows;
            if ($nrighe > 0) { //se durante la procedura del pagamento viene scelto un nick uguale allora ne genero uno io
                $usernamescelto = randomPassword();
				$corretto = true;
				// MAIL ALLO STAFF
				paymentError($titolomail, $testomail, "L'username scelto esisteva già.");
				sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);
            } else {
                $corretto = true;
            }
        }

        // ----------- INSERISCO L'UTENTE
        $stmt = $connection->prepare("INSERT INTO $table_users (Email, Username, Password, Tipo, Abilitato) VALUES (?, ?, ?, 0, 1)");
        $stmt->bind_param("sss", $email_acquirente, $usernamescelto, $passwordhash);
        $esito = $stmt->execute();
        $ultimoid = $connection->insert_id;
        if (!$esito) {
            // MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "L'utente non è stato inserito nel database.");
            sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

            // MAIL ALL'UTENTE
            paymentFailed($titolomail, $testomail);
            sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
            exit;
        }
		
		// INSERISCE LE PREFERENZE
		if(empty($lang) || $lang == "") {
			$lang = "en_US";
		}
		$stmt = $connection->prepare("INSERT INTO $table_preferences (IDUtente, Lingua) VALUES (?, ?)");
		$stmt->bind_param("is", $ultimoid, $lang);
		$esito = $stmt->execute();
		if(!$esito) {
            // MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "Le preferenze dell'utente non sono state inserite nel database.");
            sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

            // MAIL ALL'UTENTE
            paymentFailed($titolomail, $testomail);
            sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
            exit;
		}
		
        // ----------- INSERISCE IL CAMPO SESSIONE OTP
        $stmt = $connection->prepare("INSERT INTO $table_otpsessions (IDUtente, CodiceOTP, DataInizioSessione, DataFineSessione, StatoSessione, ScansioniEseguite) VALUES (?, '', NULL, NULL, 0, 0)");
        $stmt->bind_param("i", $ultimoid);
        $esito = $stmt->execute();
		$ultimoid_licenza = $connection->insert_id;
        if (!$esito) { // nel caso di un errore
            // MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "Le sessioni otp dell'utente non sono state inserite.");
            sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

            // MAIL ALL'UTENTE
            paymentFailed($titolomail, $testomail);
            sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
            exit;
        }
		
        // ----------- INSERISCO LA LICENZA
		if($duratascelta == "Monthly") {
			$stmt = $connection->prepare("INSERT INTO $table_licenses (DurataLicenza, TipoLicenza, Scadenza, IDCliente) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("sssi", $duratascelta, $tipolicenza, $scadenza, $ultimoid);
		} elseif($duratascelta == "Permanent") {
			$stmt = $connection->prepare("INSERT INTO $table_licenses (DurataLicenza, TipoLicenza, Scadenza, IDCliente) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("sssi", $duratascelta, $tipolicenza, $scadenza, $ultimoid);
		} else {
			$stmt = $connection->prepare("INSERT INTO $table_licenses (DurataLicenza, TipoLicenza, Scadenza, IDCliente) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("sssi", $duratascelta, $tipolicenza, $scadenza, $ultimoid);
		}
        $esito = $stmt->execute();
		$ultimoid_licenza = $connection->insert_id;
        if (!$esito) { // nel caso di un errore
            // MAIL ALLO STAFF
            paymentError($titolomail, $testomail, "La licenza non è stata assegnata all'utente.");
            sendMail("payments", "FireShield", "feedback@fireshield.it", $titolomail, $testomail);

            // MAIL ALL'UTENTE
            paymentFailed($titolomail, $testomail);
            sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
            exit;
        }

        // ----------- SALVO I DATI DI PAGAMENTO NEL DATABASE
        $data_attuale = date("Y-m-d H:i:s");
        $stmt = $connection->prepare("INSERT INTO $table_payments (IDUtente, IDLicenza, PaypalID, TipoLicenza, DurataLicenza, Email, Nome, Cognome, Residenza_Paese, Residenza_CodicePaese, Residenza_ZIP, Residenza_Regione, Residenza_Citta, Residenza_Indirizzo, Prezzo, Tasse, Moneta, DataPagamento, StatoPagamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssssisssddsss", $ultimoid, $ultimoid_licenza, $paypalid, $tipolicenza, $duratascelta, $email_acquirente, $nome, $cognome, $residenza_paese, $residenza_codicepaese, $residenza_zip, $residenza_regione, $residenza_citta, $residenza_indirizzo, $prezzo, $tasse, $moneta, $data_attuale, $statopagamento);
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
		
        paymentComplete($titolomail, $testomail);
        sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
		
		// --------- INVIO DELLA NOTIFICA DI PAGAMENTO ALLA MAIL PRINCIPALE
		paymentStaffNotification($titolomail, $testomail);
		sendMail("payments", "FireShield", "fireshieldcheatdetector@gmail.com", $titolomail, $testomail);
		
		sendTelegramMessage("
		💸 <b>NUOVO PAGAMENTO RICEVUTO</b> 💸
		______________________________________

		Tipo Licenza: <i>".$tipolicenza." (".$duratascelta.")</i>
		Username & ID: <i>".$usernamescelto." (".$ultimoid.")</i>
		Anagrafica: <i>".$anagrafica_acquirente." (".$residenza_codicepaese.")</i>
		Email: <i>".$email_acquirente."</i>
		ID Paypal: <i>".$paypalid."</i>
		Prezzo: <i>".$prezzo." ".$moneta."</i>

		<a href='https://www.fireshield.it/Administration/listaPagamenti'>Visualizza pagamento</a>

		Data: <b>".date("d/m/Y H:i:s")."</b>", "-1001312169410");
		
		// --------- CONFERMA A PAYPAL
		header("HTTP/1.1 200 OK");
        exit;
    } else { // --------- ERRORE PAGAMENTO
        paymentFailed($titolomail, $testomail);
        sendMail("payments", "FireShield", $email_acquirente, $titolomail, $testomail);
		
		sendTelegramMessage("
		❌ <b>ERRORE PAGAMENTO</b> ❌
		______________________________________

		Tipo Licenza: <i>".$tipolicenza." (".$duratascelta.")</i>
		Email: <i>".$email_acquirente."</i>
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
    $titolomail = _("Acquisto ricevuto!") . " - " . $GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		" . _("Thank you for choosing our tool for quick and easy Anti-Cheat Minecraft screenshares.") . "
		<br>
		" . _("This is the payment receipt for the") . " <b>" . $GLOBALS['tipolicenza'] . "</b> " . _("license") . " (<i>" . $GLOBALS['duratascelta'] . "</i>). " . _("These are the information that you will need to log-in on FireShield") . ".
		<br><br>
		" . _("Username") . ": <b>" . $GLOBALS['usernamescelto'] . "</b>
		<br>
		" . _("Password") . ": <b>" . $GLOBALS['password_acquirente'] . "</b><br><br>
		<a href='https://www.fireshield.it/login.php?fl=1'>"._("CLICK HERE TO LOG-IN")."</a>
		<br>
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
    $titolomail = _("Purchase failed") . " - " . $GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		" . _("Hello, the payment of the license was not successful") . " (" . _("payment status") . ": <b>" . $GLOBALS['statopagamento'] . "</b>).
		<br>
		" . _("We apologize for the inconvenience.") . "
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
    $titolomail = "[!] Errore per l'assegnazione della licenza [!] - " . $GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		Si è verificato un errore grave per l'assegnazione della licenza " . $GLOBALS['tipolicenza'] . " (" . $GLOBALS['duratascelta'] . ") all'utente con Username " . $GLOBALS['usernamescelto'] . "!! Motivo: " . $spiegazione . "
		<br><br>
		<p style='text-align:right;'>Il team del FireShield Cheat Detector</p>
	</body>
	";
}

function paymentStaffNotification(&$titolomail, &$testomail)
{
    $titolomail = "Acquisto ricevuto! (".$GLOBALS['tipolicenza']." ".$GLOBALS['duratascelta'].") - " . $GLOBALS['solutionname'];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		Ricevuto con successo il pagamento da " . $GLOBALS['usernamescelto'] . ". La licenza assegnata è la seguente: " . $GLOBALS['tipolicenza'] . " (" . $GLOBALS['duratascelta'] . ")!
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