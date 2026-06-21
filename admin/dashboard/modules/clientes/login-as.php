<?php
require_once '../../../../config.php';

precisaLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Cliente inválido para acesso integrado.'];
    header('Location: gerenciar.php');
    exit;
}

$cliente_id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT id, nome, email, username, status
    FROM clientes
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cliente) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Cliente não encontrado.'];
    header('Location: gerenciar.php');
    exit;
}

if ($cliente['status'] !== 'ativo') {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Este cliente não está ativo. Ative o usuário antes de acessar a área do cliente.'];
    header('Location: visualizar.php?id=' . $cliente_id);
    exit;
}

session_regenerate_id(true);

$_SESSION['cliente_id'] = (int) $cliente['id'];
$_SESSION['cliente_nome'] = $cliente['nome'];
$_SESSION['cliente_email'] = $cliente['email'];
$_SESSION['cliente_username'] = $cliente['username'];

$_SESSION['admin_impersonando_cliente'] = true;
$_SESSION['admin_impersonador_id'] = $_SESSION['admin_id'] ?? null;
$_SESSION['admin_impersonador_nome'] = $_SESSION['admin_nome'] ?? 'Administrador';
$_SESSION['admin_impersonacao_started_at'] = date('Y-m-d H:i:s');
$_SESSION['admin_return_url'] = '/admin/dashboard/modules/clientes/visualizar.php?id=' . $cliente_id;

$stmt = $conn->prepare("
    INSERT INTO cliente_logs (cliente_id, acao, ip, user_agent, data_hora)
    VALUES (?, ?, ?, ?, NOW())
");
$acao = 'acesso integrado pelo admin: ' . ($_SESSION['admin_nome'] ?? 'Administrador');
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$stmt->bind_param("isss", $cliente_id, $acao, $ip, $user_agent);
$stmt->execute();
$stmt->close();

if (!empty($_SESSION['admin_id'])) {
    registrarLog($conn, $_SESSION['admin_id'], "Acessou a área do cliente como {$cliente['nome']} (#{$cliente_id})");
}

header('Location: /cliente/dashboard/index.php');
exit;
?>
