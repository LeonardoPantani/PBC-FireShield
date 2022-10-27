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

if (!getPermission($id, "CronologiaEventi")) {
	header("Location:pannello.php");
	exit;
}

$nomifile = array_diff(scandir($droot."/".$folderhistory), array('.', '..'));
sort($nomifile, SORT_STRING);

$nfile = new FilesystemIterator("$droot/" . $folderhistory, FilesystemIterator::SKIP_DOTS);
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
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<h2>File di cronologia (<?php echo iterator_count($nfile); ?>)</h2>
			<br>
			<div class="container" style="width:75%;">
				<p>
				É possibile scaricare i file delle scansioni effettuate sulla piattaforma. In base alle impostazioni della configurazioni i file 
				<?php if($keepscanfiles) { echo "<font color='lightgreen'>saranno</font>"; } else { echo "<font color='lightcoral'>non saranno</font>"; } ?> salvati per una durata pari a <?php echo $daysbeforedelete; ?> giorni.
				<br><br>
				La formattazione di ogni nome file è la seguente:<br>
				(ID: <b>ID UTENTE</b>) Cronologia <b>DATA</b> | <b>ORA</b> | <b>Tipo file</b> | <b>Esito</b> | <b>ID SCANSIONE</b> | <b>Stato file</b> [<b>Peso</b>MB]
				</p>
			</div>
			<hr>
			<form id="selezionafile" action="Utils/scaricaHistoryDB.php" method="POST">
				<h5>Come vuoi scaricare il file?</h5>
				<input type="radio" name="mode" value="NORMAL" checked></input> <b>Modalità Normale</b> - <input type="radio" name="mode" value="COMPATIBILITY"></input> <b>Modalità di Compatibilità</b>
				<hr>
				<div class="container" style="width: 50%;">
					<select class="form-control" id="selectfile" name="selectfile" <?php if(iterator_count($nfile) == 0) { echo "disabled"; } ?>>
						<?php if(iterator_count($nfile) == 0) { 
							?><option value="0">Non ci sono file da scaricare</option><?php
						} else { ?>
							<option value="0">-- Seleziona un file da scaricare --</option>
							<?php foreach($nomifile as $valore) {
								$divisione = explode("_", $valore);
								$estensione = explode(".", $valore);
								$data = explode("-", $divisione[1]);
								?>
								<option value="<?php echo $valore; ?>">(ID: <?php echo $divisione[0]; ?>) Cronologia <?php echo $data[0]."-".$data[1]."-".$data[2]." | ".$data[3].":".$data[4].":".$data[5]; ?> | 
								<?php switch($divisione[2]) {
									case 1:
										echo $table_cheatjava;
										break;
									case 2:
										echo $table_cheatdwm;
										break;
									case 3:
										echo $table_cheatmsmpeng;
										break;
									case 4:
										echo $table_cheatlsass;
										break;
									case 5:
										echo $table_suspectjava;
										break;
									case 6:
										echo $table_suspectdwm;
										break;
									case 7:
										echo $table_suspectmsmpeng;
										break;
									case 8:
										echo $table_suspectlsass;
										exit;
									default:
										break;
								}
								?> | <?php
								switch ($divisione[3]) {
									case 0:
										echo "Errore";
										break;
									case 1:
										echo "Pulito";
										break;
									case 2:
										echo "Sospetto";
										break;
									case 3:
										echo "Cheat";
										break;
									default:
										echo "???";
										break;
								}
								?> | ScanID: <?php echo str_replace(".txt", "", $divisione[4]); ?> | <?php if($estensione[1] == "zip") { echo "C"; } elseif($estensione[1] == "txt") { echo "T"; } else { echo "?"; } ?> <b>[<?php echo round(filesize($droot.$folderhistory."/".$valore)/1000000, 1); ?> MB]</b></option>
							<?php }
						}?>
					</select>
				</div>
				<br>
				<button type="submit" id="inviaform" class="btn btn-success" <?php if(iterator_count($nfile) == 0) { echo "disabled title='Non ci sono file disponibili'"; } ?>>Scarica il file selezionato</button>
			</form>
			<span id="esitofile" style="color:lightgray;">Seleziona un file</span>
			<br><br>
        </div>
    </div>
</body>
</html>
<script>
	document.getElementById('jquery').addEventListener('load', function() {	
		$("#selezionafile").submit(function(e) {
			if($('#selectfile').val() == "0") {
				e.preventDefault();
				$("#esitofile").html("<font color='lightcoral'>Seleziona un file</font>");
			} else {
				$("#esitofile").html("<font color='lightgreen'>Attendere prego</font>");
				var stringa = $("#selectfile").val().split(".");
				if(stringa[1] == "txt") {
					swal({
					  position: 'bottom-start',
					  type: 'info',
					  title: 'Compressione in corso...',
					  html: 'Questo file sarà compresso prima di essere inviato.',
					  footer: 'Potrebbero essere necessari alcuni minuti.',
					  showConfirmButton: false,
					  toast: true,
					  timer: 8000
					})	
				} else {
					swal({
					  position: 'bottom-start',
					  type: 'info',
					  title: 'Download in corso...',
					  showConfirmButton: false,
					  toast: true,
					  timer: 5000
					})	
				}
			}
			setTimeout( function(){
				$("#esitofile").html("Seleziona un file");
			}, 2000 );
		});
	});
</script>