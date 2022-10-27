<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
session_name('__Secure-FSCDSESSION');
session_start();

if (isset($_GET["l"])) {
    $lingua = $_GET["l"];
	if(in_array($lingua, $language_list)) {
		$_SESSION["lang"] = $lingua;
	} else {
        $_SESSION["lang"] = "en_US";
    }
	$lang = $_SESSION["lang"];
    if(isset($_SERVER["HTTP_REFERER"])) {
        $previouspage = $_SERVER['HTTP_REFERER'];
    } else {
        $previouspage = "/";
    }
    header("Location:$previouspage");
} else {
    header("Location:../");
}
