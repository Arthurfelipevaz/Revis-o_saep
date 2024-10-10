<?php
// cadastro_contas_receber.php

// Inclui o arquivo de conexão ao banco de dados
require 'db_connection.php';

// Inicializa variáveis de mensagem
$mensagem = "";

// Inicializa variáveis para armazenar dados de busca
$busca_resultados = [];

// Função para sanitizar entradas
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'cadastrar') {
        // Cadastrar Conta a Receber
        $CON_numero = filter_input(INPUT_POST, 'CON_numero', FILTER_VALIDATE_INT);
        $CON_nome = sanitize_input($_POST['CON_nome']);
        $CON_valor = filter_input(INPUT_POST, 'CON_valor', FILTER_VALIDATE_FLOAT);
        $CON_vencimento = sanitize_input($_POST['CON_vencimento']);
        $CON_banco = sanitize_input($_POST['CON_banco']);

        // Validação
        if ($CON_numero === false || empty($CON_nome) || $CON_valor === false || empty($CON_vencimento) || empty($CON_banco)) {
            $mensagem = "Por favor, preencha todos os campos corretamente.";
        } else {
            // Insere a nova conta
            $stmt = $conn->prepare("INSERT INTO contas_receber (CON_numero, CON_nome, CON_valor, CON_vencimento, CON_banco) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isdss", $CON_numero, $CON_nome, $CON_valor, $CON_vencimento, $CON_banco);

                if ($stmt->execute()) {
                    $mensagem = "Conta a receber cadastrada com sucesso!";
                } else {
                    if ($conn->errno === 1062) { // Erro de duplicação de chave primária, se aplicável
                        $mensagem = "Erro: O número da conta já existe.";
                    } else {
                        $mensagem = "Erro ao cadastrar conta: " . $stmt->error;
                    }
                }

                $stmt->close();
            } else {
                $mensagem = "Erro na preparação da declaração: " . $conn->error;
            }
        }
    } elseif ($action === 'buscar') {
        // Buscar Conta a Receber
        $buscar_CON_numero = filter_input(INPUT_POST, 'buscar_CON_numero', FILTER_VALIDATE_INT);
        $buscar_CON_nome = sanitize_input($_POST['buscar_CON_nome']);

        if ($buscar_CON_numero === false && empty($buscar_CON_nome)) {
            $mensagem = "Por favor, insira um número de conta ou nome válido para buscar.";
        } else {
            if ($buscar_CON_numero !== false && !empty($buscar_CON_nome)) {
                // Buscar por número e nome
                $stmt = $conn->prepare("SELECT id, CON_numero, CON_nome, CON_valor, CON_vencimento, CON_banco FROM contas_receber WHERE CON_numero = ? AND CON_nome LIKE ?");
                $nome_like = "%" . $buscar_CON_nome . "%";
                $stmt->bind_param("is", $buscar_CON_numero, $nome_like);
            } elseif ($buscar_CON_numero !== false) {
                // Buscar apenas por número
                $stmt = $conn->prepare("SELECT id, CON_numero, CON_nome, CON_valor, CON_vencimento, CON_banco FROM contas_receber WHERE CON_numero = ?");
                $stmt->bind_param("i", $buscar_CON_numero);
            } else {
                // Buscar apenas por nome
                $stmt = $conn->prepare("SELECT id, CON_numero, CON_nome, CON_valor, CON_vencimento, CON_banco FROM contas_receber WHERE CON_nome LIKE ?");
                $nome_like = "%" . $buscar_CON_nome . "%";
                $stmt->bind_param("s", $nome_like);
            }

            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $busca_resultados[] = $row;
                    }
                    $mensagem = "Foram encontradas " . count($busca_resultados) . " conta(s) a receber.";
                } else {
                    $mensagem = "Nenhuma conta a receber encontrada com os critérios informados.";
                }

                $stmt->close();
            } else {
                $mensagem = "Erro na busca da conta: " . $conn->error;
            }
        }
    } elseif ($action === 'excluir') {
        // Excluir Conta a Receber
        $excluir_id = filter_input(INPUT_POST, 'excluir_id', FILTER_VALIDATE_INT);

        if ($excluir_id === false) {
            $mensagem = "Por favor, insira um ID válido para excluir.";
        } else {
            $stmt = $conn->prepare("DELETE FROM contas_receber WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $excluir_id);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $mensagem = "Conta a receber excluída com sucesso!";
                    } else {
                        $mensagem = "Nenhuma conta a receber encontrada com o ID informado.";
                    }
                } else {
                    $mensagem = "Erro ao excluir conta: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $mensagem = "Erro na preparação da declaração: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Contas a Receber</title>
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
            max-width: 1000px;
            width: 100%;
            overflow-y: auto;
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

        .form-section {
            margin-bottom: 40px;
        }

        .form-section h2 {
            margin-bottom: 15px;
            color: #333;
            font-size: 20px;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .form-section h2 i {
            margin-right: 8px;
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

        .buttons input[type="submit"], .buttons button {
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

        .buttons input[type="submit"]:hover, .buttons button:hover {
            background-color: #357ab8;
            transform: translateY(-2px);
        }

        /* Botão de Excluir com Cor Vermelha */
        .buttons .excluir-btn {
            background-color: #c62828;
        }

        .buttons .excluir-btn:hover {
            background-color: #8e0000;
            transform: translateY(-2px);
        }

        /* Tabela de Resultados */
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .result-table th {
            background-color: #f2f2f2;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-buttons form {
            margin: 0;
        }

        /* Responsividade */
        @media (max-width: 800px) {
            .container {
                padding: 20px 25px;
            }

            h1 {
                font-size: 24px;
            }

            .form-section h2 {
                font-size: 18px;
            }

            .buttons input[type="submit"], .buttons button {
                font-size: 14px;
                padding: 10px 16px;
            }

            .result-table th, .result-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gerenciamento de Contas a Receber</h1>
        
        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo strpos($mensagem, 'sucesso') !== false ? 'success' : 'error'; ?>">
                <i class="<?php echo strpos($mensagem, 'sucesso') !== false ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Seção de Cadastro -->
        <div class="form-section">
            <h2><i class="fas fa-plus-circle"></i> Cadastrar Conta a Receber</h2>
            <form method="POST" action="cadastro_contas_receber.php">
                <input type="hidden" name="action" value="cadastrar">
                
                <label for="CON_numero">Número da Conta</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="CON_numero" name="CON_numero" placeholder="Ex: 100100" required>
                </div>

                <label for="CON_nome">Nome</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="CON_nome" name="CON_nome" placeholder="Ex: ABC PAPELARIA" required>
                </div>

                <label for="CON_valor">Valor (R$)</label>
                <div class="input-group">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="number" step="0.01" id="CON_valor" name="CON_valor" placeholder="Ex: 5000.00" required>
                </div>

                <label for="CON_vencimento">Vencimento</label>
                <div class="input-group">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" id="CON_vencimento" name="CON_vencimento" required>
                </div>

                <label for="CON_banco">Banco</label>
                <div class="input-group">
                    <i class="fas fa-university"></i>
                    <input type="text" id="CON_banco" name="CON_banco" placeholder="Ex: ITAU" required>
                </div>

                <div class="buttons">
                    <input type="submit" value="Cadastrar Conta">
                </div>
            </form>
        </div>

        <!-- Seção de Busca -->
        <div class="form-section">
            <h2><i class="fas fa-search"></i> Buscar Contas a Receber</h2>
            <form method="POST" action="cadastro_contas_receber.php">
                <input type="hidden" name="action" value="buscar">
                
                <label for="buscar_CON_numero">Número da Conta</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="buscar_CON_numero" name="buscar_CON_numero" placeholder="Ex: 100100">
                </div>

                <label for="buscar_CON_nome">Nome</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="buscar_CON_nome" name="buscar_CON_nome" placeholder="Ex: ABC PAPELARIA">
                </div>

                <div class="buttons">
                    <input type="submit" value="Buscar Conta">
                </div>
            </form>

            <!-- Resultados da Busca -->
            <?php if (!empty($busca_resultados)): ?>
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número da Conta</th>
                            <th>Nome</th>
                            <th>Valor (R$)</th>
                            <th>Vencimento</th>
                            <th>Banco</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($busca_resultados as $conta): ?>
                            <tr>
                                <td><?php echo $conta['id']; ?></td>
                                <td><?php echo $conta['CON_numero']; ?></td>
                                <td><?php echo htmlspecialchars($conta['CON_nome']); ?></td>
                                <td><?php echo number_format($conta['CON_valor'], 2, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($conta['CON_vencimento'])); ?></td>
                                <td><?php echo htmlspecialchars($conta['CON_banco']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Formulário para Alterar -->
                                        <form method="GET" action="alterar_conta_receber.php">
                                            <input type="hidden" name="id" value="<?php echo $conta['id']; ?>">
                                            <button type="submit" title="Alterar" style="background: none; border: none; cursor: pointer; color: #4a90e2;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </form>
                                        <!-- Formulário para Excluir -->
                                        <form method="POST" action="cadastro_contas_receber.php" onsubmit="return confirm('Tem certeza que deseja excluir esta conta?');">
                                            <input type="hidden" name="action" value="excluir">
                                            <input type="hidden" name="excluir_id" value="<?php echo $conta['id']; ?>">
                                            <button type="submit" title="Excluir" style="background: none; border: none; cursor: pointer; color: #c62828;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
