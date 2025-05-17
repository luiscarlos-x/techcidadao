<?php
session_start();
require_once __DIR__ . '/../conexao.php';

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    if (!$nome || !$email || !$senha || !$senha2) {
        $mensagem = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $mensagem = 'E-mail inválido. Informe um e-mail no formato nome@dominio.com.';
    } elseif ($senha !== $senha2) {
        $mensagem = 'As senhas não coincidem.';
    } else {
        // Primeiro, verificar se o email já existe
        $check_stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
        $check_stmt->bind_param('s', $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $mensagem = 'E-mail já cadastrado. Por favor, utilize outro e-mail.';
            $check_stmt->close();
        } else {
            $check_stmt->close();
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nome, $email, $hash);
            
            try {
                if ($stmt->execute()) {
                    // Obter o ID do usuário recém-cadastrado
                    $novo_usuario_id = $conn->insert_id;
                    
                    // Definir variáveis de sessão para login automático
                    $_SESSION['usuario_id'] = $novo_usuario_id;
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['usuario_email'] = $email;
                    
                    // Definir variáveis para a mensagem de boas-vindas
                    $_SESSION['registro_sucesso'] = true;
                    $_SESSION['nome_usuario'] = $nome;
                    
                    header('Location: /tech01/main/index.php');
                    exit;
                } else {
                    $mensagem = 'Erro ao cadastrar. Tente novamente mais tarde.';
                }
            } catch (mysqli_sql_exception $e) {
                // Captura erros de SQL, incluindo duplicação de email
                $mensagem = 'E-mail já cadastrado. Por favor, utilize outro e-mail.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - TechCidadão</title>
     <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="/tech01/main/css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body style="background: var(--light-bg); min-height: 100vh;">
    <div class="register-container">
        <h2 class="register-title">Cadastro de Usuário</h2>
        <?php if ($mensagem): ?>
            <div class="register-message"> <?= htmlspecialchars($mensagem) ?> </div>
        <?php endif; ?>
        <form method="post" class="register-form" autocomplete="off">
            <input type="text" name="nome" placeholder="Nome completo" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
            <input type="email" name="email" placeholder="E-mail" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="password" name="senha2" placeholder="Confirme a senha" required>
            <button type="submit" class="button">Cadastrar</button>
        </form>
        <div class="register-link">
            Já tem conta? <a href="/tech01/cadastro_login/login.php">Entrar</a>
        </div>
    </div>
</body>
</html>
