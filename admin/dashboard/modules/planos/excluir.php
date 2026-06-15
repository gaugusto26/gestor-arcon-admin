<?php
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

// Verifica se tem ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca o plano pra saber o nome (pro log)
$stmt = $conn->prepare("SELECT nome FROM planos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$plano = $stmt->get_result()->fetch_assoc();

if(!$plano) {
    header('Location: index.php');
    exit;
}

// Exclui (as características são deletadas automático pelo ON DELETE CASCADE)
$stmt = $conn->prepare("DELETE FROM planos WHERE id = ?");
$stmt->bind_param("i", $id);

if($stmt->execute()) {
    // Log
    registrarLog($conn, $_SESSION['admin_id'], "Excluiu plano: {$plano['nome']}");
    
    $_SESSION['mensagem'] = [
        'tipo' => 'sucesso',
        'texto' => 'Plano excluído com sucesso!'
    ];
} else {
    $_SESSION['mensagem'] = [
        'tipo' => 'erro',
        'texto' => 'Erro ao excluir plano: ' . $conn->error
    ];
}

header('Location: index.php');
exit;
?>