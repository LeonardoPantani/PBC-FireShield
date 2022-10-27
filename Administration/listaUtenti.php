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

if (!getPermission($id, "Account_See")) {
	header("Location:pannello.php");
	exit;
}

$utenti_aggiuntivi = 2; // utenti che hanno pagato con paysafe

if(isset($_GET["order"])) {
	if($_GET["order"] == "id") {
		$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY $table_users.ID ASC");
	} elseif($_GET["order"] == "date") {
		$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY DataUltimoLogin DESC");
	} elseif($_GET["order"] == "type") {
		$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY Tipo DESC");
	} elseif($_GET["order"] == "expire") {
		$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY Scadenza DESC");
	} elseif($_GET["order"] == "username") {
		$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY Username ASC");
	} else {
		$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY $table_users.ID ASC");
	}
} else {
	$stmt = $connection -> prepare("SELECT *, $table_users.ID as ID FROM $table_users, $table_licenses WHERE $table_users.ID = $table_licenses.IDCliente ORDER BY $table_users.ID ASC");
}

$stmt -> execute();
$result = $stmt -> get_result();
$nrighe = $result -> num_rows;

$amministratori_totali = 0;
while($riga = $result -> fetch_assoc()) {
	if($riga["Tipo"] == 1) {
		$amministratori_totali++;
	}
}
mysqli_data_seek($result, 0);

// SALVA TUTTI GLI ID CHE HANNO UNA LICENZA NELLA LISTA PAGAMENTI
$stmt = $connection -> prepare("SELECT IDUtente FROM $table_payments");
$stmt -> execute();
$result_p = $stmt -> get_result();
$nrighe_p = ($result_p -> num_rows) + $utenti_aggiuntivi;

while($riga_p = $result_p -> fetch_assoc()) {
	$righe_p[] = $riga_p["IDUtente"];
}

