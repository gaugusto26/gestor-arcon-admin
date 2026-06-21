<?php
$page_title = 'Gerenciar Inscritos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Processa ações
if(isset($_GET['acao'])) {
    $id = (int)$_GET['id'];
    
    if($_GET['acao'] == 'confirmar') {
        $conn->query("UPDATE newsletter_inscritos SET confirmado = 1, data_confirmacao = NOW() WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Inscrito confirmado manualmente!'];
    }
    
    if($_GET['acao'] == 'ativar') {
        $conn->query("UPDATE newsletter_inscritos SET status = 'ativo' WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Inscrito ativado!'];
    }
    
    if($_GET['acao'] == 'inativar') {
        $conn->query("UPDATE newsletter_inscritos SET status = 'inativo' WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Inscrito inativado!'];
    }
    
    if($_GET['acao'] == 'bloquear') {
        $conn->query("UPDATE newsletter_inscritos SET status = 'bloqueado' WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Inscrito bloqueado!'];
    }
    
    if($_GET['acao'] == 'excluir') {
        $conn->query("DELETE FROM newsletter_inscritos WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Inscrito excluído!'];
    }
    
    header('Location: inscritos.php');
    exit;
}

// Filtros
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$filtro_confirmado = isset($_GET['confirmado']) ? (int)$_GET['confirmado'] : '';
$filtro_origem = isset($_GET['origem']) ? limparInput($_GET['origem']) : '';
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

if($filtro_confirmado !== '') {
    $sql_where .= " AND confirmado = ?";
    $params[] = $filtro_confirmado;
    $types .= "i";
}

if(!empty($filtro_origem)) {
    $sql_where .= " AND origem = ?";
    $params[] = $filtro_origem;
    $types .= "s";
}

if(!empty($busca)) {
    $sql_where .= " AND (nome LIKE ? OR email LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ss";
}

// Conta total para paginação
$sql_total = "SELECT COUNT(*) as total FROM newsletter_inscritos $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_inscritos = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_inscritos / $por_pagina);

// Busca inscritos
$sql = "SELECT * FROM newsletter_inscritos $sql_where ORDER BY created_at DESC LIMIT $offset, $por_pagina";
$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$inscritos = $stmt->get_result();

// Busca estatísticas para os cards
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos")->fetch_assoc()['total'],
    'ativos' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE status = 'ativo'")->fetch_assoc()['total'],
    'confirmados' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE confirmado = 1")->fetch_assoc()['total'],
    'pendentes' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE confirmado = 0")->fetch_assoc()['total'],
    'bloqueados' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE status = 'bloqueado'")->fetch_assoc()['total'],
];

// Origens disponíveis para filtro
$origens = $conn->query("SELECT DISTINCT origem FROM newsletter_inscritos WHERE origem IS NOT NULL ORDER BY origem");
?>

<style>
.inscritos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.stats-mini-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-mini-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-mini-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent);
}

.stat-mini-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: 5px;
}

.stat-mini-label {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.filtros-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    align-items: end;
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
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-primary);
    width: 100%;
}

.btn-filtro {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    background: var(--accent);
    color: white;
    cursor: pointer;
    font-weight: 500;
    height: 42px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-filtro:hover {
    background: #0a4fe0;
}

.btn-limpar {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

.table-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.table-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-primary);
}

.table-title i {
    color: var(--accent);
    margin-right: 8px;
}

.table-actions {
    display: flex;
    gap: 10px;
}

.btn-export {
    padding: 8px 16px;
    background: var(--hover);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}

.btn-export:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
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
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.status-ativo {
    background: #22c55e20;
    color: #22c55e;
}

.status-inativo {
    background: #94a3b820;
    color: #64748b;
}

.status-bloqueado {
    background: #ef444420;
    color: #ef4444;
}

.confirmado-sim {
    color: #22c55e;
    font-weight: 600;
}

.confirmado-nao {
    color: #f59e0b;
}

.origem-badge {
    background: var(--bg-secondary);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    border: 1px solid var(--border);
}

.btn-icon {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0 2px;
    text-decoration: none;
    display: inline-block;
}

.btn-icon:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.paginacao {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    padding: 20px;
}

.page-link {
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.2s ease;
}

.page-link.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.page-link:hover {
    background: var(--hover);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #22c55e20;
    color: #22c55e;
    border: 1px solid #22c55e;
}

