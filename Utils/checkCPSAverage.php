<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	$lineefile = file("../Functions/Task/esito.txt");
	if($lineefile[3] != "") {
		echo $lineefile[3];
	} else {
		echo "???";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>