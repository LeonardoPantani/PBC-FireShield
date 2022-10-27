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

if (isset($_GET["ordine"])) {
    if ($_GET["ordine"] == "data") {
        $stmt = $connection->prepare("SELECT * FROM $table_tickets ORDER BY Data DESC");
    } else if ($_GET["ordine"] == "id") {
        $stmt = $connection->prepare("SELECT * FROM $table_tickets ORDER BY ID ASC");
    } else {
        $stmt = $connection->prepare("SELECT * FROM $table_tickets ORDER BY Data DESC");
    }
} else {
    $stmt = $connection->prepare("SELECT * FROM $table_tickets ORDER BY Data DESC");
}
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;

$stmt = $connection->prepare("SELECT ID FROM $table_tickets WHERE Stato = 1");
$stmt->execute();
$result2 = $stmt->get_result();
$righeaperti = $result2->num_rows;
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
            <h4><?php echo $nrighe . " ";if ($nrighe == 1) {echo "ticket";} else {echo "tickets";} ?> (<?php echo _("Aperti:"); ?> <b><?php echo $righeaperti; ?></b>)</h4>
			<br>
            <?php if ($nrighe > 0) {?>
            <b><?php echo _("Come vuoi ordinare la tabella?"); ?></b> <input type="radio" name="ordine" id="data" <?php if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "data") {echo "checked";}} else {echo "checked";}?>> <?php echo _("Data"); ?> (<i>Default</i>) - <input type="radio" name="ordine" id="id" <?php if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "id") {echo "checked";}}?>> ID<br><br>
            <table class="table table-dark">
                <tr>
                    <th><?php echo _("ID Ticket"); if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "id") {echo "<b>*</b>";}}?></th>
                    <th><?php echo _("Richiedente"); ?></th>
					<th><?php echo _("Tipo"); ?></th>
                    <th><?php echo _("Spiegazione"); ?></th>
                    <th><?php echo _("Data invio"); if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "data") {echo "<b>*</b>";}} else {echo "<b>*</b>";}?></th>
					<th><?php echo _("Telegram"); ?></th>
                    <th><?php echo _("Stato"); ?></th>
                    <th><?php echo _("Staffer"); ?></th>
                    <th><?php echo _("Risposta"); ?></th>
                </tr>
                <tbody class="table-striped">
                    <?php
						while ($riga = $result->fetch_assoc()) {
							$spiegazione = substr($riga["Spiegazione"], 0, 50);
							$stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
							$stmt->bind_param("s", $riga["IDUtente"]);
							$esito = $stmt->execute();
							$resultticket = $stmt->get_result();
							$riga2 = $resultticket->fetch_assoc();
							echo "<tr>";
							echo "<td>" . $riga["ID"];?> - <button class="btn btn-secondary" type="button" onClick="location.href='visualizzaTicket.php?id=<?php echo $riga["ID"]; ?>'"><?php echo _("Vedi"); ?></button><?php
							echo "<td>" . $riga2["Username"] . "</td>";
							echo "<td>" . $riga["TipoRichiesta"] . "</td>";
							echo "<td>" . $spiegazione . "...</td>";
							echo "<td>" . formatDate($riga["Data"], true) . "</td>";
							echo "<td>" . "@".$riga["TelegramUsername"] . "</td>";
							echo "<td>";
							if ($riga["Stato"] == 0) {
								echo "<font style='color:lightgreen;'>"._("Chiuso")."</font>";
								$stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
								$stmt->bind_param("s", $riga["IDStaffer"]);
								$esito = $stmt->execute();
								$resultstaff1 = $stmt->get_result();
								$riga3 = $resultstaff1->fetch_assoc();
								$risposta = substr($riga["Risposta"], 0, 30);
								echo "<td>" . $riga3["Username"] . "</td>";
								echo "<td>" . $risposta . "...</td>";
							} elseif ($riga["Stato"] == 1) {
								echo "<font style='color:lightcoral;'>"._("Aperto")."</font>";
								echo "<td><i>"._("Nessuno")."</i></td>";
								echo "<td><i>"._("No risposta")."</i></td>";
							} elseif ($riga["Stato"] == 2) {
								echo "<font style='color:orange;'>"._("Pronto")."</font>";
								$stmt = $connection->prepare("SELECT Username FROM $table_users WHERE ID = ?");
								$stmt->bind_param("s", $riga["IDStaffer"]);
								$esito = $stmt->execute();
								$resultstaff2 = $stmt->get_result();
								$riga3 = $resultstaff2->fetch_assoc();
								$risposta = substr($riga["Risposta"], 0, 30);
								echo "<td>" . $riga3["Username"] . "</td>";
								echo "<td>" . $risposta . "...</td>";
							}
							echo "</td>";
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<?php } else {
				?><h4 style='color:lightcoral;'><?php echo _("Non ci sono tickets da visualizzare."); ?></h4><?php
			}?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
    document.getElementById('jquery').addEventListener('load', function() {
        $("#data").on("click", function() {
            window.location.href = "listaTickets.php?ordine=data";
        });

        $("#id").on("click", function() {
            window.location.href = "listaTickets.php?ordine=id";
        });
    });
</script>