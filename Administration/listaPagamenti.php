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

if (!getPermission($id, "ListaPagamenti")) {
	header("Location:pannello.php");
	exit;
}

$stmt = $connection -> prepare("SELECT * FROM $table_payments, $table_users WHERE $table_payments.IDUtente = $table_users.ID ORDER BY DataPagamento DESC");
$stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
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
			<h4>Lista dei pagamenti effettuati (<font style="color:lightblue;"><?php echo $nrighe; ?></font> in totale)</h4>
			<?php if($nrighe > 0) { ?>
            <table class="table table-dark">
                <tr>
                    <th>ID Licenza</th>
					<th>ID Utente</th>
					<th>Username</th>
					<th>Paypal ID</th>
					<th>Email</th>
					<th>Nome</th>
					<th>Cognome</th>
					<th>Residenza</th>
					<th>Licenza</th>
					<th>Prezzo Licenza</th>
					<th>Tasse</th>
					<th>Data Pagamento</th>
                </tr>
                <tbody class="table-striped">
                    <?php
						mysqli_data_seek($result, 0);
						while ($riga = $result->fetch_assoc()) {
							echo "<tr>";
							
							echo "<td>";
							if($riga["IDLicenza"] == "") {
								echo "//";
							} else {
								echo $riga["IDLicenza"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["ID"] == "") {
								echo "//";
							} else {
								echo $riga["ID"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Username"] == "") {
								echo "//";
							} else {
								echo $riga["Username"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["PaypalID"] == "") {
								echo "//";
							} else {
								echo $riga["PaypalID"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Email"] == "") {
								echo "//";
							} else {
								echo $riga["Email"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Nome"] == "") {
								echo "//";
							} else {
								echo $riga["Nome"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Cognome"] == "") {
								echo "//";
							} else {
								echo $riga["Cognome"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Residenza_Paese"] == "") {
								echo "//";
							} else {
								echo $riga["Residenza_Paese"]." (".$riga["Residenza_CodicePaese"]."), ".$riga["Residenza_ZIP"].", ".$riga["Residenza_Regione"].", ".$riga["Residenza_Citta"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["TipoLicenza"] == "") {
								echo "//";
							} else {
								if($riga["TipoLicenza"] == "single") {
									echo "Singola";
								} elseif($riga["TipoLicenza"] == "network") {
									echo "Network";
								} else {
									echo "???";
								}
								echo " (";
								if($riga["DurataLicenza"] == "monthly") {
									echo "mensile";
								} elseif($riga["DurataLicenza"] == "permanent") {
									echo "permanente";
								} else {
									echo "???";
								}
								echo ")";
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Prezzo"] == "") {
								echo "//";
							} else {
								echo $riga["Prezzo"]." ".$riga["Moneta"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Tasse"] == "") {
								echo "//";
							} else {
								echo $riga["Tasse"]." ".$riga["Moneta"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["DataPagamento"] == "") {
								echo "//";
							} else {
								echo formatDate($riga["DataPagamento"], true);
							}
							echo "</td>";
							
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<?php } else { ?>
				<p style="color:lightcoral;">La tabella <b><?php echo $table_payments; ?></b> non ha valori al suo interno.</p>
			<?php } ?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>