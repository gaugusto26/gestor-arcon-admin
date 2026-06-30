<?php
/**
 * Endpoint AJAX — ações de integração Arcon
 * POST /admin/api/arcon-action.php
 * Requer sessão admin ativa
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config.php';
require_once 'saas-core.php';
require_once 'arcon-push.php';

if (!isLogado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Não autorizado']);
    exit;
}

// Garante colunas de integração
$conn->query("ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS arcon_empresa_id   bigint DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS arcon_plano_saas   varchar(50) DEFAULT 'free',
    ADD COLUMN IF NOT EXISTS arcon_status       varchar(30) DEFAULT 'pendente',
    ADD COLUMN IF NOT EXISTS arcon_sync_em      datetime DEFAULT NULL
");

$arcon   = new ArconPush();
saasBoot($conn);
$acao    = $_POST['acao'] ?? '';
$clienteId = (int)($_POST['cliente_id'] ?? 0);

if (!$clienteId) {
    echo json_encode(['ok' => false, 'msg' => 'cliente_id obrigatório']); exit;
}

$clienteRow = $conn->query("SELECT * FROM clientes WHERE id = $clienteId")->fetch_assoc();
if (!$clienteRow) {
    echo json_encode(['ok' => false, 'msg' => 'Cliente não encontrado']); exit;
}

// Mapeia plano_saas → slug que o Arcon entende
function slugSaas(string $plano): string {
    $map = [
        'free'         => 'free',
        'basico'       => 'basico',
        'profissional' => 'profissional',
        'empresarial'  => 'empresarial',
        'enterprise'   => 'enterprise',
    ];
    return $map[strtolower($plano)] ?? 'free';
}

function mapStatus(string $s): string {
    $m = ['ativo'=>'ativo','ativa'=>'ativo','pago'=>'ativo','trial'=>'ativo',
          'pendente'=>'pendente','suspenso'=>'suspenso','cancelado'=>'cancelado','inativo'=>'cancelado'];
    return $m[strtolower($s)] ?? 'pendente';
}

// ── Vincular empresa pelo e-mail ──────────────────────────────
if ($acao === 'vincular') {
    $planoSaas = limparInput($_POST['plano_saas'] ?? 'free');
    $statusAs  = limparInput($_POST['status'] ?? 'pendente');

    // Busca plano contratado mais recente para pegar o ID
    $pcRow = $conn->query("SELECT id FROM planos_contratados WHERE cliente_id=$clienteId ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    $pcId  = $pcRow ? (int)$pcRow['id'] : 0;

    $res = $arcon->vincularEmpresaPorEmail(
        $clienteRow['email'],
        $clienteId,
        $pcId,
        slugSaas($planoSaas),
        mapStatus($statusAs)
    );

    if ($res['ok']) {
        // Captura id da empresa no Supabase
        $sbRes = $arcon->buscarEmpresaPorEmail($clienteRow['email']);
        $empId = $sbRes['ok'] && !empty($sbRes['data'][0]['id']) ? $sbRes['data'][0]['id'] : null;
        if (!$empId) {
            echo json_encode(['ok' => false, 'msg' => 'Empresa não encontrada no Arcon para este e-mail.']);
            exit;
        }


        $conn->query("UPDATE clientes SET arcon_empresa_id=$empId, arcon_plano_saas='$planoSaas', arcon_status='$statusAs', arcon_sync_em=NOW() WHERE id=$clienteId");
        saasUpsertAssinatura($conn, $clienteId, 'arcon', [
            'plano_slug' => slugSaas($planoSaas),
            'status' => $statusAs,
            'external_empresa_id' => $empId,
            'external_cliente_id' => $clienteId,
            'origem' => 'admin',
            'evento' => 'arcon_vinculado',
            'resultado' => $res,
        ]);
        echo json_encode(['ok' => true, 'msg' => "Empresa vinculada no Arcon!", 'empresa_id' => $empId]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Erro: ' . $res['msg']]);
    }
    exit;
}

// ── Ativar assinatura ─────────────────────────────────────────
if ($acao === 'ativar') {
    $planoSaas = limparInput($_POST['plano_saas'] ?? ($clienteRow['arcon_plano_saas'] ?? 'free'));
    $pcRow = $conn->query("SELECT id FROM planos_contratados WHERE cliente_id=$clienteId AND status='ativo' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    $pcId  = $pcRow ? (int)$pcRow['id'] : 0;

    $res = $arcon->atualizarEmpresa($clienteId, [
        'assinatura_status'          => 'ativo',
        'plano'                      => slugSaas($planoSaas),
        'gestor_plano_contratado_id' => $pcId ?: null,
        'assinatura_cliente'         => $clienteRow['nome'],
    ]);

    if ($res['ok']) {
        $conn->query("UPDATE clientes SET arcon_status='ativo', arcon_plano_saas='$planoSaas', arcon_sync_em=NOW(), status='ativo' WHERE id=$clienteId");
        saasUpsertAssinatura($conn, $clienteId, 'arcon', [
            'plano_slug' => slugSaas($planoSaas),
            'status' => 'ativo',
            'external_cliente_id' => $clienteId,
            'origem' => 'admin',
            'evento' => 'assinatura_ativada',
            'resultado' => $res,
        ]);
        echo json_encode(['ok' => true, 'msg' => "Assinatura ativada no Arcon!"]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Erro: ' . $res['msg']]);
    }
    exit;
}

// ── Suspender ─────────────────────────────────────────────────
if ($acao === 'suspender') {
    $res = $arcon->atualizarEmpresa($clienteId, ['assinatura_status' => 'suspenso']);
    if ($res['ok']) {
        $conn->query("UPDATE clientes SET arcon_status='suspenso', arcon_sync_em=NOW() WHERE id=$clienteId");
        // Bloqueia profiles no Arcon
        $arcon->atualizarAtivoEmpresa($clienteId, false);
        saasUpsertAssinatura($conn, $clienteId, 'arcon', [
            'plano_slug' => $clienteRow['arcon_plano_saas'] ?? 'free',
            'status' => 'suspenso',
            'external_cliente_id' => $clienteId,
            'origem' => 'admin',
            'evento' => 'assinatura_suspensa',
            'resultado' => $res,
        ]);
        echo json_encode(['ok' => true, 'msg' => "Assinatura suspensa no Arcon."]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Erro: ' . $res['msg']]);
    }
    exit;
}

// ── Cancelar ──────────────────────────────────────────────────
if ($acao === 'cancelar') {
    $res = $arcon->atualizarEmpresa($clienteId, ['assinatura_status' => 'cancelado', 'plano' => 'free']);
    if ($res['ok']) {
        $conn->query("UPDATE clientes SET arcon_status='cancelado', arcon_plano_saas='free', arcon_sync_em=NOW() WHERE id=$clienteId");
        $arcon->atualizarAtivoEmpresa($clienteId, false);
        saasUpsertAssinatura($conn, $clienteId, 'arcon', [
            'plano_slug' => 'free',
            'status' => 'cancelado',
            'external_cliente_id' => $clienteId,
            'origem' => 'admin',
            'evento' => 'assinatura_cancelada',
            'resultado' => $res,
        ]);
        echo json_encode(['ok' => true, 'msg' => "Assinatura cancelada no Arcon."]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Erro: ' . $res['msg']]);
    }
    exit;
}

// ── Sync (pull status do Arcon) ───────────────────────────────
if ($acao === 'sync') {
    $apiKey = getenv('ARCON_SYNC_KEY') ?: '';
    if ($apiKey === '') {
        echo json_encode(['ok' => false, 'msg' => 'ARCON_SYNC_KEY não configurada']);
        exit;
    }
    $siteUrl = rtrim(getenv('SITE_URL') ?: 'http://localhost', '/');
    $url = "$siteUrl/admin/api/arcon-sync.php?cliente_id=$clienteId&key=$apiKey";
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6]);
    $body = curl_exec($ch); curl_close($ch);
    $data = json_decode($body, true);
    if ($data && isset($data['assinatura_status'])) {
        $st = $data['assinatura_status'];
        $pl = $data['plano'] ?? 'free';
        $conn->query("UPDATE clientes SET arcon_status='$st', arcon_plano_saas='$pl', arcon_sync_em=NOW() WHERE id=$clienteId");
        saasUpsertAssinatura($conn, $clienteId, 'arcon', [
            'plano_slug' => $pl,
            'status' => $st,
            'external_cliente_id' => $clienteId,
            'origem' => 'sync',
            'evento' => 'assinatura_sincronizada',
            'resultado' => $data,
        ]);
        echo json_encode(['ok' => true, 'msg' => "Sincronizado: $st · plano $pl", 'data' => $data]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Falha ao buscar status do Arcon', 'raw' => $body]);
    }
    exit;
}

// ── Atualizar plano SaaS ──────────────────────────────────────
if ($acao === 'atualizar_plano') {
    $planoSaas = limparInput($_POST['plano_saas'] ?? 'free');
    $res = $arcon->atualizarEmpresa($clienteId, ['plano' => slugSaas($planoSaas)]);
    $conn->query("UPDATE clientes SET arcon_plano_saas='$planoSaas', arcon_sync_em=NOW() WHERE id=$clienteId");
    saasUpsertAssinatura($conn, $clienteId, 'arcon', [
        'plano_slug' => slugSaas($planoSaas),
        'status' => $clienteRow['arcon_status'] ?? 'pendente',
        'external_cliente_id' => $clienteId,
        'origem' => 'admin',
        'evento' => $res['ok'] ? 'plano_alterado' : 'plano_alteracao_falhou',
        'resultado' => $res,
        'ok' => $res['ok'],
    ]);
    echo json_encode(['ok' => $res['ok'], 'msg' => $res['ok'] ? "Plano atualizado para $planoSaas no Arcon." : 'Atualizado localmente. ' . $res['msg']]);
    exit;
}

echo json_encode(['ok' => false, 'msg' => "Ação '$acao' desconhecida"]);
