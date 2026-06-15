<?php
// Define o título da página
$page_title = 'Dashboard';
require_once 'modules/planos/config.php';  
require_once '../../config.php';  

// Pega a URL atual pra marcar o item ativo
$current_url = $_SERVER['REQUEST_URI'];

// =============================================
// ESTATÍSTICAS PRINCIPAIS
// =============================================

// Total de clientes
$result_clientes = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE status = 'ativo'");
$total_clientes = $result_clientes->fetch_assoc()['total'];

// Total de planos ativos
$result_planos = $conn->query("SELECT COUNT(*) as total FROM planos WHERE ativo = 1");
$total_planos = $result_planos->fetch_assoc()['total'];

// Total de contratos assinados
$result_contratos = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE status = 'assinado'");
$total_contratos = $result_contratos->fetch_assoc()['total'];

// Receita mensal (soma das mensalidades dos planos ativos)
$result_receita = $conn->query("SELECT COALESCE(SUM(valor_mensal), 0) as total FROM planos_contratados WHERE status = 'ativo'");
$receita_mensal = $result_receita->fetch_assoc()['total'];

// Total de faturas pendentes
$result_faturas_pendentes = $conn->query("SELECT COUNT(*) as total FROM pagamento_faturas WHERE status = 'pendente'");
$faturas_pendentes = $result_faturas_pendentes->fetch_assoc()['total'];

// Total de faturas pagas no mês
$result_faturas_pagas = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(valor_total), 0) as soma FROM pagamento_faturas WHERE status = 'paga' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())");
$faturas_pagas_mes = $result_faturas_pagas->fetch_assoc();
$faturas_pagas_count = $faturas_pagas_mes['total'];
$faturas_pagas_valor = $faturas_pagas_mes['soma'];

// Valor total a receber (faturas pendentes)
$result_a_receber = $conn->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM pagamento_faturas WHERE status = 'pendente'");
$a_receber = $result_a_receber->fetch_assoc()['total'];

// Clientes com faturas atrasadas
$result_clientes_atrasados = $conn->query("SELECT COUNT(DISTINCT cliente_id) as total FROM pagamento_faturas WHERE status = 'pendente' AND data_vencimento < CURDATE()");
$clientes_atrasados = $result_clientes_atrasados->fetch_assoc()['total'];

// Sistemas em desenvolvimento
$result_sistemas = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status IN ('aguardando_inicio', 'reuniao_inicial', 'levantamento_requisitos') THEN 1 ELSE 0 END) as em_andamento,
    SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos
FROM cliente_sistemas");
$sistemas = $result_sistemas->fetch_assoc();

// Posts do blog publicados
$result_blog = $conn->query("SELECT COUNT(*) as total FROM blog_posts WHERE status = 'publicado'");
$blog_posts = $result_blog->fetch_assoc()['total'];

// Inscritos na newsletter
$result_newsletter = $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE status = 'ativo'");
$newsletter_inscritos = $result_newsletter->fetch_assoc()['total'];

// =============================================
// GRÁFICO - FATURAMENTO MENSAL (ÚLTIMOS 6 MESES)
// =============================================
$faturamento_mensal = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('m', strtotime("-$i months"));
    $ano = date('Y', strtotime("-$i months"));
    $nome_mes = date('M', strtotime("-$i months"));
    
    $sql = "SELECT COALESCE(SUM(valor_total), 0) as total 
            FROM pagamento_faturas 
            WHERE status = 'paga' 
            AND MONTH(data_pagamento) = $mes 
            AND YEAR(data_pagamento) = $ano";
    $result = $conn->query($sql);
    $valor = $result->fetch_assoc()['total'];
    
    $faturamento_mensal[] = [
        'mes' => $nome_mes,
        'valor' => $valor
    ];
}

// =============================================
// GRÁFICO - STATUS DOS SISTEMAS
// =============================================
$sql_status = "SELECT 
    SUM(CASE WHEN status IN ('aguardando_inicio', 'reuniao_inicial', 'levantamento_requisitos') THEN 1 ELSE 0 END) as em_andamento,
    SUM(CASE WHEN status IN ('design_aprovacao', 'design_aprovado', 'desenvolvimento', 'desenvolvimento_frontend', 'desenvolvimento_backend', 'integracao_apis') THEN 1 ELSE 0 END) as desenvolvimento,
    SUM(CASE WHEN status IN ('testes_internos', 'homologacao_cliente', 'ajustes_finais', 'ambiente_teste') THEN 1 ELSE 0 END) as testes,
    SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluido
