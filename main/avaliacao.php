<?php
session_start();
require_once __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado
function is_logged_in() {
    return isset($_SESSION['usuario_id']);
}

// Redirecionar para login se não estiver logado
if (!is_logged_in()) {
    header('Location: /tech01/cadastro_login/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// Buscar bairros
$bairros_query = "SELECT id, nome FROM bairros ORDER BY nome";
$bairros_result = $conn->query($bairros_query);
$bairros = [];
if ($bairros_result->num_rows > 0) {
    while ($row = $bairros_result->fetch_assoc()) {
        $bairros[] = $row;
    }
}

// Buscar categorias
$categorias_query = "SELECT id, nome, slug, descricao, imagem FROM categorias ORDER BY nome";
$categorias_result = $conn->query($categorias_query);
$categorias = [];
if ($categorias_result->num_rows > 0) {
    while ($row = $categorias_result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Processar envio de avaliação
$mensagem = '';
$avaliacao_sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avaliar'])) {
    $bairro_id = intval($_POST['bairro_id'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $pontuacao = intval($_POST['pontuacao'] ?? 0);
    
    // Validar dados
    if ($bairro_id <= 0 || $categoria_id <= 0 || $pontuacao < 0 || $pontuacao > 100) {
        $mensagem = 'Dados inválidos. Por favor, preencha corretamente.';
    } else {
        // Verificar se o usuário já avaliou esta combinação
        $check_query = "SELECT id FROM avaliacoes WHERE usuario_id = ? AND bairro_id = ? AND categoria_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('iii', $usuario_id, $bairro_id, $categoria_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            // Atualizar avaliação existente
            $update_query = "UPDATE avaliacoes SET pontuacao = ?, updated_at = CURRENT_TIMESTAMP WHERE usuario_id = ? AND bairro_id = ? AND categoria_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('iiii', $pontuacao, $usuario_id, $bairro_id, $categoria_id);
            
            if ($update_stmt->execute()) {
                // Buscar nome do bairro e slug da categoria para o redirecionamento
                $bairro_query = "SELECT nome FROM bairros WHERE id = ?";
                $bairro_stmt = $conn->prepare($bairro_query);
                $bairro_stmt->bind_param('i', $bairro_id);
                $bairro_stmt->execute();
                $bairro_result = $bairro_stmt->get_result();
                $bairro_nome = $bairro_result->fetch_assoc()['nome'];
                $bairro_stmt->close();
                
                $categoria_query = "SELECT slug FROM categorias WHERE id = ?";
                $categoria_stmt = $conn->prepare($categoria_query);
                $categoria_stmt->bind_param('i', $categoria_id);
                $categoria_stmt->execute();
                $categoria_result = $categoria_stmt->get_result();
                $categoria_slug = $categoria_result->fetch_assoc()['slug'];
                $categoria_stmt->close();
                
                // Redirecionar para a página inicial com os parâmetros
                header("Location: /tech01/main/index.php?avaliacao=1&bairro=".urlencode($bairro_nome)."&categoria=".urlencode($categoria_slug));
                exit;
            } else {
                $mensagem = 'Erro ao atualizar avaliação. Por favor, tente novamente.';
            }
            $update_stmt->close();
        } else {
            // Inserir nova avaliação
            $insert_query = "INSERT INTO avaliacoes (usuario_id, bairro_id, categoria_id, pontuacao) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param('iiii', $usuario_id, $bairro_id, $categoria_id, $pontuacao);
            
            if ($insert_stmt->execute()) {
                // Buscar nome do bairro e slug da categoria para o redirecionamento
                $bairro_query = "SELECT nome FROM bairros WHERE id = ?";
                $bairro_stmt = $conn->prepare($bairro_query);
                $bairro_stmt->bind_param('i', $bairro_id);
                $bairro_stmt->execute();
                $bairro_result = $bairro_stmt->get_result();
                $bairro_nome = $bairro_result->fetch_assoc()['nome'];
                $bairro_stmt->close();
                
                $categoria_query = "SELECT slug FROM categorias WHERE id = ?";
                $categoria_stmt = $conn->prepare($categoria_query);
                $categoria_stmt->bind_param('i', $categoria_id);
                $categoria_stmt->execute();
                $categoria_result = $categoria_stmt->get_result();
                $categoria_slug = $categoria_result->fetch_assoc()['slug'];
                $categoria_stmt->close();
                
                // Redirecionar para a página inicial com os parâmetros
                header("Location: /tech01/main/index.php?avaliacao=1&bairro=".urlencode($bairro_nome)."&categoria=".urlencode($categoria_slug));
                exit;
            } else {
                $mensagem = 'Erro ao registrar avaliação. Por favor, tente novamente.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Buscar avaliações do usuário
$avaliacoes_query = "SELECT a.bairro_id, a.categoria_id, a.pontuacao 
                     FROM avaliacoes a
                     WHERE a.usuario_id = ?";
$avaliacoes_stmt = $conn->prepare($avaliacoes_query);
$avaliacoes_stmt->bind_param('i', $usuario_id);
$avaliacoes_stmt->execute();
$result = $avaliacoes_stmt->get_result();
$avaliacoes_usuario = [];

while ($row = $result->fetch_assoc()) {
    $key = $row['bairro_id'] . '-' . $row['categoria_id'];
    $avaliacoes_usuario[$key] = [
        'pontuacao' => $row['pontuacao']
    ];
}
$avaliacoes_stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Bairro - TechCidadão</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/categorias.css">
    <link rel="stylesheet" href="css/bairro.css">
    <link rel="stylesheet" href="css/gauge.css">
    <link rel="stylesheet" href="css/avaliacao.css">
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="js/apexgauge.js"></script>
</head>
<body>
    <nav class="main-nav">
        <div class="main-nav-title">TechCidadão</div>
        <div class="menu-hamburger" id="menuHamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="menu-list" id="menuList">
            <?php if (is_logged_in()): ?>
                <li><a href="/tech01/main/index.php">Início</a></li>
                <li><a href="/tech01/main/avaliacao.php" class="active">Avaliar</a></li>
                <li><a href="/tech01/cadastro_login/logout.php">Sair</a></li>
            <?php else: ?>
                <li><a href="/tech01/cadastro_login/login.php">Login</a></li>
                <li><a href="/tech01/cadastro_login/register.php">Registrar</a></li>
                <li><a href="#">Contato</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="container-main">
        <section class="avaliacao-header">
            <h2>Avaliação de Bairros</h2>
            <p>Olá, <?= htmlspecialchars($nome_usuario) ?>! Aqui você pode avaliar os serviços e infraestrutura dos bairros de Matões.</p>
        </section>
        
        <?php if ($mensagem): ?>
            <div class="avaliacao-message <?= $avaliacao_sucesso ? 'success' : 'error' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        
        <section class="avaliacao-form-section">
            <form method="post" class="avaliacao-form" id="avaliacaoForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="bairro_id">Selecione o Bairro</label>
                        <select name="bairro_id" id="bairro_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($bairros as $bairro): ?>
                                <option value="<?= $bairro['id'] ?>"><?= htmlspecialchars($bairro['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Selecione a Categoria</label>
                        <select name="categoria_id" id="categoria_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" data-descricao="<?= htmlspecialchars($categoria['descricao']) ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="categoria-descricao" id="categoriaDescricao">
                    Selecione uma categoria para ver a descrição.
                </div>
                
                <div class="form-group avaliacao-slider-container">
                    <label for="pontuacao">Qual sua avaliação? <span id="pontuacaoValor">50</span>%</label>
                    <div class="slider-container">
                        <div class="slider-labels">
                            <span>Insatisfatório</span>
                            <span>Regular</span>
                            <span>Bom</span>
                            <span>Excelente</span>
                        </div>
                        <input type="range" name="pontuacao" id="pontuacao" min="0" max="100" value="50" class="avaliacao-slider">
                        <div class="slider-track">
                            <div class="slider-fill" id="sliderFill"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="avaliar" value="1" class="button primary">Enviar Avaliação</button>
                    <button type="reset" class="button secondary">Limpar</button>
                </div>
            </form>
        </section>
        
        <section class="minhas-avaliacoes">
            <h3>Minhas Avaliações</h3>
            <div class="avaliacoes-list" id="avaliacoesList">
                <p class="no-avaliacoes" id="noAvaliacoes" <?= count($avaliacoes_usuario) > 0 ? 'style="display: none;"' : '' ?>>
                    Você ainda não fez nenhuma avaliação.
                </p>
                <!-- As avaliações serão carregadas aqui via JavaScript -->
            </div>
        </section>
    </div>
    
    <footer class="site-footer">
        <p>&copy; 2025 TechCidadão. Todos os direitos reservados.</p>
        <p>Uma iniciativa construída por professores e alunos do IEMA Pleno Matões - Maranhão.</p>
    </footer>
    
    <script>
    // Menu hamburguer
    const menuHamburger = document.getElementById('menuHamburger');
    const menuList = document.getElementById('menuList');
    menuHamburger.onclick = function() {
        menuList.classList.toggle('open');
        menuHamburger.classList.toggle('open');
    };
    
    // Slider de avaliação
    const pontuacaoSlider = document.getElementById('pontuacao');
    const pontuacaoValor = document.getElementById('pontuacaoValor');
    const sliderFill = document.getElementById('sliderFill');
    
    function updateSlider() {
        const valor = pontuacaoSlider.value;
        pontuacaoValor.textContent = valor;
        
        // Atualizar o preenchimento do slider
        const porcentagem = (valor / pontuacaoSlider.max) * 100;
        sliderFill.style.width = porcentagem + '%';
        
        // Atualizar a cor do preenchimento com base no valor
        let fillColor;
        if (valor < 25) {
            fillColor = '#ff3860'; // Vermelho - Insatisfatório
        } else if (valor < 50) {
            fillColor = '#ffdd57'; // Amarelo - Regular
        } else if (valor < 75) {
            fillColor = '#48c774'; // Verde - Bom
        } else {
            fillColor = '#3273dc'; // Azul - Excelente
        }
        sliderFill.style.backgroundColor = fillColor;
    }
    
    pontuacaoSlider.addEventListener('input', updateSlider);
    updateSlider(); // Inicializar
    
    // Exibir descrição da categoria
    const categoriaSelect = document.getElementById('categoria_id');
    const categoriaDescricao = document.getElementById('categoriaDescricao');
    
    categoriaSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            categoriaDescricao.textContent = option.dataset.descricao;
        } else {
            categoriaDescricao.textContent = "Selecione uma categoria para ver a descrição.";
        }
    });
    
    // Carregar avaliações do usuário
    const avaliacoesList = document.getElementById('avaliacoesList');
    const noAvaliacoes = document.getElementById('noAvaliacoes');
    const bairroSelect = document.getElementById('bairro_id');
    const avaliacaoForm = document.getElementById('avaliacaoForm');
    
    // Dados dos bairros e categorias do PHP para JavaScript
    const bairros = <?= json_encode($bairros) ?>;
    const categorias = <?= json_encode($categorias) ?>;
    const avaliacoesUsuario = <?= json_encode($avaliacoes_usuario) ?>;
    
    function carregarAvaliacoes() {
        // Limpar lista
        while (avaliacoesList.firstChild && avaliacoesList.firstChild !== noAvaliacoes) {
            avaliacoesList.removeChild(avaliacoesList.firstChild);
        }
        
        // Verificar se há avaliações
        if (Object.keys(avaliacoesUsuario).length === 0) {
            noAvaliacoes.style.display = 'block';
            return;
        }
        
        noAvaliacoes.style.display = 'none';
        
        // Agrupar avaliações por bairro
        const avaliacoesPorBairro = {};
        
        for (const key in avaliacoesUsuario) {
            const [bairroId, categoriaId] = key.split('-').map(Number);
            
            if (!avaliacoesPorBairro[bairroId]) {
                avaliacoesPorBairro[bairroId] = [];
            }
            
            avaliacoesPorBairro[bairroId].push({
                categoriaId: categoriaId,
                pontuacao: avaliacoesUsuario[key].pontuacao
            });
        }
        
        // Criar elementos para cada bairro e suas avaliações
        for (const bairroId in avaliacoesPorBairro) {
            const bairro = bairros.find(b => b.id == bairroId);
            if (!bairro) continue;
            
            const bairroDiv = document.createElement('div');
            bairroDiv.className = 'avaliacao-bairro';
            
            const bairroHeader = document.createElement('h4');
            bairroHeader.textContent = bairro.nome;
            bairroDiv.appendChild(bairroHeader);
            
            const avaliacoesBairro = avaliacoesPorBairro[bairroId];
            const avaliacoesContainer = document.createElement('div');
            avaliacoesContainer.className = 'avaliacao-categorias';
            
            avaliacoesBairro.forEach(aval => {
                const categoria = categorias.find(c => c.id == aval.categoriaId);
                if (!categoria) return;
                
                const itemDiv = document.createElement('div');
                itemDiv.className = 'avaliacao-item';
                
                const itemHeader = document.createElement('div');
                itemHeader.className = 'avaliacao-item-header';
                
                const categoriaSpan = document.createElement('span');
                categoriaSpan.className = 'categoria-nome';
                categoriaSpan.textContent = categoria.nome;
                
                const pontuacaoSpan = document.createElement('span');
                pontuacaoSpan.className = 'avaliacao-pontuacao';
                
                // Definir classe de cor com base na pontuação
                let corClass = '';
                if (aval.pontuacao < 25) {
                    corClass = 'insatisfatorio';
                } else if (aval.pontuacao < 50) {
                    corClass = 'regular';
                } else if (aval.pontuacao < 75) {
                    corClass = 'bom';
                } else {
                    corClass = 'excelente';
                }
                
                pontuacaoSpan.classList.add(corClass);
                pontuacaoSpan.textContent = aval.pontuacao + '%';
                
                itemHeader.appendChild(categoriaSpan);
                itemHeader.appendChild(pontuacaoSpan);
                itemDiv.appendChild(itemHeader);
                
                // Adicionar barra de progresso
                const progressDiv = document.createElement('div');
                progressDiv.className = 'avaliacao-progresso';
                
                const progressBar = document.createElement('div');
                progressBar.className = 'progresso-barra ' + corClass;
                progressBar.style.width = aval.pontuacao + '%';
                
                progressDiv.appendChild(progressBar);
                itemDiv.appendChild(progressDiv);
                
                // Adicionar botão de editar
                const editarButton = document.createElement('button');
                editarButton.className = 'editar-avaliacao';
                editarButton.textContent = 'Editar';
                editarButton.onclick = function() {
                    // Preencher o formulário com os dados desta avaliação
                    bairroSelect.value = bairroId;
                    categoriaSelect.value = aval.categoriaId;
                    pontuacaoSlider.value = aval.pontuacao;
                    
                    // Atualizar elementos visuais
                    updateSlider();
                    categoriaDescricao.textContent = categoria.descricao;
                    
                    // Rolar até o formulário
                    avaliacaoForm.scrollIntoView({ behavior: 'smooth' });
                };
                
                itemDiv.appendChild(editarButton);
                avaliacoesContainer.appendChild(itemDiv);
            });
            
            bairroDiv.appendChild(avaliacoesContainer);
            avaliacoesList.appendChild(bairroDiv);
        }
    }
    
    // Inicializar a lista de avaliações
    carregarAvaliacoes();
    
    <?php if ($avaliacao_sucesso): ?>
    // Atualizar a lista de avaliações após o envio bem-sucedido
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Avaliação Registrada!',
            text: '<?= htmlspecialchars($mensagem) ?>',
            icon: 'success',
            confirmButtonText: 'Continuar',
            confirmButtonColor: '#3273dc',
        });
    });
    <?php endif; ?>
    </script>
</body>
</html>
