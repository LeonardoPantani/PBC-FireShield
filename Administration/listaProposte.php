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

if (isset($_GET["ordine"])) {
    if ($_GET["ordine"] == "data") {
        $stmt = $connection->prepare("SELECT * FROM $table_suggestions ORDER BY Data DESC");
    } else if ($_GET["ordine"] == "id") {
        $stmt = $connection->prepare("SELECT * FROM $table_suggestions ORDER BY IDProposta ASC");
    } else {
        $stmt = $connection->prepare("SELECT * FROM $table_suggestions ORDER BY Data DESC");
    }
} else {
    $stmt = $connection->prepare("SELECT * FROM $table_suggestions ORDER BY Data DESC");
}
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;

$stmt = $connection->prepare("SELECT IDProposta FROM $table_suggestions WHERE Stato = 0");
$stmt->execute();
$result2 = $stmt->get_result();
$righeaperti = $result2->num_rows;
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
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <h4><?php echo $nrighe . " ";if ($nrighe == 1) {echo "proposta";} else {echo "proposte";} ?> (Da vedere: <b><?php echo $righeaperti; ?></b>)</h4>
			<br>
            <?php if ($nrighe > 0) {?>
            <b>Come vuoi ordinare la tabella?</b> <input type="radio" name="ordine" id="data" <?php if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "data") {echo "checked";}} else {echo "checked";}?>> Data (<i>Default</i>) - <input type="radio" name="ordine" id="id" <?php if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "id") {echo "checked";}}?>> ID<br><br>
            <table class="table table-dark">
                <tr>
                    <th>ID Proposta <?php if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "id") {echo "<b>*</b>";}}?></th>
                    <th>Richiedente</th>
					<th>Data <?php if (isset($_GET["ordine"])) {if ($_GET["ordine"] == "data") {echo "<b>*</b>";}} else {echo "<b>*</b>";}?></th>
                    <th>Stringa</th>
                    <th>Client</th>
					<th>Note</th>
                    <th>Stato</th>
                    <th>Staffer</th>
					<th>Azione</th>
                </tr>
                <tbody class="table-striped">
                    <?php
						while ($riga = $result->fetch_assoc()) {
							$stringa = substr($riga["Stringa"], 0, 50);
							$nota = substr($riga["Note"], 0, 50);
							echo "<tr>";
							echo "<td>".$riga["IDProposta"]."</td>";
							echo "<td>".getUsernameFromID($riga["IDUtente"])."</td>";
							echo "<td>".$riga["Data"]."</td>";
							echo "<td>".$stringa."</td>";
							echo "<td>".$riga["Client"]."</td>";
							echo "<td>".$nota."</td>";
							echo "<td><b>";
							if($riga["Stato"] == 0) { // Aperta
								echo "<font style='color:orange;'>Da vedere</font>";
							} elseif($riga["Stato"] == 1) { // Approvata
								echo "<font style='color:lightgreen;'>Approvata</font>";
							} elseif($riga["Stato"] == 2) { // Negata
								echo "<font style='color:lightcoral;'>Negata</font>";
							} else {
								echo "?";
							}
							echo "</b></td>";
							echo "<td>";
							if($riga["IDStaffer"] == "") {
								echo "(No staffer)";
							} else {
								echo getUsernameFromID($riga["IDStaffer"]);
							}
							?><td><button class="btn btn-info" onClick="location.href='visualizzaProposta.php?id=<?php echo $riga["IDProposta"]; ?>'">Visualizza proposta</button></td><?php
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<?php } else {
				?><h4 style='color:lightcoral;'>Non ci sono proposte da visualizzare.</h4><?php
			}?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
    document.getElementById('jquery').addEventListener('load', function() {
        $("#data").on("click", function() {
            window.location.href = "listaProposte.php?ordine=data";
        });

        $("#id").on("click", function() {
            window.location.href = "listaProposte.php?ordine=id";
        });
    });
</script>