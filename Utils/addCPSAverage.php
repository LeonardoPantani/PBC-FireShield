<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

$id = 0;
readUserData($id, $email, $username, $tipo, $network, $otp);

if(isset($_POST["mediacps"])) {
	if(empty($_POST["mediacps"])) {
		echo "unset";
	} else {
		$mediacps = mysqli_real_escape_string($connection, $_POST["mediacps"]);
		if($mediacps <= 17 && $mediacps > 0) {	
			$stmt = $connection->prepare("INSERT INTO $table_cpstest (IDUtente, Media) VALUES (?, ?)");
			$stmt->bind_param("id", $id, $mediacps);
			$esito = $stmt->execute();
			if($esito) {
				echo "success";
			} else {
				echo "error";
			}
		} else {
			echo "invalid";
		}
	}
} else {
	echo "unset";
}
?>