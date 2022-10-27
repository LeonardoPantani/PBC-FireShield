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

if (!getPermission($id, "ModificaSoftware")) {
    header("Location:pannello.php");
    exit;
}
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
        <br><br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <h4>Contenuto Cartella Applications</h4>
            <?php if ($handle = opendir('../Files/Software/Applications/')) { ?>
            <table class="table table-dark">
                <tr>
                    <th>File</th>
					<th>Altro</th>
                </tr>
                <tbody class="table-striped">
					<?php
						while (($entry = readdir($handle)) !== false) {
							echo "<tr>";
							if ($entry != "." && $entry != "..") {
								echo "<td>$entry</td>";
								?><td><a title="Clicca per eliminare." href="Utils/deleteFile.php?nomefile=<?php echo $entry; ?>">Elimina <?php echo $entry; ?></a></td><?php
							}
							echo "</tr>";
						}
						closedir($handle);
					?>
				</tbody>
			</table>
			<hr>
			<h4>Aggiungi un File</h4>
			<form id="formupload" enctype="multipart/form-data" name="aggiuntaFile" action="modificaApplicationsDB.php" method="POST">
				<input id="file" type="file" name="file" required></input>
				<br>
				<span id="esitoupload"></span>
				<br>
				<button type="submit" name="inviafile" class="btn btn-success">Invia il file alla cartella</button>
			</form>
			<?php } else { ?>
				<p style="color:lightcoral;">Non è stato possibile aprire la cartella.</p>
			<?php } ?>
        </div>
    </div>
    <?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
document.getElementById('jquery').addEventListener('load', function() {	
	$("#formupload").submit(function(e) {
		e.preventDefault();
		var file = document.getElementById("file").files[0];
		form_data = new FormData();
		form_data.append("file", file);
		$.ajax({
			url: 'Utils/addApplication.php',
			type: 'POST',
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			
			success: function(result) {
				if(result == "success") {
					$("#esitoupload").css("color", "lightgreen");
					$("#esitoupload").html("<b>File aggiunto/modificato!</b>"+"<br>");
				} else {
					$("#esitoupload").css("color", "red");
					if(result == "error_generic") {
						$("#esitoupload").html("Si è verificato un errore."+"<br>");
					} else if(result == "error_upload_fail") {
						$("#esitoupload").html("Carica il file."+"<br>");
					} else {
						$("#esitoupload").html("Caso imprevisto: "+result+"<br>");
					}
				}
				setTimeout( function(){
					location.reload();
				}, 2000 );
			},
			error: function(result) {
				$("#esitoupload").html("Caso imprevisto: "+result+"<br>");
			},
			// Custom XMLHttpRequest
			xhr: function() {
				var myXhr = $.ajaxSettings.xhr();
				var percentuale;
				$("#esitoupload").css("color", "white");
				if (myXhr.upload) {
					// For handling the progress of the upload
					myXhr.upload.addEventListener('progress', function(e) {
						if (e.lengthComputable) {
							percentuale = (100 * e.loaded) / e.total;
							percentuale = Math.round(percentuale);
							$("#esitoupload").html("Progresso Upload: <b>"+percentuale+"%</b>"+"<br>");
						}
					} , false);
				}
				return myXhr;
			}
		});
	});
});
</script>
