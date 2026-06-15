<?php
function getDocumentosCliente($cliente_id, $filtros = []) {
    global $conn;
    
    $documentos = [];
    
    // 1. BUSCAR CONTRATOS ASSINADOS
    $sql_contratos = "SELECT 
                        CONCAT('CTR-', c.id) as id,
                        c.cliente_id,
                        'contrato' as tipo,
                        CONCAT('Contrato ', c.numero_contrato) as titulo,
                        c.titulo as descricao,
                        c.numero_contrato as referencia,
                        c.valor_total as valor,
                        DATE(c.created_at) as data_emissao,
                        c.data_assinatura as data_assinatura,
                        c.status,
                        c.created_at
                    FROM contratos c
                    WHERE c.cliente_id = ? 
                    AND c.status = 'assinado'";
    
    $stmt = $conn->prepare($sql_contratos);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $contratos = $stmt->get_result();
    
    while ($row = $contratos->fetch_assoc()) {
        $documentos[] = $row;
    }
    
    // 2. BUSCAR FATURAS
    $sql_faturas = "SELECT 
                        CONCAT('FAT-', cf.id) as id,
                        cf.cliente_id,
                        'fatura' as tipo,
                        CONCAT('Fatura ', cf.numero_fatura) as titulo,
                        cf.mes_referencia as descricao,
                        cf.numero_fatura as referencia,
                        cf.valor_total as valor,
                        cf.data_vencimento as data_emissao,
                        cf.data_pagamento,
                        cf.status,
                        cf.created_at
                    FROM cliente_faturas cf
                    WHERE cf.cliente_id = ?";
    
    $stmt = $conn->prepare($sql_faturas);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $faturas = $stmt->get_result();
    
    while ($row = $faturas->fetch_assoc()) {
        $documentos[] = $row;
    }
    
    // 3. BUSCAR PLANOS CONTRATADOS (como propostas)
    $sql_planos = "SELECT 
                        CONCAT('PLN-', pc.id) as id,
                        pc.cliente_id,
                        'proposta' as tipo,
                        pc.nome_plano as titulo,
                        pc.descricao,
                        pc.id as referencia,
                        pc.valor_plano as valor,
                        pc.data_inicio as data_emissao,
                        pc.status,
                        pc.created_at
                    FROM planos_contratados pc
                    WHERE pc.cliente_id = ?";
    
    $stmt = $conn->prepare($sql_planos);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $planos = $stmt->get_result();
    
    while ($row = $planos->fetch_assoc()) {
        $documentos[] = $row;
    }
    
    // APLICAR FILTROS
    if (!empty($filtros['tipo'])) {
        $documentos = array_filter($documentos, function($doc) use ($filtros) {
            return $doc['tipo'] == $filtros['tipo'];
        });
    }
    
    if (!empty($filtros['busca'])) {
        $busca = strtolower($filtros['busca']);
        $documentos = array_filter($documentos, function($doc) use ($busca) {
            return strpos(strtolower($doc['titulo']), $busca) !== false || 
                   strpos(strtolower($doc['referencia'] ?? ''), $busca) !== false;
        });
    }
    
    // ORDENAR POR DATA (mais recentes primeiro)
    usort($documentos, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $documentos;
}

function getDocumentoDetalhes($id, $cliente_id) {
    global $conn;
    
    // Extrai o tipo e ID real do documento
    if (strpos($id, 'CTR-') === 0) {
        $contrato_id = str_replace('CTR-', '', $id);
        
        $stmt = $conn->prepare("
            SELECT 
                CONCAT('CTR-', c.id) as id,
                c.cliente_id,
                'contrato' as tipo,
                CONCAT('Contrato ', c.numero_contrato) as titulo,
                c.titulo as descricao,
                c.numero_contrato as referencia,
                c.valor_total as valor,
                c.valor_mensal,
                c.numero_parcelas,
                c.dia_vencimento,
                c.data_primeira_parcela,
                c.data_assinatura,
                c.status,
                c.created_at
            FROM contratos c
            WHERE c.id = ? AND c.cliente_id = ?
        ");
        $stmt->bind_param("ii", $contrato_id, $cliente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    elseif (strpos($id, 'FAT-') === 0) {
        $fatura_id = str_replace('FAT-', '', $id);
        
        $stmt = $conn->prepare("
            SELECT 
                CONCAT('FAT-', cf.id) as id,
                cf.cliente_id,
                'fatura' as tipo,
                CONCAT('Fatura ', cf.numero_fatura) as titulo,
                cf.mes_referencia as descricao,
                cf.numero_fatura as referencia,
                cf.valor as valor_original,
                cf.desconto,
                cf.juros,
                cf.multa,
                cf.valor_total as valor,
                cf.data_vencimento,
                cf.data_pagamento,
                cf.status,
                cf.created_at
            FROM cliente_faturas cf
            WHERE cf.id = ? AND cf.cliente_id = ?
        ");
        $stmt->bind_param("ii", $fatura_id, $cliente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    elseif (strpos($id, 'PLN-') === 0) {
        $plano_id = str_replace('PLN-', '', $id);
        
        $stmt = $conn->prepare("
            SELECT 
                CONCAT('PLN-', pc.id) as id,
                pc.cliente_id,
                'proposta' as tipo,
                pc.nome_plano as titulo,
                pc.descricao,
                pc.id as referencia,
                pc.valor_plano as valor,
                pc.valor_mensal,
                pc.data_inicio,
                pc.status,
                pc.created_at
            FROM planos_contratados pc
            WHERE pc.id = ? AND pc.cliente_id = ?
        ");
        $stmt->bind_param("ii", $plano_id, $cliente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    return null;
}

function getEstatisticasDocumentos($cliente_id) {
    global $conn;
    
    $stats = [
        'total' => 0,
        'contratos' => 0,
        'faturas' => 0,
        'propostas' => 0,
        'total_valor' => 0,
        'pendentes' => 0,
        'vencidos' => 0
    ];
    
    // Contar contratos assinados
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(valor_total) as total_valor FROM contratos WHERE cliente_id = ? AND status = 'assinado'");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $contratos = $stmt->get_result()->fetch_assoc();
    $stats['contratos'] = $contratos['total'] ?? 0;
    $stats['total'] += $contratos['total'] ?? 0;
    $stats['total_valor'] += $contratos['total_valor'] ?? 0;
    
    // Contar faturas
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(valor_total) as total_valor FROM cliente_faturas WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $faturas = $stmt->get_result()->fetch_assoc();
    $stats['faturas'] = $faturas['total'] ?? 0;
    $stats['total'] += $faturas['total'] ?? 0;
    $stats['total_valor'] += $faturas['total_valor'] ?? 0;
    
    // Contar planos
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(valor_plano) as total_valor FROM planos_contratados WHERE cliente_id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $planos = $stmt->get_result()->fetch_assoc();
    $stats['propostas'] = $planos['total'] ?? 0;
    $stats['total'] += $planos['total'] ?? 0;
    $stats['total_valor'] += $planos['total_valor'] ?? 0;
    
    // Faturas pendentes (próximos 30 dias)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM cliente_faturas 
        WHERE cliente_id = ? 
        AND status = 'pendente'
        AND data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stats['pendentes'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    
    // Faturas vencidas
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM cliente_faturas 
        WHERE cliente_id = ? 
        AND status = 'pendente'
        AND data_vencimento < CURDATE()
    ");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stats['vencidos'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    
    return $stats;
}

function getIconePorTipo($tipo) {
    switch($tipo) {
        case 'contrato': return 'fa-file-contract';
        case 'fatura': return 'fa-file-invoice-dollar';
        case 'proposta': return 'fa-file-signature';
        default: return 'fa-file';
    }
}

function getCorPorTipo($tipo) {
    switch($tipo) {
        case 'contrato': return '#4361ee';
        case 'fatura': return '#10b981';
        case 'proposta': return '#f97316';
        default: return '#64748b';
    }
}

function getNomeTipo($tipo) {
    switch($tipo) {
        case 'contrato': return 'Contrato';
        case 'fatura': return 'Fatura';
        case 'proposta': return 'Proposta';
        default: return 'Documento';
    }
}

function getStatusBadge($status) {
    switch($status) {
        case 'assinado':
        case 'paga':
            return ['badge-pago', 'Pago'];
        case 'pendente':
            return ['badge-pendente', 'Pendente'];
        case 'cancelado':
            return ['badge-vencido', 'Cancelado'];
        default:
            return ['', $status];
    }
}
?>