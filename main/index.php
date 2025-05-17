<?php
session_start();
// Adicione isso no início do arquivo, antes do <!DOCTYPE html>
function is_logged_in_preview() {
    // Retorne false para simular usuário deslogado, true para logado
    return false;
}

function get_user_email_preview() {
    // Retorne um e-mail fictício para o preview
    return 'usuario@exemplo.com';
}

// Obter o nome do usuário da sessão para exibir saudação personalizada
$nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário';

// Verificar se há uma mensagem de registro bem-sucedido
$registro_sucesso = false;
if (isset($_SESSION['registro_sucesso']) && $_SESSION['registro_sucesso']) {
    $registro_sucesso = true;
    $nome_usuario = $_SESSION['nome_usuario'] ?? $nome_usuario;
    // Limpar a variável de sessão para que a mensagem não apareça novamente após recarregar
    unset($_SESSION['registro_sucesso']);
    unset($_SESSION['nome_usuario']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCidadão - CONECTANDO SABERES E COMUNIDADES </title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/categorias.css">
    <link rel="stylesheet" href="css/bairro.css">
    <link rel="stylesheet" href="css/gauge.css">
    <link rel="stylesheet" href="css/navbar.css">
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="js/apexgauge.js"></script>
</head>
<body>
    <!-- <header class="site-header">
        <h1>TechCidadão</h1>
        <p class="subtitle">Conectando Saberes e Comunidades</p>
    </header> -->
    
    <nav class="main-nav">
        <div class="main-nav-title">TechCidadão</div>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <div class="user-greeting">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></div>
        <?php endif; ?>
        <div class="menu-hamburger" id="menuHamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="menu-list" id="menuList">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li><a href="/tech01/main/index.php" class="active">Início</a></li>
                <li><a href="/tech01/main/avaliacao.php">Avaliar</a></li>
                <li><a href="/tech01/main/perfil.php">Meu Perfil</a></li>
                <li><a href="/tech01/cadastro_login/logout.php">Sair</a></li>
            <?php else: ?>
                <li><a href="/tech01/cadastro_login/login.php">Login</a></li>
                <li><a href="/tech01/cadastro_login/register.php">Registrar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <script>
    const menuHamburger = document.getElementById('menuHamburger');
    const menuList = document.getElementById('menuList');
    menuHamburger.onclick = function() {
        menuList.classList.toggle('open');
        menuHamburger.classList.toggle('open');
    };
    
    // Adicionar rolagem suave para o botão "Consultar avaliações"
    document.addEventListener('DOMContentLoaded', function() {
        const consultarBtn = document.getElementById('consultar-btn');
        if (consultarBtn) {
            consultarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const section = document.getElementById('cidade-matoes');
                if (section) {
                    section.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        }
    });
    </script>

    <div class="hero-banner">
        <h2>Transforme Matões, Uma Avaliação Por Vez.</h2>
        <p>Junte-se à comunidade TechCidadão e faça sua voz ser ouvida. Avalie serviços, proponha soluções e colabore para um futuro urbano melhor.</p>
        <div class="cta-buttons">
             <?php if (!isset($_SESSION['usuario_id'])): ?>
                <a href="#cidade-matoes" class="button" id="consultar-btn">Consultar Avaliações</a>
                <a href="/tech01/cadastro_login/login.php" class="button secondary">Avaliar Cidade</a>
            <?php else: ?>
                <a href="/tech01/main/avaliacao.php" class="button">Avaliar agora</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container-main">
        <section class="intro-section">
            <h2>Bem-vindo ao TechCidadão!</h2>
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <p class="user-welcome">Olá, <?= htmlspecialchars($nome_usuario) ?>! Aqui você pode visualizar e avaliar os serviços e infraestrutura dos bairros de Matões.</p>
            <?php else: ?>
            <p>Sua plataforma para impulsionar a mudança urbana através da colaboração e da tecnologia. <br>Conectamos cidadãos, ideias e dados para construir uma cidade melhor.</p>
            <?php endif; ?>
        </section>

        <section class="content-section">
            <h3>Proposta</h3>
            <p>O TechCidadão é uma plataforma colaborativa destinada a envolver a comunidade no diagnóstico e solução de problemas urbanos. Permitimos que cidadãos da cidade de Matões-MA avaliem aspectos fundamentais de seus bairros — como iluminação pública, coleta de resíduos, transporte, segurança, saúde, infraestrutura e lazer. As avaliações são consolidadas e apresentadas em tempo real através de representações gráficas intuitivas, proporcionando uma visão clara e coletiva das necessidades locais e do sentimento da comunidade.</p>
        </section>

        <section class="content-section" id="cidade-matoes">
            <h3>Cidade de Matões</h3>
            
            <div class="neighborhood-selection">
                <p>Selecione o bairro</p>
                <div class="neighborhood-dropdown">
                    <div class="selected-neighborhood" id="selectedNeighborhood">Bairro1</div>
                    <div class="neighborhood-options" id="neighborhoodOptions">
                        <div class="neighborhood-option" data-neighborhood="bairro1">Bairro1</div>
                        <div class="neighborhood-option" data-neighborhood="bairro2">Bairro2</div>
                        <div class="neighborhood-option" data-neighborhood="bairro3">Bairro3</div>
                        <div class="neighborhood-option" data-neighborhood="bairro4">Bairro4</div>
                        <div class="neighborhood-option" data-neighborhood="bairro5">Bairro5</div>
                        <div class="neighborhood-option" data-neighborhood="bairro6">Bairro6</div>
                        <div class="neighborhood-option" data-neighborhood="bairro7">Bairro7</div>
                    </div>
                </div>
            </div>
            
            <div class="category-selection">
                <p>Selecione uma categoria para visualizar detalhes:</p>
                <div class="category-buttons">
                    <button class="category-btn" data-category="iluminacao">Iluminação Pública</button>
                    <button class="category-btn" data-category="residuos">Coleta de Resíduos</button>
                    <button class="category-btn" data-category="transporte">Transporte</button>
                    <button class="category-btn" data-category="seguranca">Segurança</button>
                    <button class="category-btn" data-category="saude">Saúde</button>
                    <button class="category-btn" data-category="infraestrutura">Infraestrutura</button>
                    <button class="category-btn" data-category="lazer">Lazer</button>
                </div>
            </div>

            <div class="category-details" id="category-details">
                <!-- Conteúdo da categoria selecionada será exibido aqui -->
            </div>
        </section>

    </div> <!-- Fim .container-main -->

    <footer class="site-footer">
        <p> 2025 TechCidadão. Todos os direitos reservados.</p>
        <p>Uma iniciativa construída por professores e alunos do IEMA Pleno Matões - Maranhão. <a href="#" id="aboutLink">Sobre</a> | <a href="#">Privacidade</a> | <a href="#">Termos</a></p>
    </footer>
    
    <!-- Não precisamos mais do modal HTML aqui, usando SweetAlert2 em vez disso -->

    <!-- Detectar parâmetros de avaliação para inicialização automática -->
<?php
$avaliacao_feita = isset($_GET['avaliacao']) && $_GET['avaliacao'] == '1';
$bairro = isset($_GET['bairro']) ? htmlspecialchars($_GET['bairro']) : '';
$categoria = isset($_GET['categoria']) ? htmlspecialchars($_GET['categoria']) : '';
?>

<script src="index.js"></script>

<?php if ($avaliacao_feita && !empty($bairro)): ?>
<script>
// Inicializar automaticamente com os dados da avaliação feita
document.addEventListener('DOMContentLoaded', async function() {
    // Aguardar um momento para garantir que os scripts anteriores carregaram
    setTimeout(async function() {
        // Selecionar o bairro na interface
        const selectedNeighborhood = document.getElementById('selectedNeighborhood');
        const neighborhoodOptions = document.querySelectorAll('.neighborhood-option');
        const targetBairro = '<?= $bairro ?>';
        
        // Selecionar o bairro
        if (selectedNeighborhood) {
            selectedNeighborhood.textContent = targetBairro;
            
            // Também atualizar a variável global no index.js
            window.selectedNeighborhoodName = targetBairro;
            
            // Carregar dados do bairro
            await loadNeighborhoodData(targetBairro);
            
            <?php if (!empty($categoria)): ?>
            // Selecionar a categoria específica
            const targetCategoria = '<?= $categoria ?>';
            const categoryBtn = document.querySelector(`.category-btn[data-category="${targetCategoria}"]`);
            
            if (categoryBtn) {
                // Simular clique no botão para mostrar os detalhes
                categoryBtn.click();
                
                // Rolar para a seção da categoria
                setTimeout(function() {
                    document.getElementById('category-details').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 500);
                
                // Mostrar notificação de sucesso
                Swal.fire({
                    title: 'Avaliação Registrada!',
                    text: 'Sua avaliação foi registrada com sucesso e os dados foram atualizados.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            }
            <?php endif; ?>
        }
    }, 300);
});
</script>
<?php endif; ?>
    
    <!-- Script do Sobre com SweetAlert2 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Link sobre
        const aboutLink = document.getElementById('aboutLink');
        
        // Abrir SweetAlert2 quando clicar no link
        aboutLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: '<span style="color: #0056b3">Sobre o TechCidadão</span>',
                html: `
                    <div style="text-align: left; padding: 10px">
                        <div style="margin-bottom: 20px; padding: 15px; border-radius: 8px; background-color: #f8f9fa; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <h4 style="color: #0056b3; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 12px;">Idealizador</h4>
                            <p style="margin: 5px 0; color: #333;">Prof. Júlio Lopes da Silva</p>
                            <p style="margin: 5px 0; color: #666; font-style: italic;">Professor do IEMA Pleno Matões</p>
                        </div>
                        
                        <div style="padding: 15px; border-radius: 8px; background-color: #f8f9fa; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <h4 style="color: #0056b3; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 12px;">Desenvolvimento</h4>
                            <p style="margin: 5px 0; color: #333;">Luis Carlos Barbosa de Almeida</p>
                            <p style="margin: 5px 0; color: #333;">Dúvidas ou sugestões? Entre em contato:</p>
                            <p style="margin: 5px 0; color: #666; font-size: 0.9em;">Tel: <a href="tel:+5599984281338" style="color: #0056b3; text-decoration: none;">+55 (99) 8428-1338</a></p>
                            <p style="margin: 5px 0; color: #666; font-size: 0.9em;">E-mail: <a href="mailto:luiscarlos20507@gmail.com" style="color: #0056b3; text-decoration: none;">luiscarlos20507@gmail.com</a></p>
                        </div>
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: false,
                width: '600px',
                background: '#ffffff',
                backdrop: `rgba(0,0,0,0.4)`,
                customClass: {
                    container: 'about-swal-container',
                    popup: 'about-swal-popup',
                    header: 'about-swal-header',
                    content: 'about-swal-content'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        });
    });
    </script>
    
    <?php if ($registro_sucesso): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Bem-vindo à TechCidadão!',
            text: '<?= htmlspecialchars($nome_usuario) ?>, sua conta foi criada com sucesso!',
            icon: 'success',
            confirmButtonText: 'Começar a explorar',
            confirmButtonColor: '#0056b3',
            background: '#ffffff',
            iconColor: '#0056b3',
            customClass: {
                title: 'swal-title',
                confirmButton: 'swal-confirm-button'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    });
    </script>
    
    <style>
    /* Estilos personalizados para o SweetAlert2 */
    .swal-title {
        color: #004085;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
    }
    
    .swal-confirm-button {
        font-family: 'Roboto', sans-serif;
        font-weight: 500;
        padding: 12px 24px;
        border-radius: 4px;
    }
    
    .swal2-popup {
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    
    /* Estilos adicionais para o SweetAlert2 'Sobre' */
    .about-swal-popup {
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .about-swal-header {
        border-bottom: 1px solid #eee;
    }
    
    .about-swal-content {
        padding: 0;
    }
    </style>
    <?php endif; ?>
</body>
</html>