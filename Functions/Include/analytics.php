<?php
if (!getDNT()) {
    ?>
	<!-- Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-<?php echo $trackingid; ?>"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'UA-<?php echo $trackingid; ?>');
	</script>
	<?php
} else {
    ?><!-- Google Analytics disabled as requested by the Browser. --><?php
}
?>