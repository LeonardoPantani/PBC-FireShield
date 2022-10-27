<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/dbConnection.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

readUserData($id, $email, $username, $tipo, $network, $otp);

if(isset($_GET["t"])) {
	$stmt = $connection->prepare("SELECT IDCandidatura, Stato FROM $table_applications WHERE Token = ?");
	$stmt->bind_param("s", $_GET["t"]);
	$esito = $stmt->execute();
	if ($esito) {
		$esito = $stmt->get_result();
		$nrighe = $esito->num_rows;
		if($nrighe > 0) {
			$result = $esito->fetch_assoc();
		}
	}
} else {
	$indirizzoIP = getClientIP();

	$stmt = $connection->prepare("SELECT IDCandidatura FROM $table_applications WHERE IndirizzoIP = ? AND Stato = 0");
	$stmt->bind_param("s", $indirizzoIP);
	$esito = $stmt->execute();
	if ($esito) {
		$result = $stmt->get_result();
		$nrighe = $result->num_rows;
		if($nrighe > 0) {
			$candidaturainviata = true;
		} else {
			$candidaturainviata = false;
		}
	}
}

kickOTPUser();
licenseInformation($licenza);
networkInformation($infonetwork);
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
	<?php if($recaptcha_check) { ?><script src='https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>'></script><?php } ?>
