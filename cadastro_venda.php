<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nf_numero = $_POST['nf_numero'];
    $nf_valortotal = $_POST['nf_valortotal'];
    $nf_icms = $_POST['nf_icms'];

    try {
        // Preparando a query de inserção
        $stmt = $pdo->prepare("INSERT INTO Venda (nf_numero, nf_valortotal, nf_icms) VALUES (:nf_numero, :nf_valortotal, :nf_icms)");
        
        // Bind dos parâmetros
        $stmt->bindParam(':nf_numero', $nf_numero);
        $stmt->bindParam(':nf_valortotal', $nf_valortotal);
        $stmt->bindParam(':nf_icms', $nf_icms);

        // Executando a query
        $stmt->execute();

        echo "Venda cadastrada com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao cadastrar venda: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Venda</title>
</head>
<body>
    <h1>Cadastro de Venda</h1>
    <form method="POST" action="cadastro_venda.php">
        <label for="nf_numero">Número da NF:</label><br>
        <input type="number" id="nf_numero" name="nf_numero" required><br><br>

        <label for="nf_valortotal">Valor Total:</label><br>
        <input type="number" step="0.01" id="nf_valortotal" name="nf_valortotal" required><br><br>

        <label for="nf_icms">ICMS:</label><br>
        <input type="number" step="0.01" id="nf_icms" name="nf_icms" required><br><br>

        <input type="submit" value="Cadastrar Venda">
    </form>
</body>
</html>
