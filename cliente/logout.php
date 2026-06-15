<?php
session_start();
require_once '../config.php';

if(isset($_SESSION['cliente_id'])) {
    // Registrar log
    $log = $conn->prepare("INSERT INTO cliente_logs (cliente_id, acao, ip, user_agent, data_hora) VALUES (?, 'logout', ?, ?, NOW())");
    $log->bind_param("iss", $_SESSION['cliente_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    $log->execute();
}

// Destrói a sessão
session_destroy();

// Redireciona para o login
header('Location: login.php');
exit;
?>