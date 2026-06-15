<?php
require_once '../../includes/header.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca título pro log
$stmt = $conn->prepare("SELECT titulo FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if($post) {
    // Exclui (comentários e curtidas são deletados automaticamente pelo ON DELETE CASCADE)
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        registrarLog($conn, $_SESSION['admin_id'], "Excluiu post: {$post['titulo']}");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Post excluído com sucesso!'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Erro ao excluir post: ' . $conn->error];
    }
}

header('Location: index.php');
exit;
?>