FROM cliente_sistemas";
$result_status = $conn->query($sql_status);
$status_sistemas = $result_status->fetch_assoc();

// =============================================
// ATIVIDADES RECENTES
// =============================================

// Últimos contratos assinados
$sql_contratos_recentes = "SELECT c.*, cl.nome as cliente_nome 
                          FROM contratos c
                          JOIN clientes cl ON cl.id = c.cliente_id
                          WHERE c.status = 'assinado'
                          ORDER BY c.data_assinatura DESC 
                          LIMIT 5";
$contratos_recentes = $conn->query($sql_contratos_recentes);

// Últimas faturas pagas
$sql_faturas_recentes = "SELECT f.*, cl.nome as cliente_nome 
                         FROM pagamento_faturas f
                         JOIN clientes cl ON cl.id = f.cliente_id
                         WHERE f.status = 'paga'
                         ORDER BY f.data_pagamento DESC 
                         LIMIT 5";
$faturas_recentes = $conn->query($sql_faturas_recentes);

// Últimos clientes cadastrados
$sql_clientes_recentes = "SELECT * FROM clientes 
                          ORDER BY created_at DESC 
                          LIMIT 5";
$clientes_recentes = $conn->query($sql_clientes_recentes);

// =============================================
// FATURAS A VENCER (PRÓXIMOS 7 DIAS)
// =============================================
$sql_proximas_faturas = "SELECT f.*, cl.nome as cliente_nome, cl.email
                        FROM pagamento_faturas f
                        JOIN clientes cl ON cl.id = f.cliente_id
                        WHERE f.status = 'pendente' 
                        AND f.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        ORDER BY f.data_vencimento ASC";
$proximas_faturas = $conn->query($sql_proximas_faturas);

// =============================================
// TOP CLIENTES (MAIOR VALOR EM CONTRATOS)
// =============================================
$sql_top_clientes = "SELECT 
    c.id, c.nome, c.empresa,
    COUNT(DISTINCT ct.id) as total_contratos,
    COALESCE(SUM(ct.valor_total), 0) as valor_total_contratos,
    COALESCE(SUM(CASE WHEN pc.status = 'ativo' THEN pc.valor_mensal ELSE 0 END), 0) as receita_mensal
FROM clientes c
LEFT JOIN contratos ct ON ct.cliente_id = c.id AND ct.status = 'assinado'
LEFT JOIN planos_contratados pc ON pc.cliente_id = c.id
WHERE c.status = 'ativo'
GROUP BY c.id
ORDER BY receita_mensal DESC
LIMIT 5";
$top_clientes = $conn->query($sql_top_clientes);

// =============================================
// MÉTRICAS DE CRESCIMENTO
// =============================================

// Clientes este mês vs mês passado
$sql_clientes_mes = $conn->query("SELECT 
    COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) as este_mes,
    COUNT(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 END) as mes_passado
FROM clientes");
$clientes_mes = $sql_clientes_mes->fetch_assoc();

// Faturamento este mês vs mês passado
$sql_faturamento_mes = $conn->query("SELECT 
    COALESCE(SUM(CASE WHEN MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE()) THEN valor_total END), 0) as este_mes,
    COALESCE(SUM(CASE WHEN MONTH(data_pagamento) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(data_pagamento) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN valor_total END), 0) as mes_passado
FROM pagamento_faturas WHERE status = 'paga'");
$faturamento_mes = $sql_faturamento_mes->fetch_assoc();

// Calcular percentuais de crescimento
$crescimento_clientes = $clientes_mes['mes_passado'] > 0 ? round(($clientes_mes['este_mes'] - $clientes_mes['mes_passado']) / $clientes_mes['mes_passado'] * 100, 1) : 100;
$crescimento_faturamento = $faturamento_mes['mes_passado'] > 0 ? round(($faturamento_mes['este_mes'] - $faturamento_mes['mes_passado']) / $faturamento_mes['mes_passado'] * 100, 1) : 100;

