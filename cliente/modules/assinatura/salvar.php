<?php
ob_start(); // Captura qualquer output indesejado (warnings, errors)
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

$check = $conn->prepare("SELECT id FROM cliente_assinaturas WHERE cliente_id = ?");
$check->bind_param("i", $cliente_id);
$check->execute();
$existe = $check->get_result()->num_rows > 0;
$check->close();

if ($existe) {
    $stmt = $conn->prepare("
        UPDATE cliente_assinaturas 
        SET nome_assinatura = ?, assinatura_base64 = ?, ip = ?, user_agent = ?, updated_at = NOW() 
        WHERE cliente_id = ?
    ");
    $stmt->bind_param("ssssi", $nome, $assinatura, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $cliente_id);
} else {
    $stmt = $conn->prepare("
        INSERT INTO cliente_assinaturas (cliente_id, nome_assinatura, assinatura_base64, ip, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $cliente_id, $nome, $assinatura, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
}

ob_end_clean(); // Descarta qualquer warning capturado antes de responder

if ($stmt && $stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => $conn->error ?: 'Erro ao preparar query']);
}
?>