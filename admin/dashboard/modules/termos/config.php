<?php
// Configurações do módulo Termos de Uso

function getTermosAtivos() {
    global $conn;
    $result = $conn->query("SELECT * FROM termos_uso WHERE status = 'publicado' ORDER BY versao DESC LIMIT 1");
    return $result->fetch_assoc();
}

function getTermosById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM termos_uso WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getSecoesByTermosId($termos_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM termos_secoes WHERE termos_id = ? ORDER BY ordem");
    $stmt->bind_param("i", $termos_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getHistoricoVersoes($termos_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT h.*, a.nome_completo as autor_nome 
        FROM termos_historico h
        LEFT JOIN admin_users a ON h.alterado_por = a.id
        WHERE h.termos_id = ?
        ORDER BY h.data_alteracao DESC
    ");
    $stmt->bind_param("i", $termos_id);
    $stmt->execute();
    return $stmt->get_result();
}

function gerarNovaVersao($versao_atual) {
    $partes = explode('.', $versao_atual);
    if(count($partes) == 2) {
        $partes[1] = (int)$partes[1] + 1;
        return implode('.', $partes);
    }
    return '1.1';
}
?>