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

if (isset($_GET["table"])) {
    $tabella = $_GET["table"];
	$errore = false;
	
	$stmt = $connection->prepare("SELECT * FROM $tabella");
	if($stmt) {
		$esito = $stmt->execute();
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
	} else {
		$errore = true;
	}
} else {
	header("Location:listaStringhe.php?table=StringheCheatJAVA");
	exit;
}
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title>Amministrazione <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<style>
.loader {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background: url('../CSS/Images/scanning.gif') 50% 50% no-repeat rgb(0,0,0);
	background-size: 100px 100px;
    opacity: .9;
}
</style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
	<div class="loader"></div>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white">
		<br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <button <?php if ($tabella == $table_cheatjava) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_cheatjava; ?>'"><?php echo $table_cheatjava; echo " (".getStringNumber($table_cheatjava).")"; ?></button> |
            <button <?php if ($tabella == $table_cheatdwm) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_cheatdwm; ?>'"><?php echo $table_cheatdwm; echo " (".getStringNumber($table_cheatdwm).")"; ?></button> |
			<button <?php if ($tabella == $table_cheatmsmpeng) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_cheatmsmpeng; ?>'"><?php echo $table_cheatmsmpeng; echo " (".getStringNumber($table_cheatmsmpeng).")"; ?></button> |
            <button <?php if ($tabella == $table_cheatlsass) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_cheatlsass; ?>'"><?php echo $table_cheatlsass; echo " (".getStringNumber($table_cheatlsass).")"; ?></button>
			<br>
			<button <?php if ($tabella == $table_suspectjava) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_suspectjava; ?>'"><?php echo $table_suspectjava; echo " (".getStringNumber($table_suspectjava).")"; ?></button> |
			<button <?php if ($tabella == $table_suspectdwm) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_suspectdwm; ?>'"><?php echo $table_suspectdwm; echo " (".getStringNumber($table_suspectdwm).")"; ?></button> |
			<button <?php if ($tabella == $table_suspectmsmpeng) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_suspectmsmpeng; ?>'"><?php echo $table_suspectmsmpeng; echo " (".getStringNumber($table_suspectmsmpeng).")"; ?></button> |
			<button <?php if ($tabella == $table_suspectlsass) {echo "disabled";}?> class="btn btn-secondary btn-sm" type="button" onClick="location.href='listaStringhe.php?table=<?php echo $table_suspectlsass; ?>'"><?php echo $table_suspectlsass; echo " (".getStringNumber($table_suspectlsass).")"; ?></button>
            <br><br>
			<?php if(!$errore) { ?>
			<h4>
			<?php 
				echo "<font color='lightgreen'>".$tabella."</font>: ";
				echo $nrighe;
				echo "<br><br>";
			?>
            </h4>
			<?php } ?>
			<?php if($nrighe > 0 && $errore == false) { ?>
            <table class="table table-dark table-striped">
                <tr>
                    <th>ID Stringa</th>
                    <th>Stringa</th>
					<th>Client</th>
                </tr>
                <tbody class="table-striped">
                    <?php
						while ($riga = $result->fetch_assoc()) {
							echo "<tr>";
							echo "<td>" . $riga["ID"] . " <a title='Modifica questa stringa o nome client' href='modificaStringa.php?table=".$tabella."&id=".$riga["ID"]."'>⚙</a></td>";
							if(strlen($riga["Stringa"]) > 130) {
								echo "<td>" . substr($riga["Stringa"], 0, 130) . " (...)</td>";
							} else {
								echo "<td>" . $riga["Stringa"] . "</td>";
							}
							echo "<td>" . $riga["Client"] . "</td>";
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<?php } else { ?>
				<p style="color:lightcoral;">La tabella <b><?php echo $tabella; ?></b> non esiste o non ha valori al suo interno.</p>
			<?php } ?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
document.getElementById('jquery').addEventListener('load', function() {	
	$(window).on('load', function(){
		$(".loader").fadeOut("slow");
	});
});
</script>