<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickUnlicensedUser(true);
// -----------------------------

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if ($analysis || (!$analysis && in_array(getClientIP(), $whitelisted_ips))) {
        if (($loginrequired && isLogged()) || !$loginrequired) {
            if (($_SESSION["scansioni_effettuate"] < 4 && $otp) || !$otp) {
                if (isset($_FILES["file"], $_POST["controllo"])) { // controllo se il file e il tipo di controllo sono settati
                    $idutente = $_POST["idutente"];
                    $datainizio = new DateTime("now");

                    $file = $_FILES["file"];
                    $nomecompleto = $_FILES["file"]["name"];
                    $nometemporaneo = $_FILES["file"]["tmp_name"];
                    $pesofile = $_FILES["file"]["size"];
                    $estensione = PATHINFO($nomecompleto, PATHINFO_EXTENSION);
                    $nome = PATHINFO($nomecompleto, PATHINFO_FILENAME);
                    $trovata = false;

                    if ($file['error'] != UPLOAD_ERR_OK) { // file non caricato
                        // errore upload
                        insertHistoryAnalyzer(0, $_POST["controllo"], 10);
                        $connection->close();
                        echo "error_upload";
                        exit;
                    }

                    if ($pesofile > $pesomax) {
                        // pesa troppo
                        insertHistoryAnalyzer(0, $_POST["controllo"], 11);
                        $connection->close();
                        echo "error_size";
                        exit;
                    }

                    if ($estensione != "zip") {
                        // non è del tipo corretto
                        insertHistoryAnalyzer(0, $_POST["controllo"], 12);
                        $connection->close();
                        echo "error_extension";
                        exit;
                    }

                    // --------------- OLTRE QUI SI PRESUME CHE IL FILE SIA CARICATO CON SUCCESSO
					$nomifile = array_diff(scandir($droot."/".$folderanalysis), array('.', '..'));
					$univoco = false;
					while(!$univoco) {
						$random = randomString(50);
						if(!in_array($random, $nomifile)) {
							$univoco = true;
						}
					}
					
					$zip = new ZipArchive;
					$esito = $zip->open($nometemporaneo);
					if ($esito !== TRUE) {
						insertHistoryAnalyzer(0, $_POST["controllo"], 3);
						$connection->close();
						echo "error_unzip";
						exit;
					}
					
					$esito = $zip->renameName($nome.".txt", $random.".txt");
					if(!$esito) {
                        insertHistoryAnalyzer(0, $_POST["controllo"], 5);
                        $connection->close();
                        echo "error_rename";
                        exit;	
					}
					
					$nome = $random;
					
					$esito = $zip->extractTo("$folderanalysis/", $nome . ".txt");
					if (!$esito) {
						insertHistoryAnalyzer(0, $_POST["controllo"], 4);
						$connection->close();
						echo "error_extract";
						exit;
					}
					$zip->close();

					$peso_file = filesize("$folderanalysis/$nome.txt");

                    $file = fopen("$folderanalysis/$nome.txt", "r");
                    if (!$file) {
                        insertHistoryAnalyzer(0, $_POST["controllo"], 6);
                        $connection->close();
                        echo "error_open";
                        exit;
                    }

                    // -------------------------- VERIFICO SE IL FILE E' DI PROCESS HACKER
                    if ($ph_check === true) {
                        $i = 0;
                        $verificato = false;
                        while (($rigafile = fgets($file)) !== false && $i < 3) {
                            if (strpos($rigafile, "Process Hacker") !== false) {
                                $verificato = true;
                            } else {
                                $i++;
                            }
                        }
                        if (!$verificato) {
                            insertHistoryAnalyzer(0, $_POST["controllo"], 13);
                            removeFile($file, $nome, $_POST["controllo"]);
                            $connection->close();
                            echo "error_invalid";
                            exit;
                        }
                        rewind($file);
                    }
					
					session_write_close();

                    // -------------------------- 1° controllo (stringhe cheat javaw)
                    if ($controllo1 === true) {
                        if ($_POST["controllo"] == 1) {
                            $query = "SELECT Stringa FROM $table_cheatjava";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, $_POST["controllo"], 7);
                                $connection->close();
                                echo "error_getstring";
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovata = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_cheatjava);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, $_POST["controllo"], 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovata) {
                                $id_scansione = insertHistoryAnalyzer(3, $_POST["controllo"], 0, $valore);
                                removeFile($file, $nome, $_POST["controllo"], 3, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_cheatjava WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_cheat:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }

                    // -------------------------- 2° controllo (stringhe cheat dwm)
                    if ($controllo2 === true) {
                        if ($_POST["controllo"] == 2) {
                            $query = "SELECT Stringa FROM $table_cheatdwm";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, $_POST["controllo"], 7);
                                $connection->close();
                                echo "error_getstring";
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovata = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_cheatdwm);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, $_POST["controllo"], 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovata) {
                                $id_scansione = insertHistoryAnalyzer(3, $_POST["controllo"], 0, $valore);
                                removeFile($file, $nome, $_POST["controllo"], 3, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_cheatdwm WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_cheat:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }

                    // -------------------------- 3° controllo (stringhe cheat msmpeng)
                    if ($controllo3 === true) {
                        if ($_POST["controllo"] == 3) {
                            $query = "SELECT Stringa FROM $table_cheatmsmpeng";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, $_POST["controllo"], 7);
                                $connection->close();
                                echo "error_getstring";
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovata = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_cheatmsmpeng);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, $_POST["controllo"], 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovata) {
                                $id_scansione = insertHistoryAnalyzer(3, $_POST["controllo"], 0, $valore);
                                removeFile($file, $nome, $_POST["controllo"], 3, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_cheatmsmpeng WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_cheat:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }

                    // -------------------------- 4° controllo (stringhe cheat lsass)
                    if ($controllo4 === true) {
                        if ($_POST["controllo"] == 4) {
                            $query = "SELECT Stringa FROM $table_cheatlsass";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, $_POST["controllo"], 7);
                                $connection->close();
                                echo "error_getstring";
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovata = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_cheatlsass);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, $_POST["controllo"], 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovata) {
                                $id_scansione = insertHistoryAnalyzer(3, $_POST["controllo"], 0, $valore);
                                removeFile($file, $nome, $_POST["controllo"], 3, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_cheatlsass WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_cheat:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }

                    // -------------------------- 5° controllo (stringhe sospette java)
                    if ($controllo5 === true) {
                        if ($_POST["controllo"] == 1) {
                            $trovatasospetta = false;
                            $query = "SELECT Stringa FROM $table_suspectjava";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, 5, 7);
                                $connection->close();
                                echo "error_getstring";
                                $connection->close();
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

                                rewind($file); // Reset della posizione del lettore file (dal controllo 1)
								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovatasospetta = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_suspectjava);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, 5, 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovatasospetta) {
                                $id_scansione = insertHistoryAnalyzer(2, 5, 0, $valore);
                                removeFile($file, $nome, 5, 2, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_suspectjava WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_suspect:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }
					
                    // -------------------------- 6° controllo (stringhe sospette dwm)
                    if ($controllo6 === true) {
                        if ($_POST["controllo"] == 2) {
                            $trovatasospetta = false;
                            $query = "SELECT Stringa FROM $table_suspectdwm";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, 6, 7);
                                $connection->close();
                                echo "error_getstring";
                                $connection->close();
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

                                rewind($file); // Reset della posizione del lettore file (dal controllo 2)
								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovatasospetta = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_suspectdwm);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, 6, 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovatasospetta) {
                                $id_scansione = insertHistoryAnalyzer(2, 6, 0, $valore);
                                removeFile($file, $nome, 6, 2, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_suspectdwm WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_suspect:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }
					
                    // -------------------------- 7° controllo (stringhe sospette msmpeng)
                    if ($controllo7 === true) {
                        if ($_POST["controllo"] == 3) {
                            $trovatasospetta = false;
                            $query = "SELECT Stringa FROM $table_suspectmsmpeng";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, 7, 7);
                                $connection->close();
                                echo "error_getstring";
                                $connection->close();
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

                                rewind($file); // Reset della posizione del lettore file (dal controllo 3)
								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovatasospetta = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_suspectmsmpeng);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, 7, 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovatasospetta) {
                                $id_scansione = insertHistoryAnalyzer(2, 7, 0, $valore);
                                removeFile($file, $nome, 7, 2, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_suspectmsmpeng WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_suspect:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }
					
                    // -------------------------- 8° controllo (stringhe sospette lsass)
                    if ($controllo8 === true) {
                        if ($_POST["controllo"] == 4) {
                            $trovatasospetta = false;
                            $query = "SELECT Stringa FROM $table_suspectlsass";
                            $stmt = $connection->prepare($query);
                            $stmt->execute();
                            $esito = $stmt->get_result();
                            $nrighe = $esito->num_rows;
                            if (!$esito) {
                                insertHistoryAnalyzer(0, 8, 7);
                                $connection->close();
                                echo "error_getstring";
                                $connection->close();
                                exit;
                            }
                            if ($nrighe > 0) {
                                while ($r = $esito->fetch_array()) {
                                    $riga[] = $r[0];
                                }

                                rewind($file); // Reset della posizione del lettore file (dal controllo 4)
								while(!feof($file)) {
									$rigafile = fread($file, filesize("$folderanalysis/$nome.txt"));
                                    foreach ($riga as $valore) {
                                        if (strpos($rigafile, $valore) !== false) {
                                            $trovatasospetta = true;
                                            $data = date("y-m-d H:m:s", time());
                                            $esito = insertHistoryCheat($valore, $data, $table_suspectlsass);
                                            if (!$esito) {
                                                insertHistoryAnalyzer(0, 8, 8); // Errore salvataggio cronologia
                                                $connection->close();
                                                echo "error_historycheat";
                                                exit;
                                            }
                                            break 2; //esco da entrambi i loop
                                        }
                                    }
								}
                            }
                            if ($trovatasospetta) {
                                $id_scansione = insertHistoryAnalyzer(2, 8, 0, $valore);
                                removeFile($file, $nome, 8, 2, $id_scansione);
                                $stmt = $connection->prepare("SELECT Client FROM $table_suspectlsass WHERE Stringa = ?");
                                $stmt->bind_param("s", $valore);
                                $stmt->execute();
                                $esito = $stmt->get_result();
                                $risultato = $esito->fetch_assoc();
                                $connection->close();
                                echo "result_suspect:" . $id_scansione . ":" . $risultato["Client"];
                                exit;
                            }
                        }
                    }
					// SE NON VIENE TROVATA NESSUNA STRINGA SI RESTITUISCE CLEAN
                    $id_scansione = insertHistoryAnalyzer(1, $_POST["controllo"]);
                    removeFile($file, $nome, $_POST["controllo"], 1, $id_scansione);
                    $connection->close();
                    echo "result_clean";
                    exit;
                } else { // se non sono settati tutti i valori
                    insertHistoryAnalyzer(0, $controllo, 2);
                    $connection->close();
                    echo "error_unset";
                    exit;
                }
            } else { // fine controllo limite scansioni OTP
                $connection->close();
                echo "error_max_otp";
                exit;
            }
        } else { // fine controllo $loginrequired in config
            $connection->close();
            echo "error_loginrequired";
            exit;
        }
    } else { // fine controllo $analysis in config
        $connection->close();
        echo "error_unavailable";
        exit;
    }
} else { // fine controllo se richiesta in ajax
    header("Location:/error.php");
}

