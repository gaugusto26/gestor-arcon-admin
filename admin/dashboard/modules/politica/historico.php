<?php
// PRIMEIRO: toda lógica e redirecionamentos
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$politica = getPoliticaById($id);

if(!$politica) {
    header('Location: index.php');
    exit;
}

$historico = getHistoricoVersoes($id);

// SÓ DEPOIS: includes que geram HTML
$page_title = 'Histórico de Alterações';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
?>

<style>
.historico-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.historico-header h2 {
    font-size: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.historico-header h2 i {
    color: #4361ee;
    width: 45px;
    height: 45px;
    background: #f8faff;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.politica-info {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.politica-avatar {
    width: 70px;
    height: 70px;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 2rem;
}

.politica-details {
    flex: 1;
}

.politica-details h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.politica-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.politica-meta i {
    color: #4361ee;
    margin-right: 5px;
}

.politica-status {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
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

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #4361ee 0%, #8b5cf6 100%);
    opacity: 0.2;
}

.timeline-item {
    position: relative;
    padding-left: 80px;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: 15px;
    width: 45px;
    height: 45px;
    background: #ffffff;
    border: 2px solid #4361ee;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 1.2rem;
    z-index: 1;
    box-shadow: 0 5px 15px rgba(67,97,238,0.2);
}

.timeline-content {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.02);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border-color: #4361ee;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 15px;
}

.timeline-header h4 {
    font-size: 1.1rem;
    color: var(--text-primary);
}

.timeline-badge {
    background: #f8faff;
    color: #4361ee;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid var(--border);
}

.timeline-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.timeline-meta i {
    color: #4361ee;
    margin-right: 5px;
}

.timeline-diff {
    background: #f8faff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
}

.diff-header {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.diff-old {
    color: #ef4444;
}

.diff-new {
    color: #10b981;
}

.diff-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.diff-box {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    max-height: 200px;
    overflow-y: auto;
    font-size: 0.9rem;
    line-height: 1.6;
}

.diff-box.old {
    border-left: 3px solid #ef4444;
}

.diff-box.new {
    border-left: 3px solid #10b981;
}

.btn {
    padding: 10px 18px;
    border-radius: 10px;
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

.btn-secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: #ffffff;
    border-color: #4361ee;
    transform: translateY(-2px);
}

.btn-icon {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-block;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-muted);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-history" style="color: #4361ee; margin-right: 10px;"></i>
            Histórico de Alterações
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Info da Política -->
        <div class="politica-info">
            <div class="politica-avatar">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="politica-details">
                <h3><?php echo $politica['titulo']; ?></h3>
                <div class="politica-meta">
                    <span><i class="fas fa-code-branch"></i> Versão <?php echo $politica['versao']; ?></span>
                    <span><i class="fas fa-calendar"></i> Criada em <?php echo date('d/m/Y H:i', strtotime($politica['created_at'])); ?></span>
                    <span>
                        <i class="fas fa-tag"></i> 
                        <span class="politica-status status-<?php echo $politica['status']; ?>">
                            <?php echo ucfirst($politica['status']); ?>
                        </span>
                    </span>
                </div>
            </div>
            <a href="visualizar.php?id=<?php echo $politica['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-eye"></i> Ver Política
            </a>
        </div>

        <!-- Timeline de Alterações -->
        <?php if($historico->num_rows == 0): ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <h3>Nenhuma alteração registrada</h3>
            <p>As alterações nesta política aparecerão aqui</p>
        </div>
        <?php else: ?>
        <div class="timeline">
            <?php while($item = $historico->fetch_assoc()): ?>
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-pen"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <h4>Alteração na versão <?php echo $item['versao']; ?></h4>
                        <span class="timeline-badge"><?php echo $item['alteracoes']; ?></span>
                    </div>
                    
                    <div class="timeline-meta">
                        <span><i class="fas fa-user"></i> <?php echo $item['autor_nome'] ?? 'Administrador'; ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i:s', strtotime($item['data_alteracao'])); ?></span>
                    </div>
                    
                    <?php if($item['conteudo_antigo'] && $item['conteudo_novo']): ?>
                    <div class="timeline-diff">
                        <div class="diff-header">
                            <span class="diff-old">Antigo</span>
                            <span class="diff-new">Novo</span>
                        </div>
                        <div class="diff-content">
                            <div class="diff-box old">
                                <?php echo nl2br(substr(strip_tags($item['conteudo_antigo']), 0, 300)) . (strlen($item['conteudo_antigo']) > 300 ? '...' : ''); ?>
                            </div>
                            <div class="diff-box new">
                                <?php echo nl2br(substr(strip_tags($item['conteudo_novo']), 0, 300)) . (strlen($item['conteudo_novo']) > 300 ? '...' : ''); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="restaurar.php?id=<?php echo $politica['id']; ?>&versao=<?php echo $item['versao']; ?>" class="btn-icon" onclick="return confirm('Restaurar esta versão? Isso criará uma nova versão baseada neste conteúdo.')">
                            <i class="fas fa-undo"></i> Restaurar versão
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
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