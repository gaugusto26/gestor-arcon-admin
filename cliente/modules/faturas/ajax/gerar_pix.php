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

// Gerar PIX
$result = criarPixMercadoPago($fid, $conn);

echo json_encode($result);