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
$errore4 = false;
$errore5 = false;
$errore6 = false;
$errore7 = false;
$errore8 = false;
$errore9 = false;
$errore10 = false;
if (!fopen("../Files/Software/db/HashDatabase.txt", "r")) {
    $errore1 = true;
} else {
    $file1 = fopen("../Files/Software/db/HashDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/LegitException.txt", "r")) {
    $errore2 = true;
} else {
    $file2 = fopen("../Files/Software/db/LegitException.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/ModDatabase.txt", "r")) {
    $errore3 = true;
} else {
    $file3 = fopen("../Files/Software/db/ModDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/VersionDatabase.txt", "r")) {
    $errore4 = true;
} else {
    $file4 = fopen("../Files/Software/db/VersionDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/BadProcess.txt", "r")) {
    $errore5 = true;
} else {
    $file5 = fopen("../Files/Software/db/BadProcess.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/DllDatabase.txt", "r")) {
    $errore6 = true;
} else {
    $file6 = fopen("../Files/Software/db/DllDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/ClassDatabase.txt", "r")) {
    $errore7 = true;
} else {
    $file7 = fopen("../Files/Software/db/ClassDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/DownloadDatabase.txt", "r")) {
    $errore8 = true;
} else {
    $file8 = fopen("../Files/Software/db/DownloadDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/WebsiteDatabase.txt", "r")) {
    $errore9 = true;
} else {
    $file9 = fopen("../Files/Software/db/WebsiteDatabase.txt", "r");
}
// ---
if (!fopen("../Files/Software/db/CheatSmasherDatabase.txt", "r")) {
    $errore10 = true;
} else {
    $file10 = fopen("../Files/Software/db/CheatSmasherDatabase.txt", "r");
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
        <br><br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
            <h2 align="center">Modifica File Software</h2>
			<div align="center">
				<?php if (isset($_GET["e"])) {if ($_GET["e"] == 1) {echo "<span style='color:lightgreen;'>File aggiornati!</span>";} elseif ($_GET["e"] == 2) {echo "<span style='color:lightcoral;'>Errore: File non trovati.</span>";} elseif ($_GET["e"] == 3) {echo "<span style='color:lightcoral;'>Imposta tutti i parametri.</span>";} elseif ($_GET["e"] == 4) {echo "<span style='color:lightcoral;'>I file non devono essere vuoti!</span>";} }?>
            </div>
            <div class="container text-left">
                <?php if(!$errore1 && !$errore2 && !$errore3 && !$errore4 && !$errore5 && !$errore6 && !$errore7 && !$errore18 && !$errore9 && !$errore10) { ?>
                <form name="modificaSoftware" id="modificaSoftware" action="modificaSoftwareDB.php" method="POST">
					<h5>HashDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione1" name="versione1" placeholder="Non salvare il file adesso." required><?php if($file1) { while (!feof($file1)) {echo fgets($file1);} }
                    fclose($file1);?></textarea>
                    <br>
                    <hr>
					<h5>LegitException.txt</h5>
                    <textarea class="form-control" rows="3" id="versione2" name="versione2" placeholder="Non salvare il file adesso." required><?php if($file2) { while (!feof($file2)) {echo fgets($file2);} }
                    fclose($file2);?></textarea>
                    <br>
                    <hr>
					<h5>ModDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione3" name="versione3" placeholder="Non salvare il file adesso." required><?php if($file3) { while (!feof($file3)) {echo fgets($file3);} }
                    fclose($file3);?></textarea>
                    <br>
                    <hr>
                    <h5>VersionDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione4" name="versione4" placeholder="Non salvare il file adesso." required><?php if($file4) { while (!feof($file4)) {echo fgets($file4);} }
                    fclose($file4);?></textarea>
                    <br>
                    <hr>
                    <h5>BadProcess.txt</h5>
                    <textarea class="form-control" rows="3" id="versione5" name="versione5" placeholder="Non salvare il file adesso." required><?php if($file5) { while (!feof($file5)) {echo fgets($file5);} }
                    fclose($file5);?></textarea>
                    <br>
                    <hr>
                    <h5>DllDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione6" name="versione6" placeholder="Non salvare il file adesso." required><?php if($file6) { while (!feof($file6)) {echo fgets($file6);} }
                    fclose($file6);?></textarea>
                    <br>
                    <hr>
                    <h5>ClassDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione7" name="versione7" placeholder="Non salvare il file adesso." required><?php if($file7) { while (!feof($file7)) {echo fgets($file7);} }
                    fclose($file7);?></textarea>
                    <br>
                    <hr>
                    <h5>DownloadDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione8" id="versione9" name="versione8" placeholder="Non salvare il file adesso." required><?php if($file8) { while (!feof($file8)) {echo fgets($file8);} }
                    fclose($file8);?></textarea>
                    <br>
                    <hr>
                    <h5>WebsiteDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione9" name="versione9" placeholder="Non salvare il file adesso." required><?php if($file9) { while (!feof($file9)) {echo fgets($file9);} }
                    fclose($file9);?></textarea>
                    <br>
                    <hr>
                    <h5>CheatSmasherDatabase.txt</h5>
                    <textarea class="form-control" rows="3" id="versione10" name="versione10" placeholder="Non salvare il file adesso." required><?php if($file10) { while (!feof($file10)) {echo fgets($file10);} }
                    fclose($file10);?></textarea>
                    <br>
                    <hr>
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
        $("#modificaSoftware").submit(function(e) {
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
				$.post('Utils/modificaSoftwareDB.php', {
						versione1: $("#versione1").val(),
						versione2: $("#versione2").val(),
						versione3: $("#versione3").val(),
						versione4: $("#versione4").val(),
						versione5: $("#versione5").val(),
						versione6: $("#versione6").val(),
						versione7: $("#versione7").val(),
						versione8: $("#versione8").val(),
						versione9: $("#versione9").val(),
						versione10: $("#versione10").val()
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