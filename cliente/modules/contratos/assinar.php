<?php
session_start();
require_once '../../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /cliente/modules/contratos/index.php');
    exit;
}

$cid = (int)$_SESSION['cliente_id'];
$contrato_id = (int)$_GET['id'];

// Verifica se contrato existe e pertence ao cliente
$contrato = getContrato($contrato_id, $cid);

if (!$contrato) {
    header('Location: /cliente/modules/contratos/index.php');
    exit;
}

// Verifica se já foi assinado
$check = $conn->prepare("SELECT id FROM cliente_assinaturas_contratos WHERE contrato_id = ?");
$check->bind_param("i", $contrato_id);
$check->execute();
$ja_assinado = $check->get_result()->num_rows > 0;
$check->close();

if ($ja_assinado) {
    $_SESSION['msg'] = ['tipo' => 'erro', 'texto' => 'Este contrato já foi assinado.'];
    header("Location: /cliente/modules/contratos/visualizar.php?id=$contrato_id");
    exit;
}

// Busca assinatura do cliente
$stmt_ass = $conn->prepare("SELECT * FROM cliente_assinaturas WHERE cliente_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt_ass->bind_param("i", $cid);
$stmt_ass->execute();
$assinatura = $stmt_ass->get_result()->fetch_assoc();
$stmt_ass->close();

if (!$assinatura) {
    $_SESSION['msg'] = ['tipo' => 'erro', 'texto' => 'Você precisa criar uma assinatura digital primeiro.'];
    header("Location: /cliente/modules/assinatura/index.php");
    exit;
}

// Registra a assinatura
$ip            = $_SERVER['REMOTE_ADDR'];
$user_agent    = $_SERVER['HTTP_USER_AGENT'];
$data_assinatura = date('Y-m-d H:i:s');

$stmt = $conn->prepare("
    INSERT INTO cliente_assinaturas_contratos 
    (contrato_id, assinatura_id, ip, user_agent, data_assinatura) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iisss", $contrato_id, $assinatura['id'], $ip, $user_agent, $data_assinatura);

if ($stmt->execute()) {
    // Atualiza status do contrato
    $upd = $conn->prepare("UPDATE contratos SET status = 'assinado', data_assinatura = ? WHERE id = ? AND cliente_id = ?");
    $upd->bind_param("sii", $data_assinatura, $contrato_id, $cid);
    $upd->execute();
    $upd->close();

    // ── Cria registro em cliente_sistemas ──────────────────────────
    $check_cs = $conn->prepare("SELECT id FROM cliente_sistemas WHERE cliente_id = ? AND plano_contratado_id = ?");
    $check_cs->bind_param("ii", $cid, $contrato['plano_contratado_id']);
    $check_cs->execute();
    $ja_existe = $check_cs->get_result()->num_rows > 0;
    $check_cs->close();

    if (!$ja_existe) {
        $ins = $conn->prepare("
            INSERT INTO cliente_sistemas 
                (cliente_id, plano_contratado_id, nome_sistema, status, percentual_concluido, data_inicio, created_at)
            VALUES (?, ?, ?, 'aguardando_inicio', 0, NOW(), NOW())
        ");
        $nome_sistema = $contrato['titulo'] ?: 'Sistema contratado';
        $ins->bind_param("iis", $cid, $contrato['plano_contratado_id'], $nome_sistema);
        $ins->execute();
        $ins->close();
    }
    // ───────────────────────────────────────────────────────────────

    $_SESSION['msg'] = ['tipo' => 'sucesso', 'texto' => 'Contrato assinado com sucesso!'];
} else {
    $_SESSION['msg'] = ['tipo' => 'erro', 'texto' => 'Erro ao assinar contrato: ' . $conn->error];
}

$stmt->close();
header("Location: /cliente/modules/contratos/visualizar.php?id=$contrato_id");
exit;
?>