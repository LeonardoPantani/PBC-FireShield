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

if (isset($_GET["order"])) {
    if ($_GET["order"] == "quantity") {
        $stmt = $connection->prepare("SELECT * FROM $table_historycheat ORDER BY Quantita DESC");
    } else if ($_GET["order"] == "date") {
        $stmt = $connection->prepare("SELECT * FROM $table_historycheat ORDER BY Data DESC");
    } else {
        $stmt = $connection->prepare("SELECT * FROM $table_historycheat ORDER BY Quantita DESC");
    }
} else {
    $stmt = $connection->prepare("SELECT * FROM $table_historycheat ORDER BY Quantita DESC");
}
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
            <h4>Cronologia: <?php echo $nrighe . " ";if ($nrighe == 1) {echo "evento";} else {echo "eventi";}
			echo " cheat."; ?></h4>
            <?php if ($nrighe > 0) {?>
			<br>
            <table class="table table-dark">
                <tr>
                    <th>ID Cronologia</th>
                    <th>Stringa trovata</th>
					<th>Client relativo</th>
                    <th>Tabella</th>
                    <th><a href="listaCronologiaCheat.php?order=date">Data <?php if (isset($_GET["order"])) {if ($_GET["order"] == "date") {echo "<b>*</b>";}}?></a></th>
                    <th><a href="listaCronologiaCheat.php?order=quantity">Quantità <?php if (isset($_GET["order"])) {if ($_GET["order"] == "quantity") {echo "<b>*</b>";}} else {echo "<b>*</b>";}?></a></th>
                </tr>
                <tbody class="table-striped">
                    <?php
					while ($riga = $result->fetch_assoc()) {
						echo "<tr>";
						echo "<td>" . $riga["ID"] . "</td>";
						if(strlen($riga["Stringa"]) > 200) {
							echo "<td>" . substr($riga["Stringa"], 0, 200) . "</td>";
						} else {
							echo "<td>" . $riga["Stringa"] . "</td>";
						}
						$tabella = $riga["Tabella"];
						
						$stmt = $connection -> prepare("SELECT Client FROM $tabella WHERE Stringa = ?");
						$stmt -> bind_param("s", $riga["Stringa"]);
						$stmt->execute();
						$esito = $stmt -> get_result();
						$result2 = $esito->fetch_assoc();
						if($result2["Client"] == "") {
							echo "<td><font style='color:lightcoral;'>Obsoleta</font></td>";
						} else {
							echo "<td>" . $result2["Client"] . "</td>";
						}
						echo "<td>" . $riga["Tabella"] . "</td>";
						echo "<td>" . formatDate($riga["Data"], true) . "</td>";
						echo "<td>" . $riga["Quantita"] . "</td>";
						echo "</tr>";
					}
					?>
                </tbody>
            </table>
            <?php } else {
				echo "No cronologia cheat.";
			}?>
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
		
		
        $("#quantita").on("click", function() {
            window.location.href = "listaCronologiaCheat.php?ordine=quantita";
        });

        $("#data").on("click", function() {
            window.location.href = "listaCronologiaCheat.php?ordine=data";
        });
    });
</script>