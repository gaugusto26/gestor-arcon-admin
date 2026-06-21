<?php
// Primeiro processa todas as ações ANTES de qualquer saída HTML
require_once 'config.php';
require_once '../../../../config.php';
precisaLogin();

// Processa ações ANTES de qualquer header ou HTML
if(isset($_GET['acao']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if($_GET['acao'] == 'bloquear') {
        $conn->query("UPDATE clientes SET status = 'bloqueado' WHERE id = $id");
        registrarLogCliente($id, "Usuário bloqueado pelo admin");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Usuário bloqueado com sucesso!'];
        header('Location: gerenciar.php');
        exit;
    }
    
    if($_GET['acao'] == 'ativar') {
        $conn->query("UPDATE clientes SET status = 'ativo' WHERE id = $id");
        registrarLogCliente($id, "Usuário ativado pelo admin");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Usuário ativado com sucesso!'];
        header('Location: gerenciar.php');
        exit;
    }
    
    if($_GET['acao'] == 'resetar_senha') {
        // Gera nova senha
        $nova_senha = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        // Atualiza no banco
        $conn->query("UPDATE clientes SET password_hash = '$hash' WHERE id = $id");
        
        // Busca dados do cliente
        $cliente = $conn->query("SELECT nome, email FROM clientes WHERE id = $id")->fetch_assoc();
        
        if($cliente) {
            // Envia e-mail com a nova senha
            $enviado = enviarEmailNovaSenha($cliente['email'], $cliente['nome'], $nova_senha);
            
            if($enviado) {
                $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Senha resetada! Nova senha enviada para o e-mail do cliente.'];
            } else {
                $_SESSION['mensagem'] = ['tipo' => 'aviso', 'texto' => 'Senha resetada, mas houve erro ao enviar o e-mail. A nova senha é: ' . $nova_senha];
            }
        }
        
        registrarLogCliente($id, "Senha resetada pelo admin");
        header('Location: gerenciar.php');
        exit;
    }
    
    if($_GET['acao'] == 'excluir') {
        // Verificar se tem contratos
        $check = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE cliente_id = $id")->fetch_assoc();
        if($check['total'] > 0) {
            $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Não é possível excluir cliente com contratos vinculados!'];
        } else {
            // Verificar se tem planos contratados
            $check_planos = $conn->query("SELECT COUNT(*) as total FROM planos_contratados WHERE cliente_id = $id")->fetch_assoc();
            if($check_planos['total'] > 0) {
                $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Não é possível excluir cliente com planos contratados!'];
            } else {
                $conn->query("DELETE FROM clientes WHERE id = $id");
                $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Usuário excluído com sucesso!'];
            }
        }
        header('Location: gerenciar.php');
        exit;
    }
}

// Só DEPOIS de processar as ações, inclui o header
$page_title = 'Usuários';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? limparInput($_GET['tipo']) : '';
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 20;
$offset = ($pagina - 1) * $por_pagina;

// Monta query com filtros
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
    $sql_where .= " AND (nome LIKE ? OR email LIKE ? OR username LIKE ? OR empresa LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ssss";
}

// Conta total para paginação
$sql_total = "SELECT COUNT(*) as total FROM clientes $sql_where";
$stmt_total = $conn->prepare($sql_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_usuarios = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_usuarios / $por_pagina);

// Busca usuários com paginação
$sql = "SELECT * FROM clientes $sql_where ORDER BY 
        CASE 
            WHEN tipo = 'admin' THEN 1
            WHEN tipo = 'parceiro' THEN 2
            ELSE 3
        END,
        nome ASC
        LIMIT $offset, $por_pagina";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$usuarios = $stmt->get_result();

// Estatísticas
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'],
    'admins' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo = 'admin'")->fetch_assoc()['total'],
    'clientes' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo = 'cliente'")->fetch_assoc()['total'],
    'parceiros' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo = 'parceiro'")->fetch_assoc()['total'],
    'ativos' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE status = 'ativo'")->fetch_assoc()['total'],
    'inativos' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE status = 'inativo'")->fetch_assoc()['total'],
    'bloqueados' => $conn->query("SELECT COUNT(*) as total FROM clientes WHERE status = 'bloqueado'")->fetch_assoc()['total'],
];

