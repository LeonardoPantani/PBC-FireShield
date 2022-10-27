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

if (!getPermission($id, "ModificaConfig")) {
    header("Location:pannello.php");
    exit;
}


$errore = false;
if (!fopen("../Config/config.php", "r")) {
    $errore = true;
} else {
    $file = fopen("../Config/config.php", "r");
}
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title>Amministrazione <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<style>
textarea {
  width: 100%;
  height: 100vw;
}
</style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
            <h3 align="center">Modifica configurazione <font color="orange"><?php echo $solutionname; ?></font></h3>
			<br>
            <div class="container text-center">
			<?php if ($errore == false) {?>
                <form id="form" name="form" action="modificaConfigDB.php" method="POST">
                    <textarea style="height: 65%" class="form-control" id="configmodificato" name="configmodificato" placeholder="Non salvare il file adesso." spellcheck="false" wrap="off" required><?php while (!feof($file)) {echo fgets($file);}
					fclose($file);?></textarea>
                    <br>
                    <button class="btn btn-success" type="submit" name="invio">Aggiorna configurazione</button>
                </form>
				<?php } else {
					?><h5 style="color:lightcoral;">Si è verificato un errore durante l'apertura del file config.php!</h5><?php
				}?>
                <br>
            </div>
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
			  text: "Cliccando su 'Conferma' la configurazione attuale sarà applicata.",
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
				$.post('Utils/modificaConfigDB.php', {
						configmodificato: $("#configmodificato").val()
					},
					function(result) {
						if(result == "success") {
							swal({
							  title: "Successo",
							  text: "File di configurazione aggiornato.",
							  type: "success"
							});
						} else if(result == "not_found") {
							swal({
							  title: "Si è verificato un errore",
							  text: "File di configurazione non trovato.",
							  type: "error"
							});
						} else if(result == "unset") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Imposta tutti i parametri.",
							  type: "error"
							});
						} else if(result == "file_empty") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Contenuto del file di configurazione non impostato.",
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