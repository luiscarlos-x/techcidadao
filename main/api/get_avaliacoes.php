<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require_once __DIR__ . '/../../conexao.php';

// Parâmetros da requisição
$bairro = isset($_GET['bairro']) ? $_GET['bairro'] : null;
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;

// Se não foi informado bairro ou categoria, retornar erro
if (!$bairro) {
    echo json_encode(['error' => 'Bairro não informado']);
    exit;
}

// Início da consulta SQL
$query = "SELECT ";

if ($categoria) {
    // Buscar média específica para o bairro e categoria
    $query .= "b.nome as bairro, c.nome as categoria, c.slug as categoria_slug, 
               IFNULL(ROUND(AVG(a.pontuacao)), 0) as media,
               COUNT(a.id) as total_avaliacoes
               FROM bairros b 
               LEFT JOIN categorias c ON c.slug = ?
               LEFT JOIN avaliacoes a ON a.bairro_id = b.id AND a.categoria_id = c.id
               WHERE b.nome = ?
               GROUP BY b.id, c.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $categoria, $bairro);
} else {
    // Buscar médias de todas as categorias para o bairro
    $query .= "b.nome as bairro, c.nome as categoria, c.slug as categoria_slug, 
               IFNULL(ROUND(AVG(a.pontuacao)), 0) as media,
               COUNT(a.id) as total_avaliacoes
               FROM bairros b 
               CROSS JOIN categorias c
               LEFT JOIN avaliacoes a ON a.bairro_id = b.id AND a.categoria_id = c.id
               WHERE b.nome = ?
               GROUP BY b.id, c.id
               ORDER BY c.nome";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $bairro);
}

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();

// Processar resultados
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Se for busca de categoria específica e não encontrou, buscar apenas os dados da categoria
if ($categoria && empty($data)) {
    $query = "SELECT nome as categoria, slug as categoria_slug FROM categorias WHERE slug = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $data[] = [
            'bairro' => $bairro,
            'categoria' => $row['categoria'],
            'categoria_slug' => $row['categoria_slug'],
            'media' => 0,
            'total_avaliacoes' => 0
        ];
    }
}

// Retornar os dados em formato JSON
echo json_encode(['data' => $data]);
$stmt->close();
$conn->close();
?>
