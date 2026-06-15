<?php
require_once '../../includes/header.php';

// Verifica se usuário está logado
precisaLogin();

// Pega os filtros da URL (se houver)
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$filtro_confirmado = isset($_GET['confirmado']) ? (int)$_GET['confirmado'] : '';
$filtro_origem = isset($_GET['origem']) ? limparInput($_GET['origem']) : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// Monta a query com os mesmos filtros da página de inscritos
$sql_where = "WHERE 1=1";
$params = [];
$types = "";

if(!empty($filtro_status)) {
    $sql_where .= " AND status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if($filtro_confirmado !== '') {
    $sql_where .= " AND confirmado = ?";
    $params[] = $filtro_confirmado;
    $types .= "i";
}

if(!empty($filtro_origem)) {
    $sql_where .= " AND origem = ?";
    $params[] = $filtro_origem;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (nome LIKE ? OR email LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ss";
}

// Busca todos os inscritos (sem limite)
$sql = "SELECT * FROM newsletter_inscritos $sql_where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$inscritos = $stmt->get_result();

// Nome do arquivo
$filename = 'newsletter_inscritos_' . date('Y-m-d_H-i') . '.csv';

// Headers para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Cria o output
$output = fopen('php://output', 'w');

// BOM para UTF-8 (resolve acentos no Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos das colunas
$headers = [
    'ID',
    'Nome',
    'E-mail',
    'Status',
    'Confirmado',
    'Data Confirmação',
    'Origem',
    'IP',
    'Data de Inscrição'
];
fputcsv($output, $headers, ';');

// Dados
while($insc = $inscritos->fetch_assoc()) {
    $row = [
        $insc['id'],
        $insc['nome'],
        $insc['email'],
        $insc['status'],
        $insc['confirmado'] ? 'Sim' : 'Não',
        $insc['data_confirmacao'] ? date('d/m/Y H:i', strtotime($insc['data_confirmacao'])) : '',
        $insc['origem'],
        $insc['ip'],
        date('d/m/Y H:i', strtotime($insc['created_at']))
    ];
    fputcsv($output, $row, ';');
}

fclose($output);
exit;
?>