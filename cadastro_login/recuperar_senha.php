<?php
require_once __DIR__ . '/../conexao.php';
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $mensagem = 'Informe seu e-mail.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Aqui você enviaria o e-mail de recuperação (simulação)
            $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
        } else {
            $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - TechCidadão</title>
    <link rel="stylesheet" href="/tech01/main/css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="recuperar_senha.css">
</head>
<body style="background: var(--light-bg); min-height: 100vh;">
    <div class="recuperar-container">
        <h2 class="recuperar-title">Recuperação de Senha</h2>
        <?php if ($mensagem): ?>
            <div class="recuperar-message"> <?= htmlspecialchars($mensagem) ?> </div>
        <?php endif; ?>
        <form method="post" class="recuperar-form" autocomplete="off">
            <input type="email" name="email" placeholder="Seu e-mail" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <button type="submit" class="button">Recuperar Senha</button>
        </form>
        <div class="recuperar-link">
            <a href="/tech01/cadastro_login/login.php">Voltar ao login</a>
        </div>
    </div>
</body>
</html>
