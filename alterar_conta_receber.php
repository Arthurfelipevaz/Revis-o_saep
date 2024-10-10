<?php
// alterar_conta_receber.php

// Inclui o arquivo de conexão ao banco de dados
require 'db_connection.php';

// Inicializa variáveis de mensagem e dados da conta
$mensagem = "";
$conta = null;

// Verifica se o número da conta foi passado via GET
if (isset($_GET['CON_numero'])) {
    $alterar_CON_numero = filter_input(INPUT_GET, 'CON_numero', FILTER_VALIDATE_INT);

    if ($alterar_CON_numero === false) {
        $mensagem = "Número da conta inválido.";
    } else {
        // Busca a conta pelo número
        $stmt = $conn->prepare("SELECT CON_numero, CON_nome, CON_valor, CON_vencimento, CON_banco FROM contas_receber WHERE CON_numero = ?");
        if ($stmt) {
            $stmt->bind_param("i", $alterar_CON_numero);
            $stmt->execute();
            $stmt->bind_result($CON_numero, $CON_nome, $CON_valor, $CON_vencimento, $CON_banco);

            if ($stmt->fetch()) {
                $conta = [
                    'CON_numero' => $CON_numero,
                    'CON_nome' => $CON_nome,
                    'CON_valor' => $CON_valor,
                    'CON_vencimento' => $CON_vencimento,
                    'CON_banco' => $CON_banco
                ];
            } else {
                $mensagem = "Conta a receber não encontrada.";
            }

            $stmt->close();
        } else {
            $mensagem = "Erro na busca da conta: " . $conn->error;
        }
    }
}

// Verifica se o formulário de alteração foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'alterar') {
    $alterar_CON_numero = filter_input(INPUT_POST, 'alterar_CON_numero', FILTER_VALIDATE_INT);
    $alterar_CON_nome = sanitize_input($_POST['alterar_CON_nome']);
    $alterar_CON_valor = filter_input(INPUT_POST, 'alterar_CON_valor', FILTER_VALIDATE_FLOAT);
    $alterar_CON_vencimento = sanitize_input($_POST['alterar_CON_vencimento']);
    $alterar_CON_banco = sanitize_input($_POST['alterar_CON_banco']);

    if ($alterar_CON_numero === false || empty($alterar_CON_nome) || $alterar_CON_valor === false || empty($alterar_CON_vencimento) || empty($alterar_CON_banco)) {
        $mensagem = "Por favor, preencha todos os campos corretamente para alterar.";
    } else {
        // Atualiza a conta pelo número
        $stmt = $conn->prepare("UPDATE contas_receber SET CON_nome = ?, CON_valor = ?, CON_vencimento = ?, CON_banco = ? WHERE CON_numero = ?");
        if ($stmt) {
            $stmt->bind_param("sds si", $alterar_CON_nome, $alterar_CON_valor, $alterar_CON_vencimento, $alterar_CON_banco, $alterar_CON_numero);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $mensagem = "Conta a receber alterada com sucesso!";
                    // Atualiza os dados da conta exibida
                    $conta['CON_nome'] = $alterar_CON_nome;
                    $conta['CON_valor'] = $alterar_CON_valor;
                    $conta['CON_vencimento'] = $alterar_CON_vencimento;
                    $conta['CON_banco'] = $alterar_CON_banco;
                } else {
                    $mensagem = "Nenhuma alteração realizada. Verifique se o número da conta está correto.";
                }
            } else {
                $mensagem = "Erro ao alterar conta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $mensagem = "Erro na preparação da declaração: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Conta a Receber</title>
    <!-- Importa Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Importa Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/kTcXv3dE8QZcVRuKxV8LVb3GQYtHcKbZ5xByfOWcYxV3h3+E5z9eZ7K/YvKSlX6gFkAErKk9DxQGg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Reset CSS */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #4a90e2;
            font-weight: 700;
        }

        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .message.success {
            background-color: #e6ffed;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }

        .message.error {
            background-color: #ffe6e6;
            color: #c62828;
            border: 1px solid #c62828;
        }

        .message i {
            margin-right: 10px;
            font-size: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: 500;
            margin-top: 15px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #aaa;
        }

        input[type="number"], input[type="text"], input[type="date"] {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus, input[type="text"]:focus, input[type="date"]:focus {
            border-color: #4a90e2;
            outline: none;
        }

        .buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .buttons input[type="submit"] {
            background-color: #4a90e2;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s;
            margin-left: 10px;
        }

        .buttons input[type="submit"]:hover {
            background-color: #357ab8;
            transform: translateY(-2px);
        }

        /* Responsividade */
        @media (max-width: 600px) {
            .container {
                padding: 20px 25px;
            }

            h1 {
                font-size: 24px;
            }

            .buttons input[type="submit"] {
                font-size: 14px;
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Alterar Conta a Receber</h1>
        
        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo strpos($mensagem, 'sucesso') !== false ? 'success' : 'error'; ?>">
                <i class="<?php echo strpos($mensagem, 'sucesso') !== false ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if ($conta): ?>
            <form method="POST" action="alterar_conta_receber.php">
                <input type="hidden" name="action" value="alterar">
                <input type="hidden" name="alterar_CON_numero" value="<?php echo $conta['CON_numero']; ?>">
                
                <label for="CON_numero">Número da Conta</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="CON_numero" name="CON_numero" value="<?php echo $conta['CON_numero']; ?>" disabled>
                </div>

                <label for="CON_nome">Nome</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="CON_nome" name="CON_nome" value="<?php echo htmlspecialchars($conta['CON_nome']); ?>" required>
                </div>

                <label for="CON_valor">Valor (R$)</label>
                <div class="input-group">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="number" step="0.01" id="CON_valor" name="CON_valor" value="<?php echo number_format($conta['CON_valor'], 2, '.', ''); ?>" required>
                </div>

                <label for="CON_vencimento">Vencimento</label>
                <div class="input-group">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" id="CON_vencimento" name="CON_vencimento" value="<?php echo $conta['CON_vencimento']; ?>" required>
                </div>

                <label for="CON_banco">Banco</label>
                <div class="input-group">
                    <i class="fas fa-university"></i>
                    <input type="text" id="CON_banco" name="CON_banco" value="<?php echo htmlspecialchars($conta['CON_banco']); ?>" required>
                </div>

                <div class="buttons">
                    <input type="submit" value="Alterar Conta">
                </div>
            </form>
        <?php else: ?>
            <?php if (empty($mensagem)): ?>
                <p>Nenhuma conta selecionada para alterar.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
