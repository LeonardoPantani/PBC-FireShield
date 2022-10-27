<?php
header('X-Frame-Options: sameorigin');
header('X-Content-Type-Options: nosniff');
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net static.copyrighted.com *.paypal.com *.gstatic.com *.iubenda.com www.google.com www.youtube.com images.dmca.com code.jquery.com cdnjs.cloudflare.com stackpath.bootstrapcdn.com www.googletagmanager.com use.fontawesome.com www.google-analytics.com; img-src 'self' www.sandbox.paypal.com static.copyrighted.com i.imgur.com images.dmca.com chart.googleapis.com cdn.iubenda.com www.paypalobjects.com www.google-analytics.com data:;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header_remove("X-Powered-By");

ini_set("session.cookie_httponly", 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set("session.gc_maxlifetime", 3600); // 1 ora prima della scadenza della sessione
ini_set("session.gc_probability", 1);
ini_set("session.gc_divisor", 1);
ini_set("log_errors", 1);
ini_set("error_log", "$droot/Functions/error_log.log");

session_name('__Secure-FSCDSESSION');
session_start();
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
require "$droot/Mail/PHPMailer/vendor/autoload.php";

// ------------------------- INIZIO FUNZIONI "SESSION"
function isLogged() //Restituisce VERO se l'utente è loggato, FALSE altrimenti

{
    if (isset($_SESSION["logged"])) {
        if ($_SESSION["logged"] == true) {
            return true;
        }
    } else {
        return false;
    }
}

function readUserData(&$id, &$email, &$username, &$tipo, &$network, &$otp) //Parametri per riferimento, restituisce VERO se l'utente è loggato, FALSE altrimenti

{
    if (isLogged()) {
		if($_SESSION["otp"] == false) {
			// -------- VERIFICA CHE L'ACCOUNT SIA L'UNICO IN USO
			include $GLOBALS["droot"] . "/Config/config.php";
			$connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
			$stmt = $connection->prepare("SELECT LoginToken FROM $table_users WHERE LoginToken = ? AND ID = ?");
			$stmt->bind_param("si", $_SESSION["LoginToken"], $_SESSION["id"]);
			$esito = $stmt->execute();
			if ($esito) {
				$result = $stmt->get_result();
				$nrighe = $result->num_rows;
				if($nrighe > 0) {
					$id = $_SESSION["id"];
					$email = $_SESSION["email"];
					$username = $_SESSION["username"];
					$tipo = $_SESSION["tipo"];
					$network = $_SESSION["network"];
					$otp = $_SESSION["otp"];
					return true;
				} else {
					header("Location:/Utils/deleteSession.php?m=12");
				}
			} else {
				return false;
			}
			// ----- TERMINE VERIFICA
		} else {
			$id = $_SESSION["id"];
			$email = $_SESSION["email"];
			$username = $_SESSION["username"];
			$tipo = $_SESSION["tipo"];
			$network = $_SESSION["network"];
			$otp = $_SESSION["otp"];
			return true;	
		}
    } else {
        return false;
    }
}

function setUserData($id, $email, $username, $tipo, $network = 0, $otp = false, $loggata = true) //Imposta nella session i parametri

{	
    $_SESSION["id"] = $id;
    $_SESSION["email"] = $email;
    $_SESSION["username"] = $username;
    $_SESSION["tipo"] = $tipo;
    $_SESSION["network"] = $network;
    $_SESSION["otp"] = $otp;
    $_SESSION["logged"] = $loggata;

    if (isset($_SESSION["id"]) && isset($_SESSION["email"]) && isset($_SESSION["username"]) && isset($_SESSION["logged"])) {
        return true;
    } else {
        return false;
    }
}

function adminCheck() //Se il check di sicurezza aggiuntivo sugli admin è attivo, controlla l'ultimo ip con quello attuale per verificare la corrispondenza

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		if($GLOBALS["additional_admin_check"]) {
			if(isset($_SESSION["ConfermaAdmin"]) && $_SESSION["ConfermaAdmin"] == 1) {
				$tabella = $GLOBALS["table_users"];
				$stmt = $connection -> prepare("SELECT UltimoIP FROM $tabella WHERE ID = ?");
				$stmt->bind_param("i", $GLOBALS["id"]);
				$stmt -> execute();
				$result = $stmt -> get_result();
				$riga = $result->fetch_assoc();
				if(getClientIP() != $riga["UltimoIP"]) {
					header("Location:/Administration/confermaAdmin.php?e=4");
					exit;
				}
			} else {
				header("Location:/Administration/confermaAdmin.php");
				exit;
			}
		}
	} else {
		header("Location:/"); // in caso di errore connessione
	}
}
// ------------------------- FINE FUNZIONI "SESSION"

