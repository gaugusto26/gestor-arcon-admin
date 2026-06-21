<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://digitalfive.com.br');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: public, max-age=300');

require_once '../config.php';

$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 10) : 3;

$stmt = $conn->prepare("
    SELECT p.id, p.titulo, p.slug, p.resumo, p.imagem_destaque,
           p.autor, p.data_publicacao, p.tempo_leitura, p.destaque,
           c.nome as categoria_nome, c.cor as categoria_cor
    FROM blog_posts p
    LEFT JOIN blog_categorias c ON p.categoria_id = c.id
    WHERE p.status = 'publicado' AND p.ativo = 1
    ORDER BY p.destaque DESC, p.data_publicacao DESC
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = [
        'id'             => (int)$row['id'],
        'titulo'         => $row['titulo'],
        'slug'           => $row['slug'],
        'resumo'         => $row['resumo'],
        'imagem_destaque'=> $row['imagem_destaque'],
        'autor'          => $row['autor'],
        'data_publicacao'=> $row['data_publicacao'],
        'tempo_leitura'  => (int)$row['tempo_leitura'],
        'destaque'       => (bool)$row['destaque'],
        'categoria'      => $row['categoria_nome'],
        'categoria_cor'  => $row['categoria_cor'],
    ];
}

echo json_encode(['posts' => $posts, 'total' => count($posts)], JSON_UNESCAPED_UNICODE);
