<?php
$page_title = 'Editar Post';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Verifica ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca post
$stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post) {
    header('Location: index.php');
    exit;
}

$categorias = getCategoriasBlog();
$status_list = getStatusBlog();

// Processa formulário
$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(empty($_POST['titulo'])) {
        $erros[] = 'Título é obrigatório';
    }
    if(empty($_POST['conteudo'])) {
        $erros[] = 'Conteúdo é obrigatório';
    }
    
    if(empty($erros)) {
        // Gera slug
        $slug = gerarSlug($_POST['titulo']);
        
        // Verifica se slug já existe (exceto este post)
        $check = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
        $check->bind_param("si", $slug, $id);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $slug .= '-' . $id;
        }
        
        // Upload de imagem
        $imagem_destaque = $post['imagem_destaque'];
        if(isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] == 0) {
            $upload = uploadImagem($_FILES['imagem_destaque'], 'blog');
            if($upload) {
                $imagem_destaque = $upload;
            }
        }
        
        $upload_og = $post['imagem_og'];
        if(isset($_FILES['imagem_og']) && $_FILES['imagem_og']['error'] == 0) {
            $upload = uploadImagem($_FILES['imagem_og'], 'blog/og');
            if($upload) {
                $upload_og = $upload;
            }
        }
        
        $avatar = $post['autor_avatar'];
        if(isset($_FILES['autor_avatar']) && $_FILES['autor_avatar']['error'] == 0) {
            $upload = uploadImagem($_FILES['autor_avatar'], 'blog/autores');
            if($upload) {
                $avatar = $upload;
            }
        }
        
        // Data de publicação
        $data_publicacao = $post['data_publicacao'];
        if($_POST['status'] == 'publicado' && empty($data_publicacao)) {
            $data_publicacao = date('Y-m-d H:i:s');
        } elseif($_POST['status'] != 'publicado') {
            $data_publicacao = null;
        }
        
        // Atualiza post
        $sql = "UPDATE blog_posts SET 
            categoria_id = ?, titulo = ?, slug = ?, subtitulo = ?, conteudo = ?, resumo = ?,
            imagem_destaque = ?, imagem_og = ?, autor = ?, autor_avatar = ?,
            tempo_leitura = ?, destaque = ?, status = ?, data_publicacao = ?, data_agendamento = ?,
            tags = ?, meta_title = ?, meta_description = ?, meta_keywords = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        
        $stmt->bind_param(
            "isssssssssiisssssssi",
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
            $_POST['meta_keywords'],
            $id
        );
        
        if($stmt->execute()) {
            registrarLog($conn, $_SESSION['admin_id'], "Editou post: {$_POST['titulo']}");
            $sucesso = 'Post atualizado com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao atualizar post: ' . $conn->error;
        }
    }
}
?>

<!-- MESMO FORMULÁRIO DO criar.php, mas com os valores preenchidos -->
<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-edit" style="margin-right: 10px;"></i>
            Editando: <?php echo $post['titulo']; ?>
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
                <!-- MESMO HTML DO criar.php, mas com values preenchidos com $post -->
                <h3 style="margin-bottom: 20px;">Informações Básicas</h3>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-heading"></i> Título *</label>
                        <input type="text" name="titulo" class="form-control" required value="<?php echo $post['titulo']; ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-subscript"></i> Subtítulo</label>
                        <input type="text" name="subtitulo" class="form-control" value="<?php echo $post['subtitulo']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-folder"></i> Categoria</label>
                        <select name="categoria_id" class="form-control">
                            <option value="">Selecione</option>
                            <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $post['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Status</label>
                        <select name="status" class="form-control" required>
                            <?php foreach($status_list as $key => $nome): ?>
                            <option value="<?php echo $key; ?>" <?php echo $post['status'] == $key ? 'selected' : ''; ?>>
                                <?php echo $nome; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-star"></i> Destaque</label>
                        <div class="form-check">
                            <input type="checkbox" name="destaque" id="destaque" value="1" <?php echo $post['destaque'] ? 'checked' : ''; ?>>
                            <label for="destaque">Marcar como destaque</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Tempo de leitura (minutos)</label>
                        <input type="number" name="tempo_leitura" class="form-control" value="<?php echo $post['tempo_leitura'] ?? 5; ?>">
                    </div>
                </div>
                
                <div class="separador"><span>CONTEÚDO</span></div>
                
                <div class="form-group full-width">
                    <label><i class="fas fa-align-left"></i> Resumo</label>
                    <textarea name="resumo" class="form-control" placeholder="Breve resumo do post"><?php echo $post['resumo']; ?></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label><i class="fas fa-newspaper"></i> Conteúdo *</label>
                    <textarea name="conteudo" class="form-control conteudo-editor" required><?php echo htmlspecialchars($post['conteudo']); ?></textarea>
                    <small>Você pode usar tags HTML para formatar o conteúdo</small>
                </div>
                
                <div class="separador"><span>IMAGENS</span></div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Imagem de Destaque</label>
                        <?php if($post['imagem_destaque']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../../<?php echo $post['imagem_destaque']; ?>" style="max-width: 200px; border-radius: 8px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="imagem_destaque" class="form-control" accept="image/*">
                        <small>Deixe em branco para manter a atual</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-share-alt"></i> Imagem para Compartilhamento (OG)</label>
                        <?php if($post['imagem_og']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../../<?php echo $post['imagem_og']; ?>" style="max-width: 200px; border-radius: 8px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="imagem_og" class="form-control" accept="image/*">
                    </div>
                </div>
                
                <div class="separador"><span>AUTOR</span></div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Autor</label>
                        <input type="text" name="autor" class="form-control" value="<?php echo $post['autor'] ?? 'Renan'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-camera"></i> Avatar do Autor</label>
                        <?php if($post['autor_avatar']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../../<?php echo $post['autor_avatar']; ?>" style="max-width: 50px; border-radius: 50%;">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="autor_avatar" class="form-control" accept="image/*">
                    </div>
                </div>
                
                <div class="separador"><span>AGENDAMENTO</span></div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-check"></i> Data de Publicação</label>
                        <input type="datetime-local" name="data_publicacao" class="form-control" value="<?php echo $post['data_publicacao'] ? date('Y-m-d\TH:i', strtotime($post['data_publicacao'])) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Data de Agendamento</label>
                        <input type="datetime-local" name="data_agendamento" class="form-control" value="<?php echo $post['data_agendamento'] ? date('Y-m-d\TH:i', strtotime($post['data_agendamento'])) : ''; ?>">
                    </div>
                </div>
                
                <div class="separador"><span>TAGS</span></div>
                
                <div class="form-group full-width">
                    <label><i class="fas fa-tags"></i> Tags</label>
                    <input type="text" name="tags" class="form-control" value="<?php echo $post['tags']; ?>" placeholder="tecnologia, programação, php">
                </div>
                
                <div class="separador"><span>SEO</span></div>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-heading"></i> Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?php echo $post['meta_title']; ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-paragraph"></i> Meta Description</label>
                        <textarea name="meta_description" class="form-control"><?php echo $post['meta_description']; ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label><i class="fas fa-key"></i> Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?php echo $post['meta_keywords']; ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Theme Toggle (mesmo código)
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