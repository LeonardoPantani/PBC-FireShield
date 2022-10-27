<?php
bindtextdomain("noscript", $locale);
bind_textdomain_codeset("noscript", 'UTF-8');
textdomain("noscript");
?>
<noscript>
    <div class="container text-center text-white div_visible_important div_noscript">
        <div class="p-2 mb-2 bg-dark text-white rounded div_visible_important">
			<h2><?php echo $solutionname; ?> (<?php echo $solutionver; ?>)</h2>
			<p style="color:red;"><?php echo _("Questo sito web ha bisogno di JavaScript per poter funzionare correttamente. Abilitalo o cambia browser per continuare."); ?><br>
			<?php echo _("Se non sai come abilitarlo clicca su"); ?> <a href="https://www.enable-javascript.com/<?php echo substr($lang, 0, 2); ?>" target="_blank"><?php echo _("questo link"); ?></a>.</p>
			
			<?php foreach($language_list as $chiave => $valore) {
				if($valore != $lang) {
					?><a title="<?php echo _('Clicca qui per cambiare la lingua in').' '.$language_list_complete[$chiave]; ?>" href="/Translations/setLanguage.php?l=<?php echo $valore; ?>"><?php echo $language_list_complete[$chiave]; ?></a>&nbsp;&nbsp;&nbsp;<?php
				} else {
					echo $language_list_complete[$chiave]."&nbsp;&nbsp;&nbsp;";
				}
			} ?>
		</div>
	</div>
    <style>div { display:none; } h2 { color:white; } .div_visible_important { display:block; } .div_noscript { margin-top:100px; }</style>
</noscript>
<?php
bindtextdomain($domain, $locale);
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);
?>