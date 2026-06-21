<?php
require_once '../config.php';

if(isset($_SESSION['cliente_id'])) {
    // Registrar log
    $log = $conn->prepare("INSERT INTO cliente_logs (cliente_id, acao, ip, user_agent, data_hora) VALUES (?, 'logout', ?, ?, NOW())");
    $log->bind_param("iss", $_SESSION['cliente_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    $log->execute();
}

if (!empty($_SESSION['admin_impersonando_cliente'])) {
    $return_url = $_SESSION['admin_return_url'] ?? '/admin/dashboard/modules/clientes/gerenciar.php';

    unset(
        $_SESSION['cliente_id'],
        $_SESSION['cliente_nome'],
        $_SESSION['cliente_email'],
        $_SESSION['cliente_username'],
        $_SESSION['admin_impersonando_cliente'],
        $_SESSION['admin_impersonador_id'],
        $_SESSION['admin_impersonador_nome'],
        $_SESSION['admin_impersonacao_started_at'],
        $_SESSION['admin_return_url']
    );

    header('Location: ' . $return_url);
    exit;
}

unset(
    $_SESSION['cliente_id'],
    $_SESSION['cliente_nome'],
    $_SESSION['cliente_email'],
    $_SESSION['cliente_username']
);

// Redireciona para o login
header('Location: login.php');
exit;
?>
