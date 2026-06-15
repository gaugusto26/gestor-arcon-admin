<?php
require_once __DIR__ . '/../config.php';

// ═══════════════════════════════════════════════
// AUTENTICAÇÃO
// ═══════════════════════════════════════════════

function clienteLogado() {
    return isset($_SESSION['cliente_id']);
}

function precisaLoginCliente() {
    if (!clienteLogado()) {
        header('Location: login.php');
        exit;
    }
}

// ═══════════════════════════════════════════════
// CLIENTE
// ═══════════════════════════════════════════════

function getCliente() {
    global $conn;
    $id = (int)$_SESSION['cliente_id'];
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

// ═══════════════════════════════════════════════
// SISTEMAS (contratos assinados)
// ═══════════════════════════════════════════════

function getSistemasCliente($cliente_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT s.id,
               s.id as sistema_id,
               s.status,
               s.percentual_concluido,
               s.proxima_etapa,
               s.ultima_atualizacao,
               s.data_inicio,
               s.created_at as data_contrato,
               s.nome_sistema,
               c.id as contrato_id,
               c.numero_contrato,
               c.titulo as contrato_titulo,
               c.valor_total,
               c.valor_mensal,
               c.data_assinatura,
               pc.nome_plano,
               pc.descricao as plano_descricao
        FROM cliente_sistemas s
        LEFT JOIN contratos c ON c.plano_contratado_id = s.plano_contratado_id 
                              AND c.cliente_id = s.cliente_id
                              AND c.status = 'assinado'
        LEFT JOIN planos_contratados pc ON s.plano_contratado_id = pc.id
        WHERE s.cliente_id = ?
        ORDER BY s.created_at DESC
    ");

    if (!$stmt) return false;

    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result;
}

function getSistemaDetalhes($contrato_id, $cliente_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT c.*, 
               c.created_at as data_contrato,
               pc.nome_plano,
               pc.descricao as plano_descricao,
               pc.valor_plano,
               pc.valor_mensal
        FROM contratos c
        LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
        WHERE c.id = ? 
        AND c.cliente_id = ?
        AND c.status = 'assinado'
    ");

    if (!$stmt) return null;

    $stmt->bind_param("ii", $contrato_id, $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result;
}

function calcularProgressoSistema($data_inicio, $dias_estimados = 30) {
    if (!$data_inicio || $dias_estimados <= 0) return 0;

    try {
        $inicio = new DateTime($data_inicio);
        $hoje   = new DateTime();

        if ($hoje < $inicio) return 0;

        $dias_passados = (int) $inicio->diff($hoje)->days;
        return min(100, (int) round(($dias_passados / $dias_estimados) * 100));
    } catch (Exception $e) {
        return 0;
    }
}

// ═══════════════════════════════════════════════
// CONTRATOS
// ═══════════════════════════════════════════════

function getContratosCliente($cliente_id, $status = null) {
    global $conn;

    $sql = "SELECT c.*, 
                   pc.nome_plano,
                   pc.valor_mensal,
                   (SELECT COUNT(*) FROM cliente_assinaturas_contratos WHERE contrato_id = c.id) as assinado
            FROM contratos c
            LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
            WHERE c.cliente_id = ?";

    if ($status) {
        $sql .= " AND c.status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $cliente_id, $status);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cliente_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function getContrato($contrato_id, $cliente_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT c.*, 
               pc.nome_plano,
               pc.valor_mensal,
               (SELECT id FROM cliente_assinaturas WHERE cliente_id = ? LIMIT 1) as tem_assinatura,
               (SELECT id FROM cliente_assinaturas_contratos WHERE contrato_id = c.id) as ja_assinado
        FROM contratos c
        LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
        WHERE c.id = ? AND c.cliente_id = ?
    ");

    $stmt->bind_param("iii", $cliente_id, $contrato_id, $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function contratoAssinado($contrato_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM cliente_assinaturas_contratos WHERE contrato_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $contrato_id);
    $stmt->execute();
    $rows = $stmt->get_result()->num_rows;
    $stmt->close();
    return $rows > 0;
}

// ═══════════════════════════════════════════════
// ASSINATURAS
// ═══════════════════════════════════════════════

function getAssinaturaCliente($cliente_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM cliente_assinaturas WHERE cliente_id = ? ORDER BY created_at DESC LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function clienteTemAssinatura($cliente_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM cliente_assinaturas WHERE cliente_id = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $rows = $stmt->get_result()->num_rows;
    $stmt->close();
    return $rows > 0;
}

function countContratosAssinados($cliente_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM cliente_assinaturas_contratos cac
        JOIN cliente_assinaturas ca ON cac.assinatura_id = ca.id
        WHERE ca.cliente_id = ?
    ");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($result['total'] ?? 0);
}

// ═══════════════════════════════════════════════
// FATURAS
// ═══════════════════════════════════════════════

function getFaturasCliente($cliente_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT cf.*, pc.nome_plano
        FROM cliente_faturas cf
        JOIN planos_contratados pc ON cf.plano_contratado_id = pc.id
        WHERE cf.cliente_id = ?
        ORDER BY cf.data_vencimento DESC
    ");
    if (!$stmt) return false;
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}
?>