function removeFile($fileaperto, $nomefile, $controllo, $esitoscansione = 0, $idscansione = 0) // esito: 0 - errore | 1 - pulito | 2 - sospetto | 3 - cheat

{
    // --- CHIUSURA DEL FILE
    $folderanalysis = $GLOBALS["folderanalysis"];
    $esito = fclose($fileaperto);
    if (!$esito) {
        insertHistoryAnalyzer(0, $controllo, 1);
        $connection->close();
        echo "error_remove";
        exit;
    }

    // --- COPIA DEL FILE PER CRONOLOGIA SCANSIONE
    if ($GLOBALS["keepscanfiles"] && $GLOBALS["tipo"] != 1) {
        $dataformattata = $GLOBALS["datainizio"]->format("Y-m-d-H-i-s");
        if (isset($GLOBALS["idutente"])) {
            $valore_utente = $GLOBALS["idutente"];
        } else {
            $valore_utente = 0;
        }
		
		rename($GLOBALS["folderanalysis"] . "/" . $GLOBALS["nome"] . ".txt", $GLOBALS["folderhistory"] . "/" . $valore_utente . "_" . $dataformattata . "_" . $controllo . "_" . $esitoscansione . "_" . $idscansione . ".txt");
        /* Formato finale del file: idutente_dataformattata_tipofilescansionato_esitoscansione_idscansione.txt */
    } else {
		unlink($GLOBALS["folderanalysis"] . "/" . $GLOBALS["nome"] . ".txt");
	}
}