// ------------------------- INIZIO FUNZIONI "GET"
function getNewsText() //Ottiene i parametri da mostrare nelle News a scorrimento

{
    if ($mainnews_title != "" && $mainnews_body != "") {
        $newscompleta = array($mainnews_title, $mainnews_body);
        return $newscompleta;
    } else {
        return false;
    }
}

function getUserScans($idutente) //Restituisce il numero di scansioni totale, e il numero di scansioni con esito: error, clean, suspect, cheat

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT * FROM $table_historyanalyzer WHERE IDUtente = ?");
        $stmt->bind_param("i", $idutente);
        $esito = $stmt->execute();
        if ($esito) {
            $result = $stmt->get_result();
            $nrighe = $result->num_rows;
            $esitoclean = 0;
            $esitocheat = 0;
            $esitosuspect = 0;
            $esitoerror = 0;
            while ($riga = $result->fetch_assoc()) {
                switch ($riga["Esito"]) {
                    case 0:
                        $esitoerror++;
                        break;

                    case 1:
                        $esitoclean++;
                        break;

                    case 2:
                        $esitosuspect++;
                        break;

                    case 3:
                        $esitocheat++;
                        break;

                    default:
                        $esitoerror++;
                        break;
                }
            }

            $esito = array($nrighe, $esitoerror, $esitoclean, $esitosuspect, $esitocheat);
            return $esito;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function getNetworkInfo($idnetwork) //Restituisce le informazioni del network in base all'id network fornito

{
    if ($idnetwork !== 0) {
        include $GLOBALS["droot"] . "/Config/config.php";
        $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
        if ($connection) {
            $stmt = $connection->prepare("SELECT * FROM $table_networks WHERE IDNetwork = ?");
            $stmt->bind_param("i", $idnetwork);
            $esito = $stmt->execute();
            if ($esito) {
                $result = $stmt->get_result();
                $nrighe = $result->num_rows;
                $result = $result->fetch_assoc();
                if ($nrighe === 0) {
                    return false;
                } else {
                    $risposta = array($result["IDGestore"], $result["IDLicenza"], $result["NomeNetwork"]);
                    return $risposta;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return 0;
    }
}

function getPermission($id, $permesso) //Restituisce il permesso per un determinato id utente (1 sì, 0 no, -1 errore)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT * FROM $table_permissions WHERE IDUtente = ?");
        $stmt->bind_param("i", $id);
        $esito = $stmt->execute();
        if ($esito) {
            $result = $stmt->get_result();
            $nrighe = $result->num_rows;
            $result = $result->fetch_assoc();
            if ($nrighe > 0) {
                if ($result[$permesso] == 1) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    } else {
        return -1;
    }
}

function getStringNumber($selezione = "") //Restituisce il numero di stringhe per una determinata tabella

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        if (!empty($selezione)) {
            $stmt = $connection->prepare("SELECT * FROM " . $selezione);
            if (!$stmt) {
                return "Errore";
            } else {
                $stmt->execute();
                $result = $stmt->get_result();
                $nrighe = $result->num_rows;
            }
        } else {
            $stmt = $connection->prepare("(SELECT * from $table_cheatjava) UNION (SELECT * from $table_suspectjava) UNION (SELECT * from $table_cheatdwm) UNION (SELECT * from $table_cheatlsass) UNION (SELECT * from $table_cheatmsmpeng)");
            $stmt->execute();
            $result = $stmt->get_result();
            $nrighe = $result->num_rows;
        }
        $connection->close();
        return $nrighe;
    } else {
        return false;
    }
}

function getDNT() //Restituisce i valore DO NOT TRACK fornito dal browser

{
    if (array_key_exists('HTTP_DNT', $_SERVER) && (1 === (int) $_SERVER['HTTP_DNT'])) {
        return true;
    } else {
        return false;
    }
}

function getLicense($idcliente) //Restituisce tutte le informazioni di una licenza, in base all'id utente fornito

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		$stmt = $connection->prepare("SELECT * FROM $table_licenses WHERE IDCliente = ?");
        $stmt->bind_param("i", $idcliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe > 0) {
			if($result["DurataLicenza"] != "" && $result["TipoLicenza"] != "") {
				if($result["DurataLicenza"] == "monthly" && $result["Scadenza"] == "") {
					return array(0, "ERROR", "ERROR", "2000-01-01", 0, 0, 0, false);
				} else {
					$attiva = true;
					$giornirimasti = 0;
					if ($result["DurataLicenza"] != "permanent") {
						$datacorrente = date("Y-m-d");
						$datascadenza = $result["Scadenza"];
						$differenza = strtotime($datascadenza) - strtotime($datacorrente);
						$giornirimasti = abs(round($differenza / (60*60*24)));
						if ($datacorrente >= $datascadenza) { // è scaduta (compreso il giorno corrispondente alla scadenza)
							$attiva = false;
						}
					}

					$result["IDBeneficiario"] = $result["IDCliente"]; // temporaneo
					$licenza = array($result["ID"], $result["DurataLicenza"], $result["TipoLicenza"], $result["Scadenza"], $result["IDCliente"], $result["IDBeneficiario"], $giornirimasti, $attiva);
					return $licenza;
				}
			} else {
				return array(0, "ERROR", "ERROR", "2000-01-01", 0, 0, 0, false);
			}
        } else {
			return array(0, "ERROR", "ERROR", "2000-01-01", 0, 0, 0, false);
        }
    } else {
		return array(0, "ERROR", "ERROR", "2000-01-01", 0, 0, 0, false);
    }
}

