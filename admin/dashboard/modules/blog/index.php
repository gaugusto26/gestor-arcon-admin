<?php
$page_title = 'Gerenciar Blog';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Filtros
$filtro_categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$filtro_destaque = isset($_GET['destaque']) ? (int)$_GET['destaque'] : 0;

// Monta query
$sql = "SELECT p.*, c.nome as categoria_nome, c.cor as categoria_cor,
        (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id AND aprovado = 1) as total_comentarios,
        (SELECT COUNT(*) FROM blog_curtidas WHERE post_id = p.id) as total_curtidas
        FROM blog_posts p
        LEFT JOIN blog_categorias c ON p.categoria_id = c.id
        WHERE 1=1";

$params = [];
$types = "";

if($filtro_categoria > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $filtro_categoria;
    $types .= "i";
}

if(!empty($filtro_status)) {
    $sql .= " AND p.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if($filtro_destaque == 1) {
    $sql .= " AND p.destaque = 1";
}

$sql .= " ORDER BY 
    CASE p.status 
        WHEN 'publicado' THEN 1 
        WHEN 'rascunho' THEN 2 
        ELSE 3 
    END,
    p.data_publicacao DESC,
    p.created_at DESC";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$posts = $stmt->get_result();

// Busca categorias para o filtro
$categorias = getCategoriasBlog();
$status_list = getStatusBlog();
?>

<style>
/* Estilos para o blog */
.blog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.filtros-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
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
}

.filtro-item select, .filtro-item input {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.btn-filtro {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    background: var(--accent);
    color: white;
    cursor: pointer;
    font-weight: 500;
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

/* Grid de 2 colunas no desktop, 1 coluna no mobile */
.posts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 20px;
}

@media (max-width: 900px) {
    .posts-grid {
        grid-template-columns: 1fr;
    }
}

.post-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.post-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent);
}

.post-card.destaque {
    border-left: 4px solid #f59e0b;
}

.post-imagem {
    width: 100%;
    height: 180px;
    background-size: cover;
    background-position: center;
    position: relative;
    flex-shrink: 0;
}

.post-imagem .sem-imagem {
    width: 100%;
    height: 100%;
    background: var(--hover);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 2rem;
}

.post-conteudo {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.post-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.post-categoria {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.post-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-rascunho {
    background: #f59e0b20;
    color: #f59e0b;
    border: 1px solid #f59e0b;
}

.status-publicado {
    background: #22c55e20;
    color: #22c55e;
    border: 1px solid #22c55e;
}

.status-arquivado {
    background: #ef444420;
    color: #ef4444;
    border: 1px solid #ef4444;
}

.post-titulo {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.post-titulo a {
    color: inherit;
    text-decoration: none;
}

.post-titulo a:hover {
    color: var(--accent);
}

.post-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.post-meta i {
    color: var(--accent);
    margin-right: 5px;
}

.post-resumo {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.post-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.85rem;
    color: var(--text-muted);
    flex-wrap: wrap;
}

.post-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.post-stats i {
    color: var(--accent);
}

.post-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
}

.post-tag {
    background: var(--hover);
    color: var(--text-secondary);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    border: 1px solid var(--border);
}

.post-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: auto;
}

.btn-card {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}

.btn-card:hover {
    background: var(--hover);
    color: var(--accent);
    border-color: var(--accent);
}

.btn-card.editar {
    background: var(--accent);
    color: white;
    border: none;
}

.btn-card.editar:hover {
    background: #0a4fe0;
}

.btn-card.visualizar {
    background: var(--accent-light);
    color: var(--accent);
}

.btn-card.excluir:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 20px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-blog" style="margin-right: 10px;"></i>
            <?php echo $page_title; ?>
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
            
            <a href="criar.php" class="btn btn-primary" style="text-decoration: none; padding: 10px 20px;">
                <i class="fas fa-plus"></i> Novo Post
            </a>
        </div>
    </div>

    <div class="content-area">
        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-folder"></i> Categoria</label>
                    <select name="categoria">
                        <option value="">Todas as categorias</option>
                        <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['nome']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <?php foreach($status_list as $key => $nome): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filtro_status == $key ? 'selected' : ''; ?>>
                            <?php echo $nome; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-star"></i> Destaque</label>
                    <select name="destaque">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $filtro_destaque == 1 ? 'selected' : ''; ?>>Apenas destaques</option>
                    </select>
                </div>
                
                <div class="filtro-item" style="display: flex; flex-direction: row; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Posts -->
        <div class="posts-grid">
            <?php if($posts->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>Nenhum post encontrado</h3>
                <p>Comece escrevendo seu primeiro post para o blog.</p>
                <a href="criar.php" class="btn btn-primary" style="text-decoration: none; padding: 12px 30px;">
                    <i class="fas fa-plus"></i> Criar Primeiro Post
                </a>
            </div>
            <?php else: ?>
                <?php while($post = $posts->fetch_assoc()): 
                    $status_class = '';
                    switch($post['status']) {
                        case 'rascunho': $status_class = 'status-rascunho'; break;
                        case 'publicado': $status_class = 'status-publicado'; break;
                        case 'arquivado': $status_class = 'status-arquivado'; break;
                    }
                ?>
                <div class="post-card <?php echo $post['destaque'] ? 'destaque' : ''; ?>">
                    <div class="post-imagem" style="<?php echo $post['imagem_destaque'] ? 'background-image: url(\'../../uploads/blog/' . $post['imagem_destaque'] . '\');' : ''; ?>">
                        <?php if(!$post['imagem_destaque']): ?>
                        <div class="sem-imagem">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-conteudo">
                        <div class="post-header">
                            <?php if($post['categoria_nome']): ?>
                            <span class="post-categoria" style="background: <?php echo $post['categoria_cor'] ?? '#0b5cff'; ?>">
                                <?php echo $post['categoria_nome']; ?>
                            </span>
                            <?php endif; ?>
                            
                            <span class="post-status <?php echo $status_class; ?>">
                                <?php echo $status_list[$post['status']]; ?>
                            </span>
                            
                            <?php if($post['destaque']): ?>
                            <span style="color: #f59e0b;">
                                <i class="fas fa-star"></i> Destaque
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="post-titulo">
                            <a href="visualizar.php?id=<?php echo $post['id']; ?>">
                                <?php echo $post['titulo']; ?>
                            </a>
                        </h3>
                        
                        <div class="post-meta">
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
                        
                        <?php if($post['resumo']): ?>
                        <div class="post-resumo">
                            <?php echo $post['resumo']; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="post-stats">
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
                        
                        <?php if($post['tags']): 
                            $tags = explode(',', $post['tags']);
                        ?>
                        <div class="post-tags">
                            <?php foreach(array_slice($tags, 0, 5) as $tag): ?>
                            <span class="post-tag"><?php echo trim($tag); ?></span>
                            <?php endforeach; ?>
                            <?php if(count($tags) > 5): ?>
                            <span class="post-tag">+<?php echo count($tags) - 5; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="post-footer">
                            <a href="visualizar.php?id=<?php echo $post['id']; ?>" class="btn-card visualizar">
                                <i class="fas fa-eye"></i> Visualizar
                            </a>
                            <a href="editar.php?id=<?php echo $post['id']; ?>" class="btn-card editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="excluir.php?id=<?php echo $post['id']; ?>" class="btn-card excluir" onclick="return confirm('Tem certeza que deseja excluir este post?')">
                                <i class="fas fa-trash"></i> Excluir
                            </a>
                        </div>
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