<?php
$page_title = 'Clientes';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? limparInput($_GET['tipo']) : '';
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

if(!empty($filtro_tipo)) {
    $sql_where .= " AND tipo = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

if(!empty($filtro_status)) {
    $sql_where .= " AND status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (nome LIKE ? OR email LIKE ? OR empresa LIKE ? OR username LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ssss";
}

// Total para paginação
$sql_total = "SELECT COUNT(*) as total FROM clientes $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_clientes = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_clientes / $por_pagina);

// Busca clientes
$sql = "SELECT c.*, 
               (SELECT COUNT(*) FROM planos_contratados WHERE cliente_id = c.id AND status = 'ativo') as total_planos_ativos,
               (SELECT COUNT(*) FROM contratos WHERE cliente_id = c.id AND status = 'assinado') as total_contratos
        FROM clientes c
        $sql_where
        ORDER BY c.created_at DESC
        LIMIT $offset, $por_pagina";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$clientes = $stmt->get_result();

// Estatísticas
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'],
    'ativos' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE status = 'ativo'")->fetch_assoc()['total'],
    'clientes' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo = 'cliente'")->fetch_assoc()['total'],
    'parceiros' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo = 'parceiro'")->fetch_assoc()['total'],
    'hoje' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'],
];
?>

<style>
.clientes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
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
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    border-color: #4361ee;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: rgba(67, 97, 238, 0.1);
    color: #4361ee;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 1.8rem;
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

.filtros-box {
    background: #ffffff;
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
    background: #f8faff;
    color: var(--text-primary);
}

.btn-filtro {
    padding: 12px 20px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #66b5ea 0%, #4b69a2 100%);
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

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 14px 25px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #669bea 0%, #4b6ea2 100%);
    color: white;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(102,126,234,0.3);
}

.btn-action.secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

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

.cliente-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cliente-avatar {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6689ea 0%, #4b5fa2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.cliente-details {
    flex: 1;
}

.cliente-nome {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.cliente-email {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.status-badge {
    padding: 6px 14px;
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

.tipo-badge {
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.tipo-badge.admin {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border-color: #f97316;
}

.tipo-badge.parceiro {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
    border-color: #8b5cf6;
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
    margin: 0 2px;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #66a4ea 0%, #4b69a2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

.paginacao {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
}

.page-link {
    padding: 10px 16px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.2s ease;
}

.page-link.active {
    background: linear-gradient(135deg, #66a4ea 0%, #4b71a2 100%);
    color: white;
    border-color: transparent;
}

.page-link:hover {
    background: #f8faff;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 20px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-users" style="color: #4361ee; margin-right: 10px;"></i>
            Clientes
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
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total de Clientes</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> +<?php echo $stats['hoje']; ?> hoje
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['ativos']); ?></div>
                <div class="stat-label">Ativos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['clientes']); ?></div>
                <div class="stat-label">Clientes</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['parceiros']); ?></div>
                <div class="stat-label">Parceiros</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value">R$ 45.678</div>
                <div class="stat-label">Receita Mensal</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="criar.php" class="btn-action">
                <i class="fas fa-user-plus"></i> Novo Cliente
            </a>
            <a href="gerenciar.php" class="btn-action secondary">
                <i class="fas fa-user-cog"></i> Usuários
            </a>
            <a href="importar.php" class="btn-action secondary">
                <i class="fas fa-file-import"></i> Importar
            </a>
            <a href="exportar.php" class="btn-action secondary">
                <i class="fas fa-file-export"></i> Exportar
            </a>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Nome, email, empresa" value="<?php echo $busca; ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Tipo</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <option value="cliente" <?php echo $filtro_tipo == 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                        <option value="admin" <?php echo $filtro_tipo == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="parceiro" <?php echo $filtro_tipo == 'parceiro' ? 'selected' : ''; ?>>Parceiro</option>
                    </select>
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

        <!-- Tabela de Clientes -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Lista de Clientes
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Usuário</th>
                        <th>Contato</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Planos</th>
                        <th>Último Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($clientes->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <h3>Nenhum cliente encontrado</h3>
                            <p>Comece cadastrando seu primeiro cliente</p>
                            <a href="criar.php" class="btn-action" style="display: inline-block;">
                                <i class="fas fa-user-plus"></i> Cadastrar Cliente
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($cli = $clientes->fetch_assoc()): 
                            $status_class = '';
                            switch($cli['status']) {
                                case 'ativo': $status_class = 'status-ativo'; break;
                                case 'inativo': $status_class = 'status-inativo'; break;
                                case 'bloqueado': $status_class = 'status-bloqueado'; break;
                            }
                            
                            $tipo_class = 'tipo-badge';
                            if($cli['tipo'] == 'admin') $tipo_class .= ' admin';
                            if($cli['tipo'] == 'parceiro') $tipo_class .= ' parceiro';
                        ?>
                        <tr>
                            <td>
                                <div class="cliente-info">
                                    <div class="cliente-avatar">
                                        <?php echo strtoupper(substr($cli['nome'], 0, 1)); ?>
                                    </div>
                                    <div class="cliente-details">
                                        <div class="cliente-nome"><?php echo $cli['nome']; ?></div>
                                        <div class="cliente-email"><?php echo $cli['empresa'] ?: '—'; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo $cli['username']; ?></strong>
                                <?php if($cli['ultimo_acesso']): ?>
                                <br><small style="color: var(--text-muted);">Último: <?php echo date('d/m/Y', strtotime($cli['ultimo_acesso'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?php echo $cli['email']; ?></div>
                                <small style="color: var(--text-muted);"><?php echo $cli['telefone'] ?: '—'; ?></small>
                            </td>
                            <td>
                                <span class="<?php echo $tipo_class; ?>">
                                    <?php echo ucfirst($cli['tipo']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($cli['status']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo $cli['total_planos_ativos']; ?></strong> ativos<br>
                                <small><?php echo $cli['total_contratos']; ?> contratos</small>
                            </td>
                            <td>
                                <?php if($cli['ultimo_acesso']): ?>
                                    <?php echo date('d/m/Y H:i', strtotime($cli['ultimo_acesso'])); ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">Nunca acessou</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="visualizar.php?id=<?php echo $cli['id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?php echo $cli['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="login-as.php?id=<?php echo $cli['id']; ?>" class="btn-icon" title="Logar como cliente" style="color: #8b5cf6;">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </a>
                                    <a href="contratos.php?id=<?php echo $cli['id']; ?>" class="btn-icon" title="Ver contratos">
                                        <i class="fas fa-file-contract"></i>
                                    </a>
                                    <a href="enviar-email.php?id=<?php echo $cli['id']; ?>" class="btn-icon" title="Enviar e-mail" style="color: #10b981;">
                                        <i class="fas fa-envelope"></i>
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
</script>

<?php require_once '../../includes/footer.php'; ?>
