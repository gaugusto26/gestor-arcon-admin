<?php
require_once '../../../config.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

$id = $_GET['id'];

// Busca transação
$stmt = $conn->prepare("
    SELECT * FROM pagamento_transacoes 
    WHERE id = ? AND forma_pagamento = 'pix'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$transacao = $stmt->get_result()->fetch_assoc();

if(!$transacao) {
    echo json_encode(['sucesso' => false, 'erro' => 'Transação PIX não encontrada']);
    exit;
}

// Formata data de expiração
$expiracao = date('d/m/Y H:i', strtotime($transacao['pix_expiracao']));

// Retorna dados
echo json_encode([
    'sucesso' => true,
    'qrcode' => $transacao['pix_qrcode'],
    'copiaecola' => $transacao['pix_copiaecola'],
    'expiracao' => $expiracao,
    'valor' => number_format($transacao['valor'], 2, ',', '.'),
    'status' => $transacao['status']
]);
?>