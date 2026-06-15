<?php
function getConfigPagamento() {
    global $conn;
    $result = $conn->query("SELECT * FROM pagamento_config WHERE id = 1");
    if($result->num_rows == 0) {
        $conn->query("INSERT INTO pagamento_config (gateway, modo) VALUES ('nenhum', 'teste')");
        $result = $conn->query("SELECT * FROM pagamento_config WHERE id = 1");
    }
    return $result->fetch_assoc();
}

function gerarNumeroFatura() {
    $ano = date('Y');
    $mes = date('m');
    $sequencia = mt_rand(10000, 99999);
    return "FAT-{$ano}{$mes}-{$sequencia}";
}

function registrarTransacao($dados) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO pagamento_transacoes (
            cliente_id, contrato_id, plano_contratado_id, transacao_id, gateway,
            tipo, valor, valor_original, desconto, juros, multa,
            forma_pagamento, parcelas, status, status_detalhe,
            pix_qrcode, pix_copiaecola, pix_expiracao, link_pagamento,
            ip, user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "iiisssdddddsisssssssss",
        $dados['cliente_id'],
        $dados['contrato_id'],
        $dados['plano_contratado_id'],
        $dados['transacao_id'],
        $dados['gateway'],
        $dados['tipo'],
        $dados['valor'],
        $dados['valor_original'],
        $dados['desconto'],
        $dados['juros'],
        $dados['multa'],
        $dados['forma_pagamento'],
        $dados['parcelas'],
        $dados['status'],
        $dados['status_detalhe'],
        $dados['pix_qrcode'],
        $dados['pix_copiaecola'],
        $dados['pix_expiracao'],
        $dados['link_pagamento'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    );
    
    if($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function atualizarStatusTransacao($transacao_id, $status, $detalhe = null) {
    global $conn;
    
    $sql = "UPDATE pagamento_transacoes SET status = ?, status_detalhe = ?, updated_at = NOW()";
    $params = [$status, $detalhe];
    $types = "ss";
    
    if($status == 'aprovado') {
        $sql .= ", data_aprovacao = NOW(), data_pagamento = NOW()";
    }
    
    $sql .= " WHERE transacao_id = ?";
    $params[] = $transacao_id;
    $types .= "s";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

function getEstatisticasPagamentos() {
    global $conn;
    
    $stats = [];
    
    // Totais
    $result = $conn->query("
        SELECT 
            COUNT(*) as total_transacoes,
            SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as total_aprovados,
            SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as total_pendentes,
            SUM(CASE WHEN status = 'recusado' THEN 1 ELSE 0 END) as total_recusados,
            SUM(CASE WHEN status = 'aprovado' THEN valor ELSE 0 END) as valor_total_aprovado,
            SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END) as valor_total_pendente
        FROM pagamento_transacoes
    ");
    $stats['totais'] = $result->fetch_assoc();
    
    // Por gateway
    $result = $conn->query("
        SELECT gateway, COUNT(*) as total, SUM(valor) as total_valor
        FROM pagamento_transacoes
        WHERE status = 'aprovado'
        GROUP BY gateway
    ");
    while($row = $result->fetch_assoc()) {
        $stats['gateway_' . $row['gateway']] = $row;
    }
    
    // Por forma de pagamento
    $result = $conn->query("
        SELECT forma_pagamento, COUNT(*) as total, SUM(valor) as total_valor
        FROM pagamento_transacoes
        WHERE status = 'aprovado'
        GROUP BY forma_pagamento
    ");
    while($row = $result->fetch_assoc()) {
        $stats['forma_' . $row['forma_pagamento']] = $row;
    }
    
    // Últimas 24h
    $result = $conn->query("
        SELECT COUNT(*) as total, SUM(valor) as total_valor
        FROM pagamento_transacoes
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND status = 'aprovado'
    ");
    $stats['ultimas_24h'] = $result->fetch_assoc();
    
    // Este mês
    $result = $conn->query("
        SELECT COUNT(*) as total, SUM(valor) as total_valor
        FROM pagamento_transacoes
        WHERE MONTH(created_at) = MONTH(NOW()) 
        AND YEAR(created_at) = YEAR(NOW())
        AND status = 'aprovado'
    ");
    $stats['mes_atual'] = $result->fetch_assoc();
    
    return $stats;
}
?>