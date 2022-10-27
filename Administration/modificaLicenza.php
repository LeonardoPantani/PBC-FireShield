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

if (!getPermission($id, "Account_License")) {
	header("Location:pannello.php");
	exit;
}

if(isset($_GET["id"])) {
	$stmt = $connection->prepare("SELECT * FROM $table_users WHERE ID = ?");
	$stmt->bind_param("i", $_GET["id"]);
	$stmt->execute();
	$esito = $stmt->get_result();
	$nrighe1 = $esito->num_rows;
	if($nrighe1 > 0) {
		$datiUtente = $esito->fetch_assoc();
	}
	
	$stmt = $connection->prepare("SELECT * FROM $table_licenses WHERE IDCliente = ?");
	$stmt->bind_param("i", $_GET["id"]);
	$stmt->execute();
	$esito = $stmt->get_result();
	$nrighe2 = $esito->num_rows;
	if($nrighe2 > 0) {
		$datiLicenza = $esito->fetch_assoc();
	}
} else {
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
        <form id="form" name="form" action="modificaLicenzaDB.php" method="POST">
            <input id="CSRFtoken" name="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
			<input id="idutente" name="idutente" type="text" style="display:none;" value="<?php echo $_GET["id"]; ?>">
            <div class="p-2 mb-2 bg-dark text-white rounded">
				<?php if($nrighe1 > 0) { ?>
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="15%" alt="Logo"> <h3 style="display: inline-block;">Modifica Dati di <font color="lightblue"><?php echo $datiUtente["Username"]; ?></font> (ID: <?php echo $datiLicenza["IDCliente"]; ?>) <?php if($datiUtente["Tipo"] == 2) { echo "(<font style='color:lightgreen;'>Partner</font>)"; } ?></h3>
				<div class="container text-center" style="width: 60%">
					<a href="listaUtenti.php" title="Torna alla lista Utenti"><small>Clicca qui per tornare alla lista Utenti&nbsp;<span class="oi oi-external-link"></span></small></a>
					<hr>
					<div class="container" style="width: 50%">
						<h5>Tipo di licenza</h5>
						<select id="tipolicenza" class="form-control" name="tipolicenza" autocomplete="off">
							<?php 
								if($datiLicenza["TipoLicenza"] == "single") {
									?>
										<option value="0">Singola</option>
										<option value="1">Network</option>
										<option value="-1">Nessuna licenza</option>
									<?php
								} elseif($datiLicenza["TipoLicenza"] == "network") {
									?>
										<option value="1">Network</option>
										<option value="0">Singola</option>
										<option value="-1">Nessuna licenza</option>
									<?php
								} else {
									?>
										<option value="-1">Nessuna licenza</option>
										<option value="0">Singola</option>
										<option value="1">Network</option>
									<?php
								}
							
							?>
						</select>
					</div>
						<br>
					<div class="container" style="width: 50%">
						<h5 id="testoduratalicenza">Durata della licenza</h5>
						<select id="duratalicenza" class="form-control" name="duratalicenza" autocomplete="off">
							<?php 
								if($datiLicenza["DurataLicenza"] == "monthly") {
									?>
										<option value="0">Mensile</option>
										<option value="1">Permanente</option>
										<option value="-1">Nessuna durata</option>
									<?php
								} elseif($datiLicenza["DurataLicenza"] == "permanent") {
									?>
										<option value="1">Permanente</option>
										<option value="0">Mensile</option>
										<option value="-1">Nessuna durata</option>
									<?php
								} else {
									?>
										<option value="-1">Nessuna durata</option>
										<option value="0">Mensile</option>
										<option value="1">Permanente</option>
									<?php
								}
							
							?>
						</select>
					</div>
						<br>
					<div class="container" style="width: 50%">
					<h5 id="testoscadenzalicenza">Scadenza della licenza</h5>
						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-calendar"></span>
							</div>
							</div>
							<input id="scadenzalicenza" class="form-control" type="date" name="scadenzalicenza" placeholder="Scadenza della licenza" value="<?php echo $datiLicenza["Scadenza"]; ?>" autocomplete="off" <?php if($datiLicenza["DurataLicenza"] == "permanent") { echo "disabled"; } else { echo "required"; } ?>></input>
						</div>
					</div>
					<br>
                </div>
				<button id="form_submit" class="btn btn-success" type="submit" name="send">Modifica Licenza</button>
				<br><br>
				<?php } else { ?>
					<div class="container text-center" style="width: 60%">
						<br>
						<h5 style="color:lightcoral;">L'ID <?php echo $_GET["id"]; ?> non ha nessuna corrispondenza utente.</h5>
						<br>
					</div>
				<?php } ?>
			</div>
        </form>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
    document.getElementById('jquery').addEventListener('load', function() {
		var conferma = false;
		
		$('#tipolicenza').on('change', function() {
		  if(this.value == -1) {
			  $("#testoscadenzalicenza").css('color', 'lightcoral');
			  $("#testoduratalicenza").css('color', 'lightcoral');
			  $("#scadenzalicenza").prop('disabled', true);
			  $("#duratalicenza").prop('disabled', true);
		  } else if(this.value == 1) {
			  $("#testoscadenzalicenza").css('color', 'lightcoral');
			  $("#testoduratalicenza").css('color', 'white');
			  $("#scadenzalicenza").prop('disabled', true);
			  $("#duratalicenza").prop('disabled', false);
		  } else {
			  $("#testoscadenzalicenza").css('color', 'white');
			  $("#testoduratalicenza").css('color', 'white');
			  $("#scadenzalicenza").prop('disabled', false);
			  $("#duratalicenza").prop('disabled', false);
	      }
		});
		
		$('#duratalicenza').on('change', function() {
		  if(this.value == -1 || this.value == 1) {
			  $("#testoscadenzalicenza").css('color', 'lightcoral');
			  $("#scadenzalicenza").prop('disabled', true);
		  } else {
			  $("#testoscadenzalicenza").css('color', 'white');
			  $("#scadenzalicenza").prop('disabled', false);
	      }
		});
		
        $("#form").submit(function(e) {
			if(conferma == false) {				
				e.preventDefault();
				if($("#tipolicenza").val() == 1 && $("#duratalicenza").val() == 0) { // Il tipo licenza è network e la durata è mensile
					swal({
					  title: "Errore",
					  text: "Le licenze di tipo Network non possono avere una durata mensile.",
					  type: "warning"
					})
				} else if($("#duratalicenza").val() == 0 && $("#scadenzalicenza").val() == "") { // La durata è mensile e non è impostata la data di scadenza
					swal({
					  title: "Errore",
					  text: "Devi impostare una scadenza per le licenze mensili.",
					  type: "warning",
					})
				} else {
					// SWAL
					swal({
					  title: "Conferma operazione",
					  text: "Cliccando su 'Conferma' la licenza relativa all'account sarà aggiornata.",
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
						$.post('Utils/modificaLicenzaDB.php', {
							CSRFtoken: $("#CSRFtoken").val(),
							idutente: $("#idutente").val(),
							tipolicenza: $("#tipolicenza").val(),
							duratalicenza: $("#duratalicenza").val(),
							scadenzalicenza: $("#scadenzalicenza").val()
						},
						function(result) {
							if(result == "success") {
								swal({
								  title: "Successo",
								  text: "Licenza aggiornata.",
								  type: "success",
								});
							} else if(result == "failure") {
								swal({
								  title: "Errore",
								  text: "Si è verificato un errore durante l'aggiornamento della licenza.",
								  type: "error"
								});
							} else if(result == "unset") {
								swal({
								  title: "Errore",
								  text: "Imposta tutti i parametri.",
								  type: "error"
								});
							} else if(result == "no_permission") {
								swal({
								  title: "Errore",
								  text: "Non hai i permessi.",
								  type: "error"
								});
							} else if(result == "token_error") {
								swal({
								  title: "Errore",
								  text: "Il token di sicurezza non è valido.",
								  type: "error"
								});
							} else {
								swal({
								  title: "Errore imprevisto",
								  text: result,
								  type: "error"
								});
							}
						});
					  }
					});	
				}
			}
		});
	});
</script>