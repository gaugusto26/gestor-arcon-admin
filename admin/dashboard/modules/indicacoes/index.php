<?php
$page_title = 'Indicações';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';
require_once '../../../../config.php';

$stats = getEstatisticasIndicacoes();
$config = $conn->query("SELECT * FROM indicacoes_config WHERE id = 1")->fetch_assoc();

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
    $sql_where .= " AND i.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (c.nome LIKE ? OR c.empresa LIKE ? OR i.nome_indicado LIKE ? OR i.codigo_indicacao LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ssss";
}

// Total para paginação
$sql_total = "SELECT COUNT(*) as total FROM indicacoes i LEFT JOIN clientes c ON i.indicador_id = c.id $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_indicacoes = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_indicacoes / $por_pagina);

// Busca indicações
$sql = "SELECT i.*, 
               c.nome as indicador_nome, 
               c.empresa as indicador_empresa,
               c.codigo_indicacao as codigo_indicador,
               cl.nome as cliente_nome,
               cl.empresa as cliente_empresa
        FROM indicacoes i
        LEFT JOIN clientes c ON i.indicador_id = c.id
        LEFT JOIN clientes cl ON i.indicado_id = cl.id
        $sql_where
        ORDER BY i.created_at DESC
        LIMIT $offset, $por_pagina";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$indicacoes = $stmt->get_result();
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
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
}

