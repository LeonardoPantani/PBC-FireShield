<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

if (!getPermission($id, "ModificaSoftware")) {
	echo "no_permission";
	exit;
}

if (isset($_GET["nomefile"])) {
	$nomefile = $_GET["nomefile"];
	if(unlink("../../Files/Software/Applications/".$nomefile)) {
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
		header("Location:../modificaApplications.php");
		exit;
	} else {
		echo "error_delete";
		exit;
	}
} else {
	echo "unset";
}
?>