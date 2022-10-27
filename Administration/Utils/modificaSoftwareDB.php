<?php /* Copyright FireShield. All rights reserved. */
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser(true);

kickNonAdminUser(true);

if (!getPermission($id, "ModificaSoftware")) {
	echo "no_permission";
	exit;
}

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") { // controllo se la richiesta è in XHR (ajax)
	if (isset($_POST["versione1"]) && isset($_POST["versione2"]) && isset($_POST["versione3"]) && isset($_POST["versione4"]) && isset($_POST["versione5"]) && isset($_POST["versione6"]) && isset($_POST["versione7"]) && isset($_POST["versione8"]) && isset($_POST["versione9"]) && isset($_POST["versione10"])) {
		$versione1 = $_POST["versione1"];
		$versione2 = $_POST["versione2"];
		$versione3 = $_POST["versione3"];
		$versione4 = $_POST["versione4"];
		$versione5 = $_POST["versione5"];
		$versione6 = $_POST["versione6"];
		$versione7 = $_POST["versione7"];
		$versione8 = $_POST["versione8"];
		$versione9 = $_POST["versione9"];
		$versione10 = $_POST["versione10"];
		if ($versione1 == "" || $versione2 == "" || $versione3 == "" || $versione4 == "" || $versione5 == "" || $versione6 == "" || $versione7 == "" || $versione8 == "" || $versione9 == "" || $versione10 == "") {
			echo "string_empty";
			exit;
		} else {
			$file1 = @fopen("../../Files/Software/db/HashDatabase.txt", "r");
			if ($file1) {
				file_put_contents("../../Files/Software/db/HashDatabase.txt", $versione1);
			} else {
				echo "not_found";
				exit;
			}

			$file2 = @fopen("../../Files/Software/db/LegitException.txt", "r");
			if ($file2) {
				file_put_contents("../../Files/Software/db/LegitException.txt", $versione2);
			} else {
				echo "not_found";
				exit;
			}

			$file3 = @fopen("../../Files/Software/db/ModDatabase.txt", "r");
			if ($file3) {
				file_put_contents("../../Files/Software/db/ModDatabase.txt", $versione3);
			} else {
				echo "not_found";
				exit;
			}

			$file4 = @fopen("../../Files/Software/db/VersionDatabase.txt", "r");
			if ($file4) {
				file_put_contents("../../Files/Software/db/VersionDatabase.txt", $versione4);
			} else {
				echo "not_found";
				exit;
			}

			$file5 = @fopen("../../Files/Software/db/BadProcess.txt", "r");
			if ($file5) {
				file_put_contents("../../Files/Software/db/BadProcess.txt", $versione5);
			} else {
				echo "not_found";
				exit;
			}

			$file6 = @fopen("../../Files/Software/db/DllDatabase.txt", "r");
			if ($file6) {
				file_put_contents("../../Files/Software/db/DllDatabase.txt", $versione6);
			} else {
				echo "not_found";
				exit;
			}

			$file7 = @fopen("../../Files/Software/db/ClassDatabase.txt", "r");
			if ($file7) {
				file_put_contents("../../Files/Software/db/ClassDatabase.txt", $versione7);
			} else {
				echo "not_found";
				exit;
			}

			$file8 = @fopen("../../Files/Software/db/DownloadDatabase.txt", "r");
			if ($file8) {
				file_put_contents("../../Files/Software/db/DownloadDatabase.txt", $versione8);
			} else {
				echo "not_found";
				exit;
			}
			
			$file9 = @fopen("../../Files/Software/db/WebsiteDatabase.txt", "r");
			if ($file9) {
				file_put_contents("../../Files/Software/db/WebsiteDatabase.txt", $versione9);
			} else {
				echo "not_found";
				exit;
			}

			$file10 = @fopen("../../Files/Software/db/CheatSmasherDatabase.txt", "r");
			if ($file10) {
				file_put_contents("../../Files/Software/db/CheatSmasherDatabase.txt", $versione10);
			} else {
				echo "not_found";
				exit;
			}

			echo "success";
		}
	} else {
		echo "unset";
	}
} else { // fine controllo se richiesta in ajax
	header("Location:/error.php");
}