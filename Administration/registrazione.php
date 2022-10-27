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

if (!getPermission($id, "Account_Create")) {
	header("Location:pannello.php");
	exit;
}

if ($twofactor == true) {
    $CSRFtoken = bin2hex(openssl_random_pseudo_bytes(16));
    $esito = setcookie("CSRFtoken", $CSRFtoken, time() + 60 * 60 * 24, "", "", true, true); //Imposto cookie di sicurezza contro vulnerabilità CSRF
}

$passwordgenerata = randomPassword();
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
        <form id="form" name="form" action="Utils/registrazioneDB.php" method="POST">
            <input name="CSRFtoken" id="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"> <h3 style="display: inline-block;">Registrazione Account</h3>
                <br><br>
				<div class="container text-center" style="width: 75%">
					<h3>Informaz. Utente</h3>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-envelope-closed"></span>
                        </div>
                        </div>
                        <input class="form-control" type="email" id="email" name="email" placeholder="Email" autocomplete="off" required></input>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-person"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" id="username" name="username" placeholder="Username" autocomplete="off" required></input>
                    </div>
					<span style="text-color:lightcoral;" id="esito_username"></span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <div class="input-group-text">
                            <span class="oi oi-key"></span>
                        </div>
                        </div>
                        <input class="form-control" type="text" id="password" name="password" placeholder="Password" value="<?php echo $passwordgenerata; ?>" autocomplete="off" required></input>
                    </div>
					<br>
					<div class="container" style="width: 75%">
						<select class="form-control" id="tipoutente" name="tipoutente" autocomplete="off">
							<option value="-1">Seleziona il tipo utente</option>
							<option value="0">Utente standard</option>
							<option value="2">Partner</option>
						</select>
					</div>
					<br>
					<div class="container" style="width: 75%">
						<select class="form-control" id="abilitato" name="abilitato" autocomplete="off">
							<option value="-1">Seleziona lo stato dell'utente</option>
							<option value="0">Non abilitato</option>
							<option value="1">Abilitato</option>
						</select>
					</div>
					<hr>
					<h3>Informaz. Lingua</h3>
					<div class="container" style="width: 75%">
						<select class="form-control" id="lingua" name="lingua" autocomplete="off">
							<?php foreach($language_list as $chiave => $valore) { ?>
								<option value="<?php echo $valore; ?>"><?php echo $language_list_complete[$chiave]; ?></option>
							<?php } ?>
						</select>
					</div>
					<hr>
					<h3>Informaz. Licenza</h3>
					<div class="container" style="width: 75%">
						<select class="form-control" id="tipolicenza" name="tipolicenza" autocomplete="off">
							<option value="-1">Seleziona il tipo di licenza</option>
							<option value="0">Singola</option>
							<!-- <option value="1">Network</option> -->
						</select>
					</div>
					<br>
					<div class="container" style="width: 75%">
						<select class="form-control" id="duratalicenza" name="duratalicenza" autocomplete="off">
							<option value="-1">Seleziona la durata della licenza</option>
							<option value="0">Mensile (Solo per licenze singole)</option>
							<option value="1">Permanente</option>
						</select>
					</div>
					<br>
					<div class="container" style="width: 75%">
						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-calendar"></span>
							</div>
							</div>
							<input id="scadenzalicenza" class="form-control" type="date" name="scadenzalicenza" placeholder="Scadenza della licenza" autocomplete="off" disabled></input>
						</div>
					</div>
					<hr>
					<p>Dovremmo inviare una mail di notifica all'indirizzo Email specificato?</p>
					<input class="form-control" type="checkbox" id="inviamail" name="inviamail" autocomplete="off" checked></input>
					<br>
					<br>
                </div>
				<button id="form_submit" class="btn btn-success" type="submit" name="send">Registra Utente</button>
				<hr>
                <p style="color:lightcoral;"><b>Se l'utente non deve avere una licenza lasciare i campi di default.</b></p>
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
				  title: "Conferma operazione",
				  text: "Cliccando su 'Conferma' l'account sarà registrato con i dati attualmente impostati.",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonText: 'Conferma',
				  cancelButtonText: 'Annulla'
				})
				.then((result) => {
				  if (result.value) {
					swal({
					  title: "Invio dati...",
					  text: "Per favore attendi mentre l'account viene registrato.",
					  type: "info"
					});
					$.post('Utils/registrazioneDB.php', {
							CSRFtoken: $("#CSRFtoken").val(),
							email: $("#email").val(),
							username: $("#username").val(),
							password: $("#password").val(),
							tipoutente: $("#tipoutente").val(),
							abilitato: $("#abilitato").val(),
							lingua: $("#lingua").val(),
							tipolicenza: $("#tipolicenza").val(),
							duratalicenza: $("#duratalicenza").val(),
							scadenzalicenza: $("#scadenzalicenza").val(),
							inviamail: $("#inviamail").prop("checked")
						},
						function(result) {
							if(result == "success") {
								swal({
								  title: "Successo",
								  text: "Account registrato.",
								  type: "success"
								});
							} else if(result == "failure") {
								swal({
								  title: "Si è verificato un errore",
								  text: "Non è stato possibile registrare l'account.",
								  type: "error"
								});
							} else if(result == "already_exists") {
								swal({
								  title: "Si è verificato un errore",
								  text: "La mail o l'username specificati esistono già.",
								  type: "error"
								});
							} else if(result == "invalid_username") {
								swal({
								  title: "Si è verificato un errore",
								  text: "L'username non è valido.",
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
							} else if(result == "invalid_user_type") {
								swal({
								  title: "Si è verificato un errore",
								  text: "Tipo utente non valido.",
								  type: "error"
								});
							} else if(result == "invalid_user_status") {
								swal({
								  title: "Si è verificato un errore",
								  text: "Stato utente non valido.",
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
		
		
		$('#duratalicenza').on('change', function() {
		  if(this.value == -1 || this.value == 1) {
			  $("#scadenzalicenza").prop('disabled', true);
		  } else {
			  $("#scadenzalicenza").prop('disabled', false);
	      }
		});
	});
</script>