function getPreference($id_utente, $nome_preferenza) //Restituisce vero o falso in base alla preferenza

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT " . $nome_preferenza . " FROM $table_preferences WHERE IDUtente = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return -1;
        } else {
            return $result[$nome_preferenza];
        }
    } else {
        return false;
    }
}

function updatePreference($id_utente, $nome_preferenza, $valore_preferenza) //Restituisce vero o falso in base alla preferenza

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		$stmt = $connection -> prepare("UPDATE $table_preferences SET $nome_preferenza = ? WHERE IDUtente = ?");
		if(is_int($valore_preferenza)) {
			$stmt->bind_param("ii", $valore_preferenza, $id_utente);
		} elseif(is_string($valore_preferenza)) {
			$stmt->bind_param("si", $valore_preferenza, $id_utente);
		} else {
			return -1;
		}
		$esito = $stmt->execute();
		if($esito) {
			return 1;
		} else {
			return 0;
		}
    } else {
        return -2;
    }
}

function getUsernameFromID($id_utente)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return "invalid_id";
        } else {
            return $result["Username"];
        }
    } else {
        return "Database Error";
    }
}

function getIDFromUsername($username_utente)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT ID FROM $table_users WHERE Username = ?");
        $stmt->bind_param("s", $username_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return 0;
        } else {
            return $result["ID"];
        }
    } else {
        return "Database Error";
    }
}

function getEmailFromID($id_utente)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT Email FROM $table_users WHERE ID = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return "invalid_id";
        } else {
            return $result["Email"];
        }
    } else {
        return "Database Error";
    }
}

function getIDFromEmail($email_utente)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT ID FROM $table_users WHERE Email = ?");
        $stmt->bind_param("s", $email_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return 0;
        } else {
            return $result["ID"];
        }
    } else {
        return "Database Error";
    }
}

function getUsernameFromEmail($email_utente)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT Username FROM $table_users WHERE Email = ?");
        $stmt->bind_param("s", $email_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return "invalid_email";
        } else {
            return $result["Username"];
        }
    } else {
        return "Database Error";
    }
}

function getEmailFromUsername($username_utente)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
        $stmt = $connection->prepare("SELECT Email FROM $table_users WHERE Username = ?");
        $stmt->bind_param("s", $email_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        $nrighe = $result->num_rows;
        $result = $result->fetch_assoc();
        if ($nrighe == 0) {
            return "invalid_username";
        } else {
            return $result["Email"];
        }
    } else {
        return "Database Error";
    }
}

function hasTwoFactorAuthentication($id_utente) //Restituisce vero se l'utente ha impostata la sicurezza a due fattori, false altrimenti

