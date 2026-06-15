<?php
$page_title = 'Visualizar Post';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca post com detalhes
$stmt = $conn->prepare("
    SELECT p.*, c.nome as categoria_nome, c.cor as categoria_cor,
           (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id AND aprovado = 1) as total_comentarios,
           (SELECT COUNT(*) FROM blog_curtidas WHERE post_id = p.id) as total_curtidas
    FROM blog_posts p
    LEFT JOIN blog_categorias c ON p.categoria_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post) {
    header('Location: index.php');
    exit;
}

// Busca comentários aprovados
$stmt_coment = $conn->prepare("SELECT * FROM blog_comentarios WHERE post_id = ? AND aprovado = 1 ORDER BY data_comentario DESC");
$stmt_coment->bind_param("i", $id);
$stmt_coment->execute();
$comentarios = $stmt_coment->get_result();

$status_list = getStatusBlog();
?>

<style>
.view-container {
    max-width: 900px;
    margin: 0 auto;
}

.view-header {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
}

.view-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.view-categoria {
    padding: 6px 16px;
    border-radius: 30px;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.view-status {
    padding: 6px 16px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.9rem;
}

.view-titulo {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: var(--text-primary);
}

.view-subtitulo {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: 20px;
    font-weight: 400;
}

.view-imagem {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 30px;
}

.view-conteudo {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px;
    margin-bottom: 30px;
    line-height: 1.8;
}

.view-conteudo h2 {
    margin: 30px 0 15px;
    color: var(--text-primary);
}

.view-conteudo h3 {
    margin: 25px 0 15px;
    color: var(--text-primary);
}

.view-conteudo p {
    margin-bottom: 20px;
    color: var(--text-secondary);
}

.view-conteudo ul, .view-conteudo ol {
    margin-bottom: 20px;
    padding-left: 30px;
    color: var(--text-secondary);
}

.view-conteudo li {
    margin-bottom: 10px;
}

.view-conteudo blockquote {
    border-left: 4px solid var(--accent);
    padding: 15px 25px;
    background: var(--bg-secondary);
    border-radius: 8px;
    margin: 20px 0;
    font-style: italic;
}

.view-conteudo img {
    max-width: 100%;
    border-radius: 8px;
    margin: 20px 0;
}

.view-conteudo pre {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 20px 0;
}

.view-conteudo code {
    background: var(--bg-secondary);
    padding: 2px 5px;
    border-radius: 4px;
    font-family: monospace;
}

.view-stats {
    display: flex;
    gap: 30px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.view-stats span {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
}

.view-stats i {
    color: var(--accent);
}

.view-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 30px 0;
}

.view-tag {
    background: var(--hover);
    color: var(--text-secondary);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.9rem;
    border: 1px solid var(--border);
}

.comentarios-section {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    margin-top: 30px;
}

.comentario {
    padding: 20px;
    border-bottom: 1px solid var(--border);
}

.comentario:last-child {
    border-bottom: none;
}

.comentario-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}

.comentario-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.comentario-info h4 {
    color: var(--text-primary);
    margin-bottom: 2px;
}

.comentario-info small {
    color: var(--text-muted);
}

.comentario-texto {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-left: 55px;
}

.autor-info {
    display: flex;
    align-items: center;
    gap: 15px;
    background: var(--bg-secondary);
    padding: 15px;
    border-radius: 12px;
    margin-top: 30px;
}

.autor-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.autor-detalhes h4 {
    color: var(--text-primary);
    margin-bottom: 5px;
}

.autor-detalhes p {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.btn-voltar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--hover);
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
    transition: all 0.2s ease;
}

.btn-voltar:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.btn-editar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 25px;
    background: var(--accent);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin-left: 15px;
    transition: all 0.2s ease;
}