function insertHistoryAnalyzer($esito, $check, $codiceerrore = 0, $stringatrovata = "NULLO")
{
    $idutente = $GLOBALS["idutente"];
    $datainizio = $GLOBALS["datainizio"];
    $tabella = $GLOBALS["table_historyanalyzer"];
    $datafine = new DateTime("now");
    $data = $datainizio->format("Y-m-d H:i:s");

    $differenza = date_diff($datainizio, $datafine);
    $durata = $differenza->format("%H:%I:%S");
    $check = intval($check);

    $indirizzoip = getClientIP();
    if ($GLOBALS["otp"] == true) {
        $isotp = 1;
    } else {
        $isotp = 0;
    }

    if ($stringatrovata == "NULLO") {
        $stmt = $GLOBALS["connection"]->prepare("INSERT INTO $tabella (Esito, Data, Durata, Controllo, IDUtente, CodiceErrore, OTPUsato, IndirizzoIP, PesoFile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiiiisi", $esito, $data, $durata, $check, $idutente, $codiceerrore, $isotp, $indirizzoip, $GLOBALS["pesofile"]);
        $esito = $stmt->execute();
    } else {
        $stmt = $GLOBALS["connection"]->prepare("INSERT INTO $tabella (Esito, Data, Durata, Controllo, IDUtente, CodiceErrore, StringaTrovata, OTPUsato, IndirizzoIP, PesoFile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiiisisi", $esito, $data, $durata, $check, $idutente, $codiceerrore, $stringatrovata, $isotp, $indirizzoip, $GLOBALS["pesofile"]);
        $esito = $stmt->execute();
    }

    if (!$esito) {
        $GLOBALS["connection"]->close();
        echo "error_historyanalyzer";
        exit;
    }
    $ultimo_id = $GLOBALS["connection"]->insert_id;
    if ($esito != 0 && $GLOBALS["otp"]) {
		$_SESSION["scansioni_effettuate"]++;
		$tabella = $GLOBALS["table_otpsessions"];
		$stmt = $GLOBALS["connection"]->prepare("UPDATE $tabella SET ScansioniEseguite = ScansioniEseguite + 1 WHERE IDUtente = ?");
		$stmt->bind_param("i", $idutente);
		$stmt->execute();
    }
    return $ultimo_id;
}