{
	include $GLOBALS["droot"] . "/Config/config.php";
	$connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
	if ($connection) {
		$stmt = $connection->prepare("SELECT Secret FROM $table_users WHERE ID = ?");
		$stmt->bind_param("i", $id_utente);
		$stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
		$result = $result->fetch_assoc();
		if ($nrighe > 0) {
			if($result["Secret"] != "") {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

function licenseInformation(&$licenza, $redirect = false) //Salvo le info nella variabile licenza

{
	if(isLogged()) {
		$licenza = getLicense($GLOBALS["id"]);
		if($redirect && $licenza[7] == false) {
			header("Location:/settings.php");
			exit;
		}
	}
}

function networkInformation(&$infonetwork) //Salvo le info nella variabile network

{
	if(isLogged()) {
		if($GLOBALS["network"] != 0) {
			$infonetwork = getNetworkInfo($GLOBALS["network"]);
		} else {
			$infonetwork = 0;
		}
	}
}

function getUserDataRequests($idutente) //Restituisce true se l'utente può fare una richiesta dei dati, false se è già stata effettuata una richiesta entro x giorni precedenti (e restituisce la data e i giorni rimasti)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		$tabella = $GLOBALS["table_datarequests"];
		$dataoggi = date("Y-m-d H:i:s");
		
		$stmt = $connection -> prepare("SELECT * FROM $tabella WHERE IDUtente = ? ORDER BY Data DESC LIMIT 1"); // ULTIMA RICHIESTA DI DATI
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
		$riga = $result->fetch_assoc();

		if($nrighe > 0) {
			$differenza = strtotime($dataoggi) - strtotime($riga["Data"]);
			$differenzaGiorni = round($differenza / (60 * 60 * 24));
		} else {
			$differenzaGiorni = $GLOBALS["daysbeforedeletedatarequests"] + 1;
		}
		
		if($differenzaGiorni >= $GLOBALS["daysbeforedeletedatarequests"]) { // Contenuto array: vero/falso, data di ultima richiesta (solo falso), giorni rimasti prima della prossima richiesta (solo falso)
			$risultato = [true, "0000-00-00 00:00:00", 0];
			return $risultato;
		} else {
			$risultato = [false, $riga["Data"], ($GLOBALS["daysbeforedeletedatarequests"]-abs($differenzaGiorni))];
			return $risultato;
		}
	} else {
		return false;
	}
}

function getUserUsernameChange($idutente) //Restituisce true se l'utente può fare un cambio username, false se è già stato effettuato un cambio entro x giorni precedenti (e restituisce la data e i giorni rimasti)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		$tabella = $GLOBALS["table_usernamechange"];
		$dataoggi = date("Y-m-d H:i:s");
		
		$stmt = $connection -> prepare("SELECT * FROM $tabella WHERE IDUtente = ? ORDER BY Data DESC LIMIT 1"); // ULTIMO CAMBIO USERNAME
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
		$riga = $result->fetch_assoc();

		if($nrighe > 0) {
			$differenza = strtotime($dataoggi) - strtotime($riga["Data"]);
			$differenzaGiorni = round($differenza / (60 * 60 * 24));
		} else {
			$differenzaGiorni = $GLOBALS["dayscooldownchangeusername"] + 1;
		}
		
		if($differenzaGiorni >= $GLOBALS["dayscooldownchangeusername"]) { // Contenuto array: vero/falso, data di ultimo cambio (solo falso), giorni rimasti prima del prossimo cambio (solo falso)
			$risultato = [true, "0000-00-00 00:00:00", 0];
			return $risultato;
		} else {
			$risultato = [false, $riga["Data"], ($GLOBALS["dayscooldownchangeusername"]-abs($differenzaGiorni))];
			return $risultato;
		}
	} else {
		return false;
	}
}

