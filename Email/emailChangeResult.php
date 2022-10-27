<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickLoggedUser();

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
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="12%" alt="Logo"><h3 style="display:inline;"><?php echo _("Esito del cambio indirizzo Email"); ?></h3>
			<br><br>
			<p>
			<?php
				if(isset($_GET["e"])) {
					if($_GET["e"] == 1) {
						echo _("Imposta tutti i campi.");
					} elseif($_GET["e"] == 2) {
						echo _("Il token utilizzato non è valido. Rieffettua la richiesta di cambio Email.");
					} elseif($_GET["e"] == 3) {
						echo _("Questo indirizzo Email è già in uso.");
					} elseif($_GET["e"] == 4) {
						echo _("Non è stato possibile aggiornare l'indirizzo Email.");
					} elseif($_GET["e"] == 5) {
						echo _("Indirizzo Email aggiornato con successo. Rieffettua il login per vedere le modifiche.");
					} else {
						echo _("Stato conferma sconosciuto.");
					}
				} else {
					echo _("Non è stato possibile ottenere lo stato della conferma.");
				}
			?>
			</p>
			<hr>
			<br>
			<button onClick="location.href='/'" type="button" class="btn btn-success" name="homepage"><?php echo _("Torna alla Homepage"); ?></button>
			<br><br>
        </div>
    </div>
</body>
</html>