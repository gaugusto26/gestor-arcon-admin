<?php
// Lista de todos os status disponíveis
function getTodosStatus() {
    return [
        'aguardando_inicio' => [
            'nome' => 'Aguardando Início',
            'cor' => '#64748b',
            'icone' => 'fa-clock',
            'descricao' => 'Contrato assinado, aguardando início do projeto',
            'etapa' => 1,
            'proxima' => 'reuniao_inicial'
        ],
        'reuniao_inicial' => [
            'nome' => 'Reunião Inicial',
            'cor' => '#3b82f6',
            'icone' => 'fa-users',
            'descricao' => 'Reunião de kick-off para alinhamento do projeto',
            'etapa' => 2,
            'proxima' => 'levantamento_requisitos'
        ],
        'levantamento_requisitos' => [
            'nome' => 'Levantamento de Requisitos',
            'cor' => '#8b5cf6',
            'icone' => 'fa-clipboard-list',
            'descricao' => 'Coleta de requisitos e especificações técnicas',
            'etapa' => 3,
            'proxima' => 'design_aprovacao'
        ],
        'design_aprovacao' => [
            'nome' => 'Design para Aprovação',
            'cor' => '#f97316',
            'icone' => 'fa-paint-brush',
            'descricao' => 'Criação de layouts para aprovação do cliente',
            'etapa' => 4,
            'proxima' => 'design_aprovado'
        ],
        'design_aprovado' => [
            'nome' => 'Design Aprovado',
            'cor' => '#10b981',
            'icone' => 'fa-check-circle',
            'descricao' => 'Design aprovado pelo cliente',
            'etapa' => 5,
            'proxima' => 'desenvolvimento'
        ],
        'desenvolvimento' => [
            'nome' => 'Desenvolvimento',
            'cor' => '#3b82f6',
            'icone' => 'fa-code',
            'descricao' => 'Desenvolvimento geral do sistema',
            'etapa' => 6,
            'proxima' => 'desenvolvimento_frontend'
        ],
        'desenvolvimento_frontend' => [
            'nome' => 'Desenvolvimento Frontend',
            'cor' => '#ec4899',
            'icone' => 'fa-window-maximize',
            'descricao' => 'Desenvolvimento da interface do usuário',
            'etapa' => 7,
            'proxima' => 'desenvolvimento_backend'
        ],
        'desenvolvimento_backend' => [
            'nome' => 'Desenvolvimento Backend',
            'cor' => '#6b7280',
            'icone' => 'fa-server',
            'descricao' => 'Desenvolvimento da lógica e banco de dados',
            'etapa' => 8,
            'proxima' => 'integracao_apis'
        ],
        'integracao_apis' => [
            'nome' => 'Integração de APIs',
            'cor' => '#f59e0b',
            'icone' => 'fa-plug',
            'descricao' => 'Integração com serviços e APIs externas',
            'etapa' => 9,
            'proxima' => 'testes_internos'
        ],
        'testes_internos' => [
            'nome' => 'Testes Internos',
            'cor' => '#10b981',
            'icone' => 'fa-vial',
            'descricao' => 'Testes realizados pela equipe interna',
            'etapa' => 10,
            'proxima' => 'ambiente_teste'
        ],
        'ambiente_teste' => [
            'nome' => 'Ambiente de Teste',
            'cor' => '#f97316',
            'icone' => 'fa-flask',
            'descricao' => 'Sistema disponível em ambiente de testes',
            'etapa' => 11,
            'proxima' => 'homologacao_cliente'
        ],
        'homologacao_cliente' => [
            'nome' => 'Homologação com Cliente',
            'cor' => '#8b5cf6',
            'icone' => 'fa-user-check',
            'descricao' => 'Cliente testando o sistema',
            'etapa' => 12,
            'proxima' => 'ajustes_finais'
        ],
        'ajustes_finais' => [
            'nome' => 'Ajustes Finais',
            'cor' => '#f97316',
            'icone' => 'fa-tools',
            'descricao' => 'Ajustes baseados no feedback do cliente',
            'etapa' => 13,
            'proxima' => 'treinamento_cliente'
        ],
        'treinamento_cliente' => [
            'nome' => 'Treinamento do Cliente',
            'cor' => '#3b82f6',
            'icone' => 'fa-chalkboard-teacher',
            'descricao' => 'Treinamento para utilização do sistema',
            'etapa' => 14,
            'proxima' => 'aguardando_aprovacao'
        ],
        'aguardando_aprovacao' => [
            'nome' => 'Aguardando Aprovação Final',
            'cor' => '#f59e0b',
            'icone' => 'fa-hourglass-half',
            'descricao' => 'Aguardando aprovação final do cliente',
            'etapa' => 15,
            'proxima' => 'aprovado_cliente'
        ],
        'aprovado_cliente' => [
            'nome' => 'Aprovado pelo Cliente',
            'cor' => '#10b981',
            'icone' => 'fa-thumbs-up',
            'descricao' => 'Cliente aprovou o sistema',
            'etapa' => 16,
            'proxima' => 'implantacao'
        ],
        'implantacao' => [
            'nome' => 'Implantação',
            'cor' => '#3b82f6',
            'icone' => 'fa-rocket',
            'descricao' => 'Implantação em produção',
            'etapa' => 17,
            'proxima' => 'concluido'
        ],
        'concluido' => [
            'nome' => 'Concluído',
            'cor' => '#10b981',
            'icone' => 'fa-check-double',
            'descricao' => 'Sistema entregue e concluído',
            'etapa' => 18,
            'proxima' => null
        ],
        'manutencao' => [
            'nome' => 'Em Manutenção',
            'cor' => '#f97316',
            'icone' => 'fa-tools',
            'descricao' => 'Sistema em manutenção programada',
            'etapa' => 19,
            'proxima' => null
        ],
        'pausado' => [
            'nome' => 'Pausado',
            'cor' => '#64748b',
            'icone' => 'fa-pause-circle',
            'descricao' => 'Projeto pausado temporariamente',
            'etapa' => 20,
            'proxima' => null
        ],
        'cancelado' => [
            'nome' => 'Cancelado',
            'cor' => '#ef4444',
            'icone' => 'fa-ban',
            'descricao' => 'Projeto cancelado',
            'etapa' => 21,
            'proxima' => null
        ]
    ];
}

