<?php
$page_title = 'Pagamentos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$config = getConfigPagamento();
$stats = getEstatisticasPagamentos();

// Filtros
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$filtro_gateway = isset($_GET['gateway']) ? limparInput($_GET['gateway']) : '';
$filtro_forma = isset($_GET['forma']) ? limparInput($_GET['forma']) : '';
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
    $sql_where .= " AND t.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if(!empty($filtro_gateway)) {
    $sql_where .= " AND t.gateway = ?";
    $params[] = $filtro_gateway;
    $types .= "s";
}

if(!empty($filtro_forma)) {
    $sql_where .= " AND t.forma_pagamento = ?";
    $params[] = $filtro_forma;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (c.nome LIKE ? OR c.email LIKE ? OR t.transacao_id LIKE ? OR t.id LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ssss";
}

// Total para paginação
$sql_total = "SELECT COUNT(*) as total FROM pagamento_transacoes t LEFT JOIN clientes c ON t.cliente_id = c.id $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_transacoes = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_transacoes / $por_pagina);

// Busca transações
$sql = "SELECT t.*, 
               c.nome as cliente_nome, 
               c.email as cliente_email,
               c.empresa as cliente_empresa
        FROM pagamento_transacoes t
        LEFT JOIN clientes c ON t.cliente_id = c.id
        $sql_where
        ORDER BY t.created_at DESC
        LIMIT $offset, $por_pagina";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$transacoes = $stmt->get_result();
?>

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
body.dark {
    --card-bg: #1e1e2f;
    --card-border: #333;
    --card-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.stat-card {
    background: var(--card-bg);
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

.stat-card:nth-child(1)::before { background: linear-gradient(135deg, #4361ee 0%, #667eea 100%); }
.stat-card:nth-child(2)::before { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.stat-card:nth-child(3)::before { background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); }
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
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.stat-card:nth-child(3) .stat-icon {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
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

/* Gateway Status */
.gateway-status {
    background: var(--card-bg);
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

.gateway-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.gateway-badge {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.gateway-ativo {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.gateway-inativo {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
}

.gateway-teste {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid #f97316;
}

/* Filtros */
.filtros-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
    background: var(--card-bg);
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
    background: var(--card-bg);
    color: #4361ee;
    border: 1px solid var(--border);
}

/* Tabela */
.table-container {
    background: var(--card-bg);
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
    background: var(--card-bg);
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
    background: var(--card-bg);
}

.status-badge {
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-aprovado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-recusado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-cancelado {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.status-aguardando {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.gateway-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.gateway-mp {
    background: #00b4e0;
    color: white;
}

.gateway-pb {
    background: #0db04b;
    color: white;
}

.pix-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #1e293b;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    cursor: pointer;
}

.pix-badge:hover {
    background: #0f172a;
}

.btn-icon {
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--card-bg);
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
    background: var(--card-bg);
    color: var(--text-secondary);
    text-decoration: none;
}

.page-link.active {
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    border-color: transparent;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--card-bg);
    border-radius: 24px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.3rem;
    color: var(--text-primary);
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s ease;
}

.modal-close:hover {
    color: #ef4444;
}

.modal-body {
    padding: 25px;
    text-align: center;
}

.pix-qrcode {
    max-width: 300px;
    margin: 20px auto;
    padding: 20px;
    background: var(--card-bg);
    border: 2px dashed var(--border);
    border-radius: 20px;
}

.pix-copiaecola {
    background: var(--card-bg);
    padding: 15px;
    border-radius: 12px;
    font-family: monospace;
    word-break: break-all;
    margin: 20px 0;
    border: 1px solid var(--border);
}

.copy-btn {
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.copy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(67,97,238,0.2);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-credit-card" style="color: #4361ee; margin-right: 10px;"></i>
            Pagamentos
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Status do Gateway -->
        <div class="gateway-status">
            <div class="gateway-info">
                <div class="gateway-badge <?php 
                    echo $config['gateway'] == 'nenhum' ? 'gateway-inativo' : 
                        ($config['modo'] == 'teste' ? 'gateway-teste' : 'gateway-ativo'); 
                ?>">
                    <i class="fas fa-<?php 
                        echo $config['gateway'] == 'mercadopago' ? 'dollar-sign' : 
                            ($config['gateway'] == 'pagbank' ? 'wallet' : 'times'); 
                    ?>"></i>
                    Gateway: <?php 
                        echo $config['gateway'] == 'mercadopago' ? 'Mercado Pago' : 
                            ($config['gateway'] == 'pagbank' ? 'PagBank' : 'Nenhum'); 
                    ?>
                </div>
                
                <?php if($config['gateway'] != 'nenhum'): ?>
                <div class="gateway-badge <?php echo $config['modo'] == 'teste' ? 'gateway-teste' : 'gateway-ativo'; ?>">
                    <i class="fas fa-<?php echo $config['modo'] == 'teste' ? 'flask' : 'rocket'; ?>"></i>
                    Modo: <?php echo ucfirst($config['modo']); ?>
                </div>
                
                <div class="gateway-badge gateway-ativo">
                    <i class="fas fa-qrcode"></i>
                    PIX: <?php echo $config['pix_key'] ? 'Configurado' : 'Não configurado'; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <a href="configurar.php" class="btn-filtro">
                <i class="fas fa-cog"></i> Configurar
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['totais']['total_transacoes'] ?? 0); ?></div>
                <div class="stat-label">Total de Transações</div>
                <div class="stat-trend">
                    <i class="fas fa-check-circle"></i> <?php echo $stats['totais']['total_aprovados'] ?? 0; ?> aprovadas
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($stats['totais']['valor_total_aprovado'] ?? 0, 2, ',', '.'); ?></div>
                <div class="stat-label">Valor Aprovado</div>
                <div class="stat-trend">
                    <i class="fas fa-clock"></i> R$ <?php echo number_format($stats['totais']['valor_total_pendente'] ?? 0, 2, ',', '.'); ?> pendente
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['ultimas_24h']['total'] ?? 0); ?></div>
                <div class="stat-label">Últimas 24h</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> R$ <?php echo number_format($stats['ultimas_24h']['total_valor'] ?? 0, 2, ',', '.'); ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['mes_atual']['total'] ?? 0); ?></div>
                <div class="stat-label">Este Mês</div>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i> R$ <?php echo number_format($stats['mes_atual']['total_valor'] ?? 0, 2, ',', '.'); ?>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Cliente, transação ou ID" value="<?php echo $busca; ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="aprovado" <?php echo $filtro_status == 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                        <option value="pendente" <?php echo $filtro_status == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="recusado" <?php echo $filtro_status == 'recusado' ? 'selected' : ''; ?>>Recusado</option>
                        <option value="cancelado" <?php echo $filtro_status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        <option value="aguardando" <?php echo $filtro_status == 'aguardando' ? 'selected' : ''; ?>>Aguardando</option>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-credit-card"></i> Forma</label>
                    <select name="forma">
                        <option value="">Todas</option>
                        <option value="cartao_credito" <?php echo $filtro_forma == 'cartao_credito' ? 'selected' : ''; ?>>Cartão Crédito</option>
                        <option value="cartao_debito" <?php echo $filtro_forma == 'cartao_debito' ? 'selected' : ''; ?>>Cartão Débito</option>
                        <option value="pix" <?php echo $filtro_forma == 'pix' ? 'selected' : ''; ?>>PIX</option>
                        <option value="boleto" <?php echo $filtro_forma == 'boleto' ? 'selected' : ''; ?>>Boleto</option>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-credit-card"></i> Gateway</label>
                    <select name="gateway">
                        <option value="">Todos</option>
                        <option value="mercadopago" <?php echo $filtro_gateway == 'mercadopago' ? 'selected' : ''; ?>>Mercado Pago</option>
                        <option value="pagbank" <?php echo $filtro_gateway == 'pagbank' ? 'selected' : ''; ?>>PagBank</option>
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

        <!-- Tabela de Transações -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Transações Recentes
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Forma</th>
                        <th>Gateway</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($transacoes->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px;">
                            <i class="fas fa-credit-card" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
                            <h3>Nenhuma transação encontrada</h3>
                            <p style="color: var(--text-muted);">As transações aparecerão aqui quando houver pagamentos</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($trans = $transacoes->fetch_assoc()): 
                            $status_class = '';
                            switch($trans['status']) {
                                case 'aprovado': $status_class = 'status-aprovado'; break;
                                case 'pendente': $status_class = 'status-pendente'; break;
                                case 'recusado': $status_class = 'status-recusado'; break;
                                case 'cancelado': $status_class = 'status-cancelado'; break;
                                case 'aguardando': $status_class = 'status-aguardando'; break;
                            }
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $trans['id']; ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo substr($trans['transacao_id'], 0, 15); ?>...</small>
                            </td>
                            <td>
                                <strong><?php echo $trans['cliente_nome']; ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo $trans['cliente_empresa'] ?: $trans['cliente_email']; ?></small>
                            </td>
                            <td>
                                <strong>R$ <?php echo number_format($trans['valor'], 2, ',', '.'); ?></strong><br>
                                <small><?php echo $trans['parcelas'] > 1 ? $trans['parcelas'] . 'x' : 'à vista'; ?></small>
                            </td>
                            <td>
                                <?php if($trans['forma_pagamento'] == 'pix'): ?>
                                    <span class="pix-badge" onclick="verPIX(<?php echo $trans['id']; ?>)">
                                        <i class="fas fa-qrcode"></i> PIX
                                    </span>
                                <?php else: ?>
                                    <?php 
                                    $forma = str_replace('_', ' ', $trans['forma_pagamento']);
                                    echo ucwords($forma);
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="gateway-tag gateway-<?php echo $trans['gateway'] == 'mercadopago' ? 'mp' : 'pb'; ?>">
                                    <i class="fas fa-<?php echo $trans['gateway'] == 'mercadopago' ? 'dollar-sign' : 'wallet'; ?>"></i>
                                    <?php echo $trans['gateway'] == 'mercadopago' ? 'MP' : 'PB'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($trans['status']); ?>
                                </span>
                                <?php if($trans['data_aprovacao']): ?>
                                <br><small><?php echo date('d/m H:i', strtotime($trans['data_aprovacao'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($trans['created_at'])); ?><br>
                                <small><?php echo date('H:i', strtotime($trans['created_at'])); ?></small>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="visualizar.php?id=<?php echo $trans['id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if($trans['status'] == 'pendente' && $trans['forma_pagamento'] == 'pix'): ?>
                                    <button class="btn-icon" onclick="verPIX(<?php echo $trans['id']; ?>)" title="Ver PIX">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
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

<!-- Modal PIX -->
<div id="pixModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-qrcode"></i> Pagamento PIX</h3>
            <span class="modal-close" onclick="fecharModalPIX()">&times;</span>
        </div>
        <div class="modal-body" id="pixModalBody">
            Carregando...
        </div>
    </div>
</div>

<script>
function verPIX(transacaoId) {
    const modal = document.getElementById('pixModal');
    const body = document.getElementById('pixModalBody');
    
    body.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i><p>Carregando...</p></div>';
    modal.style.display = 'flex';
    
    fetch('get_pix.php?id=' + transacaoId)
        .then(response => response.json())
        .then(data => {
            if(data.sucesso) {
                body.innerHTML = `
                    <div class="pix-qrcode">
                        <img src="${data.qrcode}" style="max-width: 100%;">
                    </div>
                    <div class="pix-copiaecola" id="pixCode">
                        ${data.copiaecola}
                    </div>
                    <button class="copy-btn" onclick="copiarPIX()">
                        <i class="fas fa-copy"></i> Copiar código PIX
                    </button>
                    <p style="margin-top: 15px; color: #f97316;">
                        <i class="fas fa-clock"></i> Expira em: ${data.expiracao}
                    </p>
                `;
            } else {
                body.innerHTML = `<p style="color: #ef4444;">${data.erro}</p>`;
            }
        })
        .catch(error => {
            body.innerHTML = `<p style="color: #ef4444;">Erro ao carregar PIX</p>`;
        });
}

function fecharModalPIX() {
    document.getElementById('pixModal').style.display = 'none';
}

function copiarPIX() {
    const pixCode = document.getElementById('pixCode').innerText;
    navigator.clipboard.writeText(pixCode);
    alert('✅ Código PIX copiado!');
}

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

// Fecha modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('pixModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>