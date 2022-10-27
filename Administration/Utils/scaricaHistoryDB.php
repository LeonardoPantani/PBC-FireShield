<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if(isset($_POST["selectfile"])) { // ------------------- PARTE POST (DOWNLOAD CRONOLOGIA)
	$divisione = explode("_", $_POST["selectfile"]);
	$data = explode("-", $divisione[1]);
	 switch($divisione[2]) {
		case 1:
			$divisione[2] = $table_cheatjava;
			break;
		case 2:
			$divisione[2] = $table_cheatdwm;
			break;
		case 3:
			$divisione[2] = $table_cheatmsmpeng;
			break;
		case 4:
			$divisione[2] = $table_cheatlsass;
			break;
		case 5:
			$divisione[2] = $table_suspectjava;
			break;
		case 6:
			$divisione[2] = $table_suspectdwm;
			break;
		case 7:
			$divisione[2] = $table_suspectmsmpeng;
			break;
		case 8:
			$divisione[2] = $table_suspectlsass;
			break;
		default:
			break;
	}
	
	switch ($divisione[3]) {
		case 0:
			$divisione[3] = "Errore";
			break;
		case 1:
			$divisione[3] = "Pulito";
			break;
		case 2:
			$divisione[3] = "Sospetto";
			break;
		case 3:
			$divisione[3] = "Cheat";
			break;
		default:
			$divisione[3] = "???";
			break;
	}
	$nome_completo = $droot.$folderhistory."/".$_POST["selectfile"];
	$estensione = explode(".", $_POST["selectfile"]);
	$idscan = explode(".", $divisione[4]);
	if($estensione[1] == "txt") {
		// SELEZIONE TIPO DOWNLOAD
		if(isset($_POST["mode"])) {
			$nome_archivio = str_replace(".txt", ".zip", $nome_completo);
			$zip = new ZipArchive();
			if ($zip->open($nome_archivio, ZipArchive::CREATE) === true) {
				$zip->addFile($nome_completo, basename($nome_completo));
				$zip->close();
			}
			unlink($nome_completo);
			
			// PREPARAZIONE HEADER
			header('Content-Description: File Transfer');
			header('Content-Type: application/zip, application/octet-stream, application/x-zip-compressed, multipart/x-zip');
			header('Content-Disposition: attachment; filename="(ID '.$divisione[0].') Cronologia '.$data[0].'-'.$data[1].'-'.$data[2].' -- '.$data[3].' '.$data[4].' '.$data[5].' -- '.$divisione[2].' -- '.$divisione[3].' -- IDScan '.$idscan[0].'.zip');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($nome_archivio));
			
			if($_POST["mode"] == "NORMAL") {
				readfile($nome_archivio);
				ob_flush();
			} elseif($_POST["mode"] == "COMPATIBILITY") {
				$grandezzaChunk = 3 * (1024 * 1024);
				$fd = fopen($nome_archivio, 'rb');
				while (!feof($fd)) {   
					$buffer = fread($fd, $grandezzaChunk);
					echo $buffer;
					ob_flush();
				}
				fclose($fd);
			} else {
				echo "error_post_2";
			}
		}
	} elseif($estensione[1] == "zip") {
		// PREPARAZIONE HEADER
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip, application/octet-stream, application/x-zip-compressed, multipart/x-zip');
		header('Content-Disposition: attachment; filename="(ID '.$divisione[0].') Cronologia '.$data[0].'-'.$data[1].'-'.$data[2].' -- '.$data[3].' '.$data[4].' '.$data[5].' -- '.$divisione[2].' -- '.$divisione[3].' -- IDScan '.$idscan[0].".".$estensione[1].'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($nome_completo));
		
		// SELEZIONE TIPO DOWNLOAD
		if(isset($_POST["mode"])) {
			if($_POST["mode"] == "NORMAL") {
				readfile($nome_completo);
				ob_flush();
			} elseif($_POST["mode"] == "COMPATIBILITY") {
				$grandezzaChunk = 3 * (1024 * 1024);
				$fd = fopen($nome_completo, 'rb');
				while (!feof($fd)) {   
					$buffer = fread($fd, $grandezzaChunk);
					echo $buffer;
					ob_flush();
				}
				fclose($fd);
			} else {
				echo "error_post_2";
			}
		}
	} else {
		echo "invalid_extension";
	}
} elseif(isset($_GET["idscansione"])) { // ------------------- PARTE GET (solo per lista cronologia analyzer)
	$nomifile = array_diff(scandir($droot."/".$folderhistory), array('.', '..'));
	sort($nomifile, SORT_STRING);
	
	$trovato = false;
	foreach($nomifile as $valore) {
		$divisione = explode("_", $valore);
		$idscan = explode(".", $divisione[4])[0];
		if($idscan == $_GET["idscansione"]) {
			$trovato = true;
			$nome_completo = $droot.$folderhistory."/".$valore;
		}
	}
	$nomefile = basename($nome_completo);
	$divisione = explode("_", $nomefile);
	$estensione = explode(".", $divisione[4]);

	if($trovato) {
		$divisione = explode("_", $nomefile);
		$data = explode("-", $divisione[1]);
		 switch($divisione[2]) {
			case 1:
				$divisione[2] = $table_cheatjava;
				break;
			case 2:
				$divisione[2] = $table_cheatdwm;
				break;
			case 3:
				$divisione[2] = $table_cheatmsmpeng;
				break;
			case 4:
				$divisione[2] = $table_cheatlsass;
				break;
			case 5:
				$divisione[2] = $table_suspectjava;
				break;
			case 6:
				$divisione[2] = $table_suspectdwm;
				break;
			case 7:
				$divisione[2] = $table_suspectmsmpeng;
				break;
			case 8:
				$divisione[2] = $table_suspectlsass;
				break;
			default:
				break;
		}
		
		switch ($divisione[3]) {
			case 0:
				$divisione[3] = "Errore";
				break;
			case 1:
				$divisione[3] = "Pulito";
				break;
			case 2:
				$divisione[3] = "Sospetto";
				break;
			case 3:
				$divisione[3] = "Cheat";
				break;
			default:
				$divisione[3] = "???";
				break;
		}
		if($estensione[1] == "txt") {
			// SELEZIONE TIPO DOWNLOAD
			if(isset($_GET["mode"])) {
				$nome_archivio = str_replace(".txt", ".zip", $nome_completo);
				$zip = new ZipArchive();
				if ($zip->open($nome_archivio, ZipArchive::CREATE) === true) {
					$zip->addFile($nome_completo, basename($nome_completo));
					$zip->close();
				}
				unlink($nome_completo);
				
				// PREPARAZIONE HEADER
				header('Content-Description: File Transfer');
				header('Content-Type: application/zip, application/octet-stream, application/x-zip-compressed, multipart/x-zip');
				header('Content-Disposition: attachment; filename="(ID '.$divisione[0].') Cronologia '.$data[0].'-'.$data[1].'-'.$data[2].' -- '.$data[3].' '.$data[4].' '.$data[5].' -- '.$divisione[2].' -- '.$divisione[3].' -- IDScan '.$idscan[0].'.zip');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($nome_archivio));
				
				if($_GET["mode"] == "NORMAL") {
					readfile($nome_archivio);
					ob_flush();
				} elseif($_GET["mode"] == "COMPATIBILITY") {
					$grandezzaChunk = 3 * (1024 * 1024);
					$fd = fopen($nome_archivio, 'rb');
					while (!feof($fd)) {   
						$buffer = fread($fd, $grandezzaChunk);
						echo $buffer;
						ob_flush();
					}
					fclose($fd);
				} else {
					echo "error_post_2";
				}
			}
		} elseif($estensione[1] == "zip") {
			// PREPARAZIONE HEADER
			header('Content-Description: File Transfer');
			header('Content-Type: application/zip, application/octet-stream, application/x-zip-compressed, multipart/x-zip');
			header('Content-Disposition: attachment; filename="(ID '.$divisione[0].') Cronologia '.$data[0].'-'.$data[1].'-'.$data[2].' -- '.$data[3].' '.$data[4].' '.$data[5].' -- '.$divisione[2].' -- '.$divisione[3].' -- IDScan '.$idscan[0].".".$estensione[1].'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($nome_completo));
			
			// SELEZIONE TIPO DOWNLOAD
			if(isset($_GET["mode"])) {
				if($_GET["mode"] == "NORMAL") {
					readfile($nome_completo);
					ob_flush();
				} elseif($_GET["mode"] == "COMPATIBILITY") {
					$grandezzaChunk = 3 * (1024 * 1024);
					$fd = fopen($nome_completo, 'rb');
					while (!feof($fd)) {   
						$buffer = fread($fd, $grandezzaChunk);
						echo $buffer;
						ob_flush();
					}
					fclose($fd);
				} else {
					echo "error_post_2";
				}
			}
		} else {
			echo "invalid_extension";
		}
	} else {
		echo "not_found";
	}

} else {
	echo "error_post_1";
}
?>