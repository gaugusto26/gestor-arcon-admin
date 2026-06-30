<?php
/**
 * Arcon Sync API — retorna dados de assinatura de um cliente pelo ID
 * Autenticação: Header X-Arcon-Key com chave definida em variável de ambiente
 *
 * GET /admin/api/arcon-sync.php?cliente_id=123
 * GET /admin/api/arcon-sync.php?cliente_id=123&plano_id=456
 * GET /admin/api/arcon-sync.php  (sem params = lista todos clientes com sistemas Arcon)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: X-Arcon-Key, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autenticação por API key
$apiKey = getenv('ARCON_SYNC_KEY') ?: '';
$requestKey = $_SERVER['HTTP_X_ARCON_KEY'] ?? $_GET['key'] ?? '';
if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['erro' => 'ARCON_SYNC_KEY não configurada', 'code' => 500]);
    exit;
}


if ($requestKey !== $apiKey) {
    http_response_code(401);
    echo json_encode(['erro' => 'Chave de API inválida', 'code' => 401]);
    exit;
}

require_once '../../../config.php';
require_once 'saas-core.php';
saasBoot($conn);

// Mapeamento de status de assinatura do Gestor → Arcon
function mapearStatus($statusGestor) {
    $map = [
        'ativo'     => 'ativo',
        'ativa'     => 'ativo',
        'pago'      => 'ativo',
        'trial'     => 'ativo',
        'pendente'  => 'pendente',
        'atrasado'  => 'pendente',
        'suspenso'  => 'suspenso',
        'bloqueado' => 'suspenso',
        'cancelado' => 'cancelado',
        'inativo'   => 'cancelado',
        'encerrado' => 'cancelado',
        'concluido' => 'concluido',
    ];
    return $map[strtolower($statusGestor)] ?? 'pendente';
}

function buscarCliente($conn, $clienteId) {
    $stmt = $conn->prepare("
        SELECT c.id, c.nome, c.email, c.status as cliente_status,
               pc.id as plano_id, pc.status as contrato_status,
               p.nome as plano_nome, p.slug as plano_slug, p.preco, p.periodo,
               pc.data_inicio, pc.data_proxima_fatura, pc.created_at as contrato_criado
        FROM clientes c
        LEFT JOIN planos_contratados pc ON pc.cliente_id = c.id
        LEFT JOIN planos p ON pc.plano_id = p.id
        WHERE c.id = ?
        ORDER BY pc.created_at DESC
        LIMIT 1
    ");
    if (!$stmt) {
        // tabela planos_contratados pode não existir, fallback simples
        $stmt = $conn->prepare("SELECT id, nome, email, status as cliente_status FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $clienteId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;
        $assinaturaSaas = saasGetAssinaturaClienteProduto($conn, (int)$row['id'], 'arcon');
        return [
            'cliente_id'          => (int)$row['id'],
            'nome'                => $row['nome'],
            'email'               => $row['email'],
            'assinatura_status'   => $assinaturaSaas['status'] ?? mapearStatus($row['cliente_status'] ?? 'pendente'),
            'plano'               => $assinaturaSaas['plano_slug'] ?? 'free',
            'plano_id'            => null,
            'plano_nome'          => null,
            'preco'               => null,
            'periodo'             => null,
            'data_vencimento'     => null,
            'sincronizado_em'     => date('Y-m-d\TH:i:s\Z'),
        ];
    }
    $stmt->bind_param("i", $clienteId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return null;
    $assinaturaSaas = saasGetAssinaturaClienteProduto($conn, (int)$row['id'], 'arcon');
    return [
        'cliente_id'        => (int)$row['id'],
        'nome'              => $row['nome'],
        'email'             => $row['email'],
        'assinatura_status' => $assinaturaSaas['status'] ?? mapearStatus($row['contrato_status'] ?? $row['cliente_status'] ?? 'pendente'),
        'plano'             => $assinaturaSaas['plano_slug'] ?? $row['plano_slug'] ?? 'free',
        'plano_id'          => $row['plano_id'] ? (int)$row['plano_id'] : null,
        'plano_nome'        => $row['plano_nome'],
        'preco'             => $row['preco'] ? (float)$row['preco'] : null,
        'periodo'           => $row['periodo'],
        'data_vencimento'   => $row['data_proxima_fatura'],
        'sincronizado_em'   => date('Y-m-d\TH:i:s\Z'),
    ];
}

// Roteamento
$clienteId = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

if ($clienteId > 0) {
    $data = buscarCliente($conn, $clienteId);
    if (!$data) {
        http_response_code(404);
        echo json_encode(['erro' => 'Cliente não encontrado', 'cliente_id' => $clienteId]);
        exit;
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Sem cliente_id: retorna lista de todos clientes
$result = $conn->query("SELECT id, nome, email, status FROM clientes ORDER BY id");
$lista = [];
while ($row = $result->fetch_assoc()) {
    $lista[] = [
        'cliente_id'        => (int)$row['id'],
        'nome'              => $row['nome'],
        'email'             => $row['email'],
        'assinatura_status' => mapearStatus($row['status'] ?? 'pendente'),
    ];
}
echo json_encode(['clientes' => $lista, 'total' => count($lista)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
