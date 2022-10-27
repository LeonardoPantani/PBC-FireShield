<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

if(isset($_GET["t"])) {
	$stmt = $connection->prepare("SELECT * FROM $table_datarequests WHERE DownloadToken = ?");
	$stmt->bind_param("s", $_GET["t"]);
	$stmt->execute();
	$esito = $stmt->get_result();
	$result = $esito->fetch_assoc();
	$nrighe = $esito->num_rows;
	
	if($nrighe > 0) {
		$dataoggi = date("Y-m-d H:i:s");
		$differenza = abs(strtotime($dataoggi) - strtotime($result["Data"]));
		$years = floor($differenza / (365*60*60*24));
		$months = floor(($differenza - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($differenza - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		
		// CONTROLLO SCADENZA FILE
		if($days <= $daysbeforedeletedatarequests) {
			$stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
			$stmt->bind_param("i", $result["IDUtente"]);
			$stmt->execute();
			$esito = $stmt->get_result();
			$resultUsername = $esito->fetch_assoc();
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="Data Request ('.$resultUsername["Username"].').txt"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize("../Files/DataRequests/DataRequest_".$result["IDRichiesta"].".txt"));
			readfile("../Files/DataRequests/DataRequest_".$result["IDRichiesta"].".txt");
		} else {
			echo "This Token has expired.";
		}
	} else {
		echo "Invalid Token";
	}
} else {
	echo "Invalid Request";
}
?>