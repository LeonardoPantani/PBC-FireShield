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

if(!isset($_GET["id"]) || $_GET["id"] < 0) {
	header("Location:aggiungiNews.php");
	exit;
}

$stmt = $connection->prepare("SELECT * FROM $table_news WHERE ID = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$result = $stmt->get_result();
$riga = $result->fetch_assoc();
$riga["Testo"] = str_replace("<br />", "", $riga["Testo"]);
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
            <h2 align="center"><?php echo _("Modificando la News"); ?> <b><?php echo $_GET["id"]; ?></b></h2>
            <br>
            <div class="container text-center" style="width: 60%">
                <form id="form" name="form" action="modificaNewsDB.php" method="POST">
					<input style="display:none;" type="text" id="id_notizia" name="id_notizia" value="<?php echo $_GET["id"]; ?>"></input>
					
                    <input class="form-control" type="text" id="titolo" name="titolo" value="<?php echo $riga["Titolo"]; ?>" placeholder="<?php echo _('Titolo'); ?> (<?php echo _('Massimo'); ?> 500 <?php echo _('caratteri'); ?>, <?php echo _('Emoji supportate'); ?>)" autocomplete="off" required></input>
                    <br>
                    <textarea maxlength="100000" class="form-control" rows="8" id="testo" name="testo" placeholder="<?php echo _('Scrivi in quest area la News'); ?> (<?php echo _('Massimo'); ?> 10.000 <?php echo _('caratteri'); ?>, <?php echo _('Emoji supportate'); ?>)" required><?php echo $riga["Testo"]; ?></textarea>
                    <br>
                    <button class="btn btn-success" type="submit" name="invio"><?php echo _("Modifica la News"); ?></button>
                </form>
				<font color="lightcoral"><?php echo sprintf(_("NON MODIFICARE MAI UNA NEWS SENZA AVER RICHIESTO CONFERMA DA PARTE DEI PROPRIETARI DI %s."), $solutionname_short); ?></font>
				<br><br>
				<?php echo _("Emoticon utili"); ?>: 👷 | 🛡 | ❤ | 🎉 | ✅ | ⚠ | ❌
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
			  title: "<?php echo _("Conferma operazione"); ?>",
			  text: "<?php echo _("Cliccando su 'Conferma' la News sarà modificata."); ?>",
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
				$.post('Utils/modificaNewsDB.php', {
						id_notizia: $("#id_notizia").val(),
						titolo: $("#titolo").val(),
						testo: $("#testo").val()
					},
					function(result) {
						if(result == "success") {
							swal({
							  title: "<?php echo _("Successo"); ?>",
							  text: "<?php echo _("La News è modificata con successo."); ?>",
							  type: "success"
							});
						} else if(result == "failure") {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Non è stato possibile aggiornare la News."); ?>",
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
</script>