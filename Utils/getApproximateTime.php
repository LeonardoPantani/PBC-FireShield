<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if(isset($_POST["pesofile"], $_POST["tipocontrollo"])) {
		$stmt = $connection->prepare("SELECT Durata, PesoFile FROM $table_historyanalyzer WHERE PesoFile != 0 AND Controllo = ? AND Esito = 1");
		$stmt->bind_param("i", $_POST["tipocontrollo"]);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$array_pesi = array();
		$array_durate = array();
		while($riga = $result->fetch_assoc()) {
			$v1 = $riga["PesoFile"];
			$v2 = $riga["Durata"];
			array_push($array_pesi, $v1);
			array_push($array_durate, $v2);
		}
		
		$numero = getClosest($_POST["pesofile"], $array_pesi);
		$chiave = array_search($numero, $array_pesi);
		
		$migliore = $array_durate[$chiave];
		echo $migliore;
	} else {
		echo "unset";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}

function getClosest($search, $arr) {
   $closest = null;
   foreach ($arr as $item) {
      if ($closest === null || abs($search - $closest) > abs($item - $search)) {
         $closest = $item;
      }
   }
   return $closest;
}