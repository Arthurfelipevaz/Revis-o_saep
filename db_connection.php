<?php
$severname = "localhost";
$database = "vendas";
$username = "root";
$password = "";

$conn = mysqli_connect($severname, $username, $password, $database);
//O Arthur e gay
if (!$conn) {
    die("Conexão Falhou:" . mysqli_connect_error());
}

?>