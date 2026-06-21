<?php
$page_title = 'Termos de Uso';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Busca todas as versões
$versoes = $conn->query("
    SELECT p.*, a.nome_completo as autor_nome,
           (SELECT COUNT(*) FROM termos_secoes WHERE termos_id = p.id) as total_secoes
    FROM termos_uso p
    LEFT JOIN admin_users a ON p.atualizado_por = a.id
    ORDER BY 
        CASE p.status 
            WHEN 'publicado' THEN 1 
            WHEN 'rascunho' THEN 2 
            ELSE 3 
        END,
        p.created_at DESC
");

$termos_ativa = getTermosAtivos();
?>

<style>
/* ===== HEADER ===== */
.termos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

body.dark {
    --card-bg: #1e1e2f;
    --card-border: #333;
    --card-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.termos-header h2 {
    font-size: 1.8rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.termos-header h2 i {
    color: #4361ee;
    width: 50px;
    height: 50px;
    background: #f8faff;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ===== ACTIVE BANNER ===== */
.active-banner {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 30px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.active-icon {
    width: 70px;
    height: 70px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
    font-size: 2rem;
}

.active-info {
    flex: 1;
}

.active-info h3 {
    font-size: 1.3rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.active-info p {
    color: var(--text-muted);
    margin-bottom: 10px;
}

.active-badge {
    display: inline-block;
    background: #10b981;
    color: white;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.active-actions {
    display: flex;
    gap: 10px;
}

/* ===== VERSIONS GRID ===== */
.versoes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.versao-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.versao-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    border-color: transparent;
}

.versao-card.ativa {
    border: 2px solid #10b981;
    position: relative;
}

.versao-card.ativa::before {
    content: '⭐ ATIVA';
    position: absolute;
    top: 15px;
    right: 15px;
    background: #10b981;
    color: white;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 1;
}

.versao-header {
    padding: 25px;
    background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.versao-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.versao-card.ativa .versao-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.versao-card.rascunho .versao-icon {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.versao-card.arquivado .versao-icon {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.versao-info {
    flex: 1;
}

.versao-info h4 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.versao-info .versao-tag {
    display: inline-block;
    background: var(--card-bg);
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 600;
    border: 1px solid var(--border);
}

.versao-body {
    padding: 25px;
}

.versao-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.versao-meta i {
    color: #4361ee;
    margin-right: 5px;
}

.versao-footer {
    padding: 20px 25px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 10px 20px rgba(102,126,234,0.2);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(102,126,234,0.3);
}

.btn-secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: #ffffff;
    border-color: #4361ee;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0d9488;
}

.btn-warning {
    background: #f97316;
    color: white;
}

.btn-warning:hover {
    background: #ea580c;
}

.btn-icon {
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.status-publicado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-rascunho {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-arquivado {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 25px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-contract" style="color: #4361ee; margin-right: 10px;"></i>
            Termos de Uso
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Banner dos Termos Ativos -->
        <?php if($termos_ativa): ?>
        <div class="active-banner">
            <div class="active-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="active-info">
                <h3>Termos em Vigor</h3>
                <p><strong><?php echo $termos_ativa['titulo']; ?></strong> - Versão <?php echo $termos_ativa['versao']; ?></p>
                <span class="active-badge">PUBLICADO EM <?php echo date('d/m/Y', strtotime($termos_ativa['data_publicacao'])); ?></span>
            </div>
            <div class="active-actions">
                <a href="editar.php?id=<?php echo $termos_ativa['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="visualizar.php?id=<?php echo $termos_ativa['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Visualizar
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ações -->
        <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
            <a href="criar.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Versão
            </a>
            <a href="secoes.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Gerenciar Seções
            </a>
            <a href="historico.php" class="btn btn-secondary">
                <i class="fas fa-history"></i> Histórico de Alterações
            </a>
            <a href="https://digitalfive.com.br/privacidade" target="_blank" class="btn btn-secondary">
                <i class="fas fa-external-link-alt"></i> Visualizar Página Pública
            </a>
        </div>

        <!-- Lista de Versões -->
        <h2 style="font-size: 1.3rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-code-branch" style="color: #4361ee;"></i>
            Todas as Versões
        </h2>

        <div class="versoes-grid">
            <?php if($versoes->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-file-contract"></i>
                <h3>Nenhuma política criada</h3>
                <p>Comece criando sua primeira política de privacidade.</p>
                <a href="criar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Criar Termos
                </a>
            </div>
            <?php else: ?>
                <?php while($versao = $versoes->fetch_assoc()): 
                    $card_class = '';
                    $status_class = '';
                    switch($versao['status']) {
                        case 'publicado': 
                            $card_class = 'ativa'; 
                            $status_class = 'status-publicado';
                            break;
                        case 'rascunho': 
                            $card_class = 'rascunho'; 
                            $status_class = 'status-rascunho';
                            break;
                        case 'arquivado': 
                            $card_class = 'arquivado'; 
                            $status_class = 'status-arquivado';
                            break;
                    }
                ?>
                <div class="versao-card <?php echo $card_class; ?>">
                    <div class="versao-header">
                        <div class="versao-icon">
                            <i class="fas <?php 
                                echo $versao['status'] == 'publicado' ? 'fa-check-circle' : 
                                    ($versao['status'] == 'rascunho' ? 'fa-pen' : 'fa-archive'); 
                            ?>"></i>
                        </div>
                        <div class="versao-info">
                            <h4><?php echo $versao['titulo']; ?></h4>
                            <span class="versao-tag">v<?php echo $versao['versao']; ?></span>
                        </div>
                    </div>
                    
                    <div class="versao-body">
                        <div class="versao-meta">
                            <span>
                                <i class="fas fa-tag"></i> 
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($versao['status']); ?>
                                </span>
                            </span>
                            <span>
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d/m/Y', strtotime($versao['created_at'])); ?>
                            </span>
                            <span>
                                <i class="fas fa-layers"></i> 
                                <?php echo $versao['total_secoes']; ?> seções
                            </span>
                            <?php if($versao['autor_nome']): ?>
                            <span>
                                <i class="fas fa-user"></i> 
                                <?php echo $versao['autor_nome']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($versao['subtitulo']): ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                            <?php echo substr($versao['subtitulo'], 0, 100) . (strlen($versao['subtitulo']) > 100 ? '...' : ''); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="versao-footer">
                        <a href="visualizar.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Visualizar">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="editar.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if($versao['status'] == 'rascunho'): ?>
                        <a href="publicar.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Publicar" style="color: #10b981;">
                            <i class="fas fa-check-circle"></i>
                        </a>
                        <?php endif; ?>
                        <?php if($versao['status'] == 'publicado' && $versao['id'] != $termos_ativa['id']): ?>
                        <a href="ativar.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Tornar Ativa" style="color: #f97316;">
                            <i class="fas fa-star"></i>
                        </a>
                        <?php endif; ?>
                        <?php if($versao['status'] == 'arquivado'): ?>
                        <a href="restaurar.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Restaurar" style="color: #8b5cf6;">
                            <i class="fas fa-undo"></i>
                        </a>
                        <?php endif; ?>
                        <a href="duplicar.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Duplicar" style="color: #8b5cf6;">
                            <i class="fas fa-copy"></i>
                        </a>
                        <a href="excluir.php?id=<?php echo $versao['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza? Isso irá apagar permanentemente esta versão.')" style="color: #ef4444;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
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