<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();

licenseInformation($licenza);
networkInformation($infonetwork);

$stmt = $connection->prepare("SELECT ID FROM $table_users WHERE AggiornaPassword = 1 AND ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
if($nrighe == 0) {
	header("Location:/");
	exit;
}
// -----------------------------

if (isset($_POST["password1"]) && isset($_POST["password2"])) {
    if(($_POST["password1"] == $_POST["password2"]) && (!empty($_POST["password1"]) && !empty($_POST["password2"]))) {
		$stmt = $connection -> prepare("SELECT Password FROM $table_users WHERE ID = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result -> num_rows;
		if($nrighe > 0) {
			$riga = $result -> fetch_assoc();
			if(!password_verify($_POST["password1"], $riga["Password"])) {
				$passwordhash = password_hash($_POST["password1"], PASSWORD_DEFAULT);
				$stmt = $connection->prepare("UPDATE $table_users SET Password = ?, AggiornaPassword = 0 WHERE ID = ?");
				$stmt->bind_param("si", $passwordhash, $id);
				$esito = $stmt->execute();
				if($esito) {
					header("Location:/"); // successo
					exit;
				} else {
					header("Location:updatePassword.php?e=3"); // errore update
					exit;
				}
			} else { 
				header("Location:updatePassword.php?e=4"); // password nuova uguale a quella vecchia
				exit;
			}
		}
    } else {
        header("Location:updatePassword.php?e=2"); // le password non corrispondono e non devono essere vuote
        exit;
    }
} else {
    header("Location:updatePassword.php?e=1"); // post non ricevuto 
    exit;
}
