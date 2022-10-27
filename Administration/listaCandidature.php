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

if (!getPermission($id, "ModificaTickets")) {
	header("Location:pannello.php");
	exit;
}

$stmt = $connection->prepare("SELECT * FROM $table_applications ORDER BY DataAggiornamento DESC");
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;

$stmt = $connection->prepare("SELECT IDCandidatura FROM $table_applications WHERE Stato = 0");
$stmt->execute();
$result2 = $stmt->get_result();
$righeaperte = $result2->num_rows;
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
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <h4><?php echo $nrighe . " ";if ($nrighe == 1) {echo _("candidatura");} else {echo _("candidature");} ?> (<?php echo _("Da valutare:"); ?> <b><?php echo $righeaperte; ?></b>)</h4>
			<br>
            <?php if ($nrighe > 0) {?>
            <table class="table table-dark">
                <tr>
					<th></th>
                    <th><?php echo _("ID Candidatura");?></th>
                    <th><?php echo _("Nome"); ?></th>
					<th><?php echo _("Cognome"); ?></th>
                    <th><?php echo _("Data Nascita"); ?></th>
					<th><?php echo _("Lingua"); ?></th>
					<th><?php echo _("Username Telegram"); ?></th>
					<th><?php echo _("Data Invio"); ?></th>
                    <th><?php echo _("Data Aggiornamento"); ?></th>
                    <th><?php echo _("Stato"); ?></th>
                </tr>
                <tbody class="table-striped">
                    <?php
						while ($riga = $result->fetch_assoc()) {
							$d1 = new DateTime($riga["DataNascita"]);
							$d2 = new DateTime();
							$differenza = $d2->diff($d1);
							echo "<tr>";
							?><td><button class="btn btn-secondary" type="button" onClick="location.href='visualizzaCandidatura.php?id=<?php echo $riga["IDCandidatura"]; ?>'"><?php echo _("Mostra"); ?></button> <button class="btn btn-danger" type="button" onClick="conferma('<?php echo $riga['SecretToken']; ?>');"><?php echo _("Elimina"); ?></button></td><?php
							echo "<td>" . $riga["IDCandidatura"] . "</td>";
							echo "<td>" . $riga["Nome"] . "</td>";
							echo "<td>" . $riga["Cognome"] . "</td>";
							echo "<td>" . formatDate($riga["DataNascita"]) . " ("; echo $differenza->y.".".$differenza->m; echo ")</td>";
							echo "<td>" . $riga["Lingua"] . "</td>";
							echo "<td><a target='_blank' href='https://t.me/".$riga["TelegramUsername"]."'>" . "@".$riga["TelegramUsername"] . "</a></td>";
							echo "<td>" . formatDate($riga["DataInvio"], true) . "</td>";
							echo "<td>" . formatDate($riga["DataAggiornamento"], true) . "</td>";
							echo "<td>";
							if ($riga["Stato"] == 0) {
								echo "<font style='color:lightgray;'>"._("In attesa")."</font>";
							} elseif ($riga["Stato"] == 1) {
								echo "<font style='color:lightgreen;'>"._("Accettata")."</font>";
							} elseif ($riga["Stato"] == 2) {
								echo "<font style='color:lightcoral;'>"._("Rifiutata")."</font>";
							}
							echo "</td>";
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<?php } else {
				?><h4 style='color:lightcoral;'><?php echo _("Non ci sono candidature da visualizzare."); ?></h4><?php
			}?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
	function conferma(token) {
		swal({
		  title: "<?php echo _("Conferma operazione"); ?>",
		  text: "<?php echo _("Cliccando su 'Conferma' la candidatura sarà eliminata definitivamente."); ?>",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonText: '<?php echo _("Conferma"); ?>',
		  cancelButtonText: '<?php echo _("Annulla"); ?>'
		})
		.then((result) => {
		  if (result.value) {
			  location.href = "Utils/eliminaCandidatura?secrettoken="+token+".php";
		  }
		});	
	}
</script>