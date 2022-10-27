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

if (!getPermission($id, "ModificaSoftware")) {
    header("Location:pannello.php");
    exit;
}

$errore1 = false;
$errore2 = false;
$errore3 = false;
if (!fopen("../Files/Software/versioni.txt", "r")) {
    $errore1 = true;
} else {
    $versione1 = fopen("../Files/Software/versioni.txt", "r");
}
// ---
if (!fopen("../Files/Software/versionisoftware.txt", "r")) {
    $errore2 = true;
} else {
    $versione2 = fopen("../Files/Software/versionisoftware.txt", "r");
}
// ---
if (!fopen("../Files/Software/versionecorrentesoftware.txt", "r")) {
    $errore3 = true;
} else {
    $versione3 = fopen("../Files/Software/versionecorrentesoftware.txt", "r");
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
    <div class="container-fluid text-center text-white" style="width: 80%">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
            <h2 align="center">Modifica Versioni</h2>
            <div class="container text-left">
                <?php if(!$errore1 && !$errore2 && !$errore3) { ?>
                <form id="modificaVersione" name="modificaVersione" id="modificaVersione" action="modificaVersioneDB.php" method="POST">
					<h5>Versioni.txt</h5>
                    <textarea class="form-control" rows="3" id="versione1" name="versione1" placeholder="Non salvare il file adesso." required><?php while (!feof($versione1)) {echo fgets($versione1);}
                    fclose($versione1);?></textarea>
                    <br>
                    <hr>
					<h5>Versionisoftware.txt</h5>
                    <textarea class="form-control" rows="3" id="versione2" name="versione2" placeholder="Non salvare il file adesso." required><?php while (!feof($versione2)) {echo fgets($versione2);}
                    fclose($versione2);?></textarea>
                    <br>
                    <hr>
					<h5>Versionecorrentesoftware.txt</h5>
                    <textarea class="form-control" rows="3" id="versione3" name="versione3" placeholder="Non salvare il file adesso." required><?php while (!feof($versione3)) {echo fgets($versione3);}
                    fclose($versione3);?></textarea>
                    <br>
					<div class="container text-center">
						<button type="submit" class="btn btn-success" name="invio">Aggiorna TUTTI i file</button>
					</div>
                </form>
                <?php } else { ?>
                    <p style="color:lightcoral;">Non è stato possibile aprire almeno uno dei file.</p>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html> <!-- titolo & testo -->
<script>
    document.getElementById('jquery').addEventListener('load', function() {	
        $("#modificaVersione").submit(function(e) {
            e.preventDefault();
			swal({
			  title: "Conferma operazione",
			  text: "Cliccando su 'Conferma' tutti i file saranno aggiornati.",
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
				$.post('Utils/modificaVersioneDB.php', {
						versione1: $("#versione1").val(),
						versione2: $("#versione2").val(),
						versione3: $("#versione3").val()
					},
					function(result) {
						if(result == "success") {
							swal({
							  title: "Successo",
							  text: "Versioni aggiornate.",
							  type: "success"
							});
						} else if(result == "failure") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Non è stato possibile aggiornare le versioni.",
							  type: "error"
							});
						} else if(result == "not_found") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Almeno un file di versione non è stato trovato.",
							  type: "error"
							});
						} else if(result == "string_empty") {
							swal({
							  title: "Si è verificato un errore",
							  text: "Le stringhe non possono essere vuote.",
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