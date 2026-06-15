<?php
require_once '../../../config.php';
require_once 'config.php';

session_start();

if (!isset($_SESSION['cliente_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

$cid = (int)$_SESSION['cliente_id'];
$doc_id = (int)$_GET['id'];

$doc = getDocumentoDetalhes($doc_id, $cid);

if (!$doc) {
    echo json_encode(['sucesso' => false, 'erro' => 'Documento não encontrado']);
    exit;
}

// Formata dados
$doc['sucesso'] = true;
$doc['icone'] = getIconePorTipo($doc['tipo']);
$doc['cor'] = getCorPorTipo($doc['tipo']);
$doc['tipo_nome'] = getNomeTipo($doc['tipo']);
$doc['tamanho'] = formatarTamanhoArquivo($doc['arquivo_tamanho']);
$doc['data_documento'] = $doc['data_documento'] ?? null;
$doc['data_vencimento'] = $doc['data_vencimento'] ?? null;
$doc['valor'] = $doc['valor'] ? (float)$doc['valor'] : null;

echo json_encode($doc);
?>