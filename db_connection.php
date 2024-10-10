<?php
// Definindo as variáveis de conexão
$host = "localhost";
$dbname = "vendas";
$username = "root";
$password = "";

try {
    // Criando uma conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se ocorrer um erro, ele será exibido aqui
    die("Erro na conexão: " . $e->getMessage());
}
?>
