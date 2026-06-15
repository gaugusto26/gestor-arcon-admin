<?php
require_once '../../../../config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['erro' => 'ID inválido']);
    exit;
}

$id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT id, nome, email, telefone, celular, cpf_cnpj, rg_ie, 
           empresa, cargo, endereco, cidade, estado, cep
    FROM clientes 
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

if($cliente) {
    echo json_encode($cliente, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['erro' => 'Cliente não encontrado']);
}
?>