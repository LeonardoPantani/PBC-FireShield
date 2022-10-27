<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";

if(isset($_GET["tipofile"])) {
	switch($_GET["tipofile"]) {
		case 1:
			$file = "versioni.txt";
		break;
		
		case 2:
			$file = "versionisoftware.txt";
		break;
		
		case 3:
			$file = "versionecorrentesoftware.txt";
		break;
		
		case 4:
			$file = "Applications.zip";
		break;
		
		case 5:
			$file = "FireShield Cheat Detector.rar";
		break;
		
		default:
			echo "file_invalid";
			exit;
		break;
	}
} else {
	$file = "FireShield Cheat Detector.rar";
}

$nome_completo = "../Files/Software/".$file;
if (file_exists($nome_completo)) {
	if(!isset($_GET["tipofile"]) || $_GET["tipofile"] == 4 || $_GET["tipofile"] == 5) { //se vuoi scaricare il software (senza specificare valori), o per scaricare applications.zip
		if($_GET["tipofile"] == 5 && $softwaredownload == false) { 
			echo "not_allowed";
		} else {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($nome_completo).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($nome_completo));
			readfile($nome_completo);
		}
	} else {
		$file = fopen($nome_completo, "r");
		echo fgets($file);
		fclose($file);
	}
	exit;
} else {
	echo "file_invalid";
	exit;
}
