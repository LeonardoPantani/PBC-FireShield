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
if(isset($_GET["m"]) && $_GET["m"] != "") {
	$esito = updatePreference(getIDFromEmail($_GET["m"]), "NewsLetter", 0);
	if($esito != 1) {
		$errore = true;
	}
} else {
	$errore = true;
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="11%" alt="Logo"><h4 style="display: inline-block;"><?php echo $solutionname." "._("Newsletter"); ?></h4>
			<br>
			<?php if($errore) { ?>
				<p>
					<font color="lightcoral"><?php echo _("Si è verificato un errore."); ?></font>
				</p>
			<?php } else { ?>
				<p>
					<font color="lightgreen"><?php echo _("Sei stato correttamente rimosso dalla mailing list."); ?></font>
					<br>
					<?php echo sprintf(_("Nel caso dovessi cambiare idea, potrai fornirci di nuovo l'autorizzazione per l'invio di email dalle %sImpostazioni%s!"), "<a href='settings.php'>", "</a>"); ?>
				</p>
			<?php } ?>
			<br>
			<button class="btn btn-secondary" onClick="location.href='/'"><?php echo _("Torna alla HomePage"); ?></button>
			<br><br>
		</div>
    </div>
	<br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>