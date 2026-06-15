<?php
$page_title = 'Empresas';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';
require_once '../../../../config.php';

$metricas = getMetricasEmpresas();

// Filtros
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 20;
$offset = ($pagina - 1) * $por_pagina;

// Monta query
$sql_where = "WHERE 1=1";
$params = [];
$types = "";

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

// Total para paginação
$sql_total = "SELECT COUNT(*) as total FROM clientes $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_empresas = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_empresas / $por_pagina);

// Busca empresas
$sql = "SELECT * FROM view_empresas_analise $sql_where ORDER BY 
        CASE 
            WHEN status = 'ativo' THEN 1
            WHEN status = 'inativo' THEN 2
            ELSE 3
        END,
        created_at DESC
        LIMIT $offset, $por_pagina";

$stmt = $conn->prepare($sql);
if($stmt === false) {
    // View não existe ou erro de SQL — define resultado vazio
    $empresas = null;
} else {
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $empresas = $stmt->get_result();
}

// Calcula variações
$variacao_empresas = calcularVariacao(
    $metricas['atual']['total_empresas'] ?? 0,
    $metricas['passado']['total_empresas'] ?? 0
);

$variacao_receita = calcularVariacao(
    $metricas['atual']['receita_total'] ?? 0,
    $metricas['passado']['receita_total'] ?? 0
);

