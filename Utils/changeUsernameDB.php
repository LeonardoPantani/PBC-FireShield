<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if(isset($_POST["usernamenuovo"])) {
		$esito_cambiousername = getUserUsernameChange($id);
		if(!$esito_cambiousername[0]) {
			echo "error_too_early";
			exit;
		}

		$usernamenuovo = $_POST["usernamenuovo"];
		if(strlen($usernamenuovo) < $username_minlength || strlen($usernamenuovo) > $username_maxlength) {
			echo "length";
			exit;
		}

		$usernamenuovo = filter_var($usernamenuovo, FILTER_SANITIZE_STRING);
		if(preg_match($string_check_not_allowed, $usernamenuovo)) {
			echo "regex";
			exit;
		}

		if(checkBannedWords($usernamenuovo)) {
			echo "banned_word";
			exit;
		}

		$stmt = $connection -> prepare("SELECT ID FROM $table_users WHERE Username = ?");
		$stmt -> bind_param("s", $usernamenuovo);
		$stmt -> execute();
		$esito = $stmt->get_result();
		$nrighe = $stmt->num_rows;
		if($nrighe > 0) {
			echo "already_exists";
			exit;
		}

		$stmt = $connection -> prepare("UPDATE $table_users SET Username = ? WHERE ID = ?");
		$stmt -> bind_param("si", $usernamenuovo, $id);
		$esito = $stmt -> execute();
		if($esito) {
			$data_attuale = date("Y-m-d H:i:s");
			$stmt = $connection -> prepare("INSERT INTO $table_usernamechange (IDUtente, Data, UsernamePrecedente, UsernameNuovo) VALUES (?, ?, ?, ?)");
			$stmt -> bind_param("isss", $id, $data_attuale, $username, $usernamenuovo);
			$stmt -> execute();
			$_SESSION["username"] = $usernamenuovo;
			echo "success";
		} else {
			echo "failure";
		}

	} else {
		echo "unset";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>