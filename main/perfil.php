<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /tech01/cadastro_login/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário');
$email_usuario = htmlspecialchars($_SESSION['usuario_email'] ?? 'email@exemplo.com');

// Contar total de avaliações do usuário
$query = "SELECT COUNT(*) as total FROM avaliacoes WHERE usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$total_avaliacoes = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Buscar as categorias mais avaliadas pelo usuário
$categorias_query = "SELECT c.nome, COUNT(*) as total 
                    FROM avaliacoes a 
                    JOIN categorias c ON a.categoria_id = c.id 
                    WHERE a.usuario_id = ? 
                    GROUP BY c.id 
                    ORDER BY total DESC 
                    LIMIT 3";
$categorias_stmt = $conn->prepare($categorias_query);
$categorias_stmt->bind_param('i', $usuario_id);
$categorias_stmt->execute();
$categorias_result = $categorias_stmt->get_result();
$categorias_favoritas = [];
while ($row = $categorias_result->fetch_assoc()) {
    $categorias_favoritas[] = $row;
}
$categorias_stmt->close();

// Buscar os bairros mais avaliados pelo usuário
$bairros_query = "SELECT b.nome, COUNT(*) as total 
                 FROM avaliacoes a 
                 JOIN bairros b ON a.bairro_id = b.id 
                 WHERE a.usuario_id = ? 
                 GROUP BY b.id 
                 ORDER BY total DESC 
                 LIMIT 3";
$bairros_stmt = $conn->prepare($bairros_query);
$bairros_stmt->bind_param('i', $usuario_id);
$bairros_stmt->execute();
$bairros_result = $bairros_stmt->get_result();
$bairros_favoritos = [];
while ($row = $bairros_result->fetch_assoc()) {
    $bairros_favoritos[] = $row;
}
$bairros_stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - TechCidadão</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/categorias.css">
    <link rel="stylesheet" href="css/bairro.css">
    <link rel="stylesheet" href="css/avaliacao.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/perfil.css">
    
    <style>
    /* Estilos específicos para a página de perfil */
    .perfil-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .perfil-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .perfil-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 20px;
    }
    
    .perfil-info {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    
    .perfil-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
    }
    
    .perfil-detalhes {
        flex: 1;
        min-width: 250px;
    }
    
    .perfil-nome {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: var(--primary-color);
    }
    
    .perfil-email {
        font-size: 1rem;
        color: #666;
        margin-bottom: 15px;
    }
    
    .perfil-stats {
        margin-top: 15px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .stat-card {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        min-width: 120px;
        flex: 1;
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #666;
    }
    
    .perfil-section {
        margin-top: 30px;
    }
    
    .perfil-section h3 {
        margin-bottom: 15px;
        color: #333;
        font-size: 1.3rem;
        border-bottom: 2px solid #eee;
        padding-bottom: 5px;
    }
    
    .favoritos-lista {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .favorito-item {
        background-color: #f0f0f0;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #555;
    }
    
    .favorito-item span {
        display: inline-block;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.8rem;
        line-height: 20px;
        text-align: center;
        margin-left: 8px;
    }
    
    .perfil-acoes {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }
    
    .perfil-btn {
        padding: 10px 20px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        margin: 0 10px;
    }
    
    .perfil-btn:hover {
        background-color: var(--primary-color-dark);
    }
    
    .perfil-btn.secondary {
        background-color: #f5f5f5;
        color: #555;
        border: 1px solid #ddd;
    }
    
    .perfil-btn.secondary:hover {
        background-color: #e0e0e0;
    }
    
    @media (max-width: 768px) {
        .perfil-info {
            flex-direction: column;
            text-align: center;
        }
        
        .perfil-avatar {
            margin: 0 auto;
        }
        
        .perfil-stats {
            flex-direction: column;
        }
    }
    </style>
</head>
<body>
    <nav class="main-nav">
        <div class="main-nav-title">TechCidadão</div>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <div class="user-greeting">Olá, <?= $nome_usuario ?></div>
        <?php endif; ?>
        <div class="menu-hamburger" id="menuHamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="menu-list" id="menuList">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li><a href="/tech01/main/index.php">Início</a></li>
                <li><a href="/tech01/main/avaliacao.php">Avaliar</a></li>
                <li><a href="/tech01/main/perfil.php" class="active">Meu Perfil</a></li>
                <li><a href="/tech01/cadastro_login/logout.php">Sair</a></li>
            <?php else: ?>
                <li><a href="/tech01/cadastro_login/login.php">Login</a></li>
                <li><a href="/tech01/cadastro_login/register.php">Registrar</a></li>
                <li><a href="#">Contato</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="container-main perfil-container">
        <section class="perfil-header">
            <h2>Meu Perfil</h2>
            <p>Visualize e gerencie suas informações pessoais</p>
        </section>
        
        <div class="perfil-card">
            <div class="perfil-info">
                <div class="perfil-avatar"><?= strtoupper(substr($nome_usuario, 0, 1)) ?></div>
                <div class="perfil-detalhes">
                    <div class="perfil-nome"><?= $nome_usuario ?></div>
                    <div class="perfil-email"><?= $email_usuario ?></div>
                    
                    <div class="perfil-stats">
                        <div class="stat-card">
                            <div class="stat-value"><?= $total_avaliacoes ?></div>
                            <div class="stat-label">Avaliações</div>
                        </div>
                        
                        <?php if (count($bairros_favoritos) > 0): ?>
                            <div class="stat-card">
                                <div class="stat-value"><?= count($bairros_favoritos) ?></div>
                                <div class="stat-label">Bairros Avaliados</div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (count($categorias_favoritas) > 0): ?>
                            <div class="stat-card">
                                <div class="stat-value"><?= count($categorias_favoritas) ?></div>
                                <div class="stat-label">Categorias Avaliadas</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($categorias_favoritas)): ?>
            <div class="perfil-card perfil-section">
                <h3>Suas Categorias Mais Avaliadas</h3>
                <div class="favoritos-lista">
                    <?php foreach ($categorias_favoritas as $categoria): ?>
                        <div class="favorito-item">
                            <?= htmlspecialchars($categoria['nome']) ?>
                            <span><?= $categoria['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($bairros_favoritos)): ?>
            <div class="perfil-card perfil-section">
                <h3>Seus Bairros Mais Avaliados</h3>
                <div class="favoritos-lista">
                    <?php foreach ($bairros_favoritos as $bairro): ?>
                        <div class="favorito-item">
                            <?= htmlspecialchars($bairro['nome']) ?>
                            <span><?= $bairro['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="perfil-acoes">
            <a href="/tech01/main/avaliacao.php" class="perfil-btn">Fazer Nova Avaliação</a>
            <a href="/tech01/main/index.php" class="perfil-btn secondary">Voltar para Início</a>
        </div>
    </div>
    
    <footer class="site-footer">
        <p>© 2025 TechCidadão. Todos os direitos reservados.</p>
        <p>Uma iniciativa construída por professores e alunos do IEMA Pleno Matões - Maranhão.</p>
    </footer>
    
    <script>
    // Menu hamburger
    const menuHamburger = document.getElementById('menuHamburger');
    const menuList = document.getElementById('menuList');
    menuHamburger.onclick = function() {
        menuList.classList.toggle('open');
        menuHamburger.classList.toggle('open');
    };
    </script>
</body>
</html>
