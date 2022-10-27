<?php /* Copyright FireShield. All rights reserved. */
	$solutionname = "FireShield Cheat Detector";
	$solutionname_short = "FireShield";
	$solutionver = "Release 1.11.0";
	$pesomax = 1000000000; // 1gb
	$signup = true; // registrazioni abilitate?
	$login = true; // login abilitati?
	$loginotp = true; // login OTP abilitati?
	$analysis = true; // scansioni abilitate?
	$tickets = false; // tickets abilitati?
	$loginrequired = false; // login necessario per effettuare scansioni?
	$payments_live = false; // debug pagamenti abilitato (false -> sandbox, true -> live)?
	$ph_check = true; // verifica file process hacker abilitata?
	$securitycheck = false; // check sicurezza csrf abilitato?
	$twofactor = false; // login a due fattori abilitato?
	$softwaredownload = false; // download fscd client permesso?
	$stringsuggestions = true; // proposte per stringhe permesse?
	$applications = false; // candidature aperte?
	$daysbeforedelete = 14; // giorni prima dell'eliminazione dei file history?
	$cpstestduration = 10.0; // durata in secondi del test cps?
	$keepscanfiles = true; // i file delle scansioni history vanno mantenuti?
	$recaptcha_check = true; // recaptcha attivo?
	$additional_admin_check = false; // gli admin devono avere un controllo aggiuntivo per accedere al pannello?
	$daysbeforedeletedatarequests = 14; // giorni prima dell'eliminazione delle richieste di download dei propri dati?
	$otpsessionmaxduration = 30; // durata massima in minuti di una sessione OTP
	$dayscooldownchangeusername = 31; // giorni di tempo tra un cambio di username e l'altro?
	$dayscooldownstaffapplication = 14; // giorni di tempo tra una candidatura e l'altra?
	$maxcharactersapplication = 2000; // caratteri massimi per ogni domanda della candidatura
	
	$whitelisted_ips = []; // lista ip ammessi durante manutenzione ($analysis = true)
	
	$language_list_short = ["en", "it", "fr"];
	$language_list = ["en_US", "it_IT", "fr_FR"];
	$language_list_complete = ["English", "Italiano", "Français"];
	
	$redirect_pages_link = ["/", "settings.php", "cpstest.php"]; // 0 homepage, 1 settings, 2 cpstest
	$redirect_pages = ["HomePage", "Settings", "CPS Test"]; // 0 homepage, 1 settings, 2 cpstest
	
	$interval_list = ["month", "year"]; // lista intervalli
	
	$scans_summary_numbers = [10, 20, 30, 40, 50]; // numero di scansioni visualizzate nel riepilogo
	
	$trackingid = "REDACTED"; // tracking id analytics?
	
	$recaptcha_sitekey = "REDACTED";
	$recaptcha_secret = "REDACTED";
	
	$recaptcha_sitekey2 = "REDACTED";
	$recaptcha_secret2 = "REDACTED";
	
	$recaptcha_sitekey3 = "REDACTED";
	$recaptcha_secret3 = "REDACTED";
	
	$recaptcha_sitekey4 = "REDACTED";
	$recaptcha_secret4 = "REDACTED";
	
	$string_check_allowed = "^[a-zA-Z0-9_]+$"; // caratteri ammessi username, nome network...		
	$string_check_not_allowed = "^[\W]+^"; // caratteri NON ammessi
	$string_check_banned_1 = [];
	$string_check_banned_2 = [];
	
	$feedbackmail = "feedback@fireshield.it";
	$infomail = "info@fireshield.it";
	$issuesmail = "issues@fireshield.it";
	$paymentsmail = "payments@fireshield.it";
	$genericmail = "fireshieldcheatdetector@gmail.com";
	
	$username_minlength = 5; //specificare la lunghezza minima dell'username	
	$username_maxlength = 20; //specificare la lunghezza massima dell'username
	$password_minlength = 5; //specificare la lunghezza minima della password
	$password_maxlength = 30; //specificare la lunghezza massima della password
	
	$mainnews_condition = "not_logged"; //specificare se la news deve apparire a tutti (always), solo a chi non è loggato (not_logged) o solo a chi è loggato (logged)
	$mainnews_title = ""; //specificare il titolo della news in inglese	
	$mainnews_body = ""; //specificare il testo della news in inglese
	
	
	$error_message_title = ""; //specificare il titolo del problema in inglese [IN MAIUSCOLO]	
	$error_message_object = ""; //specificare l'oggetto del problema in inglese (tipo: database, strings, login...)
	$error_message_color = "danger"; //specificare il colore dell'avviso (può essere: danger, warning, info)
	// ---
	$error_message_custom = ""; // specificare un body personalizzato per il messaggio di errore in inglese
	$data_error = "";
	$link_error = "";

	$controllo1 = true; //java (l'esito appare nella 1a sezione)
	$controllo2 = true; //dwm (l'esito appare nella 2a sezione)
	$controllo3 = true; //msmpeng (l'esito appare nella 3a sezione)
	$controllo4 = true; //lsass (l'esito appare nella 4a sezione)
	$controllo5 = true; //sospette java (l'esito appare nella 1a sezione)
	$controllo6 = true; //sospette dwm (l'esito appare nella 2a sezione)
	$controllo7 = true; //sospette msmpeng (l'esito appare nella 3a sezione)
	$controllo8 = true; //sospette lsass (l'esito appare nella 4a sezione)
	
	// I PREZZI SONO RELATIVI SOLO ALLA VISUALIZZAZIONE SUL SITO
	$price_single_monthly_license = "0,00"; //prezzo licenza singola mensile?
	$price_single_permanent_license = "0,00"; //prezzo licenza singola permanente?
	
	// ------- NON TOCCARE ---------
	
	//Database
	$host = "REDACTED";
	$usernamedb = "REDACTED";
	$password = "REDACTED";
	$dbname = "REDACTED";
	
	//Cartelle
	$folderimport = "Files/Import"; //Dove il pannello cercherà i file di import
	$folderexport = "Files/Export"; //Dove il pannello metterà i file di export
	$folderanalysis = "Files/Analysis"; //Dove andranno tutti i file in fase di scansione
	$folderhistory = "Files/History"; //Dove andranno tutti i file di cronologia scansione
	$folderconfigbackup = "Backups"; //Dove andranno i config dopo la modifica (nella cartella 'Config')
	$foldernetworklogs = "Logs"; //Dove andranno i file di log network (nella cartella 'gestioneNetwork')
	$folderdatarequests = "Files/DataRequests"; //Dove andranno i file delle richieste di ottenimento dati

	//Tabelle database
	$table_users = "Users";
	$table_payments = "Pagamenti";
	$table_licenses = "Licenze";
	$table_permissions = "Permessi";
	$table_networks = "Networks";
	$table_tickets = "Tickets";
	$table_news = "News";
	$table_preferences = "Preferenze";
	$table_cpstest = "CPSTest";
	$table_otpsessions = "SessioniOTP";
	$table_datarequests = "RichiesteDati";
	$table_usernamechange = "CambiUsername";
	$table_suggestions = "ProposteStringhe";
	$table_applications = "Candidature";
	$table_2fa = "2FA";

	$table_historycheat = "HistoryCheat";
	$table_historyanalyzer = "HistoryAnalyzer";

	$table_cheatjava = "StringheCheatJAVA"; //1
	$table_cheatdwm = "StringheCheatDWM"; //2
	$table_cheatmsmpeng = "StringheCheatMSMPENG"; //3
	$table_cheatlsass = "StringheCheatLSASS"; //4
	
	$table_suspectjava = "StringheSospetteJAVA"; //5
	$table_suspectdwm = "StringheSospetteDWM"; //6
	$table_suspectmsmpeng = "StringheSospetteMSMPENG"; //7
	$table_suspectlsass = "StringheSospetteLSASS"; //8
?>