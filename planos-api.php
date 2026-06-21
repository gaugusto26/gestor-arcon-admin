<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://digitalfive.com.br');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: public, max-age=300');

require_once 'config.php';

$limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 12)) : 8;
$perfil = $_GET['perfil'] ?? '';
$allowed_perfis = ['mei', 'empresa', 'ambos'];

$where = "p.ativo = 1";
$params = [];
$types = '';

if (in_array($perfil, $allowed_perfis, true) && $perfil !== 'ambos') {
    $where .= " AND (p.perfil = ? OR p.perfil = 'ambos')";
    $params[] = $perfil;
    $types .= 's';
}

$sql = "
    SELECT p.id, p.nome, p.slug, p.descricao_curta, p.descricao_completa,
           p.preco, p.periodo, p.destaque, p.badge_text, p.prazo_entrega,
           p.observacao, p.perfil, p.link_whatsapp, p.mensagem_whatsapp,
           c.nome AS categoria_nome, c.icone AS categoria_icone
    FROM planos p
    LEFT JOIN planos_categorias c ON p.categoria_id = c.id
    WHERE $where
    ORDER BY p.destaque DESC, COALESCE(c.ordem, 999), p.ordem, p.id
    LIMIT ?
";

$params[] = $limit;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$ids = [];
$plans = [];

while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $ids[] = $id;
    $plans[$id] = [
        'id' => $id,
        'nome' => $row['nome'],
        'slug' => $row['slug'],
        'descricao' => $row['descricao_curta'] ?: $row['descricao_completa'],
        'preco' => (float)$row['preco'],
        'preco_formatado' => 'R$ ' . number_format((float)$row['preco'], 2, ',', '.'),
        'periodo' => $row['periodo'],
        'destaque' => (bool)$row['destaque'],
        'badge_text' => $row['badge_text'],
        'prazo_entrega' => $row['prazo_entrega'],
        'observacao' => $row['observacao'],
        'perfil' => $row['perfil'],
        'categoria' => $row['categoria_nome'],
        'categoria_icone' => $row['categoria_icone'],
        'link_whatsapp' => $row['link_whatsapp'],
        'mensagem_whatsapp' => $row['mensagem_whatsapp'],
        'caracteristicas' => [],
    ];
}

if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $feature_types = str_repeat('i', count($ids));
    $feature_stmt = $conn->prepare("
        SELECT plano_id, caracteristica, icone
        FROM planos_caracteristicas
        WHERE plano_id IN ($placeholders)
        ORDER BY plano_id, ordem
    ");
    $feature_stmt->bind_param($feature_types, ...$ids);
    $feature_stmt->execute();
    $features = $feature_stmt->get_result();

    while ($feature = $features->fetch_assoc()) {
        $plan_id = (int)$feature['plano_id'];
        if (isset($plans[$plan_id])) {
            $plans[$plan_id]['caracteristicas'][] = [
                'texto' => $feature['caracteristica'],
                'icone' => $feature['icone'],
            ];
        }
    }
}

echo json_encode([
    'plans' => array_values($plans),
    'total' => count($plans),
], JSON_UNESCAPED_UNICODE);
