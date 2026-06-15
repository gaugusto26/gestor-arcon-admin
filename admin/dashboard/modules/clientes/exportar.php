<?php
require_once '../../includes/header.php';

precisaLogin();

$filtro_tipo = isset($_GET['tipo']) ? limparInput($_GET['tipo']) : '';
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

$sql_where = "WHERE 1=1";
$params = [];
$types = "";

if(!empty($filtro_tipo)) {
    $sql_where .= " AND tipo = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

if(!empty($filtro_status)) {
    $sql_where .= " AND status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (nome LIKE ? OR email LIKE ? OR empresa LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "sss";
}

$sql = "SELECT * FROM clientes $sql_where ORDER BY nome";
$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$clientes = $stmt->get_result();

$filename = 'clientes_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

$headers = [
    'ID', 'Nome', 'Email', 'Telefone', 'Celular', 'CPF/CNPJ', 'RG/IE',
    'Empresa', 'Cargo', 'Endereço', 'Cidade', 'Estado', 'CEP',
    'Usuário', 'Tipo', 'Status', 'Data Cadastro'
];
fputcsv($output, $headers, ';');

while($cli = $clientes->fetch_assoc()) {
    $row = [
        $cli['id'],
        $cli['nome'],
        $cli['email'],
        $cli['telefone'],
        $cli['celular'],
        $cli['cpf_cnpj'],
        $cli['rg_ie'],
        $cli['empresa'],
        $cli['cargo'],
        $cli['endereco'],
        $cli['cidade'],
        $cli['estado'],
        $cli['cep'],
        $cli['username'],
        $cli['tipo'],
        $cli['status'],
        date('d/m/Y H:i', strtotime($cli['created_at']))
    ];
    fputcsv($output, $row, ';');
}

fclose($output);
exit;
?>