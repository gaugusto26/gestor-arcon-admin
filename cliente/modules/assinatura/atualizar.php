<?php
ob_start();
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cliente_id'])) {
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!isset($dados['assinatura']) || empty($dados['assinatura'])) {
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'erro' => 'Assinatura não fornecida']);
    exit;
}

$cliente_id = (int)$_SESSION['cliente_id'];
$nome       = $dados['nome'] ?? 'Minha Assinatura';
$assinatura = $dados['assinatura'];

$stmt = $conn->prepare("
    UPDATE cliente_assinaturas 
    SET nome_assinatura = ?, assinatura_base64 = ?, ip = ?, user_agent = ?, updated_at = NOW() 
    WHERE cliente_id = ?
");
$stmt->bind_param("ssssi", $nome, $assinatura, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $cliente_id);

ob_end_clean();

if ($stmt && $stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => $conn->error ?: 'Erro ao preparar query']);
}
?>