function getUserApplicationStatus($telegramusername) //Restituisce true se l'utente può fare un'altra candidatura, false se è già stata effettuata una prima dello scadere del cooldown (e restituisce la data e i giorni rimasti)

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		$tabella = $GLOBALS["table_applications"];
		$dataoggi = date("Y-m-d H:i:s");
		
		$stmt = $connection -> prepare("SELECT * FROM $tabella WHERE TelegramUsername = ? AND Stato != 0 ORDER BY DataAggiornamento DESC LIMIT 1"); // ULTIMA AGGIORNAMENTO CANDIDATURA
		$stmt->bind_param("i", $telegramusername);
		$stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
		$riga = $result->fetch_assoc();

		if($nrighe > 0) {
			$differenza = strtotime($dataoggi) - strtotime($riga["DataAggiornamento"]);
			$differenzaGiorni = round($differenza / (60 * 60 * 24));
		} else {
			$differenzaGiorni = $GLOBALS["dayscooldownapplication"] + 1;
		}
		
		if($differenzaGiorni >= $GLOBALS["dayscooldownapplication"]) { // Contenuto array: vero/falso, data di invio ultima candidatura (solo falso), data di aggiornamento candidatura (solo falso), giorni rimasti prima della prossima candidatura (solo falso)
			$risultato = [true, "0000-00-00 00:00:00", "0000-00-00 00:00:00", 0];
			return $risultato;
		} else {
			$risultato = [false, $riga["DataInvio"], $riga["DataAggiornamento"], ($GLOBALS["dayscooldownapplication"]-abs($differenzaGiorni))];
			return $risultato;
		}
	} else {
		return false;
	}
}

// ------------------------- FINE FUNZIONI "GET"

// ------------------------- INIZIO FUNZIONI MISTE
function sendMail($mail_scelta, $setfrom, $receiver, $subject, $body, $headers = []) //Manda una mail in base ai parametri forniti

{
    $mail = new PHPMailer(true); // Passing `true` enables exceptions
    $mail->CharSet = 'UTF-8';
    try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtps.aruba.it'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        if ($mail_scelta == "info") {
            $mail->Username = "info@fireshield.it";
            $mail->Password = "MXXwwWzQCb7NDymC";
        } elseif ($mail_scelta == "feedback") {
            $mail->Username = "feedback@fireshield.it";
            $mail->Password = "fbP4JJABSEV76s79";
        } elseif ($mail_scelta == "payments") {
            $mail->Username = "payments@fireshield.it";
            $mail->Password = "9uY3e94e2JfhnFBy";
        } elseif ($mail_scelta == "issues") {
            $mail->Username = "issues@fireshield.it";
            $mail->Password = "6uyvpEw95GEuv2Hk";
        } else {
            $mail->Username = "postmaster@fireshield.it";
            $mail->Password = "f8zByGJBHEprNf9a";
        }
        $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; // TCP port to connect to

        //Recipients
        $mail->Sender = $mail->Username;
        $mail->setFrom($mail->Username, $setfrom);
        $mail->addAddress($receiver);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
		
		foreach($headers as $chiave => $valore) {
			$mail->addCustomHeader($chiave, $valore);
		}
		
        $mail->smtpConnect([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true]]);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

function randomString($lunghezza = 15) // Restituisce una stringa generate casualmente

{
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $lunghezza; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}

function randomPassword($lunghezza = 15) // Restituisce una password generata casualmente

