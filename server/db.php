<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'ube_delights_db';

$connect = mysqli_connect($host, $user, $pass, $db);

if(!$connect){
    die(mysqli_connect_error());
}
?>
