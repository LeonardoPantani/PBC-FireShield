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

if (!getPermission($id, "CronologiaEventi")) {
	header("Location:pannello.php");
	exit;
}

if(isset($_GET["id"])) {
	$idutente = $_GET["id"];
} else {
	header("Location:pannello.php");
	exit;
}

$usernameutente = getUsernameFromID($idutente);

$stmt = $connection->prepare("SELECT $table_usernamechange.* FROM $table_usernamechange WHERE IDUtente = ? ORDER BY Data DESC");
$stmt->bind_param("i", $idutente);
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
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
            <h4><?php echo sprintf(_("Cronologia di %s"), $usernameutente); ?> (<?php echo _("ID:"); ?> <?php echo $idutente; ?>)</h4>
            <?php if ($nrighe > 0) {?>
			<a href="pannello.php"><?php echo _("Clicca per tornare al pannello"); ?></a>
			<br>
			<br>
            <table class="table table-dark">
                <tr>
                    <th><?php echo _("ID Cambio"); ?></th>
                    <th><?php echo _("Data"); ?></th>
					<th><?php echo _("Modifica Username"); ?></th>
                </tr>
                <tbody class="table-striped">
                    <?php
					while ($riga = $result->fetch_assoc()) {
						echo "<tr>";
						echo "<td>" . $riga["IDCambio"] . "</td>";
						echo "<td>" . formatDateComplete($riga["Data"], true) . "</td>";
						echo "<td>" . $riga["UsernamePrecedente"] . " <b>→</b> " . $riga["UsernameNuovo"] . "</td>";
						echo "</tr>";
					}
					?>
                </tbody>
            </table>
            <?php } else {
				echo _("No cronologia per questo utente.");
			}?>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>