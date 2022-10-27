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
<html lang="it"> <!-- Copyright FireShield (author: Leonardo Pantani). All rights reserved. -->
<head>
    <title>Sound debug <?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
	<audio id="audio" ontimeupdate="document.getElementById('tracktime').innerHTML = Math.floor(this.currentTime * 100)/100 + ' <b>secondi</b> / ' + Math.floor(this.duration) + ' <b>secondi totali</b>';">
		<source src="../CSS/Sounds/Complete.mp3" type="audio/mpeg">
	</audio>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<h1 style="text-color:black;">Debug Suoni</h1>
			<br>
			<div class="container" style="width:50%;">
				<div class="progress">
				  <div id="progressbar" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
			</div>
			<br>
			<button type="button" id="debugsuono" class="btn btn-success" name="debugsuono" onClick="disabilitaButton();">Inizia / Riproduci file audio</button>
			<hr>
			<p>Stato audio:</p>
			<br>
			<span>Nome del file da riprodurre: Complete.mp3</span>
			<br>
			<span id="tracktime">In attesa di file audio...</span>
			<hr>
			<button type="button" class="btn btn-warning" onClick="pausaSuono()" name="pausasuono">Pausa</button>&nbsp;<button type="button" class="btn btn-danger" onClick="fermaSuono()" name="fermasuono">Stop</button>
			<br><br>
		</div>
	</div>
	<br><br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html> <!-- titolo & testo -->
<script>
	function disabilitaButton() {
		$("#audio")[0].play();
		document.getElementById('debugsuono').disabled = true;
	}
	
	function pausaSuono() {
		$('#audio')[0].pause();
		document.getElementById('debugsuono').disabled = false;
	}
	
	function fermaSuono() {
		$('#audio')[0].pause();
		$('#audio')[0].currentTime = 0;
		document.getElementById('debugsuono').disabled = false;
	}
		
	document.getElementById('jquery').addEventListener('load', function() {
		$('#audio').on('timeupdate', function() {
			progresso = parseInt(((this.currentTime / this.duration) * 100), 10);
			$("#progressbar").css('width', progresso+'%');
		});
	});
</script>