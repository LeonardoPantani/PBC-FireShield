<!-- INIZIO HEAD (& meta) -->
<meta charset="UTF-8">
<meta name="description" content="The <?php echo $solutionname; ?> is the best online screenshare tool. It's easy to use, reliable and detects over 500 clients.">
<meta name="keywords" content="detector, cheat, anticheat, minecraft, scanner, analyzer, <?php echo $solutionname_short; ?>">
<meta name="author" content="Leonardo Pantani">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Meta social network (facebook e altri) -->
<meta property="og:type" content="article">
<meta property="og:image" content="https://fireshield.it/CSS/Images/logo.png">
<meta property="og:title" content="<?php echo $solutionname; ?>">
<meta property="og:description" content="The <?php echo $solutionname; ?> is the best online screenshare tool. It's easy to use, reliable and detects over 500 clients.">
<meta property="og:site_name" content="<?php echo $solutionname; ?>">
<meta property="og:url" content="https://fireshield.it">
<!-- Meta social network (twitter) -->
<meta name="twitter:card" content="summary">
<meta name="twitter:image" content="https://fireshield.it/CSS/Images/logo.png">
<meta name="twitter:title" content="<?php echo $solutionname; ?>">
<meta name="twitter:description" content="The <?php echo $solutionname; ?> is the best online screenshare tool. It's easy to use, reliable and detects over 500 clients.">
<meta name="twitter:site" content="https://fireshield.it">
<!-- Link css -->
<link rel="manifest" href="/manifest.json">
<link defer rel="shortcut icon" type="image/png" href="/CSS/Images/favicon.ico" media="print">
<link defer rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css">
<link defer rel="stylesheet" type="text/css" href="/CSS/Styles/bootstrap.css">
<link defer rel="stylesheet" type="text/css" href="/CSS/Styles/offline.css">
<link defer rel="stylesheet" type="text/css" href="/CSS/Styles/style.css">

