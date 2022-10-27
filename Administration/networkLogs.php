<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

adminCheck();

networkInformation($infonetwork);

if (!getPermission($id, "VisualizzaLogs")) {
	header("Location:pannello.php");
	exit;
}

if (isset($_POST["selectnetworklog"])) {
    $idlog = $_POST["selectnetworklog"];
    $infonetworklog = getNetworkInfo($idlog);
    $nomelog = $infonetworklog[2];
} else {
    header("Location:pannello.php");
    exit;
}
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title>Amministrazione <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white" style="width:90%;">
        <br><br>
        <div class="p-1 mb-1 bg-dark text-white rounded">
			<h5>File di log del Network <span style="color:lightgreen;"><?php echo $nomelog; ?></span> (ID: <?php echo $idlog; ?>)</h5>
			<hr>
            <br>
			<div class="container text-left">
				<?php
                if ($idlog == -1) {
                    echo "Seleziona un Network per visualizzarne i log.";
                } elseif ($idlog == 0) {
                    echo "Non sono presenti Network di cui visualizzare i log al momento.";
                } else {
                    $file = file("../networkManagement/" . $foldernetworklogs . "/Network_" . $idlog . ".txt");
                    if (!$file) {
                        echo "Non è stato possibile accedere al file di log.";
                    } else {
                        $file = array_reverse($file);
                        foreach ($file as $f) {
                            echo $f . "<br />";
                        }
                    }
                }
                ?>
			</div>
            <br>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