function insertHistoryCheat($valore, $data, $tabella)
{
    $tabellacheat = $GLOBALS['table_historycheat'];

    $query = "SELECT Stringa, Quantita FROM $tabellacheat WHERE Stringa = ?";
    $stmt = $GLOBALS["connection"]->prepare($query);
    $stmt->bind_param("s", $valore);
    $esito = $stmt->execute();
    if (!$esito) {
        return false;
    }
    $result = $stmt->get_result();
    $nrighe = $result->num_rows;
    $result = $result->fetch_assoc();
    if ($nrighe == 0) { // se non è mai stata inserita la metto
        $query = "INSERT INTO $tabellacheat (Stringa, Data, Tabella, Quantita) VALUES (?, ?, ?, 1)";
        $stmt = $GLOBALS["connection"]->prepare($query);
        $stmt->bind_param("sss", $valore, $data, $tabella);
        $esito = $stmt->execute();

        if (!$esito) {
            return false;
        } else {
            return true;
        }
    } else { // altrimenti incremento quantità
        $quantita = $result["Quantita"]; //per non fare una query aggiuntiva
        $quantita = $quantita + 1;
        $data = new DateTime("now");
        $data = $data->format("Y-m-d H:i:s");

        $query = "UPDATE $tabellacheat SET Quantita = ?, Data = ? WHERE Stringa = ?";
        $stmt = $GLOBALS["connection"]->prepare($query);
        $stmt->bind_param("iss", $quantita, $data, $valore);
        $esito = $stmt->execute();
        if (!$esito) {
            return false;
        } else {
            return true;
        }
    }
}