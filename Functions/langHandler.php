<?php
	if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$language_detect = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2); //Ottiene la lingua dal browser
	} else {
		$language_detect = "en";
	}
	
	if(isset($_SESSION["lang"])) {
		$lang = $_SESSION["lang"];
	} else {
		if($language_detect == "en") { $lang = "en_US"; $_SESSION["lang"] = "en_US";  } // inglese (Stati Uniti)

		elseif($language_detect == "es") { $lang = "es_ES"; $_SESSION["lang"] = "es_ES";  } // spagnolo

		elseif($language_detect == "fr") { $lang = "fr_FR"; $_SESSION["lang"] = "fr_FR";  } // francese

		elseif($language_detect == "de") { $lang = "de_DE"; $_SESSION["lang"] = "de_DE"; } // tedesco
		
		elseif($language_detect == "it") { $lang = "it_IT"; $_SESSION["lang"] = "it_IT"; } // italiano
	}
	
	if($lang == "en_US") {
		$lang_complete = "English";
	} elseif($lang == "es_ES") {
		$lang_complete = "Español";
	} elseif($lang == "fr_FR") {
		$lang_complete = "Français";
	} elseif($lang == "de_DE") {
		$lang_complete = "Deutsch";
	} elseif($lang == "it_IT") {
		$lang_complete = "Italiano";
	} else {
		$lang_complete = "English";
		$lang = "en_US";
	}

	// setlocale non funziona su windows
	if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
		setlocale(LC_MESSAGES, $lang);
	}
	
	$path=$_SERVER['PHP_SELF'];
	$filename = basename($path);
	$domain = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
	$numSlash=substr_count($path,"/");
	$agg="../";
	if(strpos($path, "Administration") !== false) {
		$locale="Administration/Translations";
	} else {
		$locale="Translations";
	}
	for ($i=1; $i < $numSlash; $i++) {
		$locale=$agg.$locale;
	}
	
	if($lang != "it_IT") {
		$existing_translation_local = file_exists($locale."/".$lang."/LC_MESSAGES/".$domain.".mo");
		if($lang != "en_US") {
			$existing_translation_english = file_exists($locale."/en_US/LC_MESSAGES/".$domain.".mo");
		}
	} else {
		$existing_translation_local = true;
	}
	
	bindtextdomain($domain, $locale);
	bind_textdomain_codeset($domain, 'UTF-8');
	textdomain($domain);
?>