{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@#$%&_-^()[]{}=?!<>.:*';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $lunghezza; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}

function generate2FARecoveryCodes($idutente) // Genera i codici di recupero 2fa

{
    include $GLOBALS["droot"] . "/Config/config.php";
    $connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
    if ($connection) {
		$tabella = $GLOBALS["table_2fa"];
		
		$codici_recupero = array();
		for($i = 0; $i < 10; $i++) {
			$codici_recupero[] = bin2hex(random_bytes(10));
		}
		$codici = serialize($codici_recupero);
		
		$stmt = $connection->prepare("SELECT * FROM $tabella WHERE IDUtente = ?");
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		if($nrighe == 0) {
			$stmt = $connection->prepare("INSERT INTO $tabella (IDUtente, RecoveryCodes) VALUES (?, ?)");
			$stmt->bind_param("is", $idutente, $codici);
			$esito = $stmt->execute();
		} else {
			$stmt = $connection->prepare("UPDATE $tabella SET RecoveryCodes = ? WHERE IDUtente = ?");
			$stmt->bind_param("si", $codici, $idutente);
			$esito = $stmt->execute();
		}
		
		if ($esito) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function has2FARecoveryCodes($idutente) // Restituisce true se l'utente ha codici 2fa, false altrimenti

{
	include $GLOBALS["droot"] . "/Config/config.php";
	$connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
	if ($connection) {
		$tabella = $GLOBALS["table_2fa"];
		
		$stmt = $connection->prepare("SELECT * FROM $tabella WHERE IDUtente = ?");
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		if($nrighe == 0) {
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

function checkValid2FARecoveryCode($idutente, $codice) // Restituisce true se il codice di recupero esiste, false altrimenti

{
	include $GLOBALS["droot"] . "/Config/config.php";
	$connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
	if ($connection) {
		$tabella = $GLOBALS["table_2fa"];
		
		$stmt = $connection->prepare("SELECT * FROM $tabella WHERE IDUtente = ?");
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		if($nrighe == 0) {
			return false;
		} else {
			$riga = $esito->fetch_assoc();
			
			$array_codici = unserialize($riga["RecoveryCodes"]);
			foreach($array_codici as $codice1) {
				if($codice1 == $codice) {
					return true;
				}
			}
			return false;
		}
	} else {
		return false;
	}
}

function remove2FARecoveryCode($idutente, $codice) // Restituisce true se la rimozione è stata completata, false altrimenti

{
	include $GLOBALS["droot"] . "/Config/config.php";
	$connection = new mysqli($GLOBALS["host"], $GLOBALS["usernamedb"], $GLOBALS["password"], $GLOBALS["dbname"]);
	if ($connection) {
		$tabella = $GLOBALS["table_2fa"];
		
		$stmt = $connection->prepare("SELECT * FROM $tabella WHERE IDUtente = ?");
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		if($nrighe == 0) {
			return false;
		} else {
			$riga = $esito->fetch_assoc();
			
			$array_codici = unserialize($riga["RecoveryCodes"]);
			
			$index = array_search($codice, $array_codici);
			if($index !== FALSE){
				unset($array_codici[$index]);
			}
			$codici = serialize($array_codici);
			
			$stmt = $connection->prepare("UPDATE $tabella SET RecoveryCodes = ? WHERE IDUtente = ?");
			$stmt->bind_param("si", $codici, $idutente);
			$esito = $stmt->execute();
			if($esito) {
				return true;
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
}

function validateString($valore) //controlla che la stringa non abbia spazi o simboli

{
    $esito = preg_match('/' . $GLOBALS["string_check_allowed"] . '/', $valore);
    if ($esito == 1) { // tutto ok, non ha trovato valori (non ha trovato simboli eccetto _ e non ha trovato spazi)
        return true;
    } else { // ha trovato dei caratteri non corretti
        return false;
    }
}

function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = '(Sconosciuto)';
    return $ipaddress;
}

function checkBannedWords($stringa) // controlla che la stringa non contenga parole bandite

{
	$stringa = str_replace("4", "a", $stringa);
	$stringa = str_replace("0", "o", $stringa);
	$stringa = str_replace("1", "i", $stringa);
	$stringa = str_replace("3", "e", $stringa);
	$stringa = str_replace("5", "s", $stringa);
	$stringa = str_replace("9", "g", $stringa);
	$stringa = str_replace("8", "b", $stringa);
	$stringa = str_replace("_", "", $stringa);
	
	foreach($GLOBALS["string_check_banned_1"] as $valore) {
		if(stripos($stringa, $valore) !== false) {
			return true;
		}
	}
	
	foreach($GLOBALS["string_check_banned_2"] as $valore) {
		if(stripos($stringa, $valore) !== false) {
			return true;
		}
	}
	
	return false;
}

function formatDate($data, $ore = false) // formatta la data in base alle preferenze dell'utente

{
	$preferenza = getPreference($GLOBALS["id"], "FormatoData");
	if($ore) {
		if($preferenza == 0) {
			return date("d/m/Y H:i", strtotime($data));
		} elseif($preferenza == 1) {
			return date("m/d/Y h:i A", strtotime($data));
		} elseif($preferenza == 2) {
			return date("Y/m/d H:i", strtotime($data));
		} else {
			if(!isLogged()) {
				return date("d/m/Y H:i", strtotime($data));
			} else {
				return "ERROR";
			}
		}
	} else {
		if($preferenza == 0) {
			return date("d/m/Y", strtotime($data));
		} elseif($preferenza == 1) {
			return date("m/d/Y", strtotime($data));
		} elseif($preferenza == 2) {
			return date("Y/m/d", strtotime($data));
		} else {
			if(!isLogged()) {
				return date("d/m/Y H:i", strtotime($data));
			} else {
				return "ERROR";
			}
		}
	}
}

function formatDateComplete($data, $ore = false) // formatta la data in modo completo in base alle preferenza dell'utente

{
	$preferenza = getPreference($GLOBALS["id"], "FormatoData");
	setLocale(LC_TIME, $_SESSION["lang"]);
	if($ore) {
		if($preferenza == 0) {
			return utf8_encode(strftime("%e %B %Y | %H:%M", strtotime($data)));
		} elseif($preferenza == 1) {
			return utf8_encode(strftime("%B %e, %Y | %I:%M %p", strtotime($data)));
		} elseif($preferenza == 2) {
			return utf8_encode(strftime("%Y %B %e | %H:%M", strtotime($data)));
		} else {
			if(!isLogged()) {
				return utf8_encode(strftime("%e %B %Y | %H:%M", strtotime($data)));
			} else {
				return "ERROR";
			}
		}
	} else {
		if($preferenza == 0) {
			return utf8_encode(strftime("%A, %e %B %Y", strtotime($data)));
		} elseif($preferenza == 1) {
			return utf8_encode(strftime("%A, %B %e, %Y", strtotime($data)));
		} elseif($preferenza == 2) {
			return utf8_encode(strftime("%Y %B %e", strtotime($data)));
		} else {
			if(!isLogged()) {
				return utf8_encode(strftime("%e %B %Y | %H:%M", strtotime($data)));
			} else {
				return "ERROR";
			}
		}
	}
}

function sendTelegramMessage($messaggio, $idChat = "-1001413057077") // invia un messaggio di telegram alla chat id del gruppo di log (predefinito)

{
	$token = "765045885:AAG3SSy_QWkRd8XPa4YM9RF8F4iEZ9oFLMo";
	
	$url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $idChat . "&parse_mode=html";
	$url = $url . "&text=" . urlencode($messaggio);
	$ch = curl_init();
	$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true
	);
	curl_setopt_array($ch, $optArray);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function kickGuestUser($ajax = false, $redirect = "/login.php?e=7") // Impedisce l'accesso agli utenti non loggati

{
    if(!isLogged()) {
        if($ajax) {
            echo "error_unlogged";
        } else {
            header("Location:".$redirect);
        }
        exit;
    }
}

function kickOTPUser($ajax = false, $redirect = "/") // Impedisce l'accesso agli utenti OTP (questo controllo si applica agli utenti loggati)

{
    if($GLOBALS["otp"] && isLogged()) {
        if($ajax) {
            echo "error_otp";
        } else {
            header("Location:".$redirect);
        }
        exit;
    }
}

function kickUnlicensedUser($ajax = false, $redirect = "/settings.php") // Impedisce l'accesso agli utenti con licenza scaduta (questo controllo si applica agli utenti loggati)

{
    if(!getLicense($GLOBALS["id"])[7] && isLogged()) {
        if($ajax) {
            echo "error_license";
        } else {
            header("Location:".$redirect);
        }
        exit;
    }
}

function kickLoggedUser($ajax = false, $redirect = "/") // Impedisce l'accesso agli utenti loggati

{
	if(isLogged()) {
		if($ajax) {
			echo "error_logged";
		} else {
			header("Location:".$redirect);
		}
		exit;
	}
}

function kickNonAdminUser($ajax = false, $redirect = "/login.php?e=7") // Impedisce l'accesso agli utenti non amministratori (questo controllo si applica agli utenti loggati)

{
	if($GLOBALS["tipo"] != 1 && isLogged()) {
		if($ajax) {
			echo "error_no_admin";
		} else {
			header("Location:".$redirect);
		}
		exit;
	}
}

function kickNonTwoFactorUser($ajax = false, $redirect = "/settings.php") // Impedisce l'accesso agli utenti senza l'autenticazione a 2 fattori (questo controllo si applica agli utenti loggati)

{	
	if(!hasTwoFactorAuthentication($GLOBALS["id"]) && isLogged()) {
		if($ajax) {
			echo "error_nontwofactor";
		} else {
			header("Location:".$redirect);
		}
		exit;
	}
}
// ------------------------- FINE FUNZIONI MISTE
