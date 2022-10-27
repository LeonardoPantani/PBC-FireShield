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

if (!getPermission($id, "Account_Delete")) {
	header("Location:pannello.php");
	exit;
}

if ($twofactor == true) {
    $CSRFtoken = bin2hex(openssl_random_pseudo_bytes(16));
    $esito = setcookie("CSRFtoken", $CSRFtoken, time() + 60 * 60 * 24, "", "", true, true); //Imposto cookie di sicurezza contro vulnerabilità CSRF
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
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
        <form id="form" name="form" action="Utils/cancellaAccountDB.php" method="POST">
            <input name="CSRFtoken" id="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"> <h3 style="display: inline-block;">Eliminazione Account</h3>
                <br><br>
				<div class="container text-center" style="width: 50%">
					<h3>ID Utente</h3>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-fire"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" id="id_utente" name="id_utente" placeholder="ID Utente da rimuovere" autocomplete="off" required></input>
                    </div>
					<p>Dovremmo cancellarne anche i dati di pagamento, se presenti?</p>
					<input class="form-control" type="checkbox" id="cancella_dati_pagamento" name="cancella_dati_pagamento" autocomplete="off"></input>
					<br>
				</div>
				<button id="form_submit" class="btn btn-danger" type="submit" name="send">Elimina Account</button>
				<br><br>
				<hr>
				<div class="container text-left">
					<p>Dati che non vengono cancellati:</p>
					<ul>
						<li>Cambi Username</li>
						<li>Candidature (perché non sono collegate ad un ID utente)</li>
						<li>CPS Test</li>
						<li>History Analyzer (Scansioni)</li>
						<li>Richieste Dati</li>
					</ul>
				</div>
            </div>
        </form>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
    document.getElementById('jquery').addEventListener('load', function() {
        $("#form").submit(function(e) {
			e.preventDefault();

			swal({
			  title: "Invio dati...",
			  text: "Per favore attendi mentre cerchiamo l'ID.",
			  type: "info"
			});
			$.post('../Utils/checkID.php', {
				idscelto: $("#id_utente").val()
			},
			function(result) {
				if(result == "found") {
					swal({
					  title: "Conferma operazione",
					  text: "Cliccando su 'Conferma' l'account sarà eliminato e non sarà possibile recuperarlo.",
					  type: "warning",
					  showCancelButton: true,
					  confirmButtonText: 'Conferma',
					  cancelButtonText: 'Annulla'
					})
					.then((result) => {
					  if (result.value) {
						swal({
						  title: "Invio dati...",
						  text: "Per favore attendi mentre l'account viene rimosso.",
						  type: "info"
						});
						$.post('Utils/cancellaAccountDB.php', {
								CSRFtoken: $("#CSRFtoken").val(),
								id_utente: $("#id_utente").val(),
								cancella_dati_pagamento: $("#cancella_dati_pagamento").prop("checked")
							},
							function(result) {
								if(result == "success") {
									swal({
									  title: "Successo",
									  text: "Account cancellato.",
									  type: "success"
									});
								} else if(result == "failure") {
									swal({
									  title: "Si è verificato un errore",
									  text: "Non è stato possibile eliminare l'account.",
									  type: "error"
									});
								} else if(result == "invalid_id") {
									swal({
									  title: "Si è verificato un errore",
									  text: "ID non valido.",
									  type: "error"
									});
								} else if(result == "failure_admin") {
									swal({
									  title: "Si è verificato un errore",
									  text: "Non è possibile rimuovere un amministratore.",
									  type: "error"
									});
								} else if(result == "csrf_invalid") {
									swal({
									  title: "Si è verificato un errore",
									  text: "Token di sicurezza non valido.",
									  type: "error"
									});
								} else if(result == "token_error") {
									swal({
									  title: "Si è verificato un errore",
									  text: "Errore token.",
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
				} else if(result == "not_found") {
					swal({
					  title: "Si è verificato un errore",
					  text: "ID utente non trovato.",
					  type: "error"
					});
				} else if(result == "unset") {
					swal({
					  title: "Si è verificato un errore",
					  text: "Imposta tutti i parametri.",
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
		});
	});
</script>