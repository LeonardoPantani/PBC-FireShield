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
	$data_attuale = date("Y-m-d H:i:s");

	$esito_datarequest = getUserDataRequests($id);
	if(!$esito_datarequest[0]) {
		echo "error_too_early";
		exit;
	}

    $downloadtoken = bin2hex(random_bytes(25));

    $stmt = $connection->prepare("SELECT * FROM $table_users WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $esito = $stmt->get_result();
	$nrigheUtenti = $esito->num_rows;
    $datiUtente = $esito->fetch_assoc();

    $stmt = $connection->prepare("SELECT * FROM $table_payments WHERE IDUtente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $esito = $stmt->get_result();
	$nrighePagamenti = $esito->num_rows;
    $datiPagamenti = $esito->fetch_assoc();

    $stmt = $connection->prepare("SELECT * FROM $table_licenses WHERE IDCliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $esito = $stmt->get_result();
	$nrigheLicenze = $esito->num_rows;
    $datiLicenza = $esito->fetch_assoc();

    $stmt = $connection->prepare("INSERT INTO $table_datarequests (IDUtente, Data, DownloadToken) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id, $data_attuale, $downloadtoken);
    $stmt->execute();
    $id_richiesta = $stmt->insert_id;

    $contenuto = "-----------------------------------\n";
    $contenuto .= $solutionname . " " . "- "._("File dati utente");
    $contenuto .= "\n";
    $contenuto .= "-----------------------------------\n";
    $contenuto .= "\n";
    $contenuto .= _("DATI UTENTE:");
    $contenuto .= "\n";
	if($nrigheUtenti == 0) {
		$contenuto .= _("Non ci sono dati relativi all'utente.");
	} else {
		$contenuto .= _("Email:") . " " . $datiUtente["Email"];
		$contenuto .= "\n";
		$contenuto .= _("Username:") . " " . $datiUtente["Username"];
		$contenuto .= "\n";
		$contenuto .= _("Data ultimo login:") . " " . $datiUtente["DataUltimoLogin"];
		$contenuto .= "\n";
		$contenuto .= _("Ultimo indirizzo IP:") . " " . $datiUtente["UltimoIP"];	
	}
    $contenuto .= "\n\n";
    $contenuto .= "-----------------------------------\n";
    $contenuto .= "\n";
    $contenuto .= _("DATI PAGAMENTI:");
    $contenuto .= "\n";
	if($nrighePagamenti == 0) {
		$contenuto .= _("Non ci sono dati relativi ai pagamenti.");
	} else {
		$contenuto .= _("ID Paypal:") . " " . $datiPagamenti["PaypalID"];
		$contenuto .= "\n";
		$contenuto .= _("Tipo Licenza:") . " " . $datiPagamenti["TipoLicenza"];
		$contenuto .= "\n";
		$contenuto .= _("Durata Licenza:") . " " . $datiPagamenti["DurataLicenza"];
		$contenuto .= "\n";
		$contenuto .= _("Email pagamento:") . " " . $datiPagamenti["Email"];
		$contenuto .= "\n";
		$contenuto .= _("Nome:") . " " . $datiPagamenti["Nome"];
		$contenuto .= "\n";
		$contenuto .= _("Cognome:") . " " . $datiPagamenti["Cognome"];
		$contenuto .= "\n";
		$contenuto .= _("Residenza:") . " " . $datiPagamenti["Residenza_Paese"] . " (" . $datiPagamenti["Residenza_CodicePaese"] . "), " . $datiPagamenti["Residenza_ZIP"] . ", " . $datiPagamenti["Residenza_Citta"] . ", " . $datiPagamenti["Residenza_Indirizzo"];
		$contenuto .= "\n";
		$contenuto .= _("Pagamento:") . " " . $datiPagamenti["Prezzo"];
		$contenuto .= "\n";
		$contenuto .= _("Tasse:") . " " . $datiPagamenti["Tasse"];
		$contenuto .= "\n";
		$contenuto .= _("Moneta:") . " " . $datiPagamenti["Moneta"];
		$contenuto .= "\n";
		$contenuto .= _("Data pagamento:") . " " . $datiPagamenti["DataPagamento"];
	}
    $contenuto .= "\n\n";
    $contenuto .= "-----------------------------------\n";
    $contenuto .= "\n";
    $contenuto .= _("DATI LICENZA:");
    $contenuto .= "\n";
	if($nrigheLicenze == 0) {
		$contenuto .= _("Non ci sono dati relativi alle licenze.");
	} else {
		$contenuto .= _("Tipo Licenza attuale:") . " " . $datiLicenza["TipoLicenza"];
		$contenuto .= "\n";
		$contenuto .= _("Durata Licenza attuale:") . " " . $datiLicenza["DurataLicenza"];
		$contenuto .= "\n";
		$contenuto .= _("Scadenza:")." ";
		if($datiLicenza["DurataLicenza"] == "") {
			$contenuto .= $datiLicenza["Scadenza"];
		} else {
			$contenuto .= _("NESSUNA SCADENZA");
		}
	}
    $contenuto .= "\n\n";
    $contenuto .= "--------- " . _("FINE DEL FILE DATI UTENTE") . " ---------";

    file_put_contents("../Files/DataRequests/DataRequest_" . $id_richiesta . ".txt", $contenuto);

    // ----------------

    preparazioneMail($titolomail, $testomail);
    $esito = sendMail("info", "FireShield", $email, $titolomail, $testomail);

    if ($esito) {
        echo "success";
    } else {
        echo "failure";
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}

function preparazioneMail(&$titolomail, &$testomail)
{
    $titolomail = _("Richiesta Dati Utente") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
			<h1>".$GLOBALS["solutionname"]."</h1>
		</div>
		" . _("Hai richiesto l'ottenimento dei tuoi dati personali recentemente. Saranno disponibili per")." <b>".$GLOBALS["daysbeforedeletedatarequests"]." "._("giorni")."</b> "._("per il download prima che vengano cancellati automaticamente") . "
		<br><br>
		<a href='https://fireshield.it/Utils/downloadDataRequest.php?t=" . $GLOBALS['downloadtoken'] . "'>" . _("Clicca per scaricare i tuoi dati") . "</a>
		<br><br>
		<hr>
		<div style='text-align:left;'>
		<h5>" . _("Data richiesta") . ":</h5>
		<p>
		" . formatDate($GLOBALS['data_attuale'], true) . "
		</p>
		</div>
		<br><br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}
?>