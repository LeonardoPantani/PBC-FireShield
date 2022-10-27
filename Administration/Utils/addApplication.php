<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaSoftware")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
		$cartella = "../../Files/Software/Applications/";
		$nomefile = basename($_FILES["file"]["name"]);
		$nome_completo = $cartella . basename($_FILES["file"]["name"]);
		$estensione = strtolower(pathinfo($nomefile,PATHINFO_EXTENSION));
		
		if (move_uploaded_file($_FILES["file"]["tmp_name"], $nome_completo)) {
			$rootPath = realpath('../../Files/Software/Applications/');
			$zip = new ZipArchive();
			$zip->open('../../Files/Software/Applications.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($rootPath),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			foreach ($files as $name => $file)
			{
				if (!$file->isDir())
				{
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen($rootPath) + 1);
					$zip->addFile($filePath, $relativePath);
				}
			}
			$zip->close();
			echo "success";
		} else {
			echo "error_generic";
		}
	} else {
		echo "error_upload_fail";
	}
} else { // fine controllo se richiesta in ajax
    echo "<p>Mi dispiace, questa pagina è riservata.</p>";
    echo "<p>Torna alla Homepage: ";?><button type="button" onClick="location.href='/'">Homepage</button><?php echo "</p>";
    exit;
}
?>