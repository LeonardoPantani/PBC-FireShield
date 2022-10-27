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

if (!getPermission($id, "ModificaNewsLetter")) {
	header("Location:pannello.php");
	exit;
}

$stmt = $connection -> prepare("SELECT $table_users.ID, $table_users.Email, $table_preferences.IDUtente, $table_preferences.NewsLetter FROM $table_users, $table_preferences WHERE $table_users.ID != 1 AND $table_users.Email != '' AND $table_users.ID = $table_preferences.IDUtente AND $table_preferences.NewsLetter = 1");
$esito = $stmt->execute();
$result = $stmt->get_result();
$nrighe = $result->num_rows;
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Amministrazione"); ?> <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
            <div class="container text-center" style="width: 60%">
				<h2><?php echo _("Preparazione NewsLetter"); ?></h2>
				<p><?php echo _("Si può inserire l'username dell'utente scrivendo <b>[username]</b> all'interno del messaggio."); ?></p>
				<br>
                <form id="form" name="form" action="Utils/inviaNewsLetterDB.php" method="POST">
					<!-- ITALIANO -->
                    <input class="form-control" type="text" id="titolo_italiano" name="titolo_italiano" placeholder="<?php echo _('Titolo'); ?> (Italiano)" autocomplete="off" required></input>
                    <br>
                    <textarea maxlength="10000" class="form-control" rows="5" id="testo_italiano" name="testo_italiano" placeholder="<?php echo _('Corpo della NewsLetter'); ?> (Italiano) - 10.000 <?php echo _('caratteri massimi'); ?>" required></textarea>
                    <hr>
					<!-- INGLESE -->
                    <input class="form-control" type="text" id="titolo_inglese" name="titolo_inglese" placeholder="<?php echo _('Titolo'); ?> (English)" autocomplete="off" required></input>
                    <br>
                    <textarea maxlength="10000" class="form-control" rows="5" id="testo_inglese" name="testo_inglese" placeholder="<?php echo _('Corpo della NewsLetter'); ?> (English) - 10.000 <?php echo _('caratteri massimi'); ?>" required></textarea>
                    <hr>
					<!-- FRANCESE -->
                    <input class="form-control" type="text" id="titolo_francese" name="titolo_francese" placeholder="<?php echo _('Titolo'); ?> (Français)" autocomplete="off" required></input>
                    <br>
                    <textarea maxlength="10000" class="form-control" rows="5" id="testo_francese" name="testo_francese" placeholder="<?php echo _('Corpo della NewsLetter'); ?> (Français) - 10.000 <?php echo _('caratteri massimi'); ?>" required></textarea>
                    <br>
					<button id="invio" class="btn btn-success" type="submit" name="invio"><?php echo _("Invia la NewsLetter"); ?> 💨📧</button>
					<br>
					<hr>
					<font color="lightcoral"><?php echo sprintf(_("NON INVIARE MAI UNA NEWSLETTER SENZA AVER RICHIESTO CONFERMA DA PARTE DEI PROPRIETARI DI %s."), $solutionname_short); ?></font>
                </form>
            </div>
        </div>
		<br><br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
            <div class="container text-center" style="width: 60%">
				<h2><?php echo _("Test NewsLetter"); ?></h2>
				<p>
					<?php echo _("Qui è possibile provare la corretta formattazione della newsletter inviandone un'anteprima alla casella di posta specificata."); ?>
					<br>
					<?php echo _("Si può inserire l'username dell'utente scrivendo <b>[username]</b> all'interno del messaggio."); ?>
				</p>
				<br>
				<form id="form2" name="form2" action="Utils/inviaNewsLetterTestDB.php" method="POST">
					<input class="form-control" type="email" id="mail_test" name="mail_test" placeholder="<?php echo _('Casella di posta'); ?>" autocomplete="off" required></input>
					<br>
					<button id="invio2" class="btn btn-success" type="submit" name="invio2"><?php echo _("Test NewsLetter"); ?> 👷</button>
				</form>
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
			  text: "<?php echo _("Cliccando su 'Conferma' la Newsletter sarà inviata a"); echo ' '.$nrighe; ?> <?php echo _("utenti"); ?>. <?php echo _("L'operazione potrebbe richiedere alcuni secondi."); ?>",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonText: '<?php echo _("Conferma"); ?>',
			  cancelButtonText: '<?php echo _("Annulla"); ?>'
			})
			.then((result) => {
			  if (result.value) {
				swal({
				  title: "<?php echo _("Attendi..."); ?>",
				  text: "<?php echo _("Elaborazione dati."); ?>",
				  type: "info"
				});
				$.post('Utils/inviaNewsLetterDB.php', {
                    titolo_italiano: $("#titolo_italiano").val(),
					titolo_inglese: $("#titolo_inglese").val(),
					titolo_francese: $("#titolo_francese").val(),
					testo_italiano: $("#testo_italiano").val(),
					testo_inglese: $("#testo_inglese").val(),
					testo_francese: $("#testo_francese").val()
                },
                function(result) {
                    if(result == "success") {
						swal({
						  title: "<?php echo _("Successo"); ?>",
						  text: "<?php echo _("L'operazione è terminata con successo."); ?>",
						  type: "success",
						});
						$("#titolo_italiano").val("");
						$("#titolo_inglese").val("");
						$("#titolo_francese").val("");
						$("#testo_italiano").val("");
						$("#testo_inglese").val("");
						$("#testo_francese").val("");
                    } else if(result == "failure") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Si è verificato un errore durante la procedura."); ?>",
						  type: "error"
						});
                    } else if(result == "unset") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Imposta tutti i parametri."); ?>",
						  type: "error"
						});
                    } else if(result == "no_permission") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Non hai i permessi."); ?>",
						  type: "error"
						});
					} else if(result == "no_rows") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Non ci sono mail a cui mandare la NewsLetter."); ?>",
						  type: "error"
						});
                    } else {
						swal({
						  title: "<?php echo _("Errore imprevisto"); ?>",
						  text: result,
						  type: "error"
						});
                    }
                });
			  }
			});
		});
		
        $("#form2").submit(function(e) {
			e.preventDefault();
			swal({
			  title: "Conferma operazione",
			  text: "<?php echo _("Cliccando su 'Conferma' la Newsletter sarà inviata alla casella di posta specificata"); ?>",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonText: '<?php echo _("Conferma"); ?>',
			  cancelButtonText: '<?php echo _("Annulla"); ?>'
			})
			.then((result) => {
			  if (result.value) {
				swal({
				  title: "<?php echo _("Attendi..."); ?>",
				  text: "<?php echo _("Elaborazione dati."); ?>",
				  type: "info"
				});
				$.post('Utils/inviaNewsLetterTestDB.php', {
                    titolo_italiano: $("#titolo_italiano").val(),
					titolo_inglese: $("#titolo_inglese").val(),
					titolo_francese: $("#titolo_francese").val(),
					testo_italiano: $("#testo_italiano").val(),
					testo_inglese: $("#testo_inglese").val(),
					testo_francese: $("#testo_francese").val(),
					mail_test: $("#mail_test").val()
                },
                function(result) {
                    if(result == "success") {
						swal({
						  title: "<?php echo _("Successo"); ?>",
						  text: "<?php echo _("L'operazione è terminata con successo."); ?>",
						  type: "success",
						});
                    } else if(result == "failure") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Si è verificato un errore durante la procedura."); ?>",
						  type: "error"
						});
                    } else if(result == "unset") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Imposta tutti i parametri."); ?>",
						  type: "error"
						});
                    } else if(result == "no_permission") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Non hai i permessi."); ?>",
						  type: "error"
						});
					} else if(result == "no_rows") {
						swal({
						  title: "<?php echo _("Errore"); ?>",
						  text: "<?php echo _("Non ci sono mail a cui mandare la NewsLetter."); ?>",
						  type: "error"
						});
                    } else {
						swal({
						  title: "<?php echo _("Errore imprevisto"); ?>",
						  text: result,
						  type: "error"
						});
                    }
                });
			  }
			});
		});
	});
</script>