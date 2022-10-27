<?php
/* Pagina creata da Leonardo Pantani, 5a/INF, Tutti i diritti riservati.
Da includere in tutte le pagine con connessione a database */
//Apertura connessione
$connection = @new mysqli($host, $usernamedb, $password, $dbname);

if ($connection->connect_error) {
	header("Location:/error.php");
} else {
	$connection->set_charset('utf8mb4');
}
?>