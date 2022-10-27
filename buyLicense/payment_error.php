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
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if(isset($_SERVER['HTTP_ACCEPT'])) { if(strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) { ?>class="webp"<?php } else {?>class="jpg"<?php } } else { ?>class="jpg"<?php } ?>>
    <?php include "$droot/Functions/Include/navbar.php"; ?>
    <div class="container text-center text-white">
        <br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<img src="../CSS/Images/logo.<?php if(isset($_SERVER['HTTP_ACCEPT'])) { if(strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) { ?>webp<?php } else {?>png<?php } } else { ?>png<?php } ?>" width="15%" alt="Logo">
			<h2><?php echo _("Pagamento annullato"); ?> ❌</h2>
			<br>
			<div class="container text-center" style="width: 90%">
				<p>
				<?php echo _("L'acquisto della licenza"); echo " "; echo _("è stato cancellato o si è verificato un errore durante la procedura di acquisto")."."; ?>
				<br>
				<?php echo _("Stiamo raccogliendo informazioni su questo errore in modo che non si verifichi più")."."; ?>
				<br>
				<?php echo "<b>"._("Ci scusiamo per il disagio")."</b>."; ?>
				<br><br>
				<?php echo _("Per maggiori informazioni sul problema contattaci a questo indirizzo Email").": "; echo "<a href='mailto:".$issuesmail."'>".$issuesmail."</a>"; ?>
				</p>
				<br>
				<p class="container text-left"><?php echo _("Saluti"); ?>,<br><?php echo _("il Team di FireShield"); ?>.</p>
				<div class="container text-right">
					<small style="color:lightgray;"><?php echo _("Questa pagina non è considerata una ricevuta di pagamento"); ?>.</small>
				</div>
				<br>
				<button class="btn btn-secondary" onClick="location.href='buy.php'"><?php echo _("Torna alla schermata dei pagamenti"); ?></button>
			</div>
			<br>
		</div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>