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

if (!getPermission($id, "ModificaNews")) {
	header("Location:pannello.php");
	exit;
}

$stmt = $connection->prepare("SELECT * FROM $table_news ORDER BY ID DESC");
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
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded text-left">
            <h2 align="center"><?php echo _("Creando una News"); ?></h2>
            <br>
            <div class="container text-center" style="width: 60%">
                <form id="form" name="form" action="aggiungiNewsDB.php" method="POST">
                    <input class="form-control" type="text" id="titolo" name="titolo" placeholder="<?php echo _('Titolo'); ?> (<?php echo _('Massimo'); ?> 500 <?php echo _('caratteri'); ?>, <?php echo _('Emoji supportate'); ?>)" autocomplete="off" required></input>
                    <br>
                    <textarea maxlength="10000" class="form-control" rows="8" id="testo" name="testo" placeholder="<?php echo _('Scrivi in quest area la News'); ?> (<?php echo _('Massimo'); ?> 10.000 <?php echo _('caratteri'); ?>, <?php echo _('Emoji supportate'); ?>)" required></textarea>
                    <br>
                    <button class="btn btn-success" type="submit" name="invio"><?php echo _("Invia e aggiungi alla lista News"); ?></button>
                </form>
				<font color="lightcoral"><?php echo sprintf(_("NON INVIARE MAI UNA NEWS SENZA AVER RICHIESTO CONFERMA DA PARTE DEI PROPRIETARI DI %s."), $solutionname_short); ?></font>
				<br><br>
				<?php echo _("Emoji utili"); ?>: 👷 | 🛡 | ❤ | 🎉 | ✅ | ⚠ | ❌
				<br>
            </div>
		</div>
	</div>
	<br>
	<div class="container-fluid text-center text-white">
		<div class="p-2 mb-2 bg-dark text-white rounded text-left">
			<br>
			<h2 align="center"><?php echo _("Modifica una News esistente"); ?></h2>
			<br>
			<?php if($nrighe > 0) {
				?>
				<table class="table table-dark">
					<tr>
						<th><?php echo _("ID News"); ?></th>
						<th><?php echo _("Titolo"); ?></th>
						<th><?php echo _("Testo"); ?></th>
						<th><?php echo _("Data"); ?></th>
						<th><?php echo _("Altro"); ?></th>
					</tr>
					<tbody class="table-striped">
						<?php
						while($riga = $result->fetch_assoc()) {
							echo "<tr>";
							echo "<td>".$riga["ID"]."</td>";
							echo "<td>".$riga["Titolo"]."</td>";
							echo "<td>".$riga["Testo"]."</td>";
							echo "<td>".$riga["Data"]."</td>";
							?><td><a title="<?php echo _('Modifica'); ?>" href="modificaNews.php?id=<?php echo $riga["ID"]; ?>">⚙</a><br><a title="<?php echo _('Elimina'); ?>" style="cursor:pointer;" onClick="eliminaNews(<?php echo $riga["ID"]; ?>);">✖</a></td><?php
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
				<?php
			} else {
				?>
				<p><?php echo _("Non ci sono News."); ?></p>
				<?php
			} ?>
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
			  title: "<?php echo _("Conferma operazione"); ?>",
			  text: "<?php echo _("Cliccando su 'Conferma' la News sarà inviata e sarà visibile nella pagina apposita."); ?>",
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
				$.post('Utils/aggiungiNewsDB.php', {
						titolo: $("#titolo").val(),
						testo: $("#testo").val()
					},
					function(result) {
						if(result == "success") {
							$("#titolo").val("")
							$("#testo").val("");
							swal({
							  title: "<?php echo _("Successo"); ?>",
							  text: "<?php echo _("La News è stata pubblicata con successo."); ?>",
							  type: "success"
							});
						} else if(result == "failure") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Non è stato possibile aggiungere la News."); ?>",
							  type: "error"
							});
						} else if(result == "unset") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Imposta tutti i parametri."); ?>",
							  type: "error"
							});
						} else if(result == "no_permission") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Non hai i permessi."); ?>",
							  type: "error"
							});
						} else {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
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

	function eliminaNews(id_notizia) {
		swal({
		  title: "<?php echo _("Eliminazione News"); ?>",
		  text: "<?php echo _("Cliccando su 'Conferma' la News con ID"); ?> "+id_notizia+" <?php echo _("sarà cancellata"); ?>. <?php echo _("Vuoi continuare?"); ?>",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonText: '<?php echo _("Conferma"); ?>',
		  cancelButtonText: '<?php echo _("Annulla"); ?>'
		})
		.then((result) => {
		  if (result.value) {
			$.ajax({
			type: "POST",
			url: "Utils/eliminaNews.php?id="+id_notizia,
			datatype: "html",
			success:function(result)
			{
				if(result == "success") {
					swal({
					  title: "<?php echo _("Successo"); ?>",
					  text: "<?php echo _("L'operazione è terminata con successo."); ?>",
					  type: "success"
					});
				} else if (result == "failure") {
					swal({
					  title: "<?php echo _("Errore"); ?>",
					  text: "<?php echo _("Si è verificato un errore durante la procedura."); ?>",
					  type: "error"
					});
				} else if (result == "unset") {
					swal({
					  title: "<?php echo _("Errore"); ?>",
					  text: "<?php echo _("Imposta tutti i parametri."); ?>",
					  type: "error"
					});
				} else {
					swal({
					  title: "<?php echo _("Errore imprevisto"); ?>",
					  text: result,
					  type: "error"
					});
				}
			}
			});
		  }
		});
	}
</script>