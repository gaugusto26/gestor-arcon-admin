<?php
function getMetricasEmpresas() {
    global $conn;
    
    $metricas = [];
    
    // Métricas atuais
    $result = $conn->query("SELECT * FROM metricas_empresas ORDER BY ano DESC, mes DESC LIMIT 1");
    $metricas['atual'] = $result->fetch_assoc();
    
    // Métricas do mês passado
    $result = $conn->query("SELECT * FROM metricas_empresas ORDER BY ano DESC, mes DESC LIMIT 1, 1");
    $metricas['passado'] = $result->fetch_assoc();
    
    // Total de empresas
    $result = $conn->query("SELECT COUNT(*) as total FROM clientes");
    $metricas['total_empresas'] = $result->fetch_assoc()['total'];
    
    // Empresas ativas vs inativas
    $result = $conn->query("SELECT status, COUNT(*) as total FROM clientes GROUP BY status");
    while($row = $result->fetch_assoc()) {
        $metricas['status_' . $row['status']] = $row['total'];
    }
    
    // Contratos próximos ao vencimento (próximos 30 dias)
    $metricas['proximos_vencimentos'] = $conn->query("
        SELECT c.id, c.nome as cliente_nome, c.empresa, ct.numero_contrato, 
               ct.data_vencimento, ct.valor_mensal
        FROM contratos ct
        JOIN clientes c ON ct.cliente_id = c.id
        WHERE ct.status = 'assinado' 
          AND ct.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY ct.data_vencimento ASC
    ");
    
    // Contratos vencidos
    $metricas['contratos_vencidos'] = $conn->query("
        SELECT c.id, c.nome as cliente_nome, c.empresa, ct.numero_contrato, 
               ct.data_vencimento, ct.valor_mensal,
               DATEDIFF(CURDATE(), ct.data_vencimento) as dias_atraso
        FROM contratos ct
        JOIN clientes c ON ct.cliente_id = c.id
        WHERE ct.status = 'assinado' 
          AND ct.data_vencimento < CURDATE()
        ORDER BY ct.data_vencimento ASC
    ");
    
    // Top empresas por faturamento
    $metricas['top_faturamento'] = $conn->query("
        SELECT c.nome, c.empresa, 
               COUNT(DISTINCT pc.id) as total_planos,
               SUM(pc.valor_mensal) as faturamento_mensal,
               SUM(pc.valor_plano) as faturamento_total
        FROM clientes c
        LEFT JOIN planos_contratados pc ON c.id = pc.cliente_id AND pc.status = 'ativo'
        GROUP BY c.id
        HAVING faturamento_mensal > 0
        ORDER BY faturamento_mensal DESC
        LIMIT 10
    ");
    
    // Crescimento mensal (últimos 6 meses)
    $metricas['crescimento'] = $conn->query("
        SELECT ano, mes, 
               total_empresas, novas_empresas,
               receita_total, receita_mensal_total,
               (SELECT total_empresas FROM metricas_empresas m2 
                WHERE (m2.ano = m1.ano AND m2.mes = m1.mes - 1) 
                   OR (m1.mes = 1 AND m2.ano = m1.ano - 1 AND m2.mes = 12)
                LIMIT 1) as total_mes_anterior
        FROM metricas_empresas m1
        ORDER BY ano DESC, mes DESC
        LIMIT 6
    ");
    
    return $metricas;
}

function calcularVariacao($atual, $anterior) {
    if($anterior == 0) return 100;
    if($atual == 0) return -100;
    return round((($atual - $anterior) / $anterior) * 100, 1);
}
?>