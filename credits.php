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
    <title><?php echo _("Informazioni su")." "; echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="10%" alt="Logo"><h2 style="display:inline;"><?php echo $solutionname; ?></h2>
			<br><br>
			<h5><?php echo _("Versione attuale:")." ".$solutionver; ?></h5>
			<br>
			<h5><?php echo _("Profili social:"); ?></h5>
			<a target="_blank" title="Telegram" href="https://t.me/NewsFSCD"><img src="CSS/Images/logo_telegram.png" width="30px" length="30px"/></a> &nbsp; <a target="_blank" title="Discord" href="https://discord.gg/H6BWDeF"><img src="CSS/Images/logo_discord.png" width="30px" length="30px"/></a>
			<hr>
            <h3><?php echo _("Crediti"); ?></h3>
            <br>
			<div class="container text-center" style="width:50%">
				<h4 style="color:yellow;"><?php echo _("Proprietari");?></h4>
				<table class="table">
					<tr>
						<td>LeoPantani</td>
						<td><?php echo _("Leader Sito Web e Sviluppatore Web"); ?></td>
					</tr>
					
					<tr>
						<td>WitherFede</td>
						<td><?php echo _("Leader Progetto e Sviluppatore Software"); ?></td>
					</tr>
					
					<tr>
						<td>piccy17</td>
						<td><?php echo _("Leader Progetto e Sviluppatore Software"); ?></td>
					</tr>
				</table>
				
				<hr>
				<h4 style="color:yellow;"><?php echo _("Amministratori");?></h4>

				<table class="table">
					<tr>
						<td>Mαттн420</td>
						<td><?php echo _("Direttore Gestione"); ?></td>
					</tr>
					
					<!--<tr>
						<td>cocorisss</td>
						<td><?php echo _("Manager Community"); ?></td>
					</tr>
					
					<tr>
						<td>Saw</td>
						<td><?php echo _("Manager Media"); ?></td>
					</tr> -->
					
					<tr>
						<td>EdgeUser</td>
						<td><?php echo _("Manager Trial-Staff"); ?></td>
					</tr>
					
					<tr>
						<td>BritishAndre</td>
						<td><?php echo _("Manager Staff"); ?></td>
					</tr>	
				</table>
			
				<!--<hr>
				<h4 style="color:yellow;"><?php echo _("Staff");?></h4>	
				
				<table class="table">
					<tr>
						<td>fireentity</td>
						<td><?php echo _("Sviluppatore Java"); ?></td>
					</tr>
				</table>
				-->
			</div>
			<hr>
            <p>
			<font size="3"><?php echo sprintf(_("Grazie a tutti coloro che hanno contribuito alla realizzazione del %s!"), "<b>".$solutionname."</b>"); ?></font>
			</p>
			<button type="button" class="btn btn-secondary" onClick="toggleLibraries()" id="toggle"><?php echo _("Mostra Librerie"); ?></button>
			<br><br>
			<div class="container" id="librerie" style="display:none;">
				<hr>
				<h3><?php echo _("Librerie che utilizziamo"); ?></h3>
				<br>
				<div class="container text-center" style="width:75%">
					<table class="table">
						<tr>
							<td>jQuery</td>
							<td><button onClick="window.open('https://github.com/jquery/jquery')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
						
						<tr>
							<td>offline.js</td>
							<td><button onClick="window.open('https://github.com/hubspot/offline')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
						
						<tr>
							<td>limonte-sweetalert</td>
							<td><button onClick="window.open('https://github.com/sweetalert2/sweetalert2')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
						
						<tr>
							<td>popper.js</td>
							<td><button onClick="window.open('https://github.com/FezVrasta/popper.js/')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
						
						<tr>
							<td>clipboard.js</td>
							<td><button onClick="window.open('https://github.com/zenorocha/clipboard.js')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
						
						<tr>
							<td>fusioncharts</td>
							<td><button onClick="window.open('https://github.com/fusioncharts/fusionexport-php-client')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
						
						<tr>
							<td>PHPMailer</td>
							<td><button onClick="window.open('https://github.com/PHPMailer/PHPMailer')" type="button" class="btn btn-secondary"><?php echo _("Sito Web"); ?></button></td>
						</tr>
					</table>
				</div>
			</div>
        </div>
    </div>
	<br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
	var attiva = false;
	
	function toggleLibraries() {
		if(attiva == false) {
			attiva = true;
			$("#librerie").show();
			$("#toggle").html("<?php echo _("Nascondi Librerie"); ?>");
		} else {
			attiva = false;
			$("#librerie").hide();
			$("#toggle").html("<?php echo _("Mostra Librerie"); ?>");
		}
	}
</script>