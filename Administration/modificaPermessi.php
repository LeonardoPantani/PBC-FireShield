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

if(!isset($_GET["id"]) || !isset($_GET["np"])) {
	header("Location:pannello.php");
	exit;
}

$id_utente = $_GET["id"];
$nome_permesso = $_GET["np"];

$errore = false;
$stmt = $connection->prepare("SELECT * FROM $table_permissions WHERE IDUtente = ?");
if($stmt) {
	$stmt->bind_param("i", $id_utente);
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

if(!isset($riga[$nome_permesso])) {
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
				<h3 align="center">Modificando il permesso "<b><?php echo $nome_permesso; ?></b>" di <?php echo getUsernameFromID($id_utente); ?></h3>
				<a href="listaPermessi.php" title="Vai alla Lista Permessi"><small>Clicca qui per andare alla Lista Permessi&nbsp;<span class="oi oi-external-link"></span></small></a>
				<br><br>
				<?php if(getPermission($id_utente, "Account_Permissions") == 1 && $id != $id_utente) { ?><p style="color:red;"><?php echo _("Attenzione: Questo account ha dei privilegi troppo elevati per modificarne i permessi."); ?></p><?php } ?>
				<hr>
				<div class="container text-center" style="width: 60%">
					<form id="form" name="form" action="modificaPermessiDB.php" method="POST">
						<input style="display:none;" type="text" id="id_utente" name="id_utente" value="<?php echo $id_utente; ?>"></input>
						<input style="display:none;" type="text" id="nome_permesso" name="nome_permesso" value="<?php echo $nome_permesso; ?>"></input>
						
						<b>VALORE PERMESSO</b>
						<br>
						<input class="form-control" type="number" id="valore_permesso" name="valore_permesso" value="<?php echo $riga[$nome_permesso]; ?>" placeholder="Permesso" autocomplete="off" required></input>
						<br><br>
						<br>
						<button class="btn btn-success" type="submit" name="invio">Modifica permesso</button>
					</form>
				</div>
			<?php } else {
				?><p style="color:lightcoral;">Il permesso <?php echo $nome_permesso; ?> o l'utente con ID <?php echo $id_utente; ?> non esistono.</p><?php
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
			  text: "Cliccando su 'Conferma' il permesso sarà modificato.",
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
				$.post('Utils/modificaPermessiDB.php', {
						id_utente: $("#id_utente").val(),
						nome_permesso: $("#nome_permesso").val(),
						valore_permesso: $("#valore_permesso").val()
					},
					function(result) {
						if(result == "success") {
							swal({
							  title: "Successo",
							  text: "Permesso modificato.",
							  type: "success"
							});
						} else if(result == "failure") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Non è stato possibile aggiornare il permesso.",
							  type: "error"
							});
						} else if(result == "invalid_action") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Non puoi modificare i permessi di quell'account.",
							  type: "error"
							});
						} else if(result == "invalid_value") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Valore permesso non valido, deve essere 0 o 1.",
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