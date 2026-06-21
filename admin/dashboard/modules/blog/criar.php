<?php
$page_title = 'Criar Novo Post';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$categorias = getCategoriasBlog();
$status_list = getStatusBlog();

// Processa formulário
$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validações
    if(empty($_POST['titulo'])) {
        $erros[] = 'Título é obrigatório';
    }
    if(empty($_POST['conteudo'])) {
        $erros[] = 'Conteúdo é obrigatório';
    }
    
    if(empty($erros)) {
        // Gera slug
        $slug = gerarSlug($_POST['titulo']);
        
        // Verifica se slug já existe
        $check = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $check->bind_param("s", $slug);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $slug .= '-' . time();
        }
        
        // Upload de imagem
        $imagem_destaque = '';
        if(isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] == 0) {
            $upload = uploadImagem($_FILES['imagem_destaque'], 'blog');
            if($upload) {
                $imagem_destaque = $upload;
            }
        }
        
        $upload_og = '';
        if(isset($_FILES['imagem_og']) && $_FILES['imagem_og']['error'] == 0) {
            $upload = uploadImagem($_FILES['imagem_og'], 'blog/og');
            if($upload) {
                $upload_og = $upload;
            }
        }
        
        // Upload avatar autor
        $avatar = '';
        if(isset($_FILES['autor_avatar']) && $_FILES['autor_avatar']['error'] == 0) {
            $upload = uploadImagem($_FILES['autor_avatar'], 'blog/autores');
            if($upload) {
                $avatar = $upload;
            }
        }
        
        // Data de publicação
        $data_publicacao = null;
        if($_POST['status'] == 'publicado' && !empty($_POST['data_publicacao'])) {
            $data_publicacao = $_POST['data_publicacao'];
        } elseif($_POST['status'] == 'publicado') {
            $data_publicacao = date('Y-m-d H:i:s');
        }
        
        // Insere post
        $sql = "INSERT INTO blog_posts (
            categoria_id, titulo, slug, subtitulo, conteudo, resumo,
            imagem_destaque, imagem_og, autor, autor_avatar,
            tempo_leitura, destaque, status, data_publicacao, data_agendamento,
            tags, meta_title, meta_description, meta_keywords
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        
        $stmt->bind_param(
            "isssssssssiisssssss",
            $_POST['categoria_id'],
            $_POST['titulo'],
            $slug,
            $_POST['subtitulo'],
            $_POST['conteudo'],
            $_POST['resumo'],
            $imagem_destaque,
            $upload_og,
            $_POST['autor'],
            $avatar,
            $_POST['tempo_leitura'],
            $destaque,
            $_POST['status'],
            $data_publicacao,
            $_POST['data_agendamento'],
            $_POST['tags'],
            $_POST['meta_title'],
            $_POST['meta_description'],
            $_POST['meta_keywords']
        );
        
        if($stmt->execute()) {
            $post_id = $conn->insert_id;
            
            // Log
            registrarLog($conn, $_SESSION['admin_id'], "Criou post: {$_POST['titulo']}");
            
            $sucesso = 'Post criado com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao criar post: ' . $conn->error;
        }
    }
}
?>

