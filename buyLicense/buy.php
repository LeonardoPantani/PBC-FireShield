<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

licenseInformation($licenza);
networkInformation($infonetwork);
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if(isset($_SERVER['HTTP_ACCEPT'])) { if(strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) { ?>class="webp"<?php } else {?>class="jpg"<?php } } else { ?>class="jpg"<?php } ?>>
    <?php include "$droot/Functions/Include/navbar.php"; ?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img src="../CSS/Images/logo.<?php if(isset($_SERVER['HTTP_ACCEPT'])) { if(strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) { ?>webp<?php } else {?>png<?php } } else { ?>png<?php } ?>" width="10%" alt="Logo"><h5 style="display: inline;"><?php echo sprintf(_("Licenze %s"), $solutionname); ?></h5>
			<br>
            <div class="container text-center" style="width: 80%">
                <p><?php echo _("Acquistare una qualsiasi licenza garantirà l'accesso al servizio di scansioni. La licenza singola darà accesso ad una singola persona, la licenza network darà accesso ad un numero illimitato di membri dello staff del tuo network."); ?></p>
				<p style="color:lightgray; font-size:12;"><?php echo _("Effettuando l'acquisto accetterai automaticamente i nostri"); ?> <a href="../Legal/tos.php"><?php echo _("Termini di Servizio"); ?></a>.</p>
			</div>
            <br>
            <div class="container text-center text-dark" style="width: 70%">
			<?php if($payments_live) { ?>
                <div class="row">
                    <div class="card bg-light" style="width: 23rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo _("Licenze Singole"); ?></h6>
							<form id="form1" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="hosted_button_id" value="RWQK7KPPA33UG">
								<table align="center">
								<tr>
									<td><input type="hidden" name="on0" value="License duration"><font class="text-dark"><?php echo _("Durata licenza"); ?></font></td>
								</tr>
								<tr>
									<td><select class="form-control" name="os0">
									<option value="Monthly"><?php echo _("Mensile"); ?> €<?php echo $price_single_monthly_license; ?> EUR</option>
									<option value="Permanent"><?php echo _("Permanente"); ?> €<?php echo $price_single_permanent_license; ?> EUR</option>
									</select></td>
								</tr>
								<!-- --- -->
								<tr>
									<td><input type="hidden" name="on1" value="Choose a Username"></td>
								</tr>
								<tr>
									<td><input class="form-control" placeholder="<?php echo _('Scegli uno Username'); ?>" id="usernamescelto1" type="text" name="os1" maxlength="20" autocomplete="off" required></td>
								</tr>
								</table>
								<input type="hidden" name="currency_code" value="EUR">
								<br>
								<input type="image" src="https://www.paypalobjects.com/<?php echo $lang; ?>/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
								<img alt="" border="0" src="https://www.paypalobjects.com/<?php echo $lang; ?>/i/scr/pixel.gif" width="1" height="1">
							</form>
							<span id="esito1"></span>
                        </div>
                    </div>

                    <div class="card bg-light" style="width: 23rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo _("Licenze Network"); ?> (???)</h6>
							<p>
								<?php echo sprintf(_("Se sei il proprietario di un Network?%s Vogliamo metterci in contatto con te per poterti offrire uno sconto sulla licenza per ogni tuo membro dello Staff!%s %sContattaci%s"), "<br>", "<br><br>", "<button class='btn btn-primary'><a style='color: inherit;' target='_blank' href='mailto:".$infomail."?subject=FireShield Cheat Detector Network license'>", "</a></button>"); ?>
							</p>
                        </div>
                    </div>
                </div>
				<?php } elseif(!$payments_live && isLogged() && $tipo == 1) { ?>
				<h5 class="text-white"><?php echo _("Visualizzazione opzioni di pagamento di DEBUG"); ?></h6>
                <div class="row">
                    <div class="card bg-light" style="width: 23rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo _("Licenze Singole"); ?></h6>
							<form id="form1" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="hosted_button_id" value="QANWV9ZMLTPTW">
								<table align="center">
								<tr>
									<td><input type="hidden" name="on0" value="License duration"><font class="text-dark"><?php echo _("Durata licenza"); ?></font></td>
								</tr>
								<tr>
									<td><select class="form-control" name="os0">
									<option value="Monthly"><?php echo _("Mensile"); ?> €<?php echo $price_single_monthly_license; ?> EUR</option>
									<option value="Permanent"><?php echo _("Permanente"); ?> €<?php echo $price_single_permanent_license; ?> EUR</option>
									</select></td>
								</tr>
								<!-- --- -->
								<tr>
									<td><input type="hidden" name="on1" value="Choose a Username"></td>
								</tr>
								<tr>
									<td><input class="form-control" placeholder="<?php echo _('Scegli uno Username'); ?>" id="usernamescelto1" type="text" name="os1" maxlength="20" autocomplete="off" required></td>
								</tr>
								</table>
								<input type="hidden" name="currency_code" value="EUR">
								<br>
								<input type="image" src="https://www.paypalobjects.com/<?php echo $lang; ?>/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
								<img alt="" border="0" src="https://www.paypalobjects.com/<?php echo $lang; ?>/i/scr/pixel.gif" width="1" height="1">
							</form>
							<span id="esito1"></span>
                        </div>
                    </div>

                    <div class="card bg-light" style="width: 23rem; float: none; margin: 0 auto;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo _("Licenze Network"); ?> (???)</h6>
							<br>
							<p>
								<?php echo sprintf(_("Se sei il proprietario di un Network?%s Vogliamo metterci in contatto con te per poterti offrire uno sconto sulla licenza per ogni tuo membro dello Staff!%s %sContattaci%s"), "<br>", "<br><br>", "<button class='btn btn-primary'><a style='color: inherit;' target='_blank' href='mailto:".$infomail."?subject=FireShield Cheat Detector Network license'>", "</a></button>"); ?>
							</p>
                        </div>
                    </div>
                </div>
				<?php } else { ?>
				<div class="card text-white bg-danger mb-3" style="width: 100%;">
				  <div class="card-body">
					<h5 class="card-text text-left"><?php echo _("Pagamenti in manutenzione"); ?></h5>
					<p class="card-text text-left"><?php echo _("Attualmente il sistema di licenze è in fase di manutenzione, pertanto non è possibile fare acquisti mentre lo staff indaga sul problema. Ci scusiamo per il disagio."); ?></p>
				  </div>
				</div>
				<?php } ?>
            </div>
			<br><br>
        </div>
		<br>
    </div>
	<br>
	<?php include "$droot/Functions/Include/footer.php"; ?>
