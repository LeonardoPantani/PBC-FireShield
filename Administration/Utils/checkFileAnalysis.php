<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
    $nfile = new FilesystemIterator("$droot/" . $folderanalysis, FilesystemIterator::SKIP_DOTS);
    echo iterator_count($nfile);
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}
?>
