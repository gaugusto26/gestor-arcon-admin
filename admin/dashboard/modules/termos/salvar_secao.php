<?php
require_once '../../includes/header.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$termos_id = (int)$_POST['termos_id'];
$titulo = limparInput($_POST['titulo']);
$icone = limparInput($_POST['icone']);
$conteudo = limparInput($_POST['conteudo']);

if(empty($titulo) || empty($conteudo)) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Título e conteúdo são obrigatórios'];
    header('Location: secoes.php');
    exit;
}

if($id > 0) {
    // Editar
    $stmt = $conn->prepare("UPDATE termos_secoes SET titulo = ?, icone = ?, conteudo = ? WHERE id = ?");
    $stmt->bind_param("sssi", $titulo, $icone, $conteudo, $id);
} else {
    // Criar - pega a última ordem
    $result = $conn->query("SELECT MAX(ordem) as max_ordem FROM termos_secoes WHERE termos_id = $termos_id");
    $row = $result->fetch_assoc();
    $nova_ordem = ($row['max_ordem'] ?? -1) + 1;
    
    $stmt = $conn->prepare("INSERT INTO termos_secoes (termos_id, titulo, icone, conteudo, ordem) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $termos_id, $titulo, $icone, $conteudo, $nova_ordem);
}

if($stmt->execute()) {
    $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Seção salva com sucesso!'];
} else {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Erro ao salvar seção: ' . $conn->error];
}

header('Location: secoes.php');
exit;
?>