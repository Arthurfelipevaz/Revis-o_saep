<?php
// cadastro_venda.php

// Inclui o arquivo de conexão ao banco de dados
require 'db_connection.php';

// Inicializa variáveis de mensagem
$mensagem = "";

// Função para sanitizar entradas
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'cadastrar') {
        // Cadastrar Venda
        $nf_numero = filter_input(INPUT_POST, 'nf_numero', FILTER_VALIDATE_INT);
        $nf_valortotal = filter_input(INPUT_POST, 'nf_valortotal', FILTER_VALIDATE_FLOAT);
        $nf_icms = filter_input(INPUT_POST, 'nf_icms', FILTER_VALIDATE_FLOAT);

        // Validação
        if ($nf_numero === false || $nf_valortotal === false || $nf_icms === false) {
            $mensagem = "Por favor, preencha todos os campos corretamente.";
        } else {
            // Verifica se nf_numero já existe
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM Venda WHERE nf_numero = ?");
            if ($checkStmt) {
                $checkStmt->bind_param("i", $nf_numero);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                if ($count > 0) {
                    $mensagem = "O número da NF já existe. Por favor, insira um número único.";
                } else {
                    // Insere a nova venda
                    $stmt = $conn->prepare("INSERT INTO Venda (nf_numero, nf_valortotal, nf_icms) VALUES (?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("idd", $nf_numero, $nf_valortotal, $nf_icms);

                        if ($stmt->execute()) {
                            $mensagem = "Venda cadastrada com sucesso!";
                        } else {
                            if ($conn->errno === 1062) {
                                $mensagem = "Erro: O número da NF já existe.";
                            } else {
                                $mensagem = "Erro ao cadastrar venda: " . $stmt->error;
                            }
                        }

                        $stmt->close();
                    } else {
                        $mensagem = "Erro na preparação da declaração: " . $conn->error;
                    }
                }
            } else {
                $mensagem = "Erro na verificação do número da NF: " . $conn->error;
            }
        }
    } elseif ($action === 'buscar') {
        // Buscar Venda
        $buscar_nf_numero = filter_input(INPUT_POST, 'buscar_nf_numero', FILTER_VALIDATE_INT);

        if ($buscar_nf_numero === false) {
            $mensagem = "Por favor, insira um número de NF válido para buscar.";
        } else {
            $stmt = $conn->prepare("SELECT nf_numero, nf_valortotal, nf_icms FROM Venda WHERE nf_numero = ?");
            if ($stmt) {
                $stmt->bind_param("i", $buscar_nf_numero);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($nf_numero, $nf_valortotal, $nf_icms);
                    $stmt->fetch();
                    $mensagem = "Venda encontrada: NF Número: $nf_numero, Valor Total: R$ " . number_format($nf_valortotal, 2, ',', '.') . ", ICMS: " . number_format($nf_icms, 2, ',', '.') . "%.";
                } else {
                    $mensagem = "Nenhuma venda encontrada com o número da NF informado.";
                }

                $stmt->close();
            } else {
                $mensagem = "Erro na busca da venda: " . $conn->error;
            }
        }
    } elseif ($action === 'alterar') {
        // Alterar Venda
        $alterar_nf_numero = filter_input(INPUT_POST, 'alterar_nf_numero', FILTER_VALIDATE_INT);
        $alterar_nf_valortotal = filter_input(INPUT_POST, 'alterar_nf_valortotal', FILTER_VALIDATE_FLOAT);
        $alterar_nf_icms = filter_input(INPUT_POST, 'alterar_nf_icms', FILTER_VALIDATE_FLOAT);

        if ($alterar_nf_numero === false || $alterar_nf_valortotal === false || $alterar_nf_icms === false) {
            $mensagem = "Por favor, preencha todos os campos corretamente para alterar.";
        } else {
            // Atualiza a venda
            $stmt = $conn->prepare("UPDATE Venda SET nf_valortotal = ?, nf_icms = ? WHERE nf_numero = ?");
            if ($stmt) {
                $stmt->bind_param("ddi", $alterar_nf_valortotal, $alterar_nf_icms, $alterar_nf_numero);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $mensagem = "Venda alterada com sucesso!";
                    } else {
                        $mensagem = "Nenhuma alteração foi realizada. Verifique se o número da NF está correto.";
                    }
                } else {
                    $mensagem = "Erro ao alterar venda: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $mensagem = "Erro na preparação da declaração: " . $conn->error;
            }
        }
    } elseif ($action === 'excluir') {
        // Excluir Venda
        $excluir_nf_numero = filter_input(INPUT_POST, 'excluir_nf_numero', FILTER_VALIDATE_INT);

        if ($excluir_nf_numero === false) {
            $mensagem = "Por favor, insira um número de NF válido para excluir.";
        } else {
            $stmt = $conn->prepare("DELETE FROM Venda WHERE nf_numero = ?");
            if ($stmt) {
                $stmt->bind_param("i", $excluir_nf_numero);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $mensagem = "Venda excluída com sucesso!";
                    } else {
                        $mensagem = "Nenhuma venda encontrada com o número da NF informado.";
                    }
                } else {
                    $mensagem = "Erro ao excluir venda: " . $stmt->error;
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
    <title>Cadastro de Venda</title>
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
            max-width: 700px;
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

        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus, input[type="text"]:focus {
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

            .buttons input[type="submit"] {
                font-size: 14px;
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gerenciamento de Vendas</h1>
        
        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo strpos($mensagem, 'Erro') !== false ? 'error' : 'success'; ?>">
                <i class="<?php echo strpos($mensagem, 'Erro') !== false ? 'fas fa-exclamation-circle' : 'fas fa-check-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Seção de Cadastro -->
        <div class="form-section">
            <h2><i class="fas fa-plus-circle"></i> Cadastrar Venda</h2>
            <form method="POST" action="cadastro_venda.php">
                <input type="hidden" name="action" value="cadastrar">
                
                <label for="nf_numero">Número da NF</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="nf_numero" name="nf_numero" placeholder="Ex: 12345" required>
                </div>

                <label for="nf_valortotal">Valor Total (R$)</label>
                <div class="input-group">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="number" step="0.01" id="nf_valortotal" name="nf_valortotal" placeholder="Ex: 1500.00" required>
                </div>
                <label for="nf_icms">ICMS (%)</label>
                <div class="input-group">
                    <i class="fas fa-percentage"></i>
                    <input type="number" step="0.01" id="nf_icms" name="nf_icms" placeholder="Ex: 18.00" required>
                </div>

                <div class="buttons">
                    <input type="submit" value="Cadastrar Venda">
                </div>
            </form>
        </div>
        <div class="form-section">
            <h2><i class="fas fa-search"></i> Buscar Venda</h2>
            <form method="POST" action="cadastro_venda.php">
                <input type="hidden" name="action" value="buscar">
                
                <label for="buscar_nf_numero">Número da NF</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="buscar_nf_numero" name="buscar_nf_numero" placeholder="Ex: 12345" required>
                </div>

                <div class="buttons">
                    <input type="submit" value="Buscar Venda">
                </div>
            </form>
        </div>

        <!-- Seção de Alterar -->
        <div class="form-section">
            <h2><i class="fas fa-edit"></i> Alterar Venda</h2>
            <form method="POST" action="cadastro_venda.php">
                <input type="hidden" name="action" value="alterar">
                
                <label for="alterar_nf_numero">Número da NF</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="alterar_nf_numero" name="alterar_nf_numero" placeholder="Ex: 12345" required>
                </div>

                <label for="alterar_nf_valortotal">Valor Total (R$)</label>
                <div class="input-group">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="number" step="0.01" id="alterar_nf_valortotal" name="alterar_nf_valortotal" placeholder="Ex: 1500.00" required>
                </div>

                <label for="alterar_nf_icms">ICMS (%)</label>
                <div class="input-group">
                    <i class="fas fa-percentage"></i>
                    <input type="number" step="0.01" id="alterar_nf_icms" name="alterar_nf_icms" placeholder="Ex: 18.00" required>
                </div>

                <div class="buttons">
                    <input type="submit" value="Alterar Venda">
                </div>
            </form>
        </div>

        <!-- Seção de Exclusão -->
        <div class="form-section">
            <h2><i class="fas fa-trash-alt"></i> Excluir Venda</h2>
            <form method="POST" action="cadastro_venda.php" onsubmit="return confirm('Tem certeza que deseja excluir esta venda?');">
                <input type="hidden" name="action" value="excluir">
                
                <label for="excluir_nf_numero">Número da NF</label>
                <div class="input-group">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" id="excluir_nf_numero" name="excluir_nf_numero" placeholder="Ex: 12345" required>
                </div>

                <div class="buttons">
                    <input type="submit" value="Excluir Venda" style="background-color: #c62828;">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
