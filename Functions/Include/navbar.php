<?php
bindtextdomain("navbar", $locale);
bind_textdomain_codeset("navbar", 'UTF-8');
textdomain("navbar");
?>
<div class="div_visible_important">
	<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-secondary">
		<!-- MENU' SELEZIONE LINGUA -->
		<div class="dropdown">
			<button title="<?php echo _('Clicca qui per cambiare lingua'); ?>" class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="text-white"><span class="oi oi-globe"></span></span>
			</button>
			<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
				<?php foreach($language_list as $chiave => $valore) { ?>
					<a title="<?php echo _('Clicca qui per cambiare la lingua in').' '.$language_list_complete[$chiave]; ?>" class="dropdown-item" href="/Translations/setLanguage.php?l=<?php echo $valore; ?>"><?php echo $language_list_complete[$chiave]; ?></a>
				<?php } ?>
			</div>
		</div>
		<!-- FINE SELEZIONE LINGUA -->
		
		<!-- LOGO E SCRITTA "FIRESHIELD" -->
		<a title="<?php echo _('Clicca qui per andare alla Homepage'); ?>" class="navbar-brand" href="/">
			<img src="/CSS/Images/logo.<?php if (isset($_SERVER['HTTP_ACCEPT'])) {if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp')) {?>webp<?php } else {?>png<?php }} else {?>png<?php }?>" height="35" class="d-inline-block align-top" alt="Logo">
			<?php if ($mobile) {echo "FSCD";} else {echo $solutionname;}?>
		</a>
		<!-- FINE LOGO E SCRITTA "FIRESHIELD" -->
		
		<!-- PULSANTE PER ESTENDERE NAVBAR MOBILE -->
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
		</button>
		<!-- FINE PULSANTE PER ESTENDERE NAVBAR MOBILE -->
		
		<!-- TESTO NAVBAR -->
		<div class="collapse navbar-collapse" id="navbarText">
			<!-- TESTO NAVBAR A SINISTRA -->
			<ul class="navbar-nav mr-auto">
				<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "news.php") {echo "active";}?>">
					<a title="<?php echo _('Novità'); ?>" class="nav-link" href="/news.php"><b><?php echo _("Novità"); ?></b></a>
				</li>
				
				<!-- PULSANTI LOGIN (NON VISUALIZZABILI SE SI E' GIA' LOGGATI) -->
				<?php if (!isLogged()) { ?>
					<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "login.php") {echo "active";}?>">
						<a title="<?php echo _('Login'); ?>" class="nav-link" href="/login.php"><?php echo _("Login"); ?></a>
					</li>

					<?php if($loginotp) { ?>
					<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "loginOTP.php") {echo "active";}?>">
						<a title="<?php echo _('Login sicuro (OTP)'); ?>" class="nav-link" href="/loginOTP.php"><?php echo _("Login sicuro (OTP)"); ?></a>
					</li>
					<?php }
				} ?>
				<!-- FINE PULSANTI LOGIN (NON VISUALIZZABILI SE SI E' GIA' LOGGATI) -->
				
				<!-- PULSANTI NETWORK E AMMMINISTRAZIONE (VISUALIZZABILI SOLO SE LOGGATI) -->
				<?php if (isLogged()) {
					if ($tipo == 1 && !$otp) { ?>
					<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "pannello.php") {echo "active";}?>">
						<a title="<?php echo _('Pannello'); ?>" class="nav-link" href="/Administration/pannello.php"><?php echo _("Pannello"); ?></a>
					</li>
					<?php } ?>
					
					<?php if ($infonetwork[0] == $id && !$otp) { ?>
					<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "networkPanel.php") {echo "active";}?>">
						<a title="<?php echo _('Gestione del Network'); ?>: <?php echo $infonetwork[2]; ?>" class="nav-link" href="/networkManagement/networkPanel.php"><?php echo _("Il tuo Network"); ?></a>
					</li>
					<?php }
				} ?>
				<!-- FINE PULSANTI NETWORK E AMMMINISTRAZIONE (VISUALIZZABILI SOLO SE LOGGATI) -->
				
				<!-- CPS TEST -->
				<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "cpstest.php") {echo "active";}?>">
					<a title="<?php echo _('CPS Test'); ?>" class="nav-link" href="/cpstest.php"><?php echo _("CPS Test"); ?></a>
				</li>
				<!-- FINE CPS TEST -->
				
				<!-- DOWNLOAD CLIENT -->
				<?php if($softwaredownload) { ?>
				<li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == "software.php") {echo "active";}?>">
					<a title="<?php echo _('Scarica Software'); ?>" class="nav-link" href="/Client/software.php"><?php echo _("Scarica Software"); ?></a>
				</li>
				<?php } ?>
				<!-- FINE DOWNLOAD CLIENT -->
			</ul>
			<!-- FINE TESTO NAVBAR A SINISTRA -->
			
			<?php if(!isLogged() && $payments_live) { ?>
				<button class="btn btn-sm btn-success navbar-btn" onClick="location.href='/buyLicense/buy.php'" title="<?php echo _('Compra una licenza'); ?>"><?php echo _("Compra una licenza"); ?></button>&nbsp;&nbsp;&nbsp;
			<?php } ?>
			
			<?php if(isset($licenza) && $licenza[7] == false && isLogged() && basename($_SERVER['PHP_SELF']) != "settings.php") { ?>
				<button class="btn btn-sm btn-warning navbar-btn" onClick="location.href='/settings.php'" title="<?php echo _('Rinnova la tua licenza'); ?>"><?php echo _("Rinnova la tua licenza"); ?></button>&nbsp;&nbsp;&nbsp;
			<?php } ?>
			
			<!-- TESTO NAVBAR A DESTRA -->
			<span class="navbar-text">
				<?php if(!$existing_translation_local) { ?>
					<a title="<?php echo _("Questa pagina non è stata ancora tradotta nella tua lingua."); ?>"><font color="lightcoral"><span class="oi oi-warning"></span></font></a>&nbsp;
				<?php } ?>
				<?php if (isLogged()) {
					if($otp) { 
						?><a title="<?php echo _('Questa sessione non contiene dati personali e solo 4 scansioni sono permesse') ?>"><font color='lightblue'><?php echo _("Login sicuro OTP in uso"); ?></font></a> 
						&nbsp;
					<?php } else { ?>
					
					<?php if($tipo == 2) { ?>
						<a title="<?php echo _('I dati raccolti da questa sessione saranno inviati a FireShield al fine di migliorare il servizio. Grazie per essere un nostro Partner!'); ?>"><font color='lightgreen'><?php echo _("Account Partner in uso"); ?></font></a>
						&nbsp;	
					<?php
					} ?>

					<?php if(!$otp) { ?>
						<span class="text-white"><span class="oi oi-person"></span><a href="/settings.php" title="<?php echo _('Impostazioni & Stato Licenza'); ?>">
						<?php echo _("Benvenuto");?>, <b><?php if($tipo == 0) { ?><font color="lightblue"><?php } elseif($tipo == 1) { ?><font color="gold"><?php } elseif($tipo == 2) { ?><font color="lightgreen"><?php } echo $username; ?></font></b>!</a></span>&nbsp;<?php } 
					} 
				} ?>

				<a style="color:lightgray;" title="<?php echo _('Questa è la versione attuale del'); echo " ".$solutionname; ?>"><?php echo $solutionver; ?></a>
				
				<?php if(isLogged() && !$otp) { ?>
					&nbsp;
					<a href="/ticket.php" title="<?php echo _('Clicca qui per accedere al servizio di ticket'); ?>"><span class="oi oi-chat"></span></a>
				<?php } 
				
				if(isLogged()) { ?>
					&nbsp;
					<a style="cursor: pointer;" onClick="confirmLogout();" title="<?php echo _('Logout'); ?>"><span class="oi oi-power-standby"></span></a>
				<?php } ?>
			</span>
			<!-- FINE TESTO NAVBAR A DESTRA -->
		</div>
		<!-- FINE TESTO NAVBAR -->
	</nav>
	<br><br>
</div>
<?php
// se una lingua diversa dall'italiano e inglese non ha un file di traduzione, allora controllo che esista almeno la traduzione inglese e la applico
if(!$existing_translation_local && $lang != "en_US" && $lang != "it_IT" && $existing_translation_english) {
	setlocale(LC_MESSAGES, "en_US");
}

bindtextdomain($domain, $locale);
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);
?>