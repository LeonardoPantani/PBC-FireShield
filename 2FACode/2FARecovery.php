<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();
kickOTPUser();

licenseInformation($licenza, true);
networkInformation($infonetwork);

$stmt = $connection->prepare("SELECT * FROM $table_2fa WHERE IDUtente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$esito = $stmt->get_result();
$result = $esito->fetch_assoc();
$nrighe = $esito->num_rows;
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
    <div class="container-fluid text-center text-white" style="width:90%;">
		<br>
		<div class="p-2 mb-2 bg-dark text-white rounded">
			<img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="10%" alt="Logo"><h3 style="display:inline;"><?php echo _("Codici di Recupero"); ?></h3>
			<br>
			<?php if($nrighe > 0) { ?>
			<p>
				<?php echo _("Questi codici di recupero sono l'unico modo per accedere all'account qualora perdessi l'accesso all'app di Google Authenticator.<br>Se dovessi cambiare dispositivo, non potrai effettuare il login con Google Authenticator, pertanto conserva questi codici di recupero in un posto sicuro in caso di necessità!"); ?>
				<br><br>
				<?php 
				
					$array_codici = unserialize($result["RecoveryCodes"]);
					foreach($array_codici as $codice) {
						echo $codice."<br>";
					}
				?>
				<br>
				<a href="download2FARecovery.php"><span class="oi oi-data-transfer-download"></span> <?php echo _("Scarica i Codici di Recupero"); ?></a>
				<br><br>
				<?php if(isset($_GET["from"]) && $_GET["from"] == "settings") {?>
					<button class="btn btn-secondary" type="button" onClick="location.href='/settings.php#auth2fa'"><?php echo _("Torna alle Impostazioni"); ?></button>
				<?php } else { ?>
					<button class="btn btn-secondary" type="button" onClick="location.href='/'"><?php echo _("Torna alla Homepage"); ?></button>
				<?php } ?>
			</p>
			<?php } else { ?>
				<br><br>
				<h5 id="status" style="color:lightcoral;"><?php echo _("Non hai nessun codice di recupero impostato"); ?>.</h5>
				<br>
				<br>
				<button class="btn btn-primary" type="button" onClick="rc();"><?php echo _("Genera codici di recupero"); ?></button>
				<br><br>
				<?php if(isset($_GET["from"]) && $_GET["from"] == "settings") {?>
					<button class="btn btn-secondary" type="button" onClick="location.href='/settings.php#auth2fa'"><?php echo _("Torna alle Impostazioni"); ?></button>
				<?php } else { ?>
					<button class="btn btn-secondary" type="button" onClick="location.href='/'"><?php echo _("Torna alla Homepage"); ?></button>
				<?php } ?>
				<br>
			<?php } ?>
			<br>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
function rc() {
	$("#status").html("<font color='lightgray'><?php echo _("Generazione codici..."); ?></font>");
	$.ajax({
	type: "POST",
	url: "../Utils/generate2FARecoveryCodes.php",
	datatype: "html",
	success:function(result)
	{
		if(result == "success") {
			$("#status").html("<font color='lightgreen'><?php echo _("Codici creati!"); ?></font>");
			setTimeout( function(){
				window.location.reload();
			}, 1000 );
		} else if(result == "failure") {
			$("#status").html("<font color='lightcoral'><?php echo _("Si è verificato un errore durante la creazione dei codici!"); ?></font>");
		} else {
			$("#status").html("<font color='red'><?php echo _("Errore: "); ?>"+result+"</font>");
		}
	}
	});
}
</script>