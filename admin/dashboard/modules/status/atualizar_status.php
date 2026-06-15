<?php
require_once '../../../../config.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}

$sistema_id      = (int)$_POST['sistema_id'];
$status_anterior = limparInput($_POST['status_anterior']);
$status_novo     = limparInput($_POST['status_novo']);
$observacao      = limparInput($_POST['observacao']);
$percentual      = (int)$_POST['percentual_concluido'];
$proxima_etapa   = limparInput($_POST['proxima_etapa']);

if (empty($status_novo)) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Selecione um status'];
    header('Location: index.php');
    exit;
}

// Busca dados atuais do sistema
$stmt = $conn->prepare("SELECT * FROM cliente_sistemas WHERE id = ?");
$stmt->bind_param("i", $sistema_id);
$stmt->execute();
$sistema = $stmt->get_result()->fetch_assoc();

if (!$sistema) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Sistema não encontrado'];
    header('Location: index.php');
    exit;
}

// Atualiza o sistema
$sql = "UPDATE cliente_sistemas SET 
            status = ?,
            percentual_concluido = ?,
            proxima_etapa = ?,
            ultima_atualizacao = NOW()";

$params = [$status_novo, $percentual, $proxima_etapa];
$types  = "sis";

if (!empty($observacao)) {
    $sql     .= ", feedback_cliente = CONCAT(IFNULL(feedback_cliente, ''), '\n[', NOW(), '] ', ?)";
    $params[] = $observacao;
    $types   .= "s";
}

$sql     .= " WHERE id = ?";
$params[] = $sistema_id;
$types   .= "i";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Erro na query: ' . $conn->error];
    header('Location: index.php');
    exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    registrarHistorico($sistema_id, $status_anterior, $status_novo, $observacao);
    $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Status atualizado com sucesso!'];
} else {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Erro ao atualizar: ' . $stmt->error];
}

header('Location: index.php');
exit;