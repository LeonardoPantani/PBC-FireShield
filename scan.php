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
if(isset($_GET["id"])) {
	if(is_numeric($_GET["id"])) {
		$stmt = $connection -> prepare("SELECT * FROM $table_historyanalyzer WHERE ID = ?");
		$stmt -> bind_param("i", $_GET["id"]);
		$stmt -> execute();
		$result = $stmt -> get_result();
		$nrighe = $result -> num_rows;
		if($nrighe > 0) {
			$riga = $result -> fetch_assoc();
			
			$stmt = $connection -> prepare("SELECT Username FROM $table_users WHERE ID = ?");
			$stmt -> bind_param("i", $riga["IDUtente"]);
			$stmt -> execute();
			$result2 = $stmt -> get_result();
			$nrighe2 = $result2 -> num_rows;
			if($nrighe2 > 0) {
				$riga2 = $result2 -> fetch_assoc();
			}
		} else {
			$errore = true;
		}
	} else {
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
            <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"><h5 style="display:inline;"><?php if(!$errore) { ?><?php echo _("Scansione"); ?> n°<?php echo $_GET["id"]; } else { echo "("._("ID scansione non valido").")"; } ?></h5>
			<br>
			<div class="container text-left">
				<?php
				if(!$errore) {
					?>
					<p>
					<b><?php echo _("Esito scansione"); ?>:</b> <b><?php 
					if($riga["Esito"] == 0) { 
						echo "<font color='red'>"._("Errore")."</font>"; 
					} elseif($riga["Esito"] == 1) {
						echo "<font color='lightgreen'>"._("Pulito")."</font>";
					} elseif($riga["Esito"] == 2) {
						echo "<font color='orange'>"._("Sospetto")."</font>";
					} elseif($riga["Esito"] == 3) {
						echo "<font color='lightcoral'>"._("Cheat")."</font>";
					}
					?></b>
					<br>
					<b><?php echo _("Iniziata il"); ?>:</b> <?php echo formatDate($riga["Data"], true); ?>
					<br>
					<b><?php echo _("Durata"); ?>:</b> <?php echo $riga["Durata"]; ?>
					<br>
					<b><?php echo _("Tipo"); ?>:</b> <?php
					if($riga["Controllo"] == 1 || $riga["Controllo"] == 5) {
						echo "Javaw";
					} elseif($riga["Controllo"] == 2 || $riga["Controllo"] == 6) {
						echo "Dwm";
					} elseif($riga["Controllo"] == 3 || $riga["Controllo"] == 7 ) {
						echo "Msmpeng";
					} elseif($riga["Controllo"] == 4 || $riga["Controllo"] == 8) {
						echo "lsass";
					} else {
						echo _("Sconosciuto");
					}
					?>
					<br>
					<?php 
					if($riga["Controllo"] == 1) { 
						$stmt = $connection -> prepare("SELECT * FROM $table_cheatjava WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 2) {
						$stmt = $connection -> prepare("SELECT * FROM $table_cheatdwm WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 3) {
						$stmt = $connection -> prepare("SELECT * FROM $table_cheatmsmpeng WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 4) {
						$stmt = $connection -> prepare("SELECT * FROM $table_cheatlsass WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 5) {
						$stmt = $connection -> prepare("SELECT * FROM $table_suspectjava WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 6) {
						$stmt = $connection -> prepare("SELECT * FROM $table_suspectdwm WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 7) {
						$stmt = $connection -> prepare("SELECT * FROM $table_suspectmsmpeng WHERE Stringa = ?");
					} elseif($riga["Controllo"] == 8) {
						$stmt = $connection -> prepare("SELECT * FROM $table_suspectlsass WHERE Stringa = ?");
					}
					$stmt -> bind_param("s", $riga["StringaTrovata"]);
					$stmt -> execute();
					$result = $stmt -> get_result();
					$nrighe = $result -> num_rows;
					if($nrighe > 0) {
						$riga = $result -> fetch_assoc();
						?><b><?php echo _("Risultato"); ?>:</b> <?php echo $riga["Client"];
						echo "<br>";
					}
					?>
					<?php if(isset($riga2)) { ?>
					<b><?php echo _("Utente"); ?>:</b> <?php echo $riga2["Username"]."<br>"; ?>
					<?php } else {
						echo "<br>";
					} ?>
					<br><br>
					<button type="button" class="btn btn-success" onClick="location.href='Utils/generateLog.php?id=<?php echo $_GET["id"]; ?>'"><?php echo _("Scarica log"); ?></button>
					<div class="container text-right">
						<small><font color="red"><?php echo _("Avviso"); ?>:</font> <?php echo _("Una controllo completo è composto da"); ?> <b>4</b> <?php echo _("scansioni separate. In questa pagina ne viene mostrata solo una"); ?>.</small>
					</div>
					</p>
					<?php
				} else {
					?>
					<br><h4 style="color:red; text-align: center;"><?php echo _("L'ID inserito non è valido. Controlla di averlo inserito correttamente"); ?>.</h4><br><p style="text-align: right;"><?php echo _("Sintassi ricerca"); ?>: <i>https://fireshield.it/scan?id=<b>scan_id</b></i></p>
					<?php
				}
				?>
			</div>
        </div>
    </div>
</body>
</html>