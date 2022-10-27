<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Funzioni/dbConnection.php";
include "$droot/Funzioni/funzioni.php";
include "$droot/Funzioni/mobile.php";

$emailutente = "fireshieldcheatdetector@gmail.com";
$nomeutente = "AdminFireShield";
$pass = randomPassword();


$passhash = password_hash($pass, PASSWORD_DEFAULT);
$query = "INSERT INTO $table_users (Email, Username, Password, Tipo, Abilitato) VALUES ('$emailutente', '$nomeutente', '$passhash', 1, 1)";
$query = $connection->query($query);
if (!$query) {
    echo "<p style='color:red'>Impossibile aggiungere utente nella tabella $table_users, motivo: " . $connection->error . "</p>";
} else {
    echo "<p style='color:green'>Utente aggiunto alla tabella $table_users!<br><br>Username: <b>$nomeutente</b><br>Password: <b>$pass</b><br></p>";
}

function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 15; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
