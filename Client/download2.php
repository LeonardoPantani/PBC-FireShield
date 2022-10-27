<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";

if(isset($_GET["tipofile"])) {
	switch($_GET["tipofile"]) {
		case 1:
			$file = "HashDatabase.txt";
		break;
		
		case 2:
			$file = "LegitException.txt";
		break;
		
		case 3:
			$file = "ModDatabase.txt";
		break;
		
		case 4:
			$file = "VersionDatabase.txt";
		break;
		
		case 5:
			$file = "BadProcess.txt";
		break;
		
		case 6:
			$file = "DllDatabase.txt";
		break;
		
		case 7:
			$file = "ClassDatabase.txt";
		break;
		
		case 8:
			$file = "DownloadDatabase.txt";
		break;
		
		case 9:
			$file = "WebsiteDatabase.txt";
		break;
		
		case 10:
			$file = "CheatSmasherDatabase.txt";
		break;
		
		default:
			echo "file_invalid";
			exit;
		break;
	}
} else {
	echo "file_invalid_get";
}

$nome_completo = "../Files/Software/db/".$file;
if (file_exists($nome_completo)) {
	$file = fopen($nome_completo, "r");
	echo fgets($file);
	fclose($file);
	exit;
} else {
	echo "file_invalid_not_found";
	exit;
}