<style>
/* Estilos do formulário (mesmos do criar plano adaptado) */
.form-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    max-width: 1000px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group label i {
    color: var(--accent);
    margin-right: 5px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

textarea.conteudo-editor {
    min-height: 300px;
    font-family: monospace;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.form-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-check label {
    margin-bottom: 0;
    cursor: pointer;
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

.alert-danger {
    background: #ef444420;
    color: #ef4444;
    border: 1px solid #ef4444;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.95rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: #0a4fe0;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: var(--border);
}

.separador {
    margin: 30px 0;
    border-top: 2px dashed var(--border);
    position: relative;
}

.separador span {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--bg-primary);
    padding: 0 15px;
    color: var(--text-muted);
    font-size: 0.9rem;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-plus-circle" style="margin-right: 10px;"></i>
            <?php echo $page_title; ?>
        </h1>
    </div>

    <div class="content-area">
        <div class="form-container">
            <?php if(!empty($erros)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php foreach($erros as $erro): ?>
                        <div><?php echo $erro; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $sucesso; ?> Redirecionando...
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- Informações Básicas -->
                <h3 style="margin-bottom: 20px;">Informações Básicas</h3>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-heading"></i> Título *</label>
                        <input type="text" name="titulo" class="form-control" required value="<?php echo $_POST['titulo'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-subscript"></i> Subtítulo</label>
                        <input type="text" name="subtitulo" class="form-control" value="<?php echo $_POST['subtitulo'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-folder"></i> Categoria</label>
                        <select name="categoria_id" class="form-control">
                            <option value="">Selecione</option>
                            <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($_POST['categoria_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Status</label>
                        <select name="status" class="form-control" required>
                            <?php foreach($status_list as $key => $nome): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($_POST['status'] ?? 'rascunho') == $key ? 'selected' : ''; ?>>
                                <?php echo $nome; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-star"></i> Destaque</label>
                        <div class="form-check">
                            <input type="checkbox" name="destaque" id="destaque" value="1" <?php echo isset($_POST['destaque']) ? 'checked' : ''; ?>>
                            <label for="destaque">Marcar como destaque</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Tempo de leitura (minutos)</label>
                        <input type="number" name="tempo_leitura" class="form-control" value="<?php echo $_POST['tempo_leitura'] ?? 5; ?>">
                    </div>
                </div>
                
                <div class="separador"><span>CONTEÚDO</span></div>
                
                <!-- Conteúdo -->
                <div class="form-group full-width">
                    <label><i class="fas fa-align-left"></i> Resumo</label>
                    <textarea name="resumo" class="form-control" placeholder="Breve resumo do post"><?php echo $_POST['resumo'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label><i class="fas fa-newspaper"></i> Conteúdo *</label>
                    <textarea name="conteudo" class="form-control conteudo-editor" required placeholder="Escreva seu conteúdo em HTML"><?php echo $_POST['conteudo'] ?? ''; ?></textarea>
                    <small>Você pode usar tags HTML para formatar o conteúdo</small>
                </div>
                
                <div class="separador"><span>IMAGENS</span></div>
                
                <!-- Imagens -->
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Imagem de Destaque</label>
                        <input type="file" name="imagem_destaque" class="form-control" accept="image/*">
                        <small>Tamanho recomendado: 1200x630px</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-share-alt"></i> Imagem para Compartilhamento (OG)</label>
                        <input type="file" name="imagem_og" class="form-control" accept="image/*">
                        <small>Tamanho recomendado: 1200x630px</small>
                    </div>
                </div>
                
                <div class="separador"><span>AUTOR</span></div>
                
                <!-- Autor -->
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Autor</label>
                        <input type="text" name="autor" class="form-control" value="<?php echo $_POST['autor'] ?? 'Guilherme'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-camera"></i> Avatar do Autor</label>
                        <input type="file" name="autor_avatar" class="form-control" accept="image/*">
                    </div>
                </div>
                
                <div class="separador"><span>AGENDAMENTO</span></div>
                
                <!-- Agendamento -->
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-check"></i> Data de Publicação</label>
                        <input type="datetime-local" name="data_publicacao" class="form-control" value="<?php echo $_POST['data_publicacao'] ?? ''; ?>">
                        <small>Deixe em branco para usar data atual</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Data de Agendamento</label>
                        <input type="datetime-local" name="data_agendamento" class="form-control" value="<?php echo $_POST['data_agendamento'] ?? ''; ?>">
                        <small>Para posts futuros</small>
                    </div>
                </div>
                
                <div class="separador"><span>TAGS</span></div>
                
                <!-- Tags -->
                <div class="form-group full-width">
                    <label><i class="fas fa-tags"></i> Tags</label>
                    <input type="text" name="tags" class="form-control" value="<?php echo $_POST['tags'] ?? ''; ?>" placeholder="tecnologia, programação, php">
                    <small>Separe as tags por vírgula</small>
                </div>
                
                <div class="separador"><span>SEO</span></div>
                
                <!-- SEO -->
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-heading"></i> Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?php echo $_POST['meta_title'] ?? ''; ?>">
                        <small>Máximo 60 caracteres</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-paragraph"></i> Meta Description</label>
                        <textarea name="meta_description" class="form-control" placeholder="Descrição para SEO"><?php echo $_POST['meta_description'] ?? ''; ?></textarea>
                        <small>Máximo 160 caracteres</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-key"></i> Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?php echo $_POST['meta_keywords'] ?? ''; ?>" placeholder="palavra-chave, outra palavra">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Post
                    </button>
                </div>
            </form>
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