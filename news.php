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

$stmt = $connection->prepare("SELECT * FROM $table_news ORDER BY Data DESC");
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
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
            <h1><?php echo _("Novità"); ?> 📰</h1>
			<br>
            <?php
				if ($nrighe == 0) {
					?>
                    <div class="container text-left" style="color:black;">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h4><?php echo _("Beh, questo è strano..."); ?></i>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo _("Sembra che non ci sia nulla qua. Eccoti un simpatico gattino per adesso:"); ?> 🐱</p>
                            </div>
                        </div>
                    </div>
                    <br>
                    <?php
						} else {
							while ($riga = $result->fetch_assoc()) {
								?>
								<div class="container text-left" style="color:black;">
									<div class="card bg-light">
										<div class="card-header">
											<h4><?php echo $riga["Titolo"]; ?></h4><i><?php echo _("Data:"); ?> <?php echo formatDateComplete($riga["Data"], true); ?></i>
										</div>
										<div class="card-body">
											<p class="card-text"><?php echo $riga["Testo"]; ?></p>
										</div>
									</div>
								</div>
                        <br>
                        <?php
							}
						}
					?>
            <?php if ($nrighe != 0) {?><span style="color:lightgray;"><?php echo _("Nient'altro da vedere qui."); ?></span><?php }?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>