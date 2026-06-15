<?php
$page_title = 'Visualizar Política';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$politica = getPoliticaById($id);
$secoes = getSecoesByPoliticaId($id);

if(!$politica) {
    header('Location: index.php');
    exit;
}
?>

<style>
    body.dark {
    --card-bg: #1e1e2f;
    --card-border: #333;
    --card-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.view-container {
    max-width: 900px;
    margin: 0 auto;
}

.view-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.view-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid var(--border);
}

.view-header h1 {
    font-size: 2.5rem;
    color: var(--text-primary);
    margin-bottom: 15px;
}

.view-header .versao {
    display: inline-block;
    background: var(--card-bg);
    color: #4361ee;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 15px;
    border: 1px solid var(--border);
}

.view-header .subtitulo {
    font-size: 1.2rem;
    color: var(--text-muted);
    max-width: 700px;
    margin: 0 auto;
}

.view-meta {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 20px;
    color: var(--text-muted);
    font-size: 0.95rem;
}

.view-meta i {
    color: #4361ee;
    margin-right: 5px;
}

.view-conteudo {
    line-height: 1.8;
    color: var(--text-secondary);
}

.view-conteudo h2 {
    color: var(--text-primary);
    font-size: 1.8rem;
    margin: 40px 0 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.view-conteudo h3 {
    color: var(--text-primary);
    font-size: 1.3rem;
    margin: 30px 0 15px;
}

.view-conteudo p {
    margin-bottom: 20px;
    line-height: 1.8;
}

.view-conteudo ul, .view-conteudo ol {
    margin-bottom: 20px;
    padding-left: 30px;
}

.view-conteudo li {
    margin-bottom: 10px;
}

.view-conteudo a {
    color: #4361ee;
    text-decoration: none;
}

.view-conteudo a:hover {
    text-decoration: underline;
}

.view-conteudo blockquote {
    border-left: 4px solid #4361ee;
    padding: 15px 25px;
    background: var(--card-bg);
    border-radius: 12px;
    margin: 20px 0;
}

.secao-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
}

.secao-titulo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.secao-titulo i {
    color: #4393ee;
    font-size: 1.3rem;
}

.secao-titulo h3 {
    font-size: 1.2rem;
    color: var(--text-primary);
    margin: 0;
}

.secao-conteudo {
    color: var(--text-secondary);
    line-height: 1.6;
}

.view-footer {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn {
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #66a4ea 0%, #4b6ea2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102,126,234,0.2);
}

.btn-secondary {
    background: var(--card-bg);
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: var(--card-bg);
    border-color: #4361ee;
}

.status-badge {
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
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-eye" style="color: #4361ee; margin-right: 10px;"></i>
            Visualizar Política
        </h1>
    </div>

    <div class="content-area">
        <div class="view-container">
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="editar.php?id=<?php echo $politica['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="../../politica.php?versao=<?php echo $politica['versao']; ?>" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-external-link-alt"></i> Ver Página Pública
                </a>
            </div>

            <div class="view-card">
                <div class="view-header">
                    <span class="versao">Versão <?php echo $politica['versao']; ?></span>
                    <h1><?php echo $politica['titulo']; ?></h1>
                    <?php if($politica['subtitulo']): ?>
                    <div class="subtitulo"><?php echo $politica['subtitulo']; ?></div>
                    <?php endif; ?>
                    
                    <div class="view-meta">
                        <span>
                            <i class="fas fa-calendar"></i> 
                            <?php echo $politica['data_publicacao'] ? 'Publicada em ' . date('d/m/Y', strtotime($politica['data_publicacao'])) : 'Não publicada'; ?>
                        </span>
                        <span>
                            <i class="fas fa-tag"></i> 
                            <span class="status-badge status-<?php echo $politica['status']; ?>">
                                <?php echo ucfirst($politica['status']); ?>
                            </span>
                        </span>
                        <span>
                            <i class="fas fa-clock"></i> 
                            Última atualização: <?php echo date('d/m/Y H:i', strtotime($politica['updated_at'] ?? $politica['created_at'])); ?>
                        </span>
                    </div>
                </div>

                <div class="view-conteudo">
                    <?php echo $politica['conteudo']; ?>
                </div>

                <?php if($secoes->num_rows > 0): ?>
                <div style="margin-top: 50px;">
                    <h2 style="font-size: 1.8rem; margin-bottom: 30px;">Seções da Política</h2>
                    <?php while($secao = $secoes->fetch_assoc()): ?>
                    <div class="secao-card">
                        <div class="secao-titulo">
                            <i class="fas <?php echo $secao['icone']; ?>"></i>
                            <h3><?php echo $secao['titulo']; ?></h3>
                        </div>
                        <div class="secao-conteudo">
                            <?php echo nl2br($secao['conteudo']); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <div class="view-footer">
                    <div>
                        <strong>Meta Title:</strong> <?php echo $politica['meta_title'] ?: 'Não definido'; ?>
                    </div>
                    <div>
                        <strong>Meta Description:</strong> <?php echo $politica['meta_description'] ?: 'Não definido'; ?>
                    </div>
                </div>
            </div>
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