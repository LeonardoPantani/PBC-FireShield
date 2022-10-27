<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

kickGuestUser();

kickNonAdminUser();

adminCheck();

networkInformation($infonetwork);

if (!getPermission($id, "ViewApplication")) {
	header("Location:../pannello.php");
	exit;
}

if (isset($_GET["id"])) {
    $idcandidatura = $_GET["id"];
} else {
    header("Location:listaTickets.php");
    exit;
}

$stmt = $connection->prepare("SELECT * FROM $table_applications WHERE IDCandidatura = ?");
$stmt->bind_param("i", $idcandidatura);
$esito = $stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
$riga = $result->fetch_assoc();

$d1 = new DateTime($riga["DataNascita"]);
$d2 = new DateTime();
$differenza = $d2->diff($d1);
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Amministrazione")." "; echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
			<?php if($nrighe > 0) { ?>
            <h2 align="center"><?php echo _("Visualizzando candidatura"); ?> #<?php echo $idcandidatura; ?></h2>
            <br>
            <b><?php echo _("Candidato:"); ?></b> <?php echo $riga["Nome"]." ".$riga["Cognome"]; ?>
			<br><br>
			<b><?php echo _("Data di Nascita:"); ?></b> <?php echo formatDate($riga["DataNascita"]); echo " ("; echo $differenza->y.".".$differenza->m; echo ")"; ?>
            <br><br>
            <b><?php echo _("Email:"); ?></b> <?php echo $riga["Email"]; ?>
            <br><br>
            <b><?php echo _("Username Telegram:"); ?></b> <a target="_blank" href='https://t.me/<?php echo $riga["TelegramUsername"]; ?>'><?php echo "@".$riga["TelegramUsername"]; ?></a>
            <br><br>
            <b><?php echo _("Paese di Residenza:"); ?></b> <?php echo $riga["Residenza"]; ?>
			<br><br>
			<b><?php echo _("Lingua:"); ?></b> <?php echo $riga["Lingua"]; ?>
			<br><br>
			<b><?php echo _("Risposta 1:"); ?></b>
			<br>
			<?php echo $riga["Risposta1"]; ?>
			<br><br>
			<b><?php echo _("Risposta 2:"); ?></b>
			<br>
			<?php echo $riga["Risposta2"]; ?>
			<br><br>
			<b><?php echo _("Risposta 3:"); ?></b>
			<br>
			<?php echo $riga["Risposta3"]; ?>
			<br><br>
			<b><?php echo _("Risposta 4:"); ?></b>
			<br>
			<?php echo $riga["Risposta4"]; ?>
			<br><br>
			<b><?php echo _("Risposta 5:"); ?></b>
			<br>
			<?php echo $riga["Risposta5"]; ?>
			<br><br>
			<b><?php echo _("Stato candidatura:"); ?></b> <?php if($riga["Stato"] == 0) { echo "<font color='lightgray'>"._("In attesa")."</font>"; } elseif($riga["Stato"] == 1) { echo "<font color='lightgreen'>"._("Accettata")."</font>"; } elseif($riga["Stato"] == 2) { echo "<font color='lightcoral'>"._("Rifiutata")."</font>"; } else { echo _("Sconosciuto"); } ?>
			<hr>
			<?php 
			if($riga["Stato"] == 0 && getPermission($id, "EditApplication")) { ?>
				<div class="container text-center" style="width: 60%">
					<h2><?php echo _("Modifica stato candidatura"); ?></h2>
					<p><?php echo _("Il candidato sarà notificato via Email sull'aggiornamento di stato della sua candidatura."); ?></p>
					<form id="form" name="form" action="Utils/rispondiCandidaturaDB.php" method="POST">
						<input hidden class="form-control" name="idcandidatura" value="<?php echo $idcandidatura; ?>"/>
						<input hidden class="form-control" name="idutente" value="<?php echo $idutente; ?>"/>
						<select class="form-control" name="statocandidatura">
							<option value="0"><?php echo _("In attesa"); ?></option>
							<option value="1"><?php echo _("Accettata"); ?></option>
							<option value="2"><?php echo _("Rifiutata"); ?></option>
						</select>
						<br>
						<button class="btn btn-success" type="submit" name="invio"><?php echo _("Modifica candidatura"); ?></button>
					</form>
				</div>
			<?php } ?>
			<?php } else { ?>
				<h2 align="center" style="color:lightcoral;"><?php echo _("ID candidatura non valido."); ?></h2>
				<br><br><br>
			<?php } ?>
			<div class="container text-center">
				<button class="btn btn-secondary" type="button" onClick="location.href='listaCandidature.php'"><?php echo _("Torna alla lista"); ?></button>
			</div>
			<br>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>