<?php
switch($lang) {
    case "it_IT":
        ?><link defer rel="stylesheet" type="text/css" href="/CSS/Styles/offline_lang_it.css"><?php
    break;
	
	case "fr_FR":
		?><link defer rel="stylesheet" type="text/css" href="/CSS/Styles/offline_lang_fr.css"><?php
	break;
    
    default:
        ?><link defer rel="stylesheet" type="text/css" href="/CSS/Styles/offline_lang_en.css"><?php
    break;
}
?>
<!-- JS -->
<script defer id="jquery" src="/CSS/Scripts/jquery.js"></script>
<script defer src="/CSS/Scripts/bootstrap.js"></script>
<script defer src="/CSS/Scripts/offline.js"></script>
<script defer src="/CSS/Scripts/sweetalert.js"></script>
<script src="/CSS/Scripts/popper.js"></script>
<?php
bindtextdomain("head_main", $locale);
bind_textdomain_codeset("head_main", 'UTF-8');
textdomain("head_main");
?>
<script>
	<!-- AVVISO CONSOLE -->
	console.log("%c<?php echo _('Attenzione!'); ?>", "background: lightgray; color: red; font-size: x-large");
	console.log("%c<?php echo _('Se qualcuno ti ha detto di fare copia-incolla qui è molto probabile che stia provando a truffarti.'); ?>\n<?php echo _('A meno che tu sia un esperto, ti consigliamo di chiudere questa finestra per restare al sicuro.'); ?>", "background: lightgray;");

	<!-- AVVISO LOGOUT -->
	<?php if(isLogged()) { ?>
		function confirmLogout() {
			var logout = confirm("<?php echo _("Sei sicuro di voler effettuare il logout?"); ?>");

			if(logout){
				 location.href = "/Utils/deleteSession.php";
			}
		}
	<?php } ?>
	
	<!-- AVVISO CAMBIO LINGUA -->
	<?php if(isLogged() && !$otp && !isset($_COOKIE["lang_warning"]) && substr($lang, 0, 2) != $language_detect && in_array($language_detect, $language_list_short) && $tipo == 1) {
		$chiave_detect = array_search($language_detect, $language_list_short);
		setcookie("lang_warning", 1, time()+3600*24*7);
		setlocale(LC_MESSAGES, $language_list[$chiave_detect]);
		?>
		document.getElementById('jquery').addEventListener('load', function() {
			$(document).ready(function() {
				sendLangWarning();
			});
		});
		function sendLangWarning() {
			swal({
			  html: "<?php echo sprintf(_("Preferisci cambiare la lingua di %s in %s?"), $solutionname_short, $language_list_complete[$chiave_detect]); ?>",
			  type: "info",
			  toast: true,
			  position: 'bottom-end',
			  showCancelButton: true,
			  confirmButtonText: '<?php echo _("Sì"); ?>',
			  cancelButtonText: '<?php echo _("No"); ?>'
			}).then((result) => {
				if(result.value) {
					$.ajax({
					type: "POST",
					url: "/Utils/updatePreference.php?preferenza_nome=Lingua&preferenza_valore=<?php echo $language_list[$chiave_detect]; ?>",
					datatype: "html",
					success:function(result)
					{
						$.ajax({
							type: "POST",
							url: "/Translations/setLanguage.php?l=<?php echo $language_list[$chiave_detect]; ?>",
							datatype: "html",
							success:function(result)
							{
								window.location.reload();
							}
						});
					}
					});
				}
			});
		}
	<?php setlocale(LC_MESSAGES, $lang); } ?>
	
	<!-- CONTROLLO SESSIONE UTENTE STANDARD -->
	<?php if(isLogged() && !$otp) { ?>
		document.getElementById('jquery').addEventListener('load', function() {
			checkUserSession();
		});
		
		setInterval(function() {
			checkUserSession();
		}, 7500);
		var sessione_valida = false;
		
		function checkUserSession() {
			if(navigator.onLine) {
				$.ajax({
				type: "POST",
				url: "/Utils/checkUserSession.php",
				datatype: "html",
				success:function(result)
				{
					if(result == "true") {
						sessione_valida = true;
					} else if(result == "false") {
						if(sessione_valida == true) {
							swal({
							  title: "<?php echo _("Si è verificato un errore"); ?>",
							  text: "<?php echo _("Questa sessione non è più disponibile. Per favore rieffettua il login"); ?>.",
							  type: "error",
							  allowOutsideClick: false,
							  allowEscapeKey: false
							}).then((result) => {
							  if (result.value) {
								  window.location = "/login.php";
							  }
							});
							sessione_valida = false;
						}
					}
				}
				});	
			}
		}
	<?php } ?>
	
	<!-- CONTROLLO SESSIONE UTENTE OTP -->
	<?php if(isLogged() && $otp) { ?>
		document.getElementById('jquery').addEventListener('load', function() {
			checkOTPSession();
		});
		
		setInterval(function() {
			checkOTPSession();
		}, 5000);
		
		function checkOTPSession() {
			if(navigator.onLine) {
				$.ajax({
				type: "POST",
				url: "/Utils/checkOTPSession.php",
				datatype: "html",
				success:function(result)
				{
					if(result == "ok") {
						sessione_valida = true;
					} else if(result == "expired") {
						swal({
						  title: "<?php echo _("Fine della sessione OTP"); ?>",
						  text: "<?php echo _("Questa sessione OTP è scaduta per il tempo"); ?>.",
						  footer: "<?php echo _('Logout in corso'); ?>...",
						  type: "error",
						  allowOutsideClick: false,
						  allowEscapeKey: false
						}).then((result) => {
						  if (result.value) {
							  window.location = "/Utils/deleteSession.php";
						  }
						});
						setTimeout( function(){
							window.location = "/Utils/deleteSession.php";
						}, 2500 );
					} else if(result == "not_found") {
						swal({
						  title: "<?php echo _("Fine della sessione OTP"); ?>",
						  text: "<?php echo _("Il membro dello staff ha terminato la sessione"); ?>.",
						  footer: "<?php echo _('Logout in corso'); ?>...",
						  type: "error",
						  allowOutsideClick: false,
						  allowEscapeKey: false
						}).then((result) => {
						  if (result.value) {
							  window.location = "/Utils/deleteSession.php";
						  }
						});
						setTimeout( function(){
							window.location = "/Utils/deleteSession.php";
						}, 2500 );
					} else if(result == "not_logged") {
						swal({
						  title: "<?php echo _("Si è verificato un errore"); ?>",
						  text: "<?php echo _("Questa sessione non è più disponibile. Per favore rieffettua il login"); ?>.",
						  type: "error",
						  allowOutsideClick: false,
						  allowEscapeKey: false
						}).then((result) => {
						  if (result.value) {
							  window.location = "/Utils/deleteSession.php";
						  }
						});
					}
				}
				});
			}
		}
	<?php } ?>
</script>
<?php
bindtextdomain($domain, $locale);
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);
?>
<!-- FINE HEAD -->