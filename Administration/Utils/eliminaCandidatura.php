<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

if(isset($_GET["secrettoken"])) {
	$token = $_GET["secrettoken"];
	if(strlen($token) == 50) {
		$stmt = $connection->prepare("SELECT IDCandidatura, Nome, Cognome FROM $table_applications WHERE SecretToken = ?");
		$stmt->bind_param("s", $token);
		$esito = $stmt->execute();
		if($esito) {
			$result = $stmt->get_result();
			$result = $result->fetch_assoc();
			
			$id_candidatura = $result["IDCandidatura"];
			$nome_candidatura = $result["Nome"];
			$cognome_candidatura = $result["Cognome"];
		}
		
		$stmt = $connection->prepare("DELETE FROM $table_applications WHERE SecretToken = ?");
		$stmt->bind_param("s", $token);
		$esito = $stmt->execute();
		if($esito && $connection->affected_rows == 1) {
			$stmt = $connection->prepare("ALTER TABLE $table_applications AUTO_INCREMENT = 0");
			$esito = $stmt->execute();
			if($esito) {
				echo "✔";
				telegramMSG();
			} else {
				echo "❌";
			}
		}
	} else {
		header("Location:/error.php");
	}
} else {
	header("Location:/error.php");
}

function telegramMSG() {
	// Messaggio principale admin
	sendTelegramMessage("
	📄 <b>APPLICATION DELETED</b> 📄
	___________________________

	Candidate: <b>".$GLOBALS["nome_candidatura"]." ".$GLOBALS["cognome_candidatura"]."</b>
	Application ID: <b>".$GLOBALS["id_candidatura"]."</b>

	Date: <b>".date("d/m/Y H:i:s")."</b>", -339570880);

	// Messaggio headquarter
	sendTelegramMessage("
	📄 <b>APPLICATION DELETED</b> 📄
	___________________________

	Candidate: <b>".$GLOBALS["nome_candidatura"]." ".$GLOBALS["cognome_candidatura"]."</b>
	Application ID: <b>".$GLOBALS["id_candidatura"]."</b>

	Date: <b>".date("d/m/Y H:i:s")."</b>", -1001312169410);	
}