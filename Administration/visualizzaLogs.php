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

if (!getPermission($id, "ModificaConfig")) {
    header("Location:pannello.php");
    exit;
}


$errore = false;
if (!fopen("../Functions/error_log.log", "r")) {
    $errore = true;
} else {
    $file = array_reverse(file("../Functions/error_log.log"));
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
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-center">
            <h3 align="center">Visualizzazione error log <font color="orange"><?php echo $solutionname; ?></font></h3>
			<hr>
            <div class="container-fluid text-left">
			<?php if ($errore == false) {
					foreach($file as $riga) {
						echo str_replace("/web/htdocs/www.fireshield.it/home/", "", str_replace(" Europe/Rome", "", $riga))."<br>";
					}
					echo "<hr>";
				} else {
					?><h5 style="color:lightcoral;">Si è verificato un errore durante l'apertura del file error_log.log!</h5><?php
				}?>
            </div>
			<button class="btn btn-secondary" onClick="location.href='pannello.php'">Torna al pannello</button>
			<br>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html> <!-- titolo & testo -->