<?php
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

require_once '../../../../config.php';
precisaLogin();

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT titulo FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if($post) {
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