// Busca sistemas com status para gerenciar
function getSistemasParaGerenciar($filtros = []) {
    global $conn;

    $sql = "SELECT s.*, 
                   c.nome    AS cliente_nome, 
                   c.empresa AS cliente_empresa,
                   c.email   AS cliente_email,
                   pc.nome_plano,
                   pc.tipo_plano,
                   ct.numero_contrato
            FROM cliente_sistemas s
            LEFT JOIN clientes c ON s.cliente_id = c.id
            LEFT JOIN planos_contratados pc ON s.plano_contratado_id = pc.id
            LEFT JOIN contratos ct ON ct.plano_contratado_id = pc.id AND ct.status = 'assinado'
            WHERE 1=1";

    $params = [];
    $types  = "";

    if (!empty($filtros['status'])) {
        $sql   .= " AND s.status = ?";
        $params[] = $filtros['status'];
        $types   .= "s";
    }

    if (!empty($filtros['cliente_id'])) {
        $sql   .= " AND s.cliente_id = ?";
        $params[] = (int)$filtros['cliente_id'];
        $types   .= "i";
    }

    if (!empty($filtros['busca'])) {
        $busca    = "%{$filtros['busca']}%";
        $sql     .= " AND (c.nome LIKE ? OR c.empresa LIKE ? OR s.nome_sistema LIKE ?)";
        $params[] = $busca;
        $params[] = $busca;
        $params[] = $busca;
        $types   .= "sss";
    }

    $sql .= " ORDER BY
                CASE s.status
                    WHEN 'aguardando_inicio'   THEN 1
                    WHEN 'desenvolvimento'     THEN 2
                    WHEN 'homologacao_cliente' THEN 3
                    WHEN 'concluido'           THEN 4
                    ELSE 5
                END,
                s.created_at DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("getSistemasParaGerenciar ERRO: " . $conn->error);
        return false;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result();
}

// Busca histórico de um sistema
function getHistoricoSistema($sistema_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT h.*, a.nome_completo AS admin_nome
        FROM cliente_sistemas_historico h
        LEFT JOIN admin_users a ON h.alterado_por = a.id
        WHERE h.sistema_id = ?
        ORDER BY h.created_at DESC
    ");

    if (!$stmt) return false;

    $stmt->bind_param("i", $sistema_id);
    $stmt->execute();
    return $stmt->get_result();
}



// Registra alteração de status
function registrarHistorico($sistema_id, $status_anterior, $status_novo, $observacao = null) {
    global $conn;

    $admin_id = $_SESSION['admin_id'] ?? null;
    $ip       = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("
        INSERT INTO cliente_sistemas_historico 
            (sistema_id, status_anterior, status_novo, observacao, alterado_por, ip)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) return false;

    $stmt->bind_param("isssis", $sistema_id, $status_anterior, $status_novo, $observacao, $admin_id, $ip);
    return $stmt->execute();
}
?>