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

if (!getPermission($id, "Account_Permissions")) {
	header("Location:pannello.php");
	exit;
}


$stmt = $connection -> prepare("SELECT $table_users.Username, $table_permissions.* FROM $table_users, $table_permissions WHERE $table_users.ID = $table_permissions.IDUtente AND $table_users.Tipo = 1 ORDER BY $table_users.ID ASC");

$stmt -> execute();
$result1 = $stmt -> get_result();
$nrighe1 = $result1 -> num_rows;

$stmt = $connection -> prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$table_permissions'");

$stmt -> execute();
$result2 = $stmt -> get_result();
$nrighe2 = $result2 -> num_rows;
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Amministrazione")." "; echo $solutionname; ?></title>
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

a { color: #FFFFFF; }
</style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
	<div class="loader"></div>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<h4><?php echo sprintf(_("Totale amministratori: %s."), "<font style='color:lightblue;'>".$nrighe1."</font>", "<font style='color:orange;'>".$amministratori_totali."</font>", "<font style='color:orange;'>".$solutionname."</font>"); ?></h4>
			<?php if($nrighe1 > 0) { ?>
            <table class="table table-dark table-bordered">
                <tr>
					<?php
						while($riga = $result2 -> fetch_assoc()) {
							?><td><small><?php if($riga["COLUMN_NAME"] == "IDUtente") { echo _("Utente"); } else { echo $riga["COLUMN_NAME"]; }; ?></small></th><?php
						}
					?>
                </tr>
                <tbody class="table-striped">
                    <?php
						while($riga1 = $result1 -> fetch_assoc()) {
							echo "<tr>";
							foreach($riga1 as $chiave => $valore) {
								if($chiave == "Username") {
									$id_attuale = getIDFromUsername($valore);
									echo "<td>";
									echo $valore;
									echo "</td>";
								} else if($chiave != "IDUtente") {
									if($valore == "1") {
										echo "<td bgcolor='#0f6903'>";
										echo "<a href='modificaPermessi.php?id=$id_attuale&np=$chiave'>";
										echo _("Sì");
									} else if($valore == "0") {
										echo "<td bgcolor='#b80f0f'>";
										echo "<a href='modificaPermessi.php?id=$id_attuale&np=$chiave'>";
										echo _("No");
									} else {
										echo "<td>";
										echo "<a href=''>".$valore."</a>";
									}
									echo "</a>";
									echo "</td>";
								}

							}
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<p style="color:lightgray;"><small><?php echo _("Attenzione: Non è possibile modificare i permessi di un utente con il permesso 'Account_Permissions'."); ?></small></p>
			<?php } else { ?>
				<p style="color:lightcoral;"><?php echo sprintf(_("La tabella %s non ha valori al suo interno."), "<b>".$table_users."</b>"); ?></p>
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