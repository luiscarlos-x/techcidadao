<?php
require_once __DIR__ . '/../conexao.php';

// Inicializar variáveis
$mensagem = '';
$erro = false;
$tokenValido = false;

// Verificar se há token e email na URL
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = trim($_GET['token']);
    $email = trim($_GET['email']);
    
    // Verificar se o token existe e é válido
    $sql = "SELECT * FROM tokens_recuperacao WHERE token = ? AND email = ? AND usado = 0 AND expira > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $token, $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $tokenValido = true;
    } else {
        $mensagem = 'Este link de recuperação é inválido ou expirou. Por favor, solicite um novo.';
        $erro = true;
    }
    $stmt->close();
} else {
    $mensagem = 'Link inválido. É necessário usar o link completo enviado por email.';
    $erro = true;
}

// Processar o formulário de redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValido) {
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    
    // Validar senha
    if (strlen($senha) < 8) {
        $mensagem = 'A senha deve ter pelo menos 8 caracteres.';
        $erro = true;
    } elseif ($senha !== $confirmarSenha) {
        $mensagem = 'As senhas não coincidem.';
        $erro = true;
    } else {
        // Senha válida, atualizar no banco de dados
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Atualizar a senha do usuário
        $sqlUpdate = "UPDATE usuarios SET senha = ? WHERE email = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param('ss', $senhaHash, $email);
        $sucesso = $stmtUpdate->execute();
        $stmtUpdate->close();
        
        if ($sucesso) {
            // Marcar o token como usado
            $sqlToken = "UPDATE tokens_recuperacao SET usado = 1 WHERE token = ? AND email = ?";
            $stmtToken = $conn->prepare($sqlToken);
            $stmtToken->bind_param('ss', $token, $email);
            $stmtToken->execute();
            $stmtToken->close();
            
            $mensagem = 'Sua senha foi redefinida com sucesso! Agora você pode fazer login com sua nova senha.';
            $tokenValido = false; // Não mostrar mais o formulário
        } else {
            $mensagem = 'Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente.';
            $erro = true;
        }
    }
}

// CSS inline para formatação
$css = "
<style>
/* Estilos específicos para a página de redefinição de senha */
.redefinir-container {
    max-width: 500px;
    margin: 80px auto;
    padding: 30px;
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--box-shadow);
}

.redefinir-title {
    color: var(--primary-color);
    text-align: center;
    margin-bottom: 25px;
    font-family: 'Poppins', sans-serif;
    font-size: 2rem;
}

.redefinir-form {
    display: flex;
    flex-direction: column;
}

.redefinir-form input {
    padding: 12px 15px;
    margin-bottom: 15px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 16px;
}

.redefinir-form button {
    background-color: var(--primary-color);
    color: white;
    padding: 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: background-color 0.3s;
}

.redefinir-form button:hover {
    background-color: var(--primary-color-dark);
}

.redefinir-link {
    text-align: center;
    margin-top: 20px;
}

.redefinir-link a {
    color: var(--primary-color);
    text-decoration: none;
}

.redefinir-link a:hover {
    text-decoration: underline;
}

.recuperar-message {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    text-align: center;
}

.erro {
    background-color: #ff3860;
    color: white;
}

.sucesso {
    background-color: #48c774;
    color: white;
}

.password-requirements {
    margin-bottom: 15px;
    color: #666;
    font-size: 0.9rem;
}

.password-requirements ul {
    padding-left: 20px;
    margin-top: 5px;
}
</style>
";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - TechCidadão</title>
    <link rel="stylesheet" href="/tech01/main/css/index.css">
    <?php echo $css; ?>
</head>
<body style="background: var(--light-bg); min-height: 100vh;">
    <!-- Barra de navegação consistente com o resto do site -->
    <nav class="main-nav">
        <div class="main-nav-title">TechCidadão</div>
        <ul class="menu-list">
            <li><a href="/tech01/cadastro_login/login.php">Login</a></li>
            <li><a href="/tech01/cadastro_login/register.php">Registrar</a></li>
        </ul>
    </nav>

    <div class="redefinir-container">
        <h2 class="redefinir-title">Redefinir Senha</h2>
        
        <?php if ($mensagem): ?>
            <div class="recuperar-message <?= $erro ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($tokenValido): ?>
            <form method="post" class="redefinir-form">
                <div class="password-requirements">
                    <strong>Requisitos para senha:</strong>
                    <ul>
                        <li>Pelo menos 8 caracteres</li>
                        <li>Recomendamos incluir letras, números e símbolos</li>
                    </ul>
                </div>
                
                <input type="password" name="senha" placeholder="Nova senha" required>
                <input type="password" name="confirmar_senha" placeholder="Confirmar nova senha" required>
                
                <button type="submit" class="button">Confirmar Nova Senha</button>
                
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            </form>
        <?php elseif (!$erro): ?>
            <div class="redefinir-link">
                <a href="/tech01/cadastro_login/login.php">Ir para a página de login</a>
            </div>
        <?php else: ?>
            <div class="redefinir-link">
                <a href="/tech01/cadastro_login/recuperar_senha.php">Solicitar nova recuperação de senha</a>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="site-footer">
        <p> 2025 TechCidadão. Todos os direitos reservados.</p>
        <p>Uma iniciativa construída por professores e alunos do IEMA Pleno Matões - Maranhão.</p>
    </footer>
</body>
</html>
