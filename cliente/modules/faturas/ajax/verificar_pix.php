<?php
require_once '../../../includes/auth.php';
require_once '../../../includes/functions/pagamentos.php';

$auth->requerirLogin();
$cid = (int)$_SESSION['cliente_id'];

$transacao_id = $_GET['transacao'] ?? '';

// Buscar status da transação
$sql = "SELECT t.status FROM pagamento_transacoes t
        JOIN pagamento_faturas f ON f.id = t.fatura_id
        WHERE t.transacao_id = ? AND f.cliente_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $transacao_id, $cid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['status' => 'pendente']);
}