.btn-editar:hover {
    background: #2563eb;
    transform: translateY(-2px);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-eye" style="margin-right: 10px;"></i>
            Visualizar Post
        </h1>
    </div>

    <div class="content-area">
        <div class="view-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <a href="index.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar para lista
                </a>
                <a href="editar.php?id=<?php echo $post['id']; ?>" class="btn-editar">
                    <i class="fas fa-edit"></i> Editar Post
                </a>
            </div>

            <div class="view-header">
                <div class="view-meta">
                    <?php if($post['categoria_nome']): ?>
                    <span class="view-categoria" style="background: <?php echo $post['categoria_cor'] ?? '#3b82f6'; ?>">
                        <?php echo $post['categoria_nome']; ?>
                    </span>
                    <?php endif; ?>
                    
                    <span class="view-status" style="
                        background: <?php 
                            switch($post['status']) {
                                case 'rascunho': echo '#f59e0b20'; break;
                                case 'publicado': echo '#22c55e20'; break;
                                case 'arquivado': echo '#ef444420'; break;
                            }
                        ?>;
                        color: <?php 
                            switch($post['status']) {
                                case 'rascunho': echo '#f59e0b'; break;
                                case 'publicado': echo '#22c55e'; break;
                                case 'arquivado': echo '#ef4444'; break;
                            }
                        ?>;
                        border: 1px solid currentColor;
                    ">
                        <?php echo $status_list[$post['status']]; ?>
                    </span>
                    
                    <?php if($post['destaque']): ?>
                    <span style="color: #f59e0b;">
                        <i class="fas fa-star"></i> Destaque
                    </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="view-titulo"><?php echo $post['titulo']; ?></h1>
                
                <?php if($post['subtitulo']): ?>
                <h2 class="view-subtitulo"><?php echo $post['subtitulo']; ?></h2>
                <?php endif; ?>
                
                <div style="display: flex; gap: 20px; color: var(--text-muted); margin-top: 20px;">
                    <span>
                        <i class="fas fa-user"></i> <?php echo $post['autor']; ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar"></i> 
                        <?php 
                        if($post['data_publicacao']) {
                            echo date('d/m/Y H:i', strtotime($post['data_publicacao']));
                        } else {
                            echo 'Não publicado';
                        }
                        ?>
                    </span>
                    <?php if($post['tempo_leitura']): ?>
                    <span>
                        <i class="fas fa-clock"></i> <?php echo formatarTempoLeitura($post['tempo_leitura']); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($post['imagem_destaque']): ?>
            <img src="../../<?php echo $post['imagem_destaque']; ?>" class="view-imagem">
            <?php endif; ?>

            <div class="view-conteudo">
                <?php echo $post['conteudo']; ?>
            </div>

            <?php if($post['tags']): 
                $tags = explode(',', $post['tags']);
            ?>
            <div class="view-tags">
                <?php foreach($tags as $tag): ?>
                <span class="view-tag"><?php echo trim($tag); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="view-stats">
                <span>
                    <i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?> visualizações
                </span>
                <span>
                    <i class="fas fa-comment"></i> <?php echo $post['total_comentarios']; ?> comentários
                </span>
                <span>
                    <i class="fas fa-heart"></i> <?php echo $post['total_curtidas']; ?> curtidas
                </span>
            </div>

            <?php if($post['autor_avatar'] || $post['autor']): ?>
            <div class="autor-info">
                <?php if($post['autor_avatar']): ?>
                <img src="../../<?php echo $post['autor_avatar']; ?>" class="autor-avatar">
                <?php else: ?>
                <div class="autor-avatar" style="background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <?php echo strtoupper(substr($post['autor'], 0, 1)); ?>
                </div>
                <?php endif; ?>
                <div class="autor-detalhes">
                    <h4><?php echo $post['autor']; ?></h4>
                    <p>Autor do post</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comentários -->
            <?php if($comentarios->num_rows > 0): ?>
            <div class="comentarios-section">
                <h3 style="margin-bottom: 20px;">
                    <i class="fas fa-comments"></i> 
                    Comentários (<?php echo $comentarios->num_rows; ?>)
                </h3>
                
                <?php while($coment = $comentarios->fetch_assoc()): ?>
                <div class="comentario">
                    <div class="comentario-header">
                        <div class="comentario-avatar">
                            <?php echo strtoupper(substr($coment['nome'], 0, 1)); ?>
                        </div>
                        <div class="comentario-info">
                            <h4><?php echo $coment['nome']; ?></h4>
                            <small><?php echo date('d/m/Y H:i', strtotime($coment['data_comentario'])); ?></small>
                        </div>
                    </div>
                    <div class="comentario-texto">
                        <?php echo nl2br($coment['comentario']); ?>
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