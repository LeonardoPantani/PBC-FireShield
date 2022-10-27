<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

licenseInformation($licenza);
networkInformation($infonetwork);

$errore = false;
if (!isset($lang)) {
    $suffissofile = "en_US";
} else {
    $suffissofile = $lang;
}
if (!fopen("regulations-" . $suffissofile . ".txt", "r")) {
    $errore = true;
} else {
    $file = fopen("regulations-" . $suffissofile . ".txt", "r");
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Termini di Servizio"); ?> | <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white" style="width: 75%;">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img id="cima" src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="10%" alt="Logo"><h4 style="display:inline;"><?php echo _("Termini di Servizio"); ?></h4>
			<?php if ($suffissofile != "it_IT") {?>
			<br><br>
			<div class="card text-white bg-danger mb-3">
			  <div class="card-body">
				<h5 class="card-text text-left"><?php echo _("Avvertimento"); ?></h5>
				<p class="card-text text-left"><?php echo _("Il seguente documento è stato tradotto da una macchina e non da una persona, pertanto il contenuto potrebbe non essere accurato al 100%."); ?></p>
			  </div>
			</div>
			<hr>
			<?php }?>
			<div class="container-fluid text-left" style="width: 100%;">
				<?php
                if ($errore == true) {
                    echo _("Si è verificato un errore cercando di ottenere le informazioni dei Termini di Servizio.");
                    echo " (regulations-" . $suffissofile . ".txt)";
                } else {
                    while (!feof($file)) {echo fgets($file) . "<br />";}
                    fclose($file);
                }?>
				<br>
				<a href="https://www.iubenda.com/privacy-policy/70209308" class="iubenda-white iubenda-embed " title="Privacy Policy">Privacy Policy</a> <script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script>
				<br><br>
			</div>
			<a href="#cima"><?php echo _("Clicca qui per tornare in cima"); ?></a>
			<br><br>
        </div>
    </div>
	<br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>