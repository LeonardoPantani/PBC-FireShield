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
			<h2><?php echo _("Pagamento completato"); ?> ✅</h2>
			<br>
			<div class="container text-center" style="width: 90%">
				<p>
					<?php echo _("Grazie per aver acquistato la licenza!"); ?>
					<br>
					<?php echo _("I dati di login ti saranno forniti via email all'indirizzo utilizzato per l'acquisto da PayPal")."."; ?>
					<br><br>
					<?php echo _("Ti ringraziamo per l'acquisto e ci auguriamo che il servizio sia di tuo gradimento")."."; ?>
					<br>
					<?php echo _("Per qualsiasi problema relativo alla piattaforma non esitare a contattarci a questo indirizzo Email").": "; echo "<a href='mailto:".$issuesmail."'>".$issuesmail."</a>"; ?>
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