$perm_account_history = getPermission($id, "Account_History");
$perm_account_secret = getPermission($id, "Account_Secret");
$perm_account_license = getPermission($id, "Account_License");
$perm_account_password = getPermission($id, "Account_Password");
$perm_account_block = getPermission($id, "Account_Block");
?>
<html lang="it"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo _("Amministrazione")." "; echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<style>
.loader {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background: url('../CSS/Images/scanning.gif') 50% 50% no-repeat rgb(0,0,0);
	background-size: 100px 100px;
    opacity: .9;
}
</style>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
	<div class="loader"></div>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container-fluid text-center text-white">
        <br>
        <div class="p-2 mb-2 bg-dark text-white rounded">
			<h4><?php echo sprintf(_("Ci sono %s utenti e %s amministratori registrati a %s."), "<font style='color:lightblue;'>".($nrighe-$amministratori_totali)."</font>", "<font style='color:orange;'>".$amministratori_totali."</font>", "<font style='color:orange;'>".$solutionname."</font>"); ?></h4>
			<p><small>(<?php echo sprintf(_("%d utenti hanno pagato una licenza, %d%% del totale"), $nrighe_p, round(($nrighe_p*100)/($nrighe-$amministratori_totali), 2)); ?>)<?php if(getPermission($id, "ListaPagamenti")) { ?> | <a href="listaPagamenti.php"><?php echo _("Clicca qui per andare alla Lista Pagamenti"); ?></a><?php } ?></small></p>
			<?php if($nrighe > 0) { ?>
            <table class="table table-dark table-bordered">
                <tr>
                    <th><a href="listaUtenti.php?order=id"><?php echo _("ID"); ?> <?php if (isset($_GET["order"])) {if ($_GET["order"] == "id") {echo "<b>*</b>";}} else {echo "<b>*</b>";}?></a></th>
                    <th><?php echo _("Indirizzo Email"); ?></th>
					<th><a href="listaUtenti.php?order=username"><?php echo _("Username"); ?> <?php if (isset($_GET["order"])) {if ($_GET["order"] == "username") {echo "<b>*</b>";}} ?></a></th>
					<th><a href="listaUtenti.php?order=type"><?php echo _("Tipo"); ?> <?php if (isset($_GET["order"])) {if ($_GET["order"] == "type") {echo "<b>*</b>";}} ?></a></th>
					<th><?php echo _("Secret"); ?></th>
					<th><?php echo _("Codice OTP"); ?></th>
					<th><?php echo _("Licenza"); ?></th>
					<th><a href="listaUtenti.php?order=expire"><?php echo _("Scadenza"); ?> <?php if (isset($_GET["order"])) {if ($_GET["order"] == "expire") {echo "<b>*</b>";}} ?></a></th>
					<th><a href="listaUtenti.php?order=date"><?php echo _("Data ultimo login"); ?> <?php if (isset($_GET["order"])) {if ($_GET["order"] == "date") {echo "<b>*</b>";}} ?></a></th>
					<th><?php echo _("Ultimo IP"); ?></th>
					<th><b><?php echo _("Altro"); ?></b></th>
                </tr>
                <tbody class="table-striped">
                    <?php
						while ($riga = $result->fetch_assoc()) {
							echo "<tr>";
							echo "<td>";
							if($riga["ID"] == "") {
								echo "//";
							} else {
								if($riga["ID"] >= 0 && $riga["ID"] < 10) {
									echo "&nbsp;&nbsp;";
								}
								echo $riga["ID"]." ";
								if($riga["Tipo"] == 1) {
									echo "👮";
								} else {
									if(!in_array($riga["ID"], $righe_p)) {
										echo "<font color='lightcoral'>❌</font>";
									} else {
										echo "<font color='lightgreen'>✅</font>";
									}
								}
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Email"] == "") {
								echo "//";
							} else {
								if(strlen($riga["Email"]) > 22) {
									echo "<a title='".$riga["Email"]."'>" . substr($riga["Email"], 0, 22) . " (...)</a>";
								} else {
									echo $riga["Email"];
								}
							}
							echo "</td>";
							
							echo "<td>";
							?><button title="<?php echo sprintf(_("Clicca per effettuare il Login Forzato con l'account di %s.&#10;I dati relativi alla sessione attuale andranno persi."), $riga["Username"]); ?>" name="lf" onClick="lf(<?php echo $riga["ID"]; ?>)" class="btn btn-link"><span class="oi oi-key"></span></button><?php
							if($riga["Username"] == "") {
								echo "//";
							} else {
								if($riga["Abilitato"] == 0) {
									echo "<a title='"._("Questo account è disabilitato")."' style='color:lightcoral;'>".$riga["Username"]."</a>";
								} else {
									if($perm_account_history) {
										echo "<a href='cronologiaUsername.php?id=".$riga["ID"]."'>".$riga["Username"]."</a>";
									} else {
										echo $riga["Username"];
									}
								}
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Tipo"] == 0) {
								echo "<font color='lightblue'>"._("Utente")."</font>";
							} elseif($riga["Tipo"] == 1) {
								echo "<font color='gold'>"._("Admin")."</font>";
							} elseif($riga["Tipo"] == 2) {
								echo "<font color='lightgreen'>"._("Partner")."</font>";
							} else {
								echo "???";
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["Secret"] != "") {
								if($riga["Tipo"] == 1) {
									echo "<a title='"._("Gli amministratori hanno il Secret Code nascosto per motivi di sicurezza")."'>//</a>";
								} else {
									if($perm_account_secret) {
										echo $riga["Secret"];
									} else {
										echo "<font style='color:lightcoral;'>"._("Nessun permesso")."</font>";
									}
								}
							} else {
								echo "//";
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["OTP"] == "") {
								echo "//";
							} else {
								echo $riga["OTP"];
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["DurataLicenza"] == "" && $riga["TipoLicenza"] == "") {
								echo "//";
							} else {
								if($riga["ID"] == 1) {
									echo "("._("Amministratore").")";
								} else {
									if($perm_account_license) {
									?><a href="modificaLicenza.php?id=<?php echo $riga['ID']; ?>" title="<?php echo sprintf(_("Clicca per modificare la licenza di %s"), $riga['Username']); ?>"><?php
									echo $riga["TipoLicenza"]." (<i>".$riga["DurataLicenza"]."</i>)";
									?></a><?php
									} else {
										echo $riga["TipoLicenza"]." (<i>".$riga["DurataLicenza"]."</i>)";
									}
								}
							}
							echo "</td>";

							echo "<td>";
							if($riga["Scadenza"] == "" || $riga["DurataLicenza"] == "permanent") {
								echo "//";
							} else {
								if(strtotime($riga["Scadenza"]) < strtotime("now")) {
									$differenza_data = strtotime("now") - strtotime($riga["Scadenza"]);
									echo "<a title='"._("Scaduta in data:")." ".formatDate($riga["Scadenza"])."'><font style='color:lightcoral;'>".round($differenza_data / (60*60*24))." "._("giorni")." "._("fa")."</font></a>";
								} else {
									echo formatDate($riga["Scadenza"]);
								}
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["DataUltimoLogin"] == "") {
								echo "//";
							} else {
								echo "<a title='";
								$differenza_data = strtotime("now") - strtotime($riga["DataUltimoLogin"]);
								
								$differenza_data_giorni = round($differenza_data / (60*60*24));
								if($differenza_data_giorni <= 0) {
									$differenza_data_ore = round($differenza_data / 60 / 60);
									if($differenza_data_ore <= 0) {
										$differenza_data_minuti = round($differenza_data / 60);
										if($differenza_data_minuti <= 0) {
											echo _("Qualche secondo");
										} else {
											echo $differenza_data_minuti." ";
											if($differenza_data_minuti > 1) {
												echo _("minuti");
											} else {
												echo _("minuto");
											}
										}
									} else {
										echo $differenza_data_ore." ";
										if($differenza_data_ore > 1) {
											echo _("ore");
										} else {
											echo _("ora");
										}
									}
								} else {
									echo $differenza_data_giorni." ";
									if($differenza_data_giorni > 1) {
										echo _("giorni");
									} else {
										echo _("giorno");
									}
								}
								echo " "._("fa");
								echo "'>".formatDate($riga["DataUltimoLogin"], true)."</a>";
							}
							echo "</td>";
							
							echo "<td>";
							if($riga["UltimoIP"] == "") {
								echo "//";
							} else {
								if($riga["Tipo"] == 1) {
									echo "<a title='"._("Gli amministratori hanno IP nascosto per motivi di sicurezza")."'>//</a>";
								} else {
									if($perm_account_history) {
										echo "<small>".$riga["UltimoIP"]."</small>";
									} else {
										echo "<font style='color:lightcoral;'>"._("Nessun permesso")."</font>";
									}
								}
							}
							echo "</td>";
							
							echo "<td>";
							?><small>
								<?php if($perm_account_password) { ?><a href="Utils/aggiornaPass.php?id=<?php echo $riga['ID']; ?>&value=<?php if($riga["AggiornaPassword"] == 1) { echo "0"; } else { echo "1"; } ?>" title="<?php if($riga['AggiornaPassword'] == 1) { echo _("Clicca per non rendere più obbligatorio il cambio password all'utente"); } else { echo _("Clicca per obbligare questo utente a cambiare la sua password al prossimo login"); } ?>"><font color="<?php if($riga['AggiornaPassword'] == 1) { echo "lightgreen"; } else { echo "red"; } ?>"><span class="oi oi-key"></span></font></a><?php } else { ?><a title="<?php echo _("Nessun permesso"); ?>"><span class="oi oi-key"></span></a><?php } ?>&nbsp;
								<?php if($perm_account_block) { ?><a href="Utils/aggiornaStatoAccount.php?id=<?php echo $riga['ID']; ?>&value=<?php if($riga["Abilitato"] == 1) { echo "0"; } else { echo "1"; } ?>" title="<?php if($riga["Abilitato"] == 1) { echo _("Clicca per disabilitare questo account"); } else { echo _("Clicca per abilitare questo account"); } ?>"><font color="<?php if($riga["Abilitato"] == 1) { echo "red"; } else { echo "lightgreen"; } ?>"><span class="oi oi-link-broken"></span></font></a><?php } else { ?><a title="<?php echo _("Nessun permesso"); ?>"><span class="oi oi-link-broken"></span></a><?php } ?>&nbsp;
								<?php if($perm_account_secret) { if($riga["Secret"] != "") { ?><a href="Utils/eliminaSecret.php?id=<?php echo $riga['ID']; ?>" title="<?php echo sprintf(_("Clicca per resettare il Secret code di %s"), $riga['Username']); ?>"><span class="oi oi-x"></span></a>&nbsp;<?php } else { ?><a title="<?php echo _("Questo account non ha un Secret code impostato"); ?>"><font color="lightgray"><span class="oi oi-x"></span></font></a><?php } } else { ?><a title="<?php echo _("Nessun permesso"); ?>"><span class="oi oi-x"></span></a><?php } ?>
							</small><?php
							echo "</tr>";
						}
					?>
                </tbody>
            </table>
			<p style="color:lightgray;"><small><?php echo _("I dati all'interno di questa tabella sono aggiornati costantemente. In caso di problemi contattare l'amministratore di sistema."); ?></small></p>
			<a href="modificaMassa.php"><?php echo _("Modifica in massa della durata delle licenze"); ?></a>
			<?php } else { ?>
				<p style="color:lightcoral;"><?php echo sprintf(_("La tabella %s non ha valori al suo interno."), "<b>".$table_users."</b>"); ?></p>
			<?php } ?>
        </div>
    </div>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
document.getElementById('jquery').addEventListener('load', function() {	
	$(window).on('load', function(){
		$(".loader").fadeOut("slow");
	});
});

function lf(idutente) {
	swal({
	  title: "Attendi.",
	  text: "Effettuando login con token di trasferimento...",
	  type: "info"
	});
	$.post('Utils/generaLF.php', {
		idutente: idutente
	},
	function(result) {
		if(result == "error_update") {
			swal({
			  title: "Si è verificato un errore",
			  text: "Impossibile effettuare l'aggiornamento LF.",
			  type: "error"
			});
		} else if(result == "no_permission") {
			swal({
			  title: "Si è verificato un errore",
			  text: "Non hai i permessi.",
			  type: "error"
			});
		} else if(result == "cant_login") {
			swal({
			  title: "Si è verificato un errore",
			  text: "Non è possibile effettuare il login forzato ad un account con un livello di permessi uguale al tuo.",
			  type: "error"
			});
		} else if(result == "unset") {
			swal({
			  title: "Si è verificato un errore",
			  text: "Imposta tutti i parametri.",
			  type: "error"
			});
		} else {
			location.href = "/loginDB.php?lf="+result;
		}
	}
	);
};
</script>