.stat-card:nth-child(1)::before { background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); }
.stat-card:nth-child(2)::before { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
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
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.stat-card:nth-child(2) .stat-icon {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
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
}

.stat-trend {
    font-size: 0.85rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
}

/* Config Bar */
.config-bar {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.config-info {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.config-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.config-item i {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.config-badge {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.config-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(16,185,129,0.2);
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
    color: #10b981;
}

/* Top Indicadores */
.top-indicadores {
    margin-top: 20px;
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
    color: #10b981;
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
    color: #10b981;
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
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
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
    color: #10b981;
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
    color: #10b981;
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
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-pendente {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.status-convertido {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-expirado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-cancelado {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
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
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
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
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
    border-color: transparent;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-hand-holding-heart" style="color: #10b981; margin-right: 10px;"></i>
            Indicações
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Config Bar -->
        <div class="config-bar">
            <div class="config-info">
                <div class="config-item">
                    <i class="fas fa-percent"></i>
                    <span><?php echo $config['percentual_desconto']; ?>% de desconto para indicador e indicado</span>
                </div>
                <div class="config-item">
                    <i class="fas fa-clock"></i>
                    <span>Validade: <?php echo $config['dias_validade']; ?> dias</span>
                </div>
                <div class="config-item">
                    <i class="fas fa-infinity"></i>
                    <span>Limite: <?php echo $config['limite_indicacoes'] ?: 'Ilimitado'; ?></span>
                </div>
            </div>
            <a href="configurar.php" class="config-badge">
                <i class="fas fa-cog"></i> Configurar
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total de Indicações</div>
                <div class="stat-trend">
                    <i class="fas fa-user-plus"></i> <?php echo $stats['total_indicadores']; ?> indicadores
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['status_pendente'] ?? 0); ?></div>
                <div class="stat-label">Pendentes</div>
                <div class="stat-trend" style="color: #f59e0b;">
                    <i class="fas fa-hourglass-half"></i> Aguardando conversão
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['status_convertido'] ?? 0); ?></div>
                <div class="stat-label">Convertidas</div>
                <div class="stat-trend">
                    <i class="fas fa-trend-up"></i> Taxa: <?php echo $stats['total'] > 0 ? round(($stats['status_convertido'] / $stats['total']) * 100, 1) : 0; ?>%
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($stats['total_descontos'] ?? 0, 2, ',', '.'); ?></div>
                <div class="stat-label">Descontos Concedidos</div>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i> Média: <?php echo !empty($stats['status_convertido']) ? round($stats['total_descontos'] / $stats['status_convertido'], 2) : 0; ?>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <!-- Top Indicadores -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="fas fa-trophy"></i> Top Indicadores
                    </h3>
                    <a href="top.php" class="btn-icon">Ver todos</a>
                </div>
                
                <div class="top-indicadores">
                    <?php 
                    $pos = 1;
                    while($top = $stats['top_indicadores']->fetch_assoc()): 
                    ?>
                    <div class="top-item">
                        <div class="top-position">#<?php echo $pos++; ?></div>
                        <div class="top-info">
                            <div class="top-nome"><?php echo $top['nome']; ?></div>
                            <div class="top-stats">
                                <span><i class="fas fa-tag"></i> <?php echo $top['total_indicacoes']; ?> indicações</span>
                                <span><i class="fas fa-check-circle"></i> <?php echo $top['convertidas']; ?> convertidas</span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Indicações por Mês -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-line"></i> Indicações por Mês
                    </h3>
                </div>
                
                <div style="height: 200px; position: relative;">
                    <?php 
                    $meses = [];
                    $totais = [];
                    $convertidas = [];
                    
                    $stats['indicacoes_mes']->data_seek(0);
                    while($row = $stats['indicacoes_mes']->fetch_assoc()) {
                        $meses[] = date('M/Y', strtotime($row['mes'] . '-01'));
                        $totais[] = $row['total'];
                        $convertidas[] = $row['convertidas'];
                    }
                    
                    $max = !empty($totais) ? max($totais) : 1;

                    ?>
                    
                    <div style="display: flex; align-items: flex-end; gap: 10px; height: 150px; margin-top: 20px;">
                        <?php for($i = 0; $i < count($meses); $i++): ?>
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                <div style="width: 30px; height: <?php echo ($totais[$i] / $max) * 100; ?>px; background: #10b981; border-radius: 6px 6px 0 0;"></div>
                                <div style="width: 30px; height: <?php echo ($convertidas[$i] / $max) * 100; ?>px; background: #34d399; border-radius: 0 0 6px 6px;"></div>
                            </div>
                            <div style="font-size: 0.7rem; margin-top: 8px; color: var(--text-muted);"><?php echo $meses[$i]; ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 15px;">
                        <span style="display: flex; align-items: center; gap: 5px; font-size: 0.8rem;">
                            <span style="width: 12px; height: 12px; background: #10b981; border-radius: 3px;"></span> Totais
                        </span>
                        <span style="display: flex; align-items: center; gap: 5px; font-size: 0.8rem;">
                            <span style="width: 12px; height: 12px; background: #34d399; border-radius: 3px;"></span> Convertidas
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Indicador, indicado ou código" value="<?php echo $busca; ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="pendente" <?php echo $filtro_status == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="convertido" <?php echo $filtro_status == 'convertido' ? 'selected' : ''; ?>>Convertido</option>
                        <option value="expirado" <?php echo $filtro_status == 'expirado' ? 'selected' : ''; ?>>Expirado</option>
                        <option value="cancelado" <?php echo $filtro_status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
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

        <!-- Tabela de Indicações -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Lista de Indicações
                </h3>
                <a href="nova.php" class="btn-filtro">
                    <i class="fas fa-plus"></i> Nova Indicação
                </a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Indicador</th>
                        <th>Indicado</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Desconto</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($indicacoes->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px;">
                            <i class="fas fa-hand-holding-heart" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
                            <h3>Nenhuma indicação encontrada</h3>
                            <p style="color: var(--text-muted);">As indicações aparecerão aqui quando os clientes começarem a indicar</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($ind = $indicacoes->fetch_assoc()): 
                            $status_class = '';
                            switch($ind['status']) {
                                case 'pendente': $status_class = 'status-pendente'; break;
                                case 'convertido': $status_class = 'status-convertido'; break;
                                case 'expirado': $status_class = 'status-expirado'; break;
                                case 'cancelado': $status_class = 'status-cancelado'; break;
                            }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $ind['codigo_indicacao']; ?></strong>
                            </td>
                            <td>
                                <strong><?php echo $ind['indicador_nome']; ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo $ind['indicador_empresa'] ?: '—'; ?></small>
                            </td>
                            <td>
                                <?php if($ind['indicado_id']): ?>
                                    <strong><?php echo $ind['cliente_nome']; ?></strong><br>
                                    <small style="color: var(--text-muted);"><?php echo $ind['cliente_empresa'] ?: '—'; ?></small>
                                <?php else: ?>
                                    <span><?php echo $ind['nome_indicado'] ?: '—'; ?></span><br>
                                    <small style="color: var(--text-muted);"><?php echo $ind['email_indicado']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($ind['status']); ?>
                                </span>
                                <?php if($ind['data_expiracao'] && $ind['status'] == 'pendente'): ?>
                                <br><small>Expira: <?php echo date('d/m/Y', strtotime($ind['data_expiracao'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($ind['data_indicacao'])); ?><br>
                                <small style="color: var(--text-muted);"><?php echo date('H:i', strtotime($ind['data_indicacao'])); ?></small>
                            </td>
                            <td>
                                <?php if($ind['desconto_aplicado']): ?>
                                    <strong>R$ <?php echo number_format($ind['desconto_aplicado'], 2, ',', '.'); ?></strong>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="visualizar.php?id=<?php echo $ind['id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if($ind['status'] == 'pendente'): ?>
                                    <a href="converter.php?id=<?php echo $ind['id']; ?>" class="btn-icon" title="Converter" style="color: #10b981;">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                    <a href="cancelar.php?id=<?php echo $ind['id']; ?>" class="btn-icon" title="Cancelar" style="color: #ef4444;" onclick="return confirm('Cancelar esta indicação?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                    <?php endif; ?>
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
</script>

<?php require_once '../../includes/footer.php'; ?>