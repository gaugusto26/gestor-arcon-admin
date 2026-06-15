<?php
$page_title = 'Contratos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Filtros
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$filtro_tipo = isset($_GET['tipo']) ? limparInput($_GET['tipo']) : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 15;
$offset = ($pagina - 1) * $por_pagina;

// Monta query
$sql_where = "WHERE 1=1";
$params = [];
$types = "";

if(!empty($filtro_status)) {
    $sql_where .= " AND c.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if(!empty($filtro_tipo)) {
    $sql_where .= " AND c.tipo_contrato = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (c.numero_contrato LIKE ? OR cl.nome LIKE ? OR cl.email LIKE ? OR cl.empresa LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ssss";
}

// Total para paginação
$sql_total = "SELECT COUNT(*) as total FROM contratos c LEFT JOIN clientes cl ON c.cliente_id = cl.id $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_contratos = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_contratos / $por_pagina);

// Busca contratos
$sql = "SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email, cl.empresa as cliente_empresa,
               pc.nome_plano, pc.valor_plano, pc.valor_mensal
        FROM contratos c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
        $sql_where
        ORDER BY c.created_at DESC
        LIMIT $offset, $por_pagina";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$contratos = $stmt->get_result();

// Estatísticas
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as total FROM contratos")->fetch_assoc()['total'],
    'rascunho' => $conn->query("SELECT COUNT(*) as total FROM contratos WHERE status = 'rascunho'")->fetch_assoc()['total'],
    'assinados' => $conn->query("SELECT COUNT(*) as total FROM contratos WHERE status = 'assinado'")->fetch_assoc()['total'],
    'cancelados' => $conn->query("SELECT COUNT(*) as total FROM contratos WHERE status = 'cancelado'")->fetch_assoc()['total'],
    'valor_total' => $conn->query("SELECT SUM(valor_total) as total FROM contratos WHERE status = 'assinado'")->fetch_assoc()['total'],
];
?>

<style>
.contratos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

body.dark .quick-template {
    background: #1e1e2f;
    border: 1px solid #333;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    color: #f0f0f0;
}


