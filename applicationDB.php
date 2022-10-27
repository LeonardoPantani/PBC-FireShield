<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

kickOTPUser();
// -----------------------------

if($applications) {
	if(isset($_POST["nome"], $_POST["cognome"], $_POST["datanascita"], $_POST["email"], $_POST["residenza"], $_POST["lingua"], $_POST["risposta1"], $_POST["risposta2"], $_POST["risposta3"], $_POST["risposta4"], $_POST["risposta5"])) {
		if(in_array($_POST["lingua"], $language_list)) {
			$telegramusername = str_replace("@", "", $_POST["telegramusername"]);
			$indirizzoIP = getClientIP();
			
			$stmt = $connection->prepare("SELECT IDCandidatura FROM $table_applications WHERE TelegramUsername = ? OR IndirizzoIP = ? AND Stato = 0");
			$stmt->bind_param("ss", $telegramusername, $indirizzoIP);
			$esito = $stmt->execute();
			if ($esito) {
				$result = $stmt->get_result();
				$nrighe = $result->num_rows;
				if($nrighe > 0) {
					header("Location:application.php?e=4"); // è già stata inviata una candidatura ed è in attesa
					exit;
				}
			}
			
			if(getUserApplicationStatus($telegramusername)[0]) {
				if($recaptcha_check) {
					$recaptcharesponse = $_POST["g-recaptcha-response"];
					$verifica = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret4 . "&response=" . $recaptcharesponse);
					$esitoRecaptcha = json_decode($verifica);
				}
				
				if ($recaptcha_check && $esitoRecaptcha->success == true) {
					if(preg_match("/^[a-zA-Z ]*$/", $_POST["nome"]) && preg_match("/^[a-zA-Z ]*$/", $_POST["cognome"])) {
						$datanascita = explode("-", $_POST["datanascita"]);
						if(checkdate($datanascita[1], $datanascita[2], $datanascita[0])) { // mese - giorno - anno
							if(filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
								$nome = $_POST["nome"];
								$cognome = $_POST["cognome"];
								$datanascita = $_POST["datanascita"];
								$email = $_POST["email"];
								$residenza = $_POST["residenza"];
								$lingua = $_POST["lingua"];
								$datainvio = date("Y-m-d H:i:s");
								$risposta1 = substr($_POST["risposta1"], 0, 2000);
								$risposta2 = substr($_POST["risposta2"], 0, 2000);
								$risposta3 = substr($_POST["risposta3"], 0, 2000);
								$risposta4 = substr($_POST["risposta4"], 0, 2000);
								$risposta5 = substr($_POST["risposta5"], 0, 2000);
								
								$token_valido = false;
								
								while(!$token_valido) {
									$token = bin2hex(random_bytes(25));
									$stmt = $connection->prepare("SELECT IDCandidatura FROM $table_applications WHERE Token = ?");
									$stmt->bind_param("s", $token);
									$stmt->execute();
									$result = $stmt->get_result();
									$nrighe = $result->num_rows;
									if($nrighe == 0) {
										$token_valido = true;
									}
								}
								
								$token_segreto_valido = false;
								
								while(!$token_segreto_valido) {
									$secrettoken = bin2hex(random_bytes(25));
									$stmt = $connection->prepare("SELECT IDCandidatura FROM $table_applications WHERE SecretToken = ?");
									$stmt->bind_param("s", $secrettoken);
									$stmt->execute();
									$result = $stmt->get_result();
									$nrighe = $result->num_rows;
									if($nrighe == 0) {
										while(!$token_segreto_valido) {
											if($token == $secrettoken) {
												$secrettoken = bin2hex(random_bytes(25));
											} else {
												$token_segreto_valido = true;
											}
										}
									}
								}
								
								$stmt = $connection->prepare("INSERT INTO $table_applications (Nome, Cognome, DataNascita, Email, TelegramUsername, Residenza, Lingua, DataInvio, Risposta1, Risposta2, Risposta3, Risposta4, Risposta5, Stato, Token, IndirizzoIP, DataAggiornamento, SecretToken) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)");
								$stmt->bind_param("sssssssssssssssss", $nome, $cognome, $datanascita, $email, $telegramusername, $residenza, $lingua, $datainvio, $risposta1, $risposta2, $risposta3, $risposta4, $risposta5, $token, $indirizzoIP, $datainvio, $secrettoken);
								$esito = $stmt->execute();
								$ultimoid = $connection->insert_id;
								if($esito) {
									telegramMSG();
									
									preparazioneMail($titolomail, $testomail);
									sendMail("info", "FireShield", $email, $titolomail, $testomail);
									
									$_SESSION["telegramusername"] = $telegramusername;
									header("Location:application.php?e=11"); // fatto
									exit;
								} else {
									header("Location:application.php?e=10"); // impossibile salvare candidatura
									exit;
								}
							} else {
								header("Location:application.php?e=9"); // email non valida
								exit;
							}
						} else {
							header("Location:application.php?e=8"); // data nascita non valida
							exit;
						}
					} else {
						header("Location:application.php?e=7"); // nome, cognome non validi
						exit;
					}
				} else {
					header("Location:application.php?e=6"); // esito reCaptcha negativo
					exit;
				}
			} else {
				header("Location:application.php?e=5"); // cooldown non ancora scaduto
				exit;
			}
		} else {
			header("Location:application.php?e=3"); // la lingua selezionata non è valida
			exit;
		}
	} else {
		header("Location:application.php?e=2"); // non tutti i campi sono impostati
		exit;
	}
} else {
	header("Location:application.php?e=1"); // candidature disabilitate
	exit;
}

