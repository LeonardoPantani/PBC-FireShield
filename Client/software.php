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

$versionesoftware = file_get_contents("../Files/Software/versioni.txt");
if($versionesoftware === FALSE) {
	$versionesoftware = "?";
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
            <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="10%" alt="Logo"><h3 style="display:inline;"><?php echo $solutionname; ?> SOFTWARE</h3>
            <br>
			<font color="lightgray"><small><?php echo $versionesoftware; ?></small></font>
			<p>
            <?php echo _("Perché perdere tempo con infiniti controlli quando un'applicazione può fare tutto per te?"); ?><br><?php echo _("Presentiamo"); ?> <b><?php echo $solutionname; ?> SOFTWARE</b>, <?php echo _("uno Screenshare Tool dalle incredibili funzionalità!"); ?>
            <br><br>
			<div class="row">
				<div class="col-lg-3 col-md-3 col-xs-3 thumb">
					<a class="thumbnail" href="#">
						<img class="img-responsive" style="width:95%;" src="software_1.png" alt="">
					</a>
				</div>
				<div class="col-lg-3 col-md-3 col-xs-3 thumb">
					<a class="thumbnail" href="#">
						<img class="img-responsive" style="width:95%;" src="software_2.png" alt="">
					</a>
				</div>
				<div class="col-lg-3 col-md-3 col-xs-3 thumb">
					<a class="thumbnail" href="#">
						<img class="img-responsive" style="width:95%;" src="software_3.png" alt="">
					</a>
				</div>
				<div class="col-lg-3 col-md-3 col-xs-3 thumb">
					<a class="thumbnail" href="#">
						<img class="img-responsive" style="width:95%;" src="software_4.png" alt="">
					</a>
				</div>
			</div>
            <hr>
            <br>
            <div class="container text-center text-dark" style="width: 100%">
				<?php if($softwaredownload) { ?>
                <div class="row"> <!-- Riga 1 -->
                    <div class="card bg-light" style="width: 20rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <div align="center">
                                <span class="oi oi-check"></span>
								<br>
                            </div>
                            <h5 class="card-title"><?php echo _("Affidabile"); ?></h5>
                            <p class="card-text">
								<?php echo _("Potrai trovare i cheat più famosi in modo completamente gratuito."); ?>
								<br><br>
                            </p>
                        </div>
                    </div>

                    <div class="card bg-light" style="width: 20rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <div align="center">
                                <span class="oi oi-ellipses"></span>
								<br>
                            </div>
                            <h5 class="card-title"><?php echo _("Semplice"); ?></h5>
                            <p class="card-text">
								<?php echo _("L'interfaccia è pulita ed intuitiva e ogni sezione presenta un pulsante di aiuto."); ?>
								<br><br>
                            </p>
                        </div>
                    </div>

                    <div class="card bg-light" style="width: 20rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <div align="center">
                                <span class="oi oi-flash"></span>
								<br>
                            </div>
                            <h5 class="card-title"><?php echo _("Veloce"); ?></h5>
                            <p class="card-text">
								<?php echo _("Non sarai mai interrotto da inutili pubblicità e caricamenti."); ?>
								<br><br>
                            </p>
                        </div>
                    </div>
                </div>
				<br><br>
                <div class="row"> <!-- Riga 2 -->
					<div class="card bg-light" style="width: 20rem; float: none; margin: 0 auto;">
						<div class="card-body">
                            <div align="center">
                                <span class="oi oi-grid-four-up"></span>
								<br>
                            </div>
							<h5 class="card-title"><?php echo _("Completo"); ?></h5>
							<p class="card-text">
								<?php echo _("Munito di tutte le funzionalità essenziali per eseguire controlli accurati."); ?>
								<br><br>
							</p>
						</div>
					</div>

                    <div class="card bg-light" style="width: 20rem; float: none; margin: 0 auto;">
						<div class="card-body">
                            <div align="center">
                                <span class="oi oi-eyedropper"></span>
								<br>
                            </div>
							<h5 class="card-title"><?php echo _("Leggero"); ?></h5>
							<p class="card-text">
								<?php echo _("Abbiamo ridotto il peso del Software in modo che possa essere facilmente scaricabile da tutti."); ?>
								<br><br>
							</p>
						</div>
					</div>

                    <div class="card bg-light" style="width: 20rem; float: none; margin: 0 auto;">
						<div class="card-body">
                            <div align="center">
                                <span class="oi oi-cloud-download"></span>
								<br>
                            </div>
							<h5 class="card-title"><?php echo _("Aggiornato"); ?></h5>
							<p class="card-text">
								<?php echo _("Riceverai in tempo reale le notifiche di aggiornamento tramite l'auto-updater."); ?>
								<br><br>
							</p>
						</div>
					</div>
                </div>
				<?php } else { ?>
					<h4 style="color:lightcoral"><?php echo _('Il download non è al momento disponibile'); ?></h4>
				<?php } ?>
				<br>
				<br>
				<button id="download" class="btn btn-outline-<?php if($softwaredownload) { echo "success"; } else { echo "danger"; } ?> btn-lg" title="<?php if($softwaredownload) { echo _('Clicca qui per scaricare GRATUITAMENTE il software'); } else { echo _('Il download non è al momento disponibile'); } ?>" onClick="location.href='download.php'" <?php if(!$softwaredownload) { echo "disabled"; } ?>><?php echo _("Scarica il Software"); ?></button>
				<br>
            </div>
            </p>
        </div>
    </div>
	<br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>