<?php
// Configurações do módulo Política de Privacidade

function getPoliticaAtiva() {
    global $conn;
    $result = $conn->query("SELECT * FROM politica_privacidade WHERE status = 'publicado' ORDER BY versao DESC LIMIT 1");
    return $result->fetch_assoc();
}

function getPoliticaById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM politica_privacidade WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getSecoesByPoliticaId($politica_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM politica_secoes WHERE politica_id = ? ORDER BY ordem");
    $stmt->bind_param("i", $politica_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getHistoricoVersoes($politica_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT h.*, a.nome_completo as autor_nome 
        FROM politica_historico h
        LEFT JOIN admin_users a ON h.alterado_por = a.id
        WHERE h.politica_id = ?
        ORDER BY h.data_alteracao DESC
    ");
    $stmt->bind_param("i", $politica_id);
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