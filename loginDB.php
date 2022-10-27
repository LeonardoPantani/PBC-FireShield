<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

// -----------------------------

if (isset($_GET["lf"])) {
	$stmt = $connection->prepare("SELECT * FROM $table_users WHERE LF = ?");
	$stmt->bind_param("s", $_GET["lf"]);
	$stmt->execute();
	$esito = $stmt->get_result();
	if($esito->num_rows == 1) {
		$datiUtente = $esito->fetch_assoc();
		
		$esito = setUserData($datiUtente["ID"], $datiUtente["Email"], $datiUtente["Username"], $datiUtente["Tipo"], $idnetwork);
		
		// --- RIMOZIONE LF
		$stmt = $connection -> prepare("UPDATE $table_users SET LF = NULL WHERE LF = ?");
		$stmt->bind_param("s", $_GET["lf"]);
		$stmt->execute();
		
		// ----------- IMPOSTAZIONE DELLE PREFERENZE
		if ($esito) {
			// ---- SOVRASCRIVE IL TOKEN DI SESSIONE (NON OTP)
			$tokenrandom = bin2hex(random_bytes(25));
			$stmt = $connection -> prepare("UPDATE $table_users SET LoginToken = ? WHERE ID = ?");
			$stmt->bind_param("si", $tokenrandom, $datiUtente["ID"]);
			$stmt->execute();
			$_SESSION["LoginToken"] = $tokenrandom;
			session_write_close();
			session_name('__Secure-FSCDSESSION');
			session_start();
			// ----------- REDIRECT DOPO LOGIN EFFETTUATO CON SUCCESSO
			header("Location:settings.php");
		} else {
			header("Location:login.php?e=5"); // Errore: La sessione non è stata impostata
		}
	} else {
		header("Location:login.php?e=1"); // Errore (finto): I login sono disabilitati
	}
} else {
	kickLoggedUser();
	
	if ($login == true) {
		
		// ----------- CONTROLLO TOKEN DI SICUREZZA
		if($recaptcha_check) {
			$recaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$recaptcha_secret3."&response=".$_POST['recaptcha_response']);
			$recaptcha = json_decode($recaptcha);
		}
		
		if($recaptcha_check && $recaptcha->score >= 0.5) { // Se il livello di controllo di reCaptcha Google è abbastanza alto va bene
			if ((isset($_POST["CSRFtoken"], $_COOKIE["CSRFtoken"])) || $securitycheck == false) { //Controllo i token e i cookie se esistono
				if (($_POST["CSRFtoken"] == $_COOKIE["CSRFtoken"]) || $securitycheck == false) { //Controllo se corrispondono
					if (isset($_POST["username"], $_POST["password"])) {
						$username = $_POST["username"];
						$password = $_POST["password"];

						// ----------- OTTENIMENTO DATI UTENTE
						$stmt = $connection->prepare("SELECT * FROM $table_users WHERE Username = ?");
						$stmt->bind_param("s", $username);
						$stmt->execute();
						$esito = $stmt->get_result();
						$datiUtente = $esito->fetch_assoc();
						$nrighe = $esito->num_rows;
						if ($nrighe == 0) {
							header("Location:login.php?e=3");
						} else {

							// ----------- CONTROLLO PASSWORD
							$verificaPassword = password_verify($password, $datiUtente["Password"]);
							if ($verificaPassword == true) {
								// ----------- OTTENIMENTO DATI LICENZA
								$stmt = $connection->prepare("SELECT IDCliente FROM $table_licenses WHERE IDCliente = ?");
								$stmt->bind_param("i", $datiUtente["ID"]);
								$stmt->execute();
								$esito = $stmt->get_result();
								$nrighe = $esito->num_rows;
								if($nrighe > 0) {
									$datiLicenza = $esito->fetch_assoc();
								}


								// ----------- VERIFICA SE L'ACCOUNT E' ABILITATO
								if ($datiUtente["Abilitato"] == 0) { //se l'utente è stato creato da un admin ma non è abilitato dagli amministratori
									header("Location:login.php?e=11"); // Errore: L'account è in attesa di approvazione (abilitato = 0)
									exit;
								}

								// ----------- OTTENIMENTO ID NETWORK
								if($datiLicenza["IDCliente"] == "") {
									$datiLicenza["IDCliente"] = $datiUtente["ID"];
								}
								$stmt = $connection->prepare("SELECT IDNetwork FROM $table_networks WHERE IDGestore = ?");
								$stmt->bind_param("i", $datiLicenza["IDCliente"]);
								$stmt->execute();
								$esito = $stmt->get_result();
								$datiNetwork = $esito->fetch_assoc();
								$nrighe = $esito->num_rows;
								if ($nrighe == 0) { // non appartiene ad alcun network
									$idnetwork = 0;
								} else {
									$idnetwork = $datiNetwork["IDNetwork"];
								}
								
								// -----------  OTTENIMENTO PREFERENZE
								$stmt = $connection->prepare("SELECT Lingua, LoginRedirect, 2FARichiesto FROM $table_preferences WHERE IDUtente = ?");
								$stmt->bind_param("i", $datiUtente["ID"]);
								$stmt->execute();
								$esito = $stmt->get_result();
								$datiPreferenze = $esito->fetch_assoc();

								// ----------- CONTROLLO 2FA
								if ($twofactor == true && $datiPreferenze["2FARichiesto"] == 1) { //controllo se l'opzione twofactor è attiva e che l'utente abbia il 2fa abilitato
									if($datiUtente["Secret"] == "") {
										$esito = setUserData($datiUtente["ID"], $datiUtente["Email"], $datiUtente["Username"], $datiUtente["Tipo"], $idnetwork);
									} else {
										if ($_POST["secretcode"] == "") { // il codice 2fa è già impostato ma non scritto nei campi del form
											header("Location:login.php?e=9"); // Errore: Codice 2FA richiesto per le scansioni
											exit;
										} else {
											require_once '2FACode/GoogleAuthenticator.php';
											$ga = new PHPGangsta_GoogleAuthenticator();
											$secretcode = str_replace(' ', '', $_POST["secretcode"]);
											$esito2fa = $ga->verifyCode($datiUtente["Secret"], $secretcode, 3);
											if ($esito2fa) {
												$esito = setUserData($datiUtente["ID"], $datiUtente["Email"], $datiUtente["Username"], $datiUtente["Tipo"], $idnetwork);
											} else {
												//controllo validità codice
												$stmt = $connection->prepare("SELECT * FROM $table_2fa WHERE IDUtente = ?");
												$stmt->bind_param("i", $datiUtente["ID"]);
												$stmt->execute();
												$esito = $stmt->get_result();
												$nrighe = $esito->num_rows;
												if($nrighe == 0) {
													header("Location:login.php?e=10"); // Errore: Codice di verifica non valido
													exit;
												} else {
													$riga = $esito->fetch_assoc();
													$array_codici = unserialize($riga["RecoveryCodes"]);
													$ok = false;
													foreach($array_codici as $codice1) {
														if($codice1 == $_POST["secretcode"]) {
															$ok = true;
														}
													}
													if(!$ok) {
														header("Location:login.php?e=10"); // Errore: Codice di verifica non valido
														exit;
													}
												}
												//fine controllo validità codice
												
												//rimuovo codice recupero
												$stmt = $connection->prepare("SELECT * FROM $table_2fa WHERE IDUtente = ?");
												$stmt->bind_param("i", $datiUtente["ID"]);
												$stmt->execute();
												$esito = $stmt->get_result();
												$nrighe = $esito->num_rows;
												if($nrighe == 0) {
													header("Location:login.php?e=9"); // Errore: Codice 2FA richiesto per le scansioni
													exit;
												} else {
													$riga = $esito->fetch_assoc();
													
													$array_codici = unserialize($riga["RecoveryCodes"]);
													
													$index = array_search($_POST["secretcode"], $array_codici);
													if($index !== FALSE){
														unset($array_codici[$index]);
													}
													$codici = serialize($array_codici);
													
													$stmt = $connection->prepare("UPDATE $table_2fa SET RecoveryCodes = ? WHERE IDUtente = ?");
													$stmt->bind_param("si", $codici, $datiUtente["ID"]);
													$esito = $stmt->execute();
													if(!$esito) {
														header("Location:login.php?e=9"); // Errore: Codice 2FA richiesto per le scansioni
														exit;
													}
												}
												//fine rimozione codice recupero
												
												
												$esito = setUserData($datiUtente["ID"], $datiUtente["Email"], $datiUtente["Username"], $datiUtente["Tipo"], $idnetwork);
											}
										}
									}
								} else {
									$esito = setUserData($datiUtente["ID"], $datiUtente["Email"], $datiUtente["Username"], $datiUtente["Tipo"], $idnetwork);
								}

								// ----------- IMPOSTAZIONE DELLE PREFERENZE
								if ($esito) {
									// ---- SOVRASCRIVE IL TOKEN DI SESSIONE (NON OTP)
									$tokenrandom = bin2hex(random_bytes(25));
									$stmt = $connection -> prepare("UPDATE $table_users SET LoginToken = ? WHERE ID = ?");
									$stmt->bind_param("si", $tokenrandom, $_SESSION["id"]);
									$stmt->execute();
									$_SESSION["LoginToken"] = $tokenrandom;
									session_write_close();
									session_name('__Secure-FSCDSESSION');
									session_start();

									// ----------- MOSTRA L'AVVISO DI LOCALIZZAZIONE
									if($datiUtente["DataUltimoLogin"] == "") {
										$primoaccesso = true;
									} else {
										$primoaccesso = false;
										$_SESSION["lang"] = $datiPreferenze["Lingua"]; // Ottengo i dati della lingua se non è il primo login
									}
									
									// ----------- SALVATAGGIO DATA E IP ULTIMO LOGIN
									$datalogin = new DateTime("now");
									$data = $datalogin->format("Y-m-d H:i:s");
									$indirizzo_ip = getClientIP();
									$stmt = $connection->prepare("UPDATE $table_users SET DataUltimoLogin = ?, UltimoIP = ? WHERE ID = ?");
									$stmt->bind_param("ssi", $data, $indirizzo_ip, $datiUtente["ID"]);
									$stmt->execute();
									
									// ----------- REDIRECT DOPO LOGIN EFFETTUATO CON SUCCESSO
									if ($datiUtente["AggiornaPassword"] == 1) {
										header("Location:Password/updatePassword.php");
									} elseif($primoaccesso) {
										if($_SESSION["lang"] != "en_US" && $_SESSION["lang"] != "it_IT") {
											header("Location:/settings.php?lang_warning=1&first_login=1");
										} else {
											header("Location:/settings.php?first_login=1");
										}
									} else {
										header("Location:".$redirect_pages_link[$datiPreferenze["LoginRedirect"]]);
									}
								} else {
									header("Location:login.php?e=5"); // Errore: La sessione non è stata impostata
								}
							} else {
								header("Location:login.php?e=4"); // Errore: Password errata
							}
						}
					} else {
						header("Location:login.php?e=2"); // Errore: Non tutti i parametri sono impostati (USERNAME O PASSWORD)
					}
				} else {
					header("Location:login.php?e=6"); // Errore: Il token di sicurezza non corrisponde
				}
			} else {
				header("Location:login.php?e=2"); // Errore: Non tutti i parametri sono impostati (CSRF TOKEN)
			}
		} else {
			header("Location:login.php?e=13"); // Errore: Il livello di controllo di reCapcha è troppo basso (spam)
		}
	} else {
		header("Location:login.php?e=1"); // Errore: I login sono disabilitati
	}
}
exit;