$variacao_contratos = calcularVariacao(
    ($metricas['atual']['contratos_novos'] ?? 0) + ($metricas['atual']['contratos_renovados'] ?? 0),
    ($metricas['passado']['contratos_novos'] ?? 0) + ($metricas['passado']['contratos_renovados'] ?? 0)
);
?>

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card:nth-child(1)::before { background: linear-gradient(135deg, #4361ee 0%, #667eea 100%); }
.stat-card:nth-child(2)::before { background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); }
.stat-card:nth-child(3)::before { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.stat-card:nth-child(4)::before { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.stat-card:nth-child(1) .stat-icon {
    background: rgba(67, 97, 238, 0.1);
    color: #4361ee;
}

.stat-card:nth-child(2) .stat-icon {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.stat-card:nth-child(3) .stat-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.stat-card:nth-child(4) .stat-icon {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.stat-trend {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    padding-top: 10px;
    border-top: 1px solid var(--border);
}

.trend-up {
    color: #10b981;
}

.trend-down {
    color: #ef4444;
}

.trend-neutral {
    color: #64748b;
}

/* Alert Cards */
.alert-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.alert-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.alert-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.alert-title {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-title i {
    color: #f97316;
}

.alert-badge {
    background: #f97316;
    color: white;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid var(--border);
}

.alert-item:last-child {
    border-bottom: none;
}

.alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(249, 115, 22, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f97316;
}

.alert-info {
    flex: 1;
}

.alert-nome {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.alert-detalhe {
    font-size: 0.85rem;
    color: var(--text-muted);
    display: flex;
    gap: 10px;
}

.alert-destaque {
    background: #f97316;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.alert-destaque.warning {
    background: #f97316;
}

.alert-destaque.danger {
    background: #ef4444;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
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
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-title i {
    color: #4361ee;
}

/* Top Empresas */
.top-empresas {
    margin-top: 10px;
}

.top-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid var(--border);
}

.top-position {
    width: 30px;
    height: 30px;
    background: #f8faff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #4361ee;
}

.top-info {
    flex: 1;
}

.top-nome {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.top-stats {
    font-size: 0.85rem;
    color: var(--text-muted);
    display: flex;
    gap: 15px;
}

.top-stats span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.top-stats i {
    color: #4361ee;
}

.top-valor {
    font-weight: 700;
    color: #4361ee;
}

/* Filtros */
.filtros-box {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.filtro-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtro-item label {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 500;
}

.filtro-item select, .filtro-item input {
    padding: 12px 15px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: #f8faff;
    color: var(--text-primary);
}

.btn-filtro {
    padding: 12px 20px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    cursor: pointer;
    font-weight: 600;
    height: 46px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-limpar {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

/* Tabela */
.table-container {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    margin-top: 30px;
}

.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-title i {
    color: #4361ee;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 16px 25px;
    background: #f8faff;
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9rem;
}

td {
    padding: 16px 25px;
    border-bottom: 1px solid var(--border);
    color: var(--text-primary);
}

tr:hover td {
    background: #f8faff;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-ativo {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-inativo {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-bloqueado {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.vencimento-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.vencimento-proximo {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.vencimento-hoje {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.vencimento-normal {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.btn-icon {
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    border-color: transparent;
}

.paginacao {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 30px 0;
}

.page-link {
    padding: 10px 16px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
}

.page-link.active {
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    border-color: transparent;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-building" style="color: #4361ee; margin-right: 10px;"></i>
            Empresas
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-value"><?php echo number_format($metricas['total_empresas']); ?></div>
                <div class="stat-label">Total de Empresas</div>
                <div class="stat-trend <?php echo $variacao_empresas >= 0 ? 'trend-up' : 'trend-down'; ?>">
                    <i class="fas fa-arrow-<?php echo $variacao_empresas >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo abs($variacao_empresas); ?>% vs mês passado
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?php echo $metricas['status_ativo'] ?? 0; ?></div>
                <div class="stat-label">Empresas Ativas</div>
                <div class="stat-trend">
                    <i class="fas fa-circle"></i> <?php echo $metricas['status_inativo'] ?? 0; ?> inativas
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($metricas['atual']['receita_mensal_total'] ?? 0, 2, ',', '.'); ?></div>
                <div class="stat-label">Receita Mensal</div>
                <div class="stat-trend <?php echo $variacao_receita >= 0 ? 'trend-up' : 'trend-down'; ?>">
                    <i class="fas fa-arrow-<?php echo $variacao_receita >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo abs($variacao_receita); ?>% vs mês passado
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-value"><?php echo ($metricas['atual']['contratos_novos'] ?? 0) + ($metricas['atual']['contratos_renovados'] ?? 0); ?></div>
                <div class="stat-label">Contratos no Mês</div>
                <div class="stat-trend <?php echo $variacao_contratos >= 0 ? 'trend-up' : 'trend-down'; ?>">
                    <i class="fas fa-arrow-<?php echo $variacao_contratos >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo abs($variacao_contratos); ?>% vs mês passado
                </div>
            </div>
        </div>

        <!-- Alertas de Vencimento -->
        <div class="alert-grid">
            <!-- Próximos Vencimentos -->
            <div class="alert-card">
                <div class="alert-header">
                    <h3 class="alert-title">
                        <i class="fas fa-clock"></i> Próximos Vencimentos
                    </h3>
                    <span class="alert-badge"><?php echo $metricas['proximos_vencimentos']->num_rows; ?></span>
                </div>
                
                <?php if($metricas['proximos_vencimentos']->num_rows == 0): ?>
                <div style="text-align: center; padding: 30px 0;">
                    <i class="fas fa-calendar-check" style="font-size: 2rem; color: var(--text-muted);"></i>
                    <p style="margin-top: 10px; color: var(--text-muted);">Nenhum vencimento nos próximos 30 dias</p>
                </div>
                <?php else: ?>
                    <?php while($venc = $metricas['proximos_vencimentos']->fetch_assoc()): 
                        $dias = (strtotime($venc['data_vencimento']) - time()) / 86400;
                        $classe = $dias <= 7 ? 'vencimento-proximo' : 'vencimento-normal';
                    ?>
                    <div class="alert-item">
                        <div class="alert-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="alert-info">
                            <div class="alert-nome"><?php echo $venc['empresa'] ?: $venc['cliente_nome']; ?></div>
                            <div class="alert-detalhe">
                                <span><i class="fas fa-hashtag"></i> <?php echo $venc['numero_contrato']; ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($venc['data_vencimento'])); ?></span>
                                <span><i class="fas fa-dollar-sign"></i> R$ <?php echo number_format($venc['valor_mensal'], 2, ',', '.'); ?></span>
                            </div>
                        </div>
                        <div class="alert-destaque <?php echo $classe; ?>">
                            <?php echo $dias <= 0 ? 'HOJE' : ($dias <= 7 ? $dias . ' dias' : 'em ' . round($dias) . ' dias'); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <!-- Contratos Vencidos -->
            <div class="alert-card">
                <div class="alert-header">
                    <h3 class="alert-title">
                        <i class="fas fa-exclamation-triangle"></i> Contratos Vencidos
                    </h3>
                    <span class="alert-badge" style="background: #ef4444;"><?php echo $metricas['contratos_vencidos']->num_rows; ?></span>
                </div>
                
                <?php if($metricas['contratos_vencidos']->num_rows == 0): ?>
                <div style="text-align: center; padding: 30px 0;">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981;"></i>
                    <p style="margin-top: 10px; color: var(--text-muted);">Nenhum contrato vencido</p>
                </div>
                <?php else: ?>
                    <?php while($venc = $metricas['contratos_vencidos']->fetch_assoc()): ?>
                    <div class="alert-item">
                        <div class="alert-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-info">
                            <div class="alert-nome"><?php echo $venc['empresa'] ?: $venc['cliente_nome']; ?></div>
                            <div class="alert-detalhe">
                                <span><i class="fas fa-hashtag"></i> <?php echo $venc['numero_contrato']; ?></span>
                                <span><i class="fas fa-calendar"></i> Venceu em <?php echo date('d/m/Y', strtotime($venc['data_vencimento'])); ?></span>
                            </div>
                        </div>
                        <div class="alert-destaque danger">
                            <?php echo $venc['dias_atraso']; ?> dias
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <!-- Crescimento Mensal -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-line"></i> Crescimento de Empresas
                    </h3>
                </div>
                
                <div style="height: 200px; position: relative;">
                    <?php 
                    $meses = [];
                    $totais = [];
                    $novas = [];
                    
                    $metricas['crescimento']->data_seek(0);
                    while($row = $metricas['crescimento']->fetch_assoc()) {
                        $meses[] = date('M/Y', strtotime($row['ano'] . '-' . $row['mes'] . '-01'));
                        $totais[] = $row['total_empresas'];
                        $novas[] = $row['novas_empresas'];
                    }
                    
                    $max = max($totais) ?: 1;
                    ?>
                    
                    <div style="display: flex; align-items: flex-end; gap: 10px; height: 150px; margin-top: 20px;">
                        <?php for($i = 0; $i < count($meses); $i++): ?>
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                <div style="width: 30px; height: <?php echo ($totais[$i] / $max) * 100; ?>px; background: #4361ee; border-radius: 6px 6px 0 0;"></div>
                                <div style="width: 30px; height: <?php echo ($novas[$i] / $max) * 100; ?>px; background: #10b981; border-radius: 0 0 6px 6px;"></div>
                            </div>
                            <div style="font-size: 0.7rem; margin-top: 8px; color: var(--text-muted);"><?php echo $meses[$i]; ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 15px;">
                        <span style="display: flex; align-items: center; gap: 5px; font-size: 0.8rem;">
                            <span style="width: 12px; height: 12px; background: #4361ee; border-radius: 3px;"></span> Total
                        </span>
                        <span style="display: flex; align-items: center; gap: 5px; font-size: 0.8rem;">
                            <span style="width: 12px; height: 12px; background: #10b981; border-radius: 3px;"></span> Novas
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top Empresas por Faturamento -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="fas fa-trophy"></i> Top Faturamento Mensal
                    </h3>
                </div>
                
                <div class="top-empresas">
                    <?php 
                    $pos = 1;
                    while($top = $metricas['top_faturamento']->fetch_assoc()): 
                    ?>
                    <div class="top-item">
                        <div class="top-position">#<?php echo $pos++; ?></div>
                        <div class="top-info">
                            <div class="top-nome"><?php echo $top['empresa'] ?: $top['nome']; ?></div>
                            <div class="top-stats">
                                <span><i class="fas fa-box"></i> <?php echo $top['total_planos']; ?> planos</span>
                                <span><i class="fas fa-calendar"></i> R$ <?php echo number_format($top['faturamento_mensal'], 2, ',', '.'); ?>/mês</span>
                            </div>
                        </div>
                        <div class="top-valor">
                            R$ <?php echo number_format($top['faturamento_total'] ?? 0, 2, ',', '.'); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Nome, email ou empresa" value="<?php echo $busca; ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="ativo" <?php echo $filtro_status == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="inativo" <?php echo $filtro_status == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                        <option value="bloqueado" <?php echo $filtro_status == 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                    </select>
                </div>
                
                <div class="filtro-item" style="display: flex; flex-direction: row; gap: 10px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabela de Empresas -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Lista de Empresas
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Contato</th>
                        <th>Planos</th>
                        <th>Receita Mensal</th>
                        <th>Status</th>
                        <th>Próximo Vencimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!$empresas || $empresas->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px;">
                            <i class="fas fa-building" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
                            <h3>Nenhuma empresa encontrada</h3>
                            <p style="color: var(--text-muted);">Cadastre sua primeira empresa</p>
                            <a href="../clientes/criar.php" class="btn-filtro" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i> Nova Empresa
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($empresas && $emp = $empresas->fetch_assoc()): 
                            $status_class = '';
                            switch($emp['cliente_status']) {
                                case 'ativo': $status_class = 'status-ativo'; break;
                                case 'inativo': $status_class = 'status-inativo'; break;
                                case 'bloqueado': $status_class = 'status-bloqueado'; break;
                            }
                            
                            $vencimento_class = 'vencimento-normal';
                            $vencimento_text = '—';
                            if($emp['proximo_vencimento']) {
                                $dias = (strtotime($emp['proximo_vencimento']) - time()) / 86400;
                                if($dias <= 0) {
                                    $vencimento_class = 'vencimento-hoje';
                                    $vencimento_text = 'VENCIDO';
                                } elseif($dias <= 7) {
                                    $vencimento_class = 'vencimento-proximo';
                                    $vencimento_text = round($dias) . ' dias';
                                } else {
                                    $vencimento_text = date('d/m/Y', strtotime($emp['proximo_vencimento']));
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $emp['empresa'] ?: $emp['nome']; ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo $emp['cpf_cnpj'] ?: '—'; ?></small>
                            </td>
                            <td>
                                <div><?php echo $emp['nome']; ?></div>
                                <small style="color: var(--text-muted);"><?php echo $emp['email']; ?></small>
                                <div><small><?php echo $emp['telefone'] ?: '—'; ?></small></div>
                            </td>
                            <td>
                                <strong><?php echo $emp['planos_ativos']; ?>/<?php echo $emp['total_planos']; ?></strong> ativos<br>
                                <small><?php echo $emp['total_contratos']; ?> contratos</small>
                            </td>
                            <td>
                                <strong>R$ <?php echo number_format($emp['receita_mensal_total'] ?? 0, 2, ',', '.'); ?></strong>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($emp['cliente_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($emp['proximo_vencimento']): ?>
                                <span class="vencimento-badge <?php echo $vencimento_class; ?>">
                                    <?php echo $vencimento_text; ?>
                                </span>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="../clientes/visualizar.php?id=<?php echo $emp['cliente_id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../clientes/editar.php?id=<?php echo $emp['cliente_id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../contratos/index.php?cliente_id=<?php echo $emp['cliente_id']; ?>" class="btn-icon" title="Contratos">
                                        <i class="fas fa-file-contract"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if($total_paginas > 1): ?>
        <div class="paginacao">
            <a href="?pagina=<?php echo max(1, $pagina-1); ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) : ''; ?>" class="page-link <?php echo $pagina == 1 ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            
            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) : ''; ?>" class="page-link <?php echo $i == $pagina ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <a href="?pagina=<?php echo min($total_paginas, $pagina+1); ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) : ''; ?>" class="page-link <?php echo $pagina == $total_paginas ? 'disabled' : ''; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Theme Toggle
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const body = document.body;

if(themeToggle) {
    themeToggle.addEventListener('click', () => {
        const currentTheme = body.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        body.setAttribute('data-theme', newTheme);
        document.cookie = `admin_theme=${newTheme}; path=/`;
        themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });
}

// Atualizar métricas a cada 5 minutos (opcional)
setInterval(() => {
    location.reload();
}, 300000);
</script>

<?php require_once '../../includes/footer.php'; ?>