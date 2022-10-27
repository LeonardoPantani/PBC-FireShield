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

if(isset($_GET["duration"])) {
	if($_GET["duration"] != 0) {
		$duratacps = $_GET["duration"];
	} else {
		$duratacps = $cpstestduration;
	}
} else {
	$duratacps = $cpstestduration;
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body onload="checkRankingAverage()" <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="11%" alt="Logo"><h4 style="display: inline-block;"><?php echo $solutionname." "._("CPS Test"); ?></h4>
			<br>
			<?php if(!isLogged()) { ?>
				<font color="lightgray"><?php echo _("Acquista una licenza")." <a href='buyLicense/buy.php'>"._("qui")."</a> "._("per ottenere l'accesso a tutte le funzionalità di")." ".$solutionname_short."!"; ?></font>
			<?php } ?>
			<br>
			<span style="color:lightgray;" id="esito_ranking"><i><?php echo _("Ottengo dati..."); ?></i></span>
			
			<?php if(isset($_GET["duration"]) && $_GET["duration"] != $cpstestduration) { ?>
				<br><small><?php echo _("Durata test personalizzata"); ?>: <?php echo $duratacps." "; if($duratacps == 1) { echo _("secondo"); } else { echo _("secondi"); } ?></small>
			<?php } else { echo "<br>"; } ?>
			<div id="container" class="container btn-group" style="width:100%; height:50%;">
				<button id="buttoncps" type="button" class="btn btn-primary btn-lg btn-block"><?php echo _("Clicca per iniziare!"); ?></button>
			</div>
			<br><br>
			<p id="esitocps"><?php echo _("Il test mostrerà in tempo reale i click per secondo effettuati! Al termine saranno visualizzati i risultati finali."); ?></p>
        </div>
    </div>
	<br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>

	function checkRankingAverage() {
		$.ajax({
		type: "POST",
		url: "Utils/checkCPSAverage.php",
		datatype: "html",
		success:function(result)
		{
			$("#esito_ranking").css("color", "white");
			$("#esito_ranking").html("<?php echo _('Media degli utenti'); ?>: <b>"+result+" CPS</b>");
		}
		});
	}
	
	document.getElementById('jquery').addEventListener('load', function() {
		var duratatest = <?php echo $duratacps; ?>;
		var started, resetTimeoutHandle, resetTimeout = 1000, container = document.getElementById('container'), counter = document.getElementById('esitocps'), zone = document.getElementById('buttoncps'), clicks = 0, sommacps = 0;

		zone.onselect = zone.onselectstart = function() {
			return false;
		};

		function clicksPerSecond(started, clicks, now = new Date()) {
			return clicks / (now - started) * 1000;
		}

		function count() {
			clearTimeout(resetTimeoutHandle);
			clicks++;
			valore = clicksPerSecond(started, clicks);
			if(clicks != 1) {
				sommacps += valore;
			}
			
			if(valore == Number.POSITIVE_INFINITY || valore == Number.NEGATIVE_INFINITY || valore == 1000) {
				$("#esitocps").html("<b>CPS</b>: ??? | <b><?php echo _('Click totali'); ?></b>: "+clicks);
			} else {
				$("#esitocps").html("<b>CPS</b>: "+Math.round(valore*100)/100+" | <b><?php echo _('Click totali'); ?></b>: "+clicks);
			}
			return false;
		}

		function start() {
			started = new Date();
			clicks = 0;
			secondi = 0;
			sommacps = 0;
			$("#buttoncps").text( '<?php echo _("Clicca qui!"); ?> ['+duratatest+' <?php echo _("secondi"); ?> <?php echo _("rimasti"); ?>]');
			this.onmousedown = count;
			this.onmousedown();
			timer();
			return false;
		}
		
		function timer() {
			intervallo = setInterval(function(){
				ora = new Date();
				secondi++;
				if(secondi == duratatest) {
					$("#buttoncps").removeClass("btn-primary");
					$("#buttoncps").addClass("btn-danger");
					$("#buttoncps").text("<?php echo _('Test terminato!'); ?>");
					$("#esitocps").html("<b>CPS</b>: "+ (clicks/duratatest) +" | <b><?php echo _('Click totali'); ?></b>: "+clicks+"<br><?php echo _('Clicca sul pulsante per ricominciare da capo il test CPS'); ?>.");
					$("#buttoncps").prop("disabled",true);
					if(duratatest == 10) {
						if((clicks/duratatest) <= 14 && clicks >= 10) {
							upload();
						} else {
							console.log("<?php echo _('CPS Test'); ?>: <?php echo _('I risultati sono stati respinti dal server delle classifiche'); ?>.");
						}
					}
					clearInterval(intervallo);
					clearInterval(intervallo_timer);
					setTimeout(function(){
						reset();
					}, 1000);
				}
			}, 1000);
			
			tempotot = 0;
			intervallo_timer = setInterval(function(){
				tempotot = tempotot+0.1;
				temporimasto = Math.round((duratatest-tempotot)*100)/100;
				if(temporimasto == 1) {
					$("#buttoncps").text( '<?php echo _("Clicca qui!"); ?> [1.0 <?php echo _("secondo"); ?> <?php echo _("rimasto"); ?>]');
				} else {
					$("#buttoncps").text( '<?php echo _("Clicca qui!"); ?> ['+(temporimasto).toFixed(1)+' <?php echo _("secondi"); ?> <?php echo _("rimasti"); ?>]');
				}
			}, 100);
		}

		function reset() {
			zone.onmousedown = start;
			$("#buttoncps").prop("disabled",false);
			$("#buttoncps").removeClass("btn-danger");
			$("#buttoncps").addClass("btn-primary");
			$("#buttoncps").text("<?php echo _('Clicca per iniziare!'); ?>");
		}
		
		reset();
		
		function upload() {
			if(duratatest == 10) {
				if((clicks/duratatest) <= 17 && clicks >= 10) {
					$.post('Utils/addCPSAverage.php', {
						mediacps: (clicks/duratatest)
					},
					function(result) {
						if(result == "success") {
							console.log("<?php echo _('CPS Test'); ?>: <?php echo _('I risultati sono stati inviati al server delle classifiche'); ?>.");
						} else if(result == "invalid") {
							console.log("<?php echo _('CPS Test'); ?>: <?php echo _('I risultati sono stati respinti dal server delle classifiche'); ?>.");
						} else if(result == "error") {
							console.log("<?php echo _('CPS Test'); ?>: <?php echo _('Si è verificato un errore cercando di inviare i dati al server delle classifiche'); ?>.");
						} else if(result == "unset") {
							console.log("<?php echo _('CPS Test'); ?>: <?php echo _('Si è verificato un errore interno'); ?>.");
						} else {
							console.log("<?php echo _('CPS Test'); ?>: "+result);
						}
					}
					);
				} else {
					console.log("<?php echo _('CPS Test'); ?>: <?php echo _('I risultati sono stati respinti dal server delle classifiche'); ?>.");
				}
			}
		}
	});
</script>