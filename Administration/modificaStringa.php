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

if (!getPermission($id, "ModificaStringhe")) {
	header("Location:pannello.php");
	exit;
}

if(!isset($_GET["id"]) || !isset($_GET["table"])) {
	header("Location:pannello.php");
	exit;
}

$tabella = $_GET["table"];
$idstringa = $_GET["id"];
$errore = false;

$stmt = $connection->prepare("SELECT * FROM $tabella WHERE ID = ?");
if($stmt) {
	$stmt->bind_param("i", $idstringa);
	$stmt->execute();
	$result = $stmt->get_result();
	$nrighe = $result->num_rows;
	if($nrighe > 0) {
		$riga = $result->fetch_assoc();
	} else {
		$errore = true;
	}
} else {
	$errore = true;
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
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<br><br>
			<?php if(!$errore) { ?>
				<h3 align="center">Modificando la stringa con ID <b><?php echo $idstringa; ?></b> (<b><?php echo $tabella; ?></b>)</h3>
				<a href="listaStringhe.php" title="Vai alla Lista Stringhe"><small>Clicca qui per andare alla Lista Stringhe&nbsp;<span class="oi oi-external-link"></span></small></a>
				<br><br>
				<hr>
				<div class="container text-center" style="width: 60%">
					<form id="form" name="form" action="modificaStringaDB.php" method="POST">
						<input style="display:none;" type="text" id="id_stringa" name="id_stringa" value="<?php echo $idstringa; ?>"></input>
						<input style="display:none;" type="text" id="nome_tabella" name="nome_tabella" value="<?php echo $tabella; ?>"></input>
						
						<b>VALORE STRINGA</b>
						<br>
						<input class="form-control" type="text" id="stringa" name="stringa" value="<?php echo $riga['Stringa']; ?>" placeholder="Stringa" autocomplete="off" required></input>
						<br><br>
						<b>NOME CLIENT</b>
						<br>
						<input class="form-control" type="text" id="client" name="client" value="<?php echo $riga['Client']; ?>" placeholder="Nome client" autocomplete="off" required></input>
						<br>
						<button class="btn btn-success" type="submit" name="invio">Modifica stringa</button>
					</form>
				</div>
			<?php } else {
				?><p style="color:lightcoral;">La tabella <b><?php echo $tabella; ?></b> non esiste o non ha valori al suo interno.</p><?php
			}	
			?>
			<br>
		</div>
	</div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html> <!-- titolo & testo -->
<script>
    document.getElementById('jquery').addEventListener('load', function() {	
        $("#form").submit(function(e) {
            e.preventDefault();
			swal({
			  title: "Conferma operazione",
			  text: "Cliccando su 'Conferma' la Stringa sarà modificata.",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonText: 'Conferma',
			  cancelButtonText: 'Annulla'
			})
			.then((result) => {
			  if (result.value) {
				swal({
				  title: "Attendi...",
				  text: "Elaborazione dati.",
				  type: "info"
				});
				$.post('Utils/modificaStringaDB.php', {
						id_stringa: $("#id_stringa").val(),
						nome_tabella: $("#nome_tabella").val(),
						stringa: $("#stringa").val(),
						client: $("#client").val()
					},
					function(result) {
						if(result == "success") {
							swal({
							  title: "Successo",
							  text: "La Stringa è modificata con successo.",
							  type: "success"
							});
						} else if(result == "failure") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Non è stato possibile aggiornare la Stringa.",
							  type: "error"
							});
						} else if(result == "unset") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Imposta tutti i parametri.",
							  type: "error"
							});
						} else if(result == "no_permission") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Non hai i permessi.",
							  type: "error"
							});
						} else {
							swal({
							  title: "Si è verificato un errore",
							  text: result,
							  type: "error"
							});
						}
					}
				);
			  }
			});
        });
	});
</script>