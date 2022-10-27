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

if (isset($_GET["id"])) {
    $idticket = $_GET["id"];
} else {
    header("Location:listaTickets.php");
    exit;
}

$stmt = $connection->prepare("SELECT * FROM $table_tickets WHERE ID = ?");
$stmt->bind_param("i", $idticket);
$esito = $stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
$riga = $result->fetch_assoc();

if($nrighe > 0) {
	$linguautente = getPreference($riga["IDUtente"], "Lingua");
	
	$stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
	$stmt->bind_param("s", $riga["IDUtente"]);
	$esito = $stmt->execute();
	$result = $stmt->get_result();
	$riga2 = $result->fetch_assoc();
	$usernameticketer = $riga2["Username"];

	if ($riga["Stato"] == 0 || $riga["Stato"] == 2) {
		$stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
		$stmt->bind_param("s", $riga["IDStaffer"]);
		$esito = $stmt->execute();
		$result = $stmt->get_result();
		$riga3 = $result->fetch_assoc();
		$usernamestaffer = $riga3["Username"];
	}
}
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
            <h2 align="center"><?php echo _("Visualizzando ticket"); ?> #<?php echo $idticket; ?></h2>
            <br>
            <b><?php echo _("Richiedente:"); ?></b> <?php echo $usernameticketer; ?>
			<br><br>
			<b><?php echo _("Tipo:"); ?></b> <?php echo $riga["TipoRichiesta"]; ?>
            <br><br>
            <b><?php echo _("Spiegazione:"); ?></b>
            <br>
            <?php echo $riga["Spiegazione"]; ?>
            <br><br>
            <b><?php echo _("Inviato il:"); ?></b> <?php echo formatDate($riga["Data"], true); ?>
            <br><br>
            <b><?php echo _("Stato:"); ?></b> <?php if ($riga["Stato"] == 0) {echo "<font style='color:lightgreen;'>"._("Chiuso")."</font>";} elseif ($riga["Stato"] == 1) {echo "<font style='color:lightcoral;'>"._("Aperto")."</font>";} elseif ($riga["Stato"] == 2) {echo "<font style='color:orange;'>"._("Pronto")."</font>";}?>
			<br><br>
			<b><?php echo _("Username Telegram:"); ?></b> <?php echo "@".$riga["TelegramUsername"]; ?>
			<br><br>
			<b><?php echo _("Lingua dell'utente:"); ?></b> <?php echo $linguautente; ?>
			<br>
            <?php if ($riga["Stato"] == 0) { //chiuso e visualizzato
				?>
                <br>
                <b><?php echo _("Staffer:"); ?></b> <?php echo $usernamestaffer; ?>
                <br><br>
                <b><?php echo _("Risposta:"); ?></b> <?php echo $riga["Risposta"]; ?>
                <br>
                <div class="container text-center">
                    <i><?php echo _("(Ticket chiuso)"); ?></i>
                    <br><br>
                    <button class="btn btn-secondary" type="button" onClick="location.href='listaTickets.php'"><?php echo _("Torna alla lista"); ?></button>
                </div>
                <?php
				} elseif ($riga["Stato"] == 1) { //aperto
					?>
                <br>
                <div class="container text-center" style="width: 60%">
                    <h2><?php echo _("Risposta"); ?></h2>
                    <p><?php echo sprintf(_("Rispondere al ticket lo imposta in automatico come %s, cioè che questo ticket ha ricevuto risposta ma deve essere ancora letto da chi l'ha inviato."), "<font style='color:orange;'>"._("Pronto")."</font>"); ?></p>
                    <form name="form" action="Utils/rispondiTicketDB.php" method="POST">
                        <input style="display:none;" name="idticket" type="text" placeholder="<?php echo _("ID ticket"); ?>" value="<?php echo $riga['ID']; ?>"></input>
                        <input style="display:none;" name="idutente" type="text"  value="<?php echo $riga["IDUtente"]; ?>"></input>
						<textarea maxlength="2500" class="form-control" rows="5" name="risposta" placeholder="<?php echo sprintf(_("Rispondi alla ticket in lingua %s, limite caratteri: 2500"), $linguautente); ?>" required></textarea>
                        <br>
                        <button class="btn btn-success" type="submit" name="invio"><?php echo _("Invia risposta"); ?></button>
                    </form>
                </div>
                <?php
				} elseif ($riga["Stato"] == 2) { //chiuso ma non visualizzato
				?>
				<br>
                <b><?php echo _("Staffer:"); ?></b> <?php echo $usernamestaffer; ?>
                <br><br>
                <b><?php echo _("Risposta:"); ?></b> <?php echo $riga["Risposta"]; ?>
                <br>
                <div class="container text-center">
                    <i><?php echo _("(Ticket chiuso)"); ?></i>
                    <br><br>
                    <button class="btn btn-secondary" type="button" onClick="location.href='listaTickets.php'"><?php echo _("Torna alla lista"); ?></button>
                </div>
                <?php
				}
				?>
            <br>
			<?php } else { ?>
				<h2 align="center" style="color:lightcoral;"><?php echo _("ID ticket non valido."); ?></h2>
				<br><br><br>
				<div class="container text-center">
					<button class="btn btn-secondary" type="button" onClick="location.href='listaTickets.php'"><?php echo _("Torna alla lista"); ?></button>
				</div>
				<br>
			<?php } ?>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>