</head>
<?php include "$droot/Functions/Include/noscript.php"; ?>
<body <?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>class="webp"<?php } else {?>class="jpg"<?php }} else {?>class="jpg"<?php }?>>
    <?php include "$droot/Functions/Include/navbar.php";?>
    <div class="container text-center text-white">
        <br>
		<!-- AVVISO -->
		<?php if (isset($_GET["e"])) { ?>
		<div class="container text-center">
		<div class="card text-white bg-<?php if($_GET["e"] == 11) { echo "success"; } else { echo "danger"; } ?> mb-2" style="width: 100%;">
		  <div class="card-body">
			<h5 class="card-text text-center"><?php if($_GET["e"] == 11) { echo _("Candidatura inviata"); } else { echo _("Si è verificato un errore"); } ?></h5>
			<p class="card-text text-center">
				<?php if ($_GET["e"] == 1) {echo _("Candidature chiuse.");} elseif ($_GET["e"] == 2) {echo _("Imposta tutti i parametri.");} elseif ($_GET["e"] == 3) {echo _("La lingua non è valida.");} elseif ($_GET["e"] == 4) {echo _("Un'altra candidatura è in attesa di essere valutata.");} elseif ($_GET["e"] == 5) {echo _("Devi aspettare prima di inviare un'altra candidatura.");} elseif ($_GET["e"] == 6) {echo _("Errore reCaptcha."); } elseif ($_GET["e"] == 7) {echo _("Nome e/o Cognome non validi."); } elseif ($_GET["e"] == 8) {echo _("Data di nascita non valida."); } elseif ($_GET["e"] == 9) {echo _("Email non valida."); } elseif ($_GET["e"] == 10) {echo _("Si è verificato un errore durante l'invio della candidatura."); } elseif ($_GET["e"] == 11) { echo _("Sarai contattato entro 1 mese via Email e/o Telegram."); } ?>
			</p>
		  </div>
		</div>
		</div>
		<?php } ?>
		<!-- FINE AVVISO -->
		
        <div class="p-2 mb-2 bg-dark text-white rounded">
            <img src="CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" width="10%" alt="Logo"><h2 style="display:inline;"><?php echo _("Candidature")." ".$solutionname; ?></h2>
			<br>
			<?php if(isset($_GET["t"])) { ?>
				<?php if($nrighe == 0) { ?>
					<h4><?php echo _("Stato candidatura:"); ?></h4>
					<div class="container text-center">
					<div class="card text-white bg-danger mb-2" style="width: 100%;">
					  <div class="card-body">
						<h5 class="card-text text-center"><?php echo _("Token non valido"); ?></h5>
						<p><?php echo _("Questa candidatura non esiste."); ?></p>
					  </div>
					</div>
					</div>
				<?php } else { ?>
					<h4><?php echo _("Stato candidatura:"); ?></h4>
					<?php switch($result["Stato"]) {
						case 0: ?>
							<div class="container text-center">
							<div class="card text-white bg-secondary mb-2" style="width: 100%;">
							  <div class="card-body">
								<h5 class="card-text text-center"><?php echo _("In valutazione"); ?></h5>
								<p>
									<?php echo _("Questa candidatura è stata ricevuta dal nostro staff ed è attualmente in valutazione."); ?>
									<br>
									<?php echo _("Una notifica via Email/Telegram sarà inviata una volta che lo stato della richiesta cambierà."); ?>
								</p>
							  </div>
							</div>
							</div>
						<?php break;
						
						case 1: ?>
							<div class="container text-center">
							<div class="card text-white bg-success mb-2" style="width: 100%;">
							  <div class="card-body">
								<h5 class="card-text text-center"><?php echo _("Accettata"); ?></h5>
								<p><?php echo _("Questa candidatura è stata accettata e lo staff cercherà di mettersi in contatto con te quanto prima via Email/Telegram."); ?></p>
							  </div>
							</div>
							</div>
						<?php break;
						
						case 2: ?>
							<div class="container text-center">
							<div class="card text-white bg-danger mb-2" style="width: 100%;">
							  <div class="card-body">
								<h5 class="card-text text-center"><?php echo _("Rifiutata"); ?></h5>
								<p>
									<?php echo _("Questa candidatura è stata rifiutata."); ?>
									<br>
									<?php echo sprintf(_("Per favore attendi almeno %d giorni tra una candidatura e l'altra."), $dayscooldownstaffapplication); ?>
								</p>
							  </div>
							</div>
							</div>
						<?php break;
						
						default: ?>
							<div class="container text-center">
							<div class="card text-white bg-secondary mb-2" style="width: 100%;">
							  <div class="card-body">
								<h5 class="card-text text-center"><?php echo _("Sconosciuto"); ?></h5>
								<p><?php echo _("Lo stato di questa candidatura è sconosciuto. Non abbiamo altre informazioni al riguardo."); ?></p>
							  </div>
							</div>
							</div>
						<?php break;
					}
					?>
				<?php } ?>
			<?php } else { ?>
				<?php if(!$candidaturainviata) { ?>
				<?php echo _("Compila il seguente modulo per inviare la tua candidatura."); ?>
				<div class="text-left">
					<p>
						<h5 style="color:lightcoral;"><?php echo _("È vietato:"); ?></h5>
						<ul>
							<li><?php echo _("Aggirare il sistema in modo da poter inviare più candidature alla volta."); ?></li>
							<li><?php echo _("Falsificare i dati anagrafici."); ?></li>
							<li><?php echo _("Contattare lo Staff per argomenti riguardanti la candidatura."); ?></li>
						</ul>
					</p>
				</div>
				<font color="lightcoral"><?php echo _("In caso di abusi la tua candidatura sarà annullata."); ?></font>
				<?php } else { ?>
					<div class="container" style="width: 80%;">
						<h5><?php echo _("Una tua candidatura è in corso di valutazione"); ?></h5>
						<font color="lightcoral"><?php echo _("Cerchiamo di prendere in considerazione le candidature il prima possibile, ma considera che il tempo di valutazione potrebbe variare in base a diversi fattori."); ?><br><?php echo _("Potrebbe essere necessario fino a un mese per ricevere l'esito della tua candidatura."); ?></font>
					</div>
				<?php } ?>
				<hr>
				<?php if($applications) { ?>
				<form id="form" name="form" action="applicationDB.php" method="POST">
					<div class="p-2 mb-2 bg-dark text-white rounded">
					<div class="container text-center" style="width: 80%">
						<div class="row">
							<div class="col">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-person"></span>
									</div>
									</div>
									<input class="form-control" type="text" name="nome" placeholder="<?php if(!$candidaturainviata) { echo _('Il tuo Nome'); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></input>
								</div>
							</div>

							<div class="col">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
									<div class="input-group-text">
										<span class="oi oi-person"></span>
									</div>
									</div>
									<input class="form-control" type="text" name="cognome" placeholder="<?php if(!$candidaturainviata) { echo _('Il tuo Cognome'); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></input>
								</div>
							</div>
						</div>

						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-calendar"></span>
							</div>
							</div>
							<input onfocus="(this.type='date')" class="form-control" type="text" name="datanascita" placeholder="<?php if(!$candidaturainviata) { echo _('Quando sei nato?'); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></input>
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-envelope-closed"></span>
							</div>
							</div>
							<input class="form-control" type="text" name="email" placeholder="<?php if(!$candidaturainviata) { echo _('Il tuo Indirizzo Email'); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></input>
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-map-marker"></span>
							</div>
							</div>
							<select class="form-control" name="residenza" <?php if($candidaturainviata) { echo "disabled"; } ?> required>
								<option value="0"><?php if(!$candidaturainviata) { echo _("Il tuo Paese di Residenza"); } else { echo _("In attesa di valutazione..."); } ?></option>
								<option value="Afghanistan">Afghanistan</option>
								<option value="Åland Islands">Åland Islands</option>
								<option value="Albania">Albania</option>
								<option value="Algeria">Algeria</option>
								<option value="American Samoa">American Samoa</option>
								<option value="Andorra">Andorra</option>
								<option value="Angola">Angola</option>
								<option value="Anguilla">Anguilla</option>
								<option value="Antarctica">Antarctica</option>
								<option value="Antigua and Barbuda">Antigua and Barbuda</option>
								<option value="Argentina">Argentina</option>
								<option value="Armenia">Armenia</option>
								<option value="Aruba">Aruba</option>
								<option value="Australia">Australia</option>
								<option value="Austria">Austria</option>
								<option value="Azerbaijan">Azerbaijan</option>
								<option value="Bahamas">Bahamas</option>
								<option value="Bahrain">Bahrain</option>
								<option value="Bangladesh">Bangladesh</option>
								<option value="Barbados">Barbados</option>
								<option value="Belarus">Belarus</option>
								<option value="Belgium">Belgium</option>
								<option value="Belize">Belize</option>
								<option value="Benin">Benin</option>
								<option value="Bermuda">Bermuda</option>
								<option value="Bhutan">Bhutan</option>
								<option value="Bolivia">Bolivia</option>
								<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
								<option value="Botswana">Botswana</option>
								<option value="Bouvet Island">Bouvet Island</option>
								<option value="Brazil">Brazil</option>
								<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
								<option value="Brunei Darussalam">Brunei Darussalam</option>
								<option value="Bulgaria">Bulgaria</option>
								<option value="Burkina Faso">Burkina Faso</option>
								<option value="Burundi">Burundi</option>
								<option value="Cambodia">Cambodia</option>
								<option value="Cameroon">Cameroon</option>
								<option value="Canada">Canada</option>
								<option value="Cape Verde">Cape Verde</option>
								<option value="Cayman Islands">Cayman Islands</option>
								<option value="Central African Republic">Central African Republic</option>
								<option value="Chad">Chad</option>
								<option value="Chile">Chile</option>
								<option value="China">China</option>
								<option value="Christmas Island">Christmas Island</option>
								<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
								<option value="Colombia">Colombia</option>
								<option value="Comoros">Comoros</option>
								<option value="Congo">Congo</option>
								<option value="The Democratic Republic of The Congo">The Democratic Republic of The Congo</option>
								<option value="Cook Islands">Cook Islands</option>
								<option value="Costa Rica">Costa Rica</option>
								<option value="Cote D'ivoire">Cote D'ivoire</option>
								<option value="Croatia">Croatia</option>
								<option value="Cuba">Cuba</option>
								<option value="Cyprus">Cyprus</option>
								<option value="Czech Republic">Czech Republic</option>
								<option value="Denmark">Denmark</option>
								<option value="Djibouti">Djibouti</option>
								<option value="Dominica">Dominica</option>
								<option value="Dominican Republic">Dominican Republic</option>
								<option value="Ecuador">Ecuador</option>
								<option value="Egypt">Egypt</option>
								<option value="El Salvador">El Salvador</option>
								<option value="Equatorial Guinea">Equatorial Guinea</option>
								<option value="Eritrea">Eritrea</option>
								<option value="Estonia">Estonia</option>
								<option value="Ethiopia">Ethiopia</option>
								<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
								<option value="Faroe Islands">Faroe Islands</option>
								<option value="Fiji">Fiji</option>
								<option value="Finland">Finland</option>
								<option value="France">France</option>
								<option value="French Guiana">French Guiana</option>
								<option value="French Polynesia">French Polynesia</option>
								<option value="French Southern Territories">French Southern Territories</option>
								<option value="Gabon">Gabon</option>
								<option value="Gambia">Gambia</option>
								<option value="Georgia">Georgia</option>
								<option value="Germany">Germany</option>
								<option value="Ghana">Ghana</option>
								<option value="Gibraltar">Gibraltar</option>
								<option value="Greece">Greece</option>
								<option value="Greenland">Greenland</option>
								<option value="Grenada">Grenada</option>
								<option value="Guadeloupe">Guadeloupe</option>
								<option value="Guam">Guam</option>
								<option value="Guatemala">Guatemala</option>
								<option value="Guernsey">Guernsey</option>
								<option value="Guinea">Guinea</option>
								<option value="Guinea-bissau">Guinea-bissau</option>
								<option value="Guyana">Guyana</option>
								<option value="Haiti">Haiti</option>
								<option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
								<option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
								<option value="Honduras">Honduras</option>
								<option value="Hong Kong">Hong Kong</option>
								<option value="Hungary">Hungary</option>
								<option value="Iceland">Iceland</option>
								<option value="India">India</option>
								<option value="Indonesia">Indonesia</option>
								<option value="Islamic Republic of Iran">Islamic Republic of Iran</option>
								<option value="Iraq">Iraq</option>
								<option value="Ireland">Ireland</option>
								<option value="Isle of Man">Isle of Man</option>
								<option value="Israel">Israel</option>
								<option value="Italy">Italy</option>
								<option value="Jamaica">Jamaica</option>
								<option value="Japan">Japan</option>
								<option value="Jersey">Jersey</option>
								<option value="Jordan">Jordan</option>
								<option value="Kazakhstan">Kazakhstan</option>
								<option value="Kenya">Kenya</option>
								<option value="Kiribati">Kiribati</option>
								<option value="Democratic People's Republic of Korea">Democratic People's Republic of Korea</option>
								<option value="Republic of Korea">Republic of Korea</option>
								<option value="Kuwait">Kuwait</option>
								<option value="Kyrgyzstan">Kyrgyzstan</option>
								<option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
								<option value="Latvia">Latvia</option>
								<option value="Lebanon">Lebanon</option>
								<option value="Lesotho">Lesotho</option>
								<option value="Liberia">Liberia</option>
								<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
								<option value="Liechtenstein">Liechtenstein</option>
								<option value="Lithuania">Lithuania</option>
								<option value="Luxembourg">Luxembourg</option>
								<option value="Macao">Macao</option>
								<option value="The Former Yugoslav Republic of Macedonia">The Former Yugoslav Republic of Macedonia</option>
								<option value="Madagascar">Madagascar</option>
								<option value="Malawi">Malawi</option>
								<option value="Malaysia">Malaysia</option>
								<option value="Maldives">Maldives</option>
								<option value="Mali">Mali</option>
								<option value="Malta">Malta</option>
								<option value="Marshall Islands">Marshall Islands</option>
								<option value="Martinique">Martinique</option>
								<option value="Mauritania">Mauritania</option>
								<option value="Mauritius">Mauritius</option>
								<option value="Mayotte">Mayotte</option>
								<option value="Mexico">Mexico</option>
								<option value="Federated States of Micronesia">Federated States of Micronesia</option>
								<option value="Republic of Moldova">Moldova</option>
								<option value="Monaco">Monaco</option>
								<option value="Mongolia">Mongolia</option>
								<option value="Montenegro">Montenegro</option>
								<option value="Montserrat">Montserrat</option>
								<option value="Morocco">Morocco</option>
								<option value="Mozambique">Mozambique</option>
								<option value="Myanmar">Myanmar</option>
								<option value="Namibia">Namibia</option>
								<option value="Nauru">Nauru</option>
								<option value="Nepal">Nepal</option>
								<option value="Netherlands">Netherlands</option>
								<option value="Netherlands Antilles">Netherlands Antilles</option>
								<option value="New Caledonia">New Caledonia</option>
								<option value="New Zealand">New Zealand</option>
								<option value="Nicaragua">Nicaragua</option>
								<option value="Niger">Niger</option>
								<option value="Nigeria">Nigeria</option>
								<option value="Niue">Niue</option>
								<option value="Norfolk Island">Norfolk Island</option>
								<option value="Northern Mariana Islands">Northern Mariana Islands</option>
								<option value="Norway">Norway</option>
								<option value="Oman">Oman</option>
								<option value="Pakistan">Pakistan</option>
								<option value="Palau">Palau</option>
								<option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
								<option value="Panama">Panama</option>
								<option value="Papua New Guinea">Papua New Guinea</option>
								<option value="Paraguay">Paraguay</option>
								<option value="Peru">Peru</option>
								<option value="Philippines">Philippines</option>
								<option value="Pitcairn">Pitcairn</option>
								<option value="Poland">Poland</option>
								<option value="Portugal">Portugal</option>
								<option value="Puerto Rico">Puerto Rico</option>
								<option value="Qatar">Qatar</option>
								<option value="Reunion">Reunion</option>
								<option value="Romania">Romania</option>
								<option value="Russian Federation">Russian Federation</option>
								<option value="Rwanda">Rwanda</option>
								<option value="Saint Helena">Saint Helena</option>
								<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
								<option value="Saint Lucia">Saint Lucia</option>
								<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
								<option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
								<option value="Samoa">Samoa</option>
								<option value="San Marino">San Marino</option>
								<option value="Sao Tome and Principe">Sao Tome and Principe</option>
								<option value="Saudi Arabia">Saudi Arabia</option>
								<option value="Senegal">Senegal</option>
								<option value="Serbia">Serbia</option>
								<option value="Seychelles">Seychelles</option>
								<option value="Sierra Leone">Sierra Leone</option>
								<option value="Singapore">Singapore</option>
								<option value="Slovakia">Slovakia</option>
								<option value="Slovenia">Slovenia</option>
								<option value="Solomon Islands">Solomon Islands</option>
								<option value="Somalia">Somalia</option>
								<option value="South Africa">South Africa</option>
								<option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
								<option value="Spain">Spain</option>
								<option value="Sri Lanka">Sri Lanka</option>
								<option value="Sudan">Sudan</option>
								<option value="Suriname">Suriname</option>
								<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
								<option value="Swaziland">Swaziland</option>
								<option value="Sweden">Sweden</option>
								<option value="Switzerland">Switzerland</option>
								<option value="Syrian Arab Republic">Syrian Arab Republic</option>
								<option value="Taiwan, Province of China">Taiwan, Province of China</option>
								<option value="Tajikistan">Tajikistan</option>
								<option value="United Republic of Tanzania">United Republic of Tanzania</option>
								<option value="Thailand">Thailand</option>
								<option value="Timor-leste">Timor-leste</option>
								<option value="Togo">Togo</option>
								<option value="Tokelau">Tokelau</option>
								<option value="Tonga">Tonga</option>
								<option value="Trinidad and Tobago">Trinidad and Tobago</option>
								<option value="Tunisia">Tunisia</option>
								<option value="Turkey">Turkey</option>
								<option value="Turkmenistan">Turkmenistan</option>
								<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
								<option value="Tuvalu">Tuvalu</option>
								<option value="Uganda">Uganda</option>
								<option value="Ukraine">Ukraine</option>
								<option value="United Arab Emirates">United Arab Emirates</option>
								<option value="United Kingdom">United Kingdom</option>
								<option value="United States">United States</option>
								<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
								<option value="Uruguay">Uruguay</option>
								<option value="Uzbekistan">Uzbekistan</option>
								<option value="Vanuatu">Vanuatu</option>
								<option value="Venezuela">Venezuela</option>
								<option value="Viet Nam">Viet Nam</option>
								<option value="Virgin Islands, British">Virgin Islands, British</option>
								<option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
								<option value="Wallis and Futuna">Wallis and Futuna</option>
								<option value="Western Sahara">Western Sahara</option>
								<option value="Yemen">Yemen</option>
								<option value="Zambia">Zambia</option>
								<option value="Zimbabwe">Zimbabwe</option>
							</select>
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-globe"></span>
							</div>
							</div>
							<select class="form-control" name="lingua" <?php if($candidaturainviata) { echo "disabled"; } ?> required>
								<option value="0"><?php echo _("Lingua madre (Se non è presente scegli quella che conosci meglio)"); ?></option>
								<?php foreach($language_list as $chiave => $valore) { ?>
										<option value="<?php echo $valore; ?>"><?php echo $language_list_complete[$chiave]; ?></option>
								<?php } ?>
							</select>
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-prepend">
							<div class="input-group-text">
								<span class="oi oi-target"></span>
							</div>
							</div>
							<input class="form-control" type="text" name="telegramusername" placeholder="<?php if(!$candidaturainviata) { echo _("Username di Telegram"); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></input>
						</div>
						
						<hr>

						<h6 class="text-left"><?php echo _("Parlaci di te, descrivi la tua personalità, le tue passioni e hobby. Se conosci altre lingue elencale qui."); ?></h6>
						<textarea maxlength="<?php echo $maxcharactersapplication; ?>" class="form-control" rows="4" name="risposta1" placeholder="<?php if(!$candidaturainviata) { echo sprintf(_("Rispondi alla domanda. Hai a disposizione %d caratteri."), $maxcharactersapplication); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></textarea>
						<br>
						
						<h6 class="text-left"><?php echo _("Parlaci della tua esperienza con cheat, autoclicker, stringhe eccetera."); ?></h6>
						<textarea maxlength="<?php echo $maxcharactersapplication; ?>" class="form-control" rows="4" name="risposta2" placeholder="<?php if(!$candidaturainviata) { echo sprintf(_("Rispondi alla domanda. Hai a disposizione %d caratteri."), $maxcharactersapplication); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></textarea>
						<br>
						
						<h6 class="text-left"><?php echo _("Come svolgi, di solito e a grandi linee, un controllo anticheat (SS)?"); ?></h6>
						<textarea maxlength="<?php echo $maxcharactersapplication; ?>" class="form-control" rows="4" name="risposta3" placeholder="<?php if(!$candidaturainviata) { echo sprintf(_("Rispondi alla domanda. Hai a disposizione %d caratteri."), $maxcharactersapplication); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></textarea>
						<br>
						<h6 class="text-left"><?php echo sprintf(_("Quanto tempo saresti disposto a dedicare a %s?"), $solutionname_short); ?></h6>
						<textarea maxlength="<?php echo $maxcharactersapplication; ?>" class="form-control" rows="4" name="risposta4" placeholder="<?php if(!$candidaturainviata) { echo sprintf(_("Rispondi alla domanda. Hai a disposizione %d caratteri."), $maxcharactersapplication); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></textarea>
						<br>
						<h6 class="text-left"><?php echo sprintf(_("Perché vorresti entrare a far parte dello staff di %s?"), $solutionname_short); ?></h6>
						<textarea maxlength="<?php echo $maxcharactersapplication; ?>" class="form-control" rows="4" name="risposta5" placeholder="<?php if(!$candidaturainviata) { echo sprintf(_("Rispondi alla domanda. Hai a disposizione %d caratteri."), $maxcharactersapplication); } else { echo _("In attesa di valutazione..."); } ?>" required <?php if($candidaturainviata) { echo "disabled"; } ?>></textarea>
						<br>
						<?php if($recaptcha_check) { ?>
							<div class="text-xs-center" <?php if($candidaturainviata) { echo "style='display:none;'"; } ?>>
								<div style="display: inline-block;" name="recaptchaResponse" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_sitekey4; ?>"><?php echo _("Caricamento"); ?>...</div>
							</div>
						<?php } ?>
						<br>
						<button class="btn btn-success" type="submit" name="send" <?php if($candidaturainviata) { echo "disabled"; } ?>><?php if(!$candidaturainviata) { echo _("Invia la candidatura"); } else { echo _("In attesa di valutazione..."); } ?></button>
						<br>
					</div>
					</div>
				</form>
				<?php } else { ?>
					<div class="container text-center">
					<div class="card text-white bg-danger mb-2" style="width: 100%;">
					  <div class="card-body">
						<h5 class="card-text text-center"><?php echo _("Impossibile inviare candidature"); ?></h5>
						<p><?php echo _("Le candidature sono al momento chiuse."); ?></p>
					  </div>
					</div>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
    </div>
	<br><br>
	<?php include "$droot/Functions/Include/footer.php";?>
</body>
</html>
<script>
	document.getElementById('jquery').addEventListener('load', function() {
		$("#form").submit(function(e) {
			swal({
			  title: "<?php echo _('Attendi'); ?>",
			  text: "<?php echo _('Invio candidatura in corso'); ?>...",
			  type: "info"
			})
		});
	});
</script>