// =============================================
// FUNÇÕES AUXILIARES
// =============================================

function formatarValor($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function getStatusBadge($status) {
    $badges = [
        'paga' => 'success',
        'pendente' => 'warning',
        'atrasada' => 'danger',
        'cancelada' => 'secondary',
        'assinado' => 'success',
        'enviado' => 'info',
        'rascunho' => 'secondary',
        'ativo' => 'success',
        'inativo' => 'danger',
        'bloqueado' => 'dark'
    ];
    
    $class = $badges[$status] ?? 'secondary';
    $label = ucfirst($status);
    
    return "<span class='status-badge status-{$class}'>{$label}</span>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | NTW Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --sidebar-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-light: #dbeafe;
            --border: #e2e8f0;
            --hover: #f1f5f9;
            --card-bg: #ffffff;
            --shadow: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.05);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --sidebar-bg: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-light: #1e293b;
            --border: #334155;
            --hover: #2d3a4f;
            --card-bg: #1e293b;
            --shadow: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.3);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease;
            overflow-x: hidden;
        }

        /* Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar - agora vem do menu.php, mas mantemos o estilo aqui */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .toggle-sidebar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--hover);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .toggle-sidebar:hover {
            background: var(--accent);
            color: white;
        }

        .sidebar.collapsed .toggle-sidebar {
            transform: rotate(180deg);
        }

        /* Profile */
        .profile-section {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .profile-details {
            flex: 1;
        }

        .profile-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .profile-role {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .sidebar.collapsed .profile-details {
            display: none;
        }

        /* Menu */
        .sidebar-menu {
            padding: 20px 16px;
        }

        .menu-section {
            margin-bottom: 24px;
        }

        .menu-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            padding: 0 12px;
            margin-bottom: 12px;
        }

        .sidebar.collapsed .menu-title {
            text-align: center;
            font-size: 0.6rem;
            padding: 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 4px;
            position: relative;
            white-space: nowrap;
        }

        .menu-item:hover {
            background: var(--hover);
            color: var(--accent);
        }

        .menu-item.active {
            background: var(--accent-light);
            color: var(--accent);
        }

        .menu-item i {
            width: 20px;
            font-size: 1.1rem;
        }

        .menu-item span {
            flex: 1;
        }

        .menu-badge {
            background: var(--accent);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 12px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .menu-item span {
            display: none;
        }

        .sidebar.collapsed .menu-badge {
            position: absolute;
            right: 8px;
            top: 8px;
            font-size: 0.6rem;
            padding: 2px 4px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Top Bar */
        .top-bar {
            height: 80px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .theme-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--hover);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: var(--accent);
            color: white;
        }

        .notification-badge {
            position: relative;
            cursor: pointer;
        }

        .notification-badge i {
            font-size: 1.3rem;
            color: var(--text-secondary);
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--accent), #2563eb);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .welcome-text h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .welcome-text p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .welcome-date {
            background: rgba(255,255,255,0.2);
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 500;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, transparent, var(--accent-light));
            opacity: 0.1;
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--accent-light);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .stat-trend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 20px;
        }

        .trend-up {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .trend-down {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Tables */
        .table-container {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-title {
            font-weight: 600;
            color: var(--text-primary);
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-secondary);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn:hover {
            background: var(--hover);
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
            border: none;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
            border: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 16px 20px;
            background: var(--hover);
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        tr:hover td {
            background: var(--hover);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-success {
            background: #10b98120;
            color: #10b981;
        }

        .status-warning {
            background: #f59e0b20;
            color: #f59e0b;
        }

        .status-danger {
            background: #ef444420;
            color: #ef4444;
        }

        .status-info {
            background: #3b82f620;
            color: #3b82f6;
        }

        .status-secondary {
            background: #64748b20;
            color: #64748b;
        }

        .client-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .client-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--accent-light);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .client-details {
            line-height: 1.3;
        }

        .client-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .client-email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .progress {
            width: 100%;
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-bar {
            height: 100%;
            background: var(--accent);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .action-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-primary);
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--accent-light);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 12px;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .action-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-visible {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                padding: 0 20px;
            }
            
            .content-area {
                padding: 20px;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body data-theme="light">
    <div class="dashboard">
        
        <!-- INCLUI O MENU AQUI - AGORA VEM DO ARQUIVO EXTERNO -->
        <?php include 'includes/menu.php'; ?>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">Dashboard</h1>
                
                <div class="top-bar-actions">
                    <div class="theme-toggle" id="themeToggle">
                        <i class="fas fa-sun" id="themeIcon"></i>
                    </div>
                    
                    <div class="notification-badge" onclick="window.location.href='?page=faturas&status=pendente'">
                        <i class="far fa-bell"></i>
                        <?php if ($faturas_pendentes > 0): ?>
                        <span class="notification-count"><?php echo $faturas_pendentes; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div class="welcome-text">
                        <h1>Olá, <?php echo explode(' ', $_SESSION['admin_nome'] ?? 'Admin')[0]; ?>! 👋</h1>
                        <p>Aqui está o resumo do seu negócio. Tenha um ótimo dia!</p>
                    </div>
                    <div class="welcome-date">
                        <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y'); ?>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?php echo $total_clientes; ?></div>
                        <div class="stat-label">Clientes ativos</div>
                        <div class="stat-trend <?php echo $crescimento_clientes >= 0 ? 'trend-up' : 'trend-down'; ?>">
                            <i class="fas fa-<?php echo $crescimento_clientes >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($crescimento_clientes); ?>% vs mês anterior
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value"><?php echo formatarValor($receita_mensal); ?></div>
                        <div class="stat-label">Receita mensal recorrente</div>
                        <div class="stat-trend <?php echo $crescimento_faturamento >= 0 ? 'trend-up' : 'trend-down'; ?>">
                            <i class="fas fa-<?php echo $crescimento_faturamento >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($crescimento_faturamento); ?>% vs mês anterior
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-value"><?php echo $faturas_pagas_count; ?></div>
                        <div class="stat-label">Faturas pagas no mês</div>
                        <div class="stat-trend">
                            <i class="fas fa-check-circle"></i>
                            <?php echo formatarValor($faturas_pagas_valor); ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value"><?php echo formatarValor($a_receber); ?></div>
                        <div class="stat-label">A receber</div>
                        <div class="stat-trend <?php echo $clientes_atrasados > 0 ? 'trend-down' : 'trend-up'; ?>">
                            <?php echo $clientes_atrasados; ?> cliente(s) com atraso
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="charts-row">
                    <!-- Faturamento Mensal -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <span class="chart-title">Faturamento Mensal</span>
                            <span class="chart-value"><?php echo formatarValor($faturamento_mes['este_mes']); ?></span>
                        </div>
                        <div class="chart-container">
                            <canvas id="faturamentoChart"></canvas>
                        </div>
                    </div>

                    <!-- Status dos Sistemas -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <span class="chart-title">Status dos Sistemas</span>
                            <span class="chart-value"><?php echo $sistemas['total']; ?> total</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="sistemasChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Próximos Vencimentos -->
                <?php if ($proximas_faturas->num_rows > 0): ?>
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">📅 Faturas a vencer nos próximos 7 dias</h3>
                        <a href="?page=faturas" class="btn btn-primary">Ver todas</a>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Fatura</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Dias</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fatura = $proximas_faturas->fetch_assoc()): 
                                $dias = (strtotime($fatura['data_vencimento']) - time()) / 86400;
                                $dias = ceil($dias);
                                $classe_dias = $dias <= 2 ? 'status-danger' : ($dias <= 4 ? 'status-warning' : 'status-info');
                            ?>
                            <tr>
                                <td>
                                    <div class="client-info">
                                        <div class="client-avatar"><?php echo strtoupper(substr($fatura['cliente_nome'], 0, 1)); ?></div>
                                        <div class="client-details">
                                            <div class="client-name"><?php echo htmlspecialchars($fatura['cliente_nome']); ?></div>
                                            <div class="client-email"><?php echo htmlspecialchars($fatura['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $fatura['numero_fatura']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($fatura['data_vencimento'])); ?></td>
                                <td><strong><?php echo formatarValor($fatura['valor_total']); ?></strong></td>
                                <td><span class="status-badge <?php echo $classe_dias; ?>"><?php echo $dias; ?> dia(s)</span></td>
                                <td>
                                    <a href="?page=faturas&action=ver&id=<?php echo $fatura['id']; ?>" class="btn btn-primary btn-sm">Ver</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Atividades Recentes e Top Clientes -->
                <div class="charts-row">
                    <!-- Atividades Recentes -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="table-title">📝 Atividades Recentes</h3>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <?php if ($contratos_recentes->num_rows > 0): ?>
                            <div style="margin-bottom: 20px;">
                                <h4 style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 10px;">Últimos contratos assinados</h4>
                                <?php while($contrato = $contratos_recentes->fetch_assoc()): ?>
                                <div style="display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border);">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-file-signature"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $contrato['numero_contrato']; ?> • <?php echo date('d/m/Y H:i', strtotime($contrato['data_assinatura'])); ?></div>
                                    </div>
                                    <span class="status-badge status-success">Assinado</span>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($faturas_recentes->num_rows > 0): ?>
                            <div>
                                <h4 style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 10px;">Últimos pagamentos</h4>
                                <?php while($fatura = $faturas_recentes->fetch_assoc()): ?>
                                <div style="display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border);">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($fatura['cliente_nome']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $fatura['numero_fatura']; ?> • <?php echo date('d/m/Y H:i', strtotime($fatura['data_pagamento'])); ?></div>
                                    </div>
                                    <strong style="color: #10b981;"><?php echo formatarValor($fatura['valor_total']); ?></strong>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Top Clientes -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="table-title">🏆 Top Clientes</h3>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <?php 
                            $rank = 1;
                            while($cliente = $top_clientes->fetch_assoc()): 
                                $medalha = $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : ($rank == 3 ? '🥉' : '📌'));
                            ?>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border);">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                    <?php echo $rank++; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($cliente['nome']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($cliente['empresa'] ?: 'Pessoa Física'); ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 700; color: var(--accent);"><?php echo formatarValor($cliente['receita_mensal']); ?></div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);"><?php echo $cliente['total_contratos']; ?> contrato(s)</div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <?php if ($clientes_recentes->num_rows > 0): ?>
                        <div style="margin-top: 30px;">
                            <h4 style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 10px;">Novos clientes</h4>
                            <?php while($cliente = $clientes_recentes->fetch_assoc()): ?>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 8px 0;">
                                <div style="width: 28px; height: 28px; border-radius: 6px; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div style="flex: 1;">
                                    <span style="font-weight: 500;"><?php echo htmlspecialchars($cliente['nome']); ?></span>
                                </div>
                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('d/m', strtotime($cliente['created_at'])); ?></span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="?page=planos&action=novo" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="action-title">Novo Plano</div>
                        <div class="action-desc">Criar um novo plano</div>
                    </a>
                    
                    <a href="?page=contratos&action=gerador" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="action-title">Gerar Contrato</div>
                        <div class="action-desc">Criar novo contrato</div>
                    </a>
                    
                    <a href="?page=clientes&action=novo" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="action-title">Novo Cliente</div>
                        <div class="action-desc">Cadastrar cliente</div>
                    </a>
                    
                    <a href="?page=blog&action=novo" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-pen"></i>
                        </div>
                        <div class="action-title">Novo Post</div>
                        <div class="action-desc">Escrever no blog</div>
                    </a>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }

        // Gráfico de Faturamento
        const ctx1 = document.getElementById('faturamentoChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: [<?php foreach ($faturamento_mensal as $m) echo "'" . $m['mes'] . "',"; ?>],
                datasets: [{
                    label: 'Faturamento',
                    data: [<?php foreach ($faturamento_mensal as $m) echo $m['valor'] . ","; ?>],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'R$ ' + context.raw.toFixed(2).replace('.', ',');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Status dos Sistemas
        const ctx2 = document.getElementById('sistemasChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Em Andamento', 'Desenvolvimento', 'Testes', 'Concluído'],
                datasets: [{
                    data: [
                        <?php echo $status_sistemas['em_andamento'] ?? 0; ?>,
                        <?php echo $status_sistemas['desenvolvimento'] ?? 0; ?>,
                        <?php echo $status_sistemas['testes'] ?? 0; ?>,
                        <?php echo $status_sistemas['concluido'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#f59e0b',
                        '#3b82f6',
                        '#8b5cf6',
                        '#10b981'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>
