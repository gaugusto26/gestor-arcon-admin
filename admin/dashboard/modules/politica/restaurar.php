<?php
require_once '../../includes/header.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['versao'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$versao = $_GET['versao'];

// Busca o conteúdo da versão a ser restaurada
$stmt = $conn->prepare("SELECT conteudo_novo FROM politica_historico WHERE politica_id = ? AND versao = ? ORDER BY data_alteracao DESC LIMIT 1");
$stmt->bind_param("is", $id, $versao);
$stmt->execute();
$historico = $stmt->get_result()->fetch_assoc();

if(!$historico) {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Versão não encontrada'];
    header('Location: historico.php?id=' . $id);
    exit;
}

// Busca política atual
$stmt = $conn->prepare("SELECT * FROM politica_privacidade WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$politica = $stmt->get_result()->fetch_assoc();

if(!$politica) {
    header('Location: index.php');
    exit;
}

// Gera nova versão
$nova_versao = gerarNovaVersao($politica['versao']);

// Salva histórico da restauração
$stmt_hist = $conn->prepare("
    INSERT INTO politica_historico (politica_id, versao, conteudo_antigo, conteudo_novo, alteracoes, alterado_por, data_alteracao)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$alteracoes = "Restaurado para versão $versao";
$stmt_hist->bind_param("issssi", $id, $nova_versao, $politica['conteudo'], $historico['conteudo_novo'], $alteracoes, $_SESSION['admin_id']);
$stmt_hist->execute();

// Atualiza política com o conteúdo restaurado
$stmt = $conn->prepare("
    UPDATE politica_privacidade SET 
        conteudo = ?, 
        versao = ?,
        atualizado_por = ?,
        updated_at = NOW()
    WHERE id = ?
");
$stmt->bind_param("ssii", $historico['conteudo_novo'], $nova_versao, $_SESSION['admin_id'], $id);

if($stmt->execute()) {
    $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => "Versão $versao restaurada como versão $nova_versao!"];
} else {
    $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Erro ao restaurar versão'];
}

header('Location: historico.php?id=' . $id);
exit;
?>