.alert-error {
    background: #ef444420;
    color: #ef4444;
    border: 1px solid #ef4444;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-users"></i>
            Gerenciar Inscritos
        </h1>
    </div>

    <div class="content-area">
        <!-- Mensagem de sucesso/erro -->
        <?php if(isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['mensagem']['tipo']; ?>">
            <i class="fas <?php echo $_SESSION['mensagem']['tipo'] == 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
            <?php 
            echo $_SESSION['mensagem']['texto'];
            unset($_SESSION['mensagem']);
            ?>
        </div>
        <?php endif; ?>

        <!-- Mini Stats Cards -->
        <div class="stats-mini-cards">
            <div class="stat-mini-card">
                <div class="stat-mini-value"><?php echo $stats['total']; ?></div>
                <div class="stat-mini-label">Total</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-value"><?php echo $stats['ativos']; ?></div>
                <div class="stat-mini-label">Ativos</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-value"><?php echo $stats['confirmados']; ?></div>
                <div class="stat-mini-label">Confirmados</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-value"><?php echo $stats['pendentes']; ?></div>
                <div class="stat-mini-label">Pendentes</div>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-value"><?php echo $stats['bloqueados']; ?></div>
                <div class="stat-mini-label">Bloqueados</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Nome ou e-mail" value="<?php echo $busca; ?>">
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
                
                <div class="filtro-item">
                    <label><i class="fas fa-check-circle"></i> Confirmação</label>
                    <select name="confirmado">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $filtro_confirmado === 1 ? 'selected' : ''; ?>>Confirmados</option>
                        <option value="0" <?php echo $filtro_confirmado === 0 ? 'selected' : ''; ?>>Pendentes</option>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-source"></i> Origem</label>
                    <select name="origem">
                        <option value="">Todas</option>
                        <?php while($origem = $origens->fetch_assoc()): ?>
                        <option value="<?php echo $origem['origem']; ?>" <?php echo $filtro_origem == $origem['origem'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst($origem['origem']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filtro-item" style="display: flex; flex-direction: row; gap: 10px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="inscritos.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabela de Inscritos -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Lista de Inscritos
                </h3>
                <div class="table-actions">
                    <a href="exportar.php?<?php echo http_build_query($_GET); ?>" class="btn-export" target="_blank">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                    <a href="importar.php" class="btn-export">
                        <i class="fas fa-file-import"></i> Importar
                    </a>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Confirmação</th>
                        <th>Origem</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($inscritos->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px;">
                            <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px; display: block;"></i>
                            <h3 style="margin-bottom: 10px;">Nenhum inscrito encontrado</h3>
                            <p style="color: var(--text-muted);">Tente ajustar os filtros ou importar uma lista.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($insc = $inscritos->fetch_assoc()): 
                            $status_class = '';
                            switch($insc['status']) {
                                case 'ativo': $status_class = 'status-ativo'; break;
                                case 'inativo': $status_class = 'status-inativo'; break;
                                case 'bloqueado': $status_class = 'status-bloqueado'; break;
                            }
                        ?>
                        <tr>
                            <td>#<?php echo $insc['id']; ?></td>
                            <td>
                                <strong><?php echo $insc['nome']; ?></strong>
                                <?php if($insc['token']): ?>
                                <br><small style="color: var(--text-muted);">Token: <?php echo substr($insc['token'], 0, 8); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $insc['email']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($insc['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($insc['confirmado']): ?>
                                <span class="confirmado-sim">
                                    <i class="fas fa-check-circle"></i> Confirmado
                                </span>
                                <br><small><?php echo $insc['data_confirmacao'] ? date('d/m/Y', strtotime($insc['data_confirmacao'])) : ''; ?></small>
                                <?php else: ?>
                                <span class="confirmado-nao">
                                    <i class="fas fa-clock"></i> Pendente
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="origem-badge">
                                    <i class="fas <?php 
                                        switch($insc['origem']) {
                                            case 'blog': echo 'fa-blog'; break;
                                            case 'site': echo 'fa-home'; break;
                                            case 'planos': echo 'fa-crown'; break;
                                            case 'footer': echo 'fa-shoe-prints'; break;
                                            default: echo 'fa-globe';
                                        }
                                    ?>"></i>
                                    <?php echo ucfirst($insc['origem']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($insc['created_at'])); ?><br>
                                <small style="color: var(--text-muted);"><?php echo date('H:i', strtotime($insc['created_at'])); ?></small>
                            </td>
                            <td>
                                <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                                    <?php if(!$insc['confirmado']): ?>
                                    <a href="?acao=confirmar&id=<?php echo $insc['id']; ?>" class="btn-icon" title="Confirmar manualmente">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($insc['status'] != 'ativo'): ?>
                                    <a href="?acao=ativar&id=<?php echo $insc['id']; ?>" class="btn-icon" title="Ativar" style="color: #22c55e;">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($insc['status'] == 'ativo'): ?>
                                    <a href="?acao=inativar&id=<?php echo $insc['id']; ?>" class="btn-icon" title="Inativar" style="color: #f59e0b;">
                                        <i class="fas fa-pause"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($insc['status'] != 'bloqueado'): ?>
                                    <a href="?acao=bloquear&id=<?php echo $insc['id']; ?>" class="btn-icon" title="Bloquear" style="color: #ef4444;">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="editar_inscrito.php?id=<?php echo $insc['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="?acao=excluir&id=<?php echo $insc['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este inscrito?')" style="color: #ef4444;">
                                        <i class="fas fa-trash"></i>
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