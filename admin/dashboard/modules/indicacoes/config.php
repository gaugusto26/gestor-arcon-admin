<?php
function gerarCodigoIndicacao($empresa, $cliente_id) {
    // Remove acentos e caracteres especiais
    $empresa = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($empresa));
    if(empty($empresa)) {
        $empresa = 'cliente';
    }
    // Pega as primeiras letras + ID do cliente
    $codigo = substr($empresa, 0, 8) . $cliente_id;
    return strtoupper($codigo);
}

function getEstatisticasIndicacoes() {
    global $conn;
    
    $stats = [];
    
    // Total de indicações
    $result = $conn->query("SELECT COUNT(*) as total FROM indicacoes");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Indicações por status
    $result = $conn->query("
        SELECT status, COUNT(*) as total 
        FROM indicacoes 
        GROUP BY status
    ");
    while($row = $result->fetch_assoc()) {
        $stats['status_' . $row['status']] = $row['total'];
    }
    
    // Total de clientes que indicaram
    $result = $conn->query("SELECT COUNT(DISTINCT indicador_id) as total FROM indicacoes");
    $stats['total_indicadores'] = $result->fetch_assoc()['total'];
    
    // Total de descontos concedidos
    $result = $conn->query("SELECT SUM(desconto_aplicado) as total FROM indicacoes WHERE status = 'convertido'");
    $stats['total_descontos'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Média de indicações por cliente
    $stats['media_indicacoes'] = $stats['total_indicadores'] > 0 
        ? round($stats['total'] / $stats['total_indicadores'], 1) 
        : 0;
    
    // Top indicadores
    $stats['top_indicadores'] = $conn->query("
        SELECT c.nome, c.empresa, COUNT(i.id) as total_indicacoes,
               SUM(CASE WHEN i.status = 'convertido' THEN 1 ELSE 0 END) as convertidas
        FROM clientes c
        JOIN indicacoes i ON c.id = i.indicador_id
        GROUP BY c.id
        ORDER BY convertidas DESC, total_indicacoes DESC
        LIMIT 5
    ");
    
    // Indicações por mês (últimos 6 meses)
    $stats['indicacoes_mes'] = $conn->query("
        SELECT DATE_FORMAT(data_indicacao, '%Y-%m') as mes,
               COUNT(*) as total,
               SUM(CASE WHEN status = 'convertido' THEN 1 ELSE 0 END) as convertidas
        FROM indicacoes
        WHERE data_indicacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_indicacao, '%Y-%m')
        ORDER BY mes DESC
    ");
    
    return $stats;
}

function registrarHistoricoIndicacao($indicacao_id, $acao, $detalhes = null) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("
        INSERT INTO indicacoes_historico (indicacao_id, acao, detalhes, ip, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isss", $indicacao_id, $acao, $detalhes, $ip);
    $stmt->execute();
}

function aplicarDescontoIndicacao($cliente_id, $valor_mensal, $percentual = 10) {
    global $conn;
    
    $desconto = ($valor_mensal * $percentual) / 100;
    $validade = date('Y-m-d', strtotime('+3 months'));
    
    $stmt = $conn->prepare("
        UPDATE clientes SET 
            desconto_indicacao = ?,
            validade_desconto = ?
        WHERE id = ?
    ");
    $stmt->bind_param("dsi", $desconto, $validade, $cliente_id);
    return $stmt->execute();
}
?>