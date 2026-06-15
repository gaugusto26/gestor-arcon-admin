<?php
require_once '../config.php';

session_start();
$ip = $_SERVER['REMOTE_ADDR'];
$session_id = session_id();

$data = json_decode(file_get_contents('php://input'), true);
$post_id = (int)$data['post_id'];

if(!$post_id) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

// Verifica se já curtiu
$stmt = $conn->prepare("SELECT id FROM blog_curtidas WHERE post_id = ? AND (ip = ? OR session_id = ?)");
$stmt->bind_param("iss", $post_id, $ip, $session_id);
$stmt->execute();
$ja_curtiu = $stmt->get_result()->num_rows > 0;

if($ja_curtiu) {
    // Remove curtida
    $stmt = $conn->prepare("DELETE FROM blog_curtidas WHERE post_id = ? AND (ip = ? OR session_id = ?)");
    $stmt->bind_param("iss", $post_id, $ip, $session_id);
    $stmt->execute();
    $curtiu = false;
} else {
    // Adiciona curtida
    $stmt = $conn->prepare("INSERT INTO blog_curtidas (post_id, ip, session_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $post_id, $ip, $session_id);
    $stmt->execute();
    $curtiu = true;
}

// Busca total atualizado
$result = $conn->query("SELECT COUNT(*) as total FROM blog_curtidas WHERE post_id = $post_id");
$total = $result->fetch_assoc()['total'];

echo json_encode([
    'sucesso' => true,
    'curtiu' => $curtiu,
    'total' => $total
]);
?>