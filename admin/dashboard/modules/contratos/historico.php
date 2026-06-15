<?php
$page_title = 'Histórico do Contrato';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca contrato
$stmt = $conn->prepare("SELECT numero_contrato, titulo FROM contratos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$contrato = $stmt->get_result()->fetch_assoc();

if(!$contrato) {
    header('Location: index.php');
    exit;
}

// Busca histórico
$historico = $conn->query("
    SELECT h.*, a.nome_completo as admin_nome
    FROM contrato_historico h
    LEFT JOIN admin_users a ON h.created_by = a.id
    WHERE h.contrato_id = $id
    ORDER BY h.created_at DESC
");
?>

<style>
.historico-container {
    max-width: 800px;
    margin: 0 auto;
}

.historico-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.contrato-info {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px 25px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.contrato-icon {
    width: 50px;
    height: 50px;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 1.5rem;
}

.contrato-details h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.contrato-details p {
    color: var(--text-muted);
    font-size: 0.9rem;
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
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    opacity: 0.2;
}

.timeline-item {
    position: relative;
    padding-left: 80px;
    margin-bottom: 30px;
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

.diff-box {
    background: #f8faff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
    max-height: 200px;
    overflow-y: auto;
    font-size: 0.9rem;
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

.btn {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
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
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-history" style="color: #4361ee; margin-right: 10px;"></i>
            Histórico do Contrato
        </h1>
    </div>

    <div class="content-area">
        <div class="historico-container">
            <div class="historico-header">
                <a href="visualizar.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <div class="contrato-info">
                <div class="contrato-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="contrato-details">
                    <h3><?php echo $contrato['titulo']; ?></h3>
                    <p><i class="fas fa-hashtag"></i> <?php echo $contrato['numero_contrato']; ?></p>
                </div>
            </div>

            <?php if($historico->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>Nenhum histórico encontrado</h3>
                <p>Este contrato ainda não possui registros de alterações</p>
            </div>
            <?php else: ?>
            <div class="timeline">
                <?php while($item = $historico->fetch_assoc()): ?>
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4><?php echo $item['acao']; ?></h4>
                        </div>
                        
                        <div class="timeline-meta">
                            <span><i class="fas fa-user"></i> <?php echo $item['admin_nome'] ?? 'Sistema'; ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i:s', strtotime($item['created_at'])); ?></span>
                        </div>
                        
                        <?php if($item['dados_anteriores'] && $item['dados_novos']): ?>
                        <div class="diff-box">
                            <strong>Alterações realizadas</strong>
                            <p style="margin-top: 10px; color: var(--text-secondary);">
                                Versão anterior → Versão nova
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
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