$abas_usuarios = [
    [
        'label' => 'Todos',
        'icone' => 'fa-layer-group',
        'href' => 'gerenciar.php',
        'count' => $stats['total'],
        'active' => empty($filtro_tipo) && empty($filtro_status)
    ],
    [
        'label' => 'Clientes',
        'icone' => 'fa-users',
        'href' => 'gerenciar.php?tipo=cliente',
        'count' => $stats['clientes'],
        'active' => $filtro_tipo === 'cliente'
    ],
    [
        'label' => 'Administradores',
        'icone' => 'fa-user-shield',
        'href' => 'gerenciar.php?tipo=admin',
        'count' => $stats['admins'],
        'active' => $filtro_tipo === 'admin'
    ],
    [
        'label' => 'Parceiros',
        'icone' => 'fa-handshake',
        'href' => 'gerenciar.php?tipo=parceiro',
        'count' => $stats['parceiros'],
        'active' => $filtro_tipo === 'parceiro'
    ],
    [
        'label' => 'Bloqueados',
        'icone' => 'fa-ban',
        'href' => 'gerenciar.php?status=bloqueado',
        'count' => $stats['bloqueados'],
        'active' => $filtro_status === 'bloqueado'
    ],
    [
        'label' => 'Inativos',
        'icone' => 'fa-circle-minus',
        'href' => 'gerenciar.php?status=inativo',
        'count' => $stats['inativos'],
        'active' => $filtro_status === 'inativo'
    ],
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

.user-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.user-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 14px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.user-tab:hover {
    border-color: #4361ee;
    color: #4361ee;
    transform: translateY(-1px);
}

.user-tab.active {
    background: linear-gradient(135deg, #0b5cff 0%, #6c5ce7 100%);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 12px 24px rgba(11, 92, 255, 0.2);
}

.user-tab-count {
    min-width: 24px;
    height: 24px;
    padding: 0 8px;
    border-radius: 999px;
    background: #f1f5f9;
    color: var(--text-secondary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
}

.user-tab.active .user-tab-count {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
}

.section-note {
    color: var(--text-muted);
    margin: -12px 0 22px;
    line-height: 1.6;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    font-weight: 600;
    height: 46px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-filtro:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102,126,234,0.2);
}

.btn-limpar {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-limpar:hover {
    background: #ffffff;
    border-color: #4361ee;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.btn-action.secondary:hover {
    background: #ffffff;
    border-color: #4361ee;
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

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
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

.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
}

.role-admin {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid #f97316;
}

.role-cliente {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.role-parceiro {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
    border: 1px solid #8b5cf6;
}

.permissions-list {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.permission-item {
    font-size: 0.75rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.permission-item i.fa-check {
    color: #10b981;
}

.permission-item i.fa-times {
    color: #ef4444;
}

.last-access {
    font-size: 0.8rem;
    color: var(--text-muted);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.page-link:hover {
    background: #f8faff;
}

.page-link.disabled {
    opacity: 0.5;
    pointer-events: none;
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

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
}

.alert-aviso {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid #f97316;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-user-cog" style="color: #4361ee; margin-right: 10px;"></i>
            Usuários
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Mensagem de feedback -->
        <?php if(isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['mensagem']['tipo']; ?>">
            <i class="fas <?php 
                echo $_SESSION['mensagem']['tipo'] == 'sucesso' ? 'fa-check-circle' : 
                    ($_SESSION['mensagem']['tipo'] == 'aviso' ? 'fa-exclamation-triangle' : 'fa-times-circle'); 
            ?>"></i>
            <?php 
            echo $_SESSION['mensagem']['texto'];
            unset($_SESSION['mensagem']);
            ?>
        </div>
        <?php endif; ?>

        <p class="section-note">
            Gerencie os acessos do portal do cliente: clientes, parceiros e usuários administrativos do portal.
        </p>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-value"><?php echo $stats['admins']; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $stats['clientes']; ?></div>
                <div class="stat-label">Clientes</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-value"><?php echo $stats['parceiros']; ?></div>
                <div class="stat-label">Parceiros</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $stats['ativos']; ?></div>
                <div class="stat-label">Usuários Ativos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-value"><?php echo $stats['bloqueados']; ?></div>
                <div class="stat-label">Bloqueados</div>
            </div>
        </div>

        <nav class="user-tabs" aria-label="Abas de usuários">
            <?php foreach($abas_usuarios as $aba): ?>
            <a href="<?php echo $aba['href']; ?>" class="user-tab <?php echo $aba['active'] ? 'active' : ''; ?>">
                <i class="fas <?php echo $aba['icone']; ?>"></i>
                <span><?php echo $aba['label']; ?></span>
                <span class="user-tab-count"><?php echo number_format($aba['count']); ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Nome, email, usuário ou empresa" value="<?php echo htmlspecialchars($busca); ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Tipo</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <option value="admin" <?php echo $filtro_tipo == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="cliente" <?php echo $filtro_tipo == 'cliente' ? 'selected' : ''; ?>>Cliente</option>
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
                    <a href="gerenciar.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Botões de Ação -->
        <div class="action-buttons">
            <a href="criar.php" class="btn-action">
                <i class="fas fa-user-plus"></i> Novo Usuário
            </a>
            <a href="index.php" class="btn-action secondary">
                <i class="fas fa-address-book"></i> Cadastro detalhado
            </a>
            <a href="exportar.php?<?php echo http_build_query($_GET); ?>" class="btn-action secondary">
                <i class="fas fa-file-export"></i> Exportar Lista
            </a>
        </div>

        <!-- Tabela de Usuários -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Lista de Usuários
                    <span style="font-size: 0.9rem; color: var(--text-muted); margin-left: 10px;">
                        Total: <?php echo $total_usuarios; ?>
                    </span>
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Nome / Empresa</th>
                        <th>Contato</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Último Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($usuarios->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <h3>Nenhum usuário encontrado</h3>
                            <p>Tente ajustar os filtros ou cadastrar um novo usuário.</p>
                            <a href="criar.php" class="btn-action" style="display: inline-block;">
                                <i class="fas fa-user-plus"></i> Novo Usuário
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($user = $usuarios->fetch_assoc()): 
                            $status_class = '';
                            switch($user['status']) {
                                case 'ativo': $status_class = 'status-ativo'; break;
                                case 'inativo': $status_class = 'status-inativo'; break;
                                case 'bloqueado': $status_class = 'status-bloqueado'; break;
                            }
                            
                            $role_class = '';
                            switch($user['tipo']) {
                                case 'admin': $role_class = 'role-admin'; break;
                                case 'cliente': $role_class = 'role-cliente'; break;
                                case 'parceiro': $role_class = 'role-parceiro'; break;
                            }
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['nome'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($user['nome']); ?></div>
                                <?php if($user['empresa']): ?>
                                <small style="color: var(--text-muted);">
                                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($user['empresa']); ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><i class="fas fa-envelope" style="color: #4361ee; width: 16px;"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                                <?php if($user['telefone']): ?>
                                <small><i class="fas fa-phone" style="color: #4361ee; width: 16px;"></i> <?php echo htmlspecialchars($user['telefone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="role-badge <?php echo $role_class; ?>">
                                    <?php echo ucfirst($user['tipo']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($user['ultimo_acesso']): ?>
                                    <span title="<?php echo date('d/m/Y H:i:s', strtotime($user['ultimo_acesso'])); ?>">
                                        <?php echo date('d/m/Y', strtotime($user['ultimo_acesso'])); ?>
                                    </span>
                                    <br>
                                    <small class="last-access">
                                        <?php 
                                        $diff = time() - strtotime($user['ultimo_acesso']);
                                        if($diff < 3600) echo 'há menos de 1 hora';
                                        elseif($diff < 86400) echo 'hoje';
                                        elseif($diff < 172800) echo 'ontem';
                                        else echo date('d/m', strtotime($user['ultimo_acesso']));
                                        ?>
                                    </small>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">Nunca acessou</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="visualizar.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <?php if($user['status'] == 'ativo'): ?>
                                    <a href="login-as.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Acessar ARCON como este cliente" style="color: #0b5cff;" onclick="return confirm('Acessar a área do cliente como <?php echo htmlspecialchars($user['nome'], ENT_QUOTES); ?>?')">
                                        <i class="fas fa-right-to-bracket"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="editar.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if($user['status'] == 'ativo'): ?>
                                    <a href="?acao=bloquear&id=<?php echo $user['id']; ?>" class="btn-icon" title="Bloquear" style="color: #f97316;" onclick="return confirm('Tem certeza que deseja BLOQUEAR este usuário?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="?acao=ativar&id=<?php echo $user['id']; ?>" class="btn-icon" title="Ativar" style="color: #10b981;" onclick="return confirm('Tem certeza que deseja ATIVAR este usuário?')">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="?acao=resetar_senha&id=<?php echo $user['id']; ?>" class="btn-icon" title="Resetar senha" style="color: #8b5cf6;" onclick="return confirm('Resetar a senha deste usuário? Uma nova senha será enviada por e-mail.')">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    
                                    <?php if($user['tipo'] != 'admin'): ?>
                                    <a href="?acao=excluir&id=<?php echo $user['id']; ?>" class="btn-icon" title="Excluir" style="color: #ef4444;" onclick="return confirm('Tem certeza que deseja EXCLUIR este usuário? Esta ação não pode ser desfeita.')">
                                        <i class="fas fa-trash"></i>
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
            
            <?php 
            $inicio = max(1, $pagina - 2);
            $fim = min($total_paginas, $pagina + 2);
            
            if($inicio > 1) {
                echo '<a href="?pagina=1' . (!empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) : '') . '" class="page-link">1</a>';
                if($inicio > 2) echo '<span class="page-link disabled">...</span>';
            }
            
            for($i = $inicio; $i <= $fim; $i++): 
            ?>
            <a href="?pagina=<?php echo $i; ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) : ''; ?>" class="page-link <?php echo $i == $pagina ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if($fim < $total_paginas): ?>
                <?php if($fim < $total_paginas - 1) echo '<span class="page-link disabled">...</span>'; ?>
                <a href="?pagina=<?php echo $total_paginas; ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) : ''; ?>" class="page-link"><?php echo $total_paginas; ?></a>
            <?php endif; ?>
            
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
        
        // Animação
        themeToggle.style.transform = 'scale(1.1)';
        setTimeout(() => {
            themeToggle.style.transform = 'scale(1)';
        }, 200);
    });
}

// Confirmar ações (já temos nos links, mas podemos adicionar mais segurança)
document.querySelectorAll('a[onclick]').forEach(link => {
    // Mantém os confirms existentes
});
</script>

<?php require_once '../../includes/footer.php'; ?>