</body>
</html>
<script>
	var conferma1 = false;
	var conferma2 = false;
	var regex = new RegExp("<?php echo $string_check_allowed; ?>");
	
    document.getElementById('jquery').addEventListener('load', function() {
        $("#form1").submit(function(e) {
			var form1 = this;
			e.preventDefault();
			if($("#usernamescelto1").val().length < <?php echo $username_minlength; ?> || $("#usernamescelto1").val().length > <?php echo $username_maxlength; ?>) {
				$("#esito1").css("color", "red");
				$("#esito1").html("<?php echo _('L′Username deve avere una lunghezza tra i'); echo " ".$username_minlength; ?> <?php echo _('ed i'); echo " ".$username_maxlength; echo _(' caratteri'); ?>.");
				conferma1 = false;
			} else {
				$.post('../Utils/checkUsername.php', {
						usernamescelto: $("#usernamescelto1").val()
					},
					function(result) {
						if(result == "not_found") {
							if(!regex.test($("#usernamescelto1").val())) {
								$("#esito1").css("color", "red");
								$("#esito1").html("<?php echo _('L′Username contiene caratteri non validi'); ?>.");
								conferma1 = false;
							} else {
								$.post('../Utils/checkBannedWords.php', {
										stringa: $("#usernamescelto1").val()
									},
									function(result) {
										if(result == "not_found") {
											$("#esito1").css("color", "green");
											$("#esito1").html("<?php echo _('Tutti i campi sono corretti. Clicca di nuovo per CONFERMARE'); ?>.");
											if(conferma1 == true) {
												form1.submit();
											} else {
												conferma1 = true;
											}
										} else if(result == "found") {
											$("#esito1").css("color", "red");
											$("#esito1").html("<?php echo _('Scegli un altro Username'); ?>.");
											conferma1 = false;
										} else if(result == "unset") {
											$("#esito1").css("color", "red");
											$("#esito1").html("<?php echo _('Inserisci uno Username'); ?>.");
											conferma1 = false;
										} else {
											$("#esito1").css("color", "white");
											$("#esito1").html("<?php echo _('Caso imprevisto'); ?>: "+result);
											conferma1 = false
										}
									}
								);
							}
						} else if(result == "found") {
							$("#esito1").css("color", "red");
							$("#esito1").html("<?php echo _('Questo Username è già in uso'); ?>");
							conferma1 = false;
						} else if(result == "unset") {
							$("#esito1").css("color", "red");
							$("#esito1").html("<?php echo _('Inserisci uno Username'); ?>.");
							conferma1 = false;
						} else {
							$("#esito1").css("color", "white");
							$("#esito1").html("<?php echo _('Caso imprevisto'); ?>: "+result);
							conferma1 = false
						}
					}
				);
			}
        });
	});
</script>