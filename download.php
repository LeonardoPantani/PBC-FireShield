<?php
if(isset($_GET["tipofile"])) {
	header("Location:Client/download.php?tipofile=".$_GET["tipofile"]);
} else {
	header("Location:Client/software.php");
}
?>