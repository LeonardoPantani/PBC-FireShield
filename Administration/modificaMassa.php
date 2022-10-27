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

if ($twofactor == true) {
    $CSRFtoken = bin2hex(openssl_random_pseudo_bytes(16));
    $esito = setcookie("CSRFtoken", $CSRFtoken, time() + 60 * 60 * 24, "", "", true, true); //Imposto cookie di sicurezza contro vulnerabilità CSRF
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>">
<!-- Copyright FireShield. All rights reserved. -->

<head>
    <title>Amministrazione <?php echo $solutionname; ?></title>
    <?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>

<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"
    <?php } else {?>class="jpg" <?php }} else {?>class="jpg" <?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <form id="form" name="form" action="Utils/modificaMassaDB.php" method="POST">
            <input name="CSRFtoken" id="CSRFtoken" type="hidden" style="display: none;" value="<?php if ($twofactor == true) {echo $CSRFtoken;}?>">
			<div class="p-2 mb-2 bg-dark text-white rounded">
                <img src="../CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>"
                    width="15%" alt="Logo">
                <h3 style="display: inline-block;">Modifica durata licenze</h3>
                <br><br>
                <div class="container text-center" style="width: 50%">
                    <h3>Inserisci modifica</h3>
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-book"></span>
							</div>
						</div>
						<input class="form-control" type="text" id="segno" name="segno" placeholder="+ o - durata licenza?" autocomplete="off" required></input>
						</div>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <span class="oi oi-clock"></span>
                                </div>
                            </div>
                            <input onfocus="(this.type='number')" class="form-control" type="text" id="giorni" name="giorni" placeholder="Modifica in giorni?" autocomplete="off" required></input>
                        </div>
						<br><br>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <span class="oi oi-circle-check"></span>
                                </div>
                            </div>
                            <input onKeyUp="checkConfirmTyping();" class="form-control" type="text" id="conferma" name="conferma" placeholder="Scrivi 'CONFERMA' per modificare" autocomplete="off" required></input>
                        </div>
                </div>
				<p>Dovremmo modificare le licenze scadute?</p>
				<input class="form-control" type="checkbox" id="modificalicenzescadute" name="modificalicenzescadute" autocomplete="off"></input>
				<br>
                <button id="form_submit" class="btn btn-success" type="submit" name="send"disabled>Modifica licenze</button>
                <br><br>
            </div>
        </form>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>

</html>
<script>
	function checkConfirmTyping() {
		if($("#conferma").val() != "CONFERMA") {
			$("#form_submit").prop("disabled",true);
		} else {
			$("#form_submit").prop("disabled",false);
		}
	}

	document.getElementById('jquery').addEventListener('load', function() {
		$("#form").submit(function(e) {
			e.preventDefault();
			if($("#conferma").val() == "CONFERMA") {
				swal({
					title: "Invio dati...",
					text: "Per favore attendi mentre modifichiamo le licenze.",
					type: "info"
				});
				$.post('Utils/modificaMassaDB.php', {
						CSRFtoken: $("#CSRFtoken").val(),
						segno: $("#segno").val(),
						giorni: $("#giorni").val(),
						ore: $("#ore").val(),
						modificaLicenzeScadute: $("#modificalicenzescadute").prop("checked")
					},
					function(result) {
						if (result == "success") {
							swal({
								title: "Successo",
								text: "Licenze modificate.",
								type: "success"
							});
						} else if (result == "failure") {
							swal({
								title: "Si è verificato un errore",
								text: "Non è stato possibile modificare le licenze.",
								type: "error"
							});
						} else if (result == "csrf_invalid") {
							swal({
								title: "Si è verificato un errore",
								text: "Token di sicurezza non valido.",
								type: "error"
							});
						} else if (result == "sign_error") {
							swal({
								title: "Si è verificato un errore",
								text: "Segno non valido.",
								type: "error"
							});
						} else if (result == "not_numeric") {
							swal({
								title: "Si è verificato un errore",
								text: "Valore dei giorni non valido.",
								type: "error"
							});
						} else if (result == "token_error") {
							swal({
								title: "Si è verificato un errore",
								text: "Errore token.",
								type: "error"
							});
						} else if (result == "unset") {
							swal({
								title: "Si è verificato un errore",
								text: "Imposta tutti i parametri.",
								type: "error"
							});
						} else if (result == "no_permission") {
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
			} else {
				alert("Devi confermare l'operazione.");
			}
		});
	});
</script>