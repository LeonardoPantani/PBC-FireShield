<?php
bindtextdomain("footer", $locale);
bind_textdomain_codeset("footer", 'UTF-8');
textdomain("footer");
?>
<br><br>
<!--<style>
#footer {
  position: absolute;
  bottom: 0;
  width: 100%;
  height: 5rem;
}
</style>-->
<footer id="footer" style="background-color: #343a40;">
  <div class="text-center py-3 text-white">
	<!-- <a rel="noopener" target="_blank" href="//www.dmca.com/Protection/Status.aspx?ID=f24d620b-b6a5-4cba-9864-e34514a7165f" title="DMCA.com Protection Status" class="dmca-badge"> <img src ="https://images.dmca.com/Badges/dmca-badge-w100-5x1-07.png?ID=f24d620b-b6a5-4cba-9864-e34514a7165f"  alt="DMCA.com Protection Status" /></a>  <script defer src="https://images.dmca.com/Badges/DMCABadgeHelper.min.js"> </script> | -->
	<a rel="noopener" class="copyrighted-badge" title="Copyrighted.com Registered &amp; Protected" target="_blank" href="https://www.copyrighted.com/website/f3TwFSSVPqYVUeLA"><img alt="Copyrighted.com Registered &amp; Protected" border="0" width="125" height="25" srcset="https://static.copyrighted.com/badges/125x25/01_2_2x.png 2x" src="https://static.copyrighted.com/badges/125x25/01_2.png" /></a><script src="https://static.copyrighted.com/badges/helper.js"></script> | Copyright 2018-<?php echo date("Y"); ?> <b>FireShield</b> | <a title="<?php echo _('Clicca per visualizzare i Termini di Servizio'); ?>" href="/Legal/tos.php"><?php echo _("Termini di Servizio"); ?></a>
	<br>
	<a href="/credits.php" title="<?php echo _("Clicca qui per vedere chi sta sviluppando"); echo " ".$solutionname; ?>"><?php echo _("Crediti"); ?></a>
  </div>
</footer>
<?php
bindtextdomain($domain, $locale);
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);

$connection -> close();
?>