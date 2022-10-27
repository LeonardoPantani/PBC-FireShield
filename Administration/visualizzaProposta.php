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

if (!getPermission($id, "ListaStringhe")) {
	header("Location:pannello.php");
	exit;
}

if (isset($_GET["id"])) {
    $idproposta = $_GET["id"];
} else {
    header("Location:listaProposte.php");
    exit;
}

$stmt = $connection->prepare("SELECT * FROM $table_suggestions WHERE IDProposta = ?");
$stmt->bind_param("i", $idproposta);
$esito = $stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
$riga = $result->fetch_assoc();

if($nrighe > 0) {
	$linguautente = getPreference($riga["IDUtente"], "Lingua");
	
	$usernameproposta = getUsernameFromID($riga["IDUtente"]);

	if ($riga["Stato"] == 0 || $riga["Stato"] == 2) {
		$usernamestaffer = getUsernameFromID($riga["IDStaffer"]);
	}
}
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title>Amministrazione <?php echo $solutionname; ?></title>
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
            <h2 align="center">Visualizzando proposta #<?php echo $idproposta; ?></h2>
            <br>
            <b>Utente:</b> <?php echo $usernameproposta; ?>
			<br><br>
			<b>Tipo stringa:</b> <?php echo $riga["TipoStringa"]; ?>
            <br><br>
            <b>Stringa:</b> <?php echo $riga["Stringa"]; ?>
            <br><br>
            <b>Client:</b> <?php echo $riga["Client"]; ?>
            <br><br>
            <b>Note:</b>
            <br>
            <?php if($riga["Note"] != "") { echo $riga["Note"]; } else { echo "<i>Nessuna nota</i>"; } ?>
            <br><br>
            <b>Data:</b> <?php echo formatDate($riga["Data"], true); ?>
            <br><br>
            <b>Stato:</b> <?php if ($riga["Stato"] == 0) {echo "<font style='color:orange;'>Da vedere</font>";} elseif ($riga["Stato"] == 1) {echo "<font style='color:lightcoral;'>Approvata</font>";} elseif ($riga["Stato"] == 2) {echo "<font style='color:lightcoral;'>Negata</font>";}?>
			<br><br>
            <?php if ($riga["Stato"] == 1 || $riga["Stato"] == 2) { ?>
                <b>Staffer:</b> <?php echo $usernamestaffer; ?>
                <br>
            <?php } ?>
				<br>
                <div class="container text-center" style="width:50%;">
					<h5>Stato della proposta:</h5>
                    <form name="form" action="Utils/cambiaStatoPropostaDB.php" method="POST">
						<input style="display:none;" name="idproposta" value="<?php echo $riga["IDProposta"]; ?>">
                        <select class="form-control" name="stato">
                            <?php
                            if($riga["Stato"] == 0) {
                                ?>
                                <option value="0">Da vedere</option>
                                <option value="1">Approvata</option>
                                <option value="2">Negata</option>
                                <?php
                            } elseif($riga["Stato"] == 1) {
                                ?>
                                <option value="1">Approvata</option>
                                <option value="0">Da vedere</option>
                                <option value="2">Negata</option>
                                <?php
                            } elseif($riga["Stato"] == 2) {
                                ?>
                                <option value="2">Negata</option>
                                <option value="0">Da vedere</option>
                                <option value="1">Approvata</option>
                                <?php
                            } ?>
                        </select>
                        <br><br>
                        <button class="btn btn-success" type="submit" name="invia">Cambia stato della proposta</button>
                    </form>
                </div>
			<?php } else { ?>
				<h2 align="center" style="color:lightcoral;">ID proposta non valido.</h2>
			<?php } ?>
			<br>
			<hr>
			<div class="container text-center">
				<button class="btn btn-secondary" type="button" onClick="location.href='listaProposte.php'">Torna alla lista</button>
			</div>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>