.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
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
    background: linear-gradient(135deg, #6685ea 0%, #4b6ea2 100%);
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
    background: linear-gradient(135deg, #669bea 0%, #4b78a2 100%);
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

.status-rascunho {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-enviado {
    background: rgba(67, 97, 238, 0.1);
    color: #4361ee;
}

.status-assinado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-cancelado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-vencido {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.tipo-badge {
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--card-bg);
    color: #4361ee;
    border: 1px solid var(--border);
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
    margin: 0 2px;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #669fea 0%, #4b62a2 100%);
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
    background: var(--card-bg);
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.2s ease;
}

.page-link.active {
    background: linear-gradient(135deg, #66afea 0%, #4b74a2 100%);
    color: white;
    border-color: transparent;
}

.page-link:hover {
    background: var(--card-bg);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-signature" style="color: #4361ee; margin-right: 10px;"></i>
            Contratos
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
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total de Contratos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-pen"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['rascunho']); ?></div>
                <div class="stat-label">Rascunhos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['assinados']); ?></div>
                <div class="stat-label">Assinados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['cancelados']); ?></div>
                <div class="stat-label">Cancelados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($stats['valor_total'] ?? 0, 2, ',', '.'); ?></div>
                <div class="stat-label">Valor Total</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="criar.php" class="btn-action">
                <i class="fas fa-plus"></i> Novo Contrato
            </a>
            <a href="../gerador/index.php" class="btn-action secondary">
                <i class="fas fa-file-alt"></i> Gerador de Contratos
            </a>
            <a href="../clientes/index.php" class="btn-action secondary">
                <i class="fas fa-users"></i> Clientes
            </a>
            <a href="modelos.php" class="btn-action secondary">
                <i class="fas fa-copy"></i> Modelos
            </a>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Nº contrato, cliente, empresa" value="<?php echo $busca; ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="rascunho" <?php echo $filtro_status == 'rascunho' ? 'selected' : ''; ?>>Rascunho</option>
                        <option value="enviado" <?php echo $filtro_status == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                        <option value="assinado" <?php echo $filtro_status == 'assinado' ? 'selected' : ''; ?>>Assinado</option>
                        <option value="cancelado" <?php echo $filtro_status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        <option value="vencido" <?php echo $filtro_status == 'vencido' ? 'selected' : ''; ?>>Vencido</option>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-file"></i> Tipo</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <option value="adesao" <?php echo $filtro_tipo == 'adesao' ? 'selected' : ''; ?>>Adesão</option>
                        <option value="renovacao" <?php echo $filtro_tipo == 'renovacao' ? 'selected' : ''; ?>>Renovação</option>
                        <option value="cancelamento" <?php echo $filtro_tipo == 'cancelamento' ? 'selected' : ''; ?>>Cancelamento</option>
                        <option value="aditivo" <?php echo $filtro_tipo == 'aditivo' ? 'selected' : ''; ?>>Aditivo</option>
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

        <!-- Tabela de Contratos -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Lista de Contratos
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Nº Contrato</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Plano</th>
                        <th>Valores</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($contratos->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px;">
                            <i class="fas fa-file-contract" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
                            <h3 style="margin-bottom: 10px;">Nenhum contrato encontrado</h3>
                            <p style="color: var(--text-muted);">Crie seu primeiro contrato</p>
                            <a href="criar.php" class="btn-action" style="display: inline-block; margin-top: 20px;">
                                <i class="fas fa-plus"></i> Criar Contrato
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($cont = $contratos->fetch_assoc()): 
                            $status_class = '';
                            switch($cont['status']) {
                                case 'rascunho': $status_class = 'status-rascunho'; break;
                                case 'enviado': $status_class = 'status-enviado'; break;
                                case 'assinado': $status_class = 'status-assinado'; break;
                                case 'cancelado': $status_class = 'status-cancelado'; break;
                                case 'vencido': $status_class = 'status-vencido'; break;
                            }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $cont['numero_contrato']; ?></strong><br>
                                <small style="color: var(--text-muted);">v<?php echo $cont['versao']; ?></small>
                            </td>
                            <td>
                                <strong><?php echo $cont['cliente_nome']; ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo $cont['cliente_empresa'] ?: $cont['cliente_email']; ?></small>
                            </td>
                            <td>
                                <span class="tipo-badge">
                                    <?php echo ucfirst($cont['tipo_contrato']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo $cont['nome_plano'] ?: 'Personalizado'; ?></strong><br>
                                <small style="color: var(--text-muted);">Mensal: R$ <?php echo number_format($cont['valor_mensal'] ?? 0, 2, ',', '.'); ?></small>
                            </td>
                            <td>
                                <strong>R$ <?php echo number_format($cont['valor_total'] ?? 0, 2, ',', '.'); ?></strong><br>
                                <small style="color: var(--text-muted);">Multa: R$ <?php echo number_format($cont['multa_cancelamento'] ?? 0, 2, ',', '.'); ?></small>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($cont['status']); ?>
                                </span>
                                <?php if($cont['data_assinatura']): ?>
                                <br><small style="color: var(--text-muted);"><?php echo date('d/m/Y', strtotime($cont['data_assinatura'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($cont['created_at'])); ?><br>
                                <small style="color: var(--text-muted);"><?php echo date('H:i', strtotime($cont['created_at'])); ?></small>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="visualizar.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if($cont['status'] == 'rascunho'): ?>
                                    <a href="enviar.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Enviar para cliente" style="color: #10b981;">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if($cont['status'] == 'assinado'): ?>
                                    <a href="pdf.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Baixar PDF" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="historico.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Histórico">
                                        <i class="fas fa-history"></i>
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