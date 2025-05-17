<?php
require_once __DIR__ . '/../conexao.php';
session_start();

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        $mensagem = 'Preencha todos os campos.';
    } else {
        $stmt = $conn->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $nome, $hash);
            $stmt->fetch();
            if (password_verify($senha, $hash)) {
                $_SESSION['usuario_id'] = $id;
                $_SESSION['usuario_nome'] = $nome;
                $_SESSION['usuario_email'] = $email;
                header('Location: /tech01/main/index.php');
                exit;
            } else {
                $mensagem = 'Senha incorreta.';
            }
        } else {
            $mensagem = 'Usuário não encontrado.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - TechCidadão</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="/tech01/main/css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body style="background: var(--light-bg); min-height: 100vh;">
    <div class="login-container">
        <h2 class="login-title">Login</h2>
        <?php if ($mensagem): ?>
            <div class="login-message"> <?= htmlspecialchars($mensagem) ?> </div>
        <?php endif; ?>
        <form method="post" class="login-form" autocomplete="off">
            <input type="email" name="email" placeholder="E-mail" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" class="button">Entrar</button>
        </form>
        <div class="login-link">
            <a href="/tech01/cadastro_login/recuperar_senha.php">Esqueci minha senha</a><br>
            Não tem conta? <a href="/tech01/cadastro_login/register.php">Cadastre-se</a>
        </div>
    </div>
</body>
</html>
