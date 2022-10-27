<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    if (isset($_POST["stringa"])) {
        $stringa = $_POST["stringa"];
        if (empty($stringa)) {
            echo "unset";
        } else {
			$stringa = str_replace("4", "a", $stringa);
			$stringa = str_replace("0", "o", $stringa);
			$stringa = str_replace("1", "i", $stringa);
			$stringa = str_replace("3", "e", $stringa);
			$stringa = str_replace("5", "s", $stringa);
			$stringa = str_replace("9", "g", $stringa);
			$stringa = str_replace("8", "b", $stringa);
			$stringa = str_replace("_", "", $stringa);
			
			foreach($string_check_banned_1 as $valore) {
				if(stripos($stringa, $valore) !== false) {
					echo "found";
					exit;
				}
			}
			
			foreach($string_check_banned_2 as $valore) {
				if(stripos($stringa, $valore) !== false) {
					echo "found";
					exit;
				}
			}
			
			echo "not_found";
        }
    } else {
        echo "unset";
    }
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>