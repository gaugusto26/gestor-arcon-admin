<?php
require_once '../../../includes/auth.php';
require_once '../../../includes/functions/pagamentos.php';

$auth->requerirLogin();
$cid = (int)$_SESSION['cliente_id'];

$dados = json_decode(file_get_contents('php://input'), true);
$fid = $dados['fatura_id'] ?? 0;

// Verificar se a fatura pertence ao cliente
$sql = "SELECT id FROM pagamento_faturas WHERE id = ? AND cliente_id = ? AND status = 'pendente'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $fid, $cid);
$stmt->execute();
if ($stmt->get_result()->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => 'Fatura não encontrada']);
    exit;
}

// Processar cartão (simulado - em produção, chamar API)
$aprovado = true; // Simulação

if ($aprovado) {
    $transacao_id = 'CARD_' . uniqid() . '_' . time();
    
    // Registrar transação
    $sql = "INSERT INTO pagamento_transacoes 
            (cliente_id, fatura_id, transacao_id, gateway, forma_pagamento, valor, status, created_at)
            VALUES (?, ?, ?, 'mercadopago', 'cartao_credito', ?, 'aprovado', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisd", $cid, $fid, $transacao_id, $dados['valor']);
    $stmt->execute();
    
    // Atualizar fatura
    $sql = "UPDATE pagamento_faturas SET status = 'paga', data_pagamento = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fid);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'transacao_id' => $transacao_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Pagamento recusado']);
}