function telegramMSG()
{
	// Messaggio principale admin
	sendTelegramMessage("
	📄 <b>NEW APPLICATION</b> 📄
	___________________________

	Candidate: <b>".$GLOBALS["nome"]." ".$GLOBALS["cognome"]."</b>
	Application ID: <b>".$GLOBALS["ultimoid"]."</b>
	Language: <b>".$GLOBALS["lingua"]."</b>
	
	<i>Candidate's answers below 👇</i>
	
	<a href='https://www.fireshield.it/Administration/visualizzaCandidatura.php?id=".$GLOBALS["ultimoid"]."'>View application</a>

	Date: <b>".date("d/m/Y H:i:s")."</b>", -339570880);
	
	// Messaggio headquarter
	sendTelegramMessage("
	📄 <b>NUOVA CANDIDATURA</b> 📄
	___________________________

	Candidato: <b>".$GLOBALS["nome"]." ".$GLOBALS["cognome"]."</b>
	ID Candidatura: <b>".$GLOBALS["ultimoid"]."</b>
	Lingua: <b>".$GLOBALS["lingua"]."</b>
	
	<a href='https://www.fireshield.it/Administration/visualizzaCandidatura.php?id=".$GLOBALS["ultimoid"]."'>Vedi candidatura</a>
	
	<a href='https://www.fireshield.it/Administration/Utils/eliminaCandidatura.php?secrettoken=".$GLOBALS["secrettoken"]."'>Elimina Candidatura</a>

	Data: <b>".date("d/m/Y H:i:s")."</b>", -1001312169410);
	
	// Risposte admin
	for($i = 1; $i <= 5; $i++) {
		telegramMSG2($i);
	}
	sendTelegramMessage("
	<a href='https://www.fireshield.it/Administration/visualizzaCandidatura.php?id=".$GLOBALS["ultimoid"]."'>View application</a>

	Date: <b>".date("d/m/Y H:i:s")."</b>", -339570880);
}

function telegramMSG2($numero_risposta)
{
	sendTelegramMessage("
	<b>Answer ".$numero_risposta.":</b>
	".$GLOBALS["risposta".$numero_risposta]."
	", -339570880);
}

function preparazioneMail(&$titolomail, &$testomail)
{
    $titolomail = _("Stato candidatura") . " - ".$GLOBALS["solutionname_short"];
    $testomail = "
	<body align='center'>
		<div style='text-align:center;'>
			<img src='https://www.fireshield.it/CSS/Images/logo.png' width='20%'>
		</div>
		<h1>".$GLOBALS["solutionname"]."</h1>
		" . _("Ciao, abbiamo ricevuto la tua candidatura e al momento è in attesa di essere valutata. Ti sarà inviata una notifica quando lo stato della tua richiesta cambierà.") . "
		<br>
		" . _("Cerchiamo di prendere in considerazione le candidature il prima possibile, ma considera che il tempo di valutazione potrebbe variare in base a diversi fattori.") . "
		<br>
		" . _("Potrebbe essere necessario fino a un mese per ricevere l'esito della tua candidatura.") . "
		<br><br>
		<a href='https://fireshield.it/application.php?t=" . $GLOBALS["token"] . "'>" . _("STATO CANDIDATURA") . "</a>
		</div>
		<br>
		<p style='text-align:right;'>".sprintf(_("Il team del %s"), $GLOBALS["solutionname"])."</p>
	</body>
	";
}