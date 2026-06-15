<?php
$page_title = 'Editar Política de Privacidade';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
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

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = limparInput($_POST['titulo']);
    $subtitulo = limparInput($_POST['subtitulo']);
    $conteudo = $_POST['conteudo'];
    $versao = limparInput($_POST['versao']);
    $status = limparInput($_POST['status']);
    $meta_title = limparInput($_POST['meta_title']);
    $meta_description = limparInput($_POST['meta_description']);
    $meta_keywords = limparInput($_POST['meta_keywords']);
    
    if(empty($titulo)) {
        $erros[] = 'Título é obrigatório';
    }
    if(empty($conteudo)) {
        $erros[] = 'Conteúdo é obrigatório';
    }
    
    if(empty($erros)) {
        // Salva histórico antes de alterar
        $stmt_hist = $conn->prepare("
            INSERT INTO politica_historico (politica_id, versao, conteudo_antigo, conteudo_novo, alteracoes, alterado_por, data_alteracao)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $alteracoes = "Editado por administrador";
        $stmt_hist->bind_param("issssi", $id, $politica['versao'], $politica['conteudo'], $conteudo, $alteracoes, $_SESSION['admin_id']);
        $stmt_hist->execute();
        
        $data_publicacao = $politica['data_publicacao'];
        if($status == 'publicado' && $politica['status'] != 'publicado') {
            $data_publicacao = date('Y-m-d H:i:s');
        }
        
        $stmt = $conn->prepare("
            UPDATE politica_privacidade SET 
                titulo = ?, subtitulo = ?, conteudo = ?, versao = ?, status = ?,
                data_publicacao = ?, meta_title = ?, meta_description = ?, meta_keywords = ?,
                atualizado_por = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "sssssssssii",
            $titulo,
            $subtitulo,
            $conteudo,
            $versao,
            $status,
            $data_publicacao,
            $meta_title,
            $meta_description,
            $meta_keywords,
            $_SESSION['admin_id'],
            $id
        );
        
        if($stmt->execute()) {
            // Se for publicado, despublica outras versões
            if($status == 'publicado') {
                $conn->query("UPDATE politica_privacidade SET status = 'arquivado' WHERE id != $id AND status = 'publicado'");
            }
            
            $sucesso = 'Política atualizada com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao atualizar política: ' . $conn->error;
        }
    }
}
?>

<style>
.form-container {
    max-width: 900px;
    margin: 0 auto;
}

.form-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
}

.form-group label i {
    color: #4361ee;
    margin-right: 8px;
    width: 20px;
}

.form-control {
    width: 100%;
    padding: 12px 18px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--card-bg);
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}

textarea.form-control {
    min-height: 300px;
    font-family: monospace;
    line-height: 1.6;
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
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

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
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

.btn {
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.variables-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.variables-box h4 {
    margin-bottom: 15px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.variable-tag {
    display: inline-block;
    background: var(--card-bg);
    color: #4361ee;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    margin: 3px;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.2s ease;
}

.variable-tag:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}
</style>

<!-- MESMO FORMULÁRIO DO CRIAR.PHP, COM OS VALORES PREENCHIDOS -->
<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-edit" style="color: #4361ee; margin-right: 10px;"></i>
            Editando: <?php echo $politica['titulo']; ?>
        </h1>
    </div>

    <div class="content-area">
        <div class="form-container">
            <?php if(!empty($erros)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php foreach($erros as $erro): ?>
                        <div><?php echo $erro; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?> Redirecionando...
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Título *</label>
                            <input type="text" name="titulo" class="form-control" required value="<?php echo $politica['titulo']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-code-branch"></i> Versão</label>
                            <input type="text" name="versao" class="form-control" value="<?php echo $politica['versao']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-quote-right"></i> Subtítulo</label>
                        <input type="text" name="subtitulo" class="form-control" value="<?php echo $politica['subtitulo']; ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-code"></i> Status</label>
                        <select name="status" class="form-control">
                            <option value="rascunho" <?php echo $politica['status'] == 'rascunho' ? 'selected' : ''; ?>>Rascunho</option>
                            <option value="publicado" <?php echo $politica['status'] == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                            <option value="arquivado" <?php echo $politica['status'] == 'arquivado' ? 'selected' : ''; ?>>Arquivado</option>
                        </select>
                    </div>

                    <div class="variables-box">
                        <h4><i class="fas fa-code"></i> Atalhos HTML</h4>
                        <span class="variable-tag" onclick="inserirTag('&lt;h2&gt;&lt;/h2&gt;')">&lt;h2&gt;Título&lt;/h2&gt;</span>
                        <span class="variable-tag" onclick="inserirTag('&lt;h3&gt;&lt;/h3&gt;')">&lt;h3&gt;Subtítulo&lt;/h3&gt;</span>
                        <span class="variable-tag" onclick="inserirTag('&lt;p&gt;&lt;/p&gt;')">&lt;p&gt;Parágrafo&lt;/p&gt;</span>
                        <span class="variable-tag" onclick="inserirTag('&lt;ul&gt;\n    &lt;li&gt;Item 1&lt;/li&gt;\n    &lt;li&gt;Item 2&lt;/li&gt;\n&lt;/ul&gt;')">Lista</span>
                        <span class="variable-tag" onclick="inserirTag('&lt;strong&gt;&lt;/strong&gt;')">Negrito</span>
                        <span class="variable-tag" onclick="inserirTag('&lt;em&gt;&lt;/em&gt;')">Itálico</span>
                        <span class="variable-tag" onclick="inserirTag('&lt;a href=&quot;#&quot;&gt;link&lt;/a&gt;')">Link</span>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-newspaper"></i> Conteúdo *</label>
                        <textarea name="conteudo" class="form-control" required id="conteudo"><?php echo htmlspecialchars($politica['conteudo']); ?></textarea>
                        <small style="color: var(--text-muted);">Você pode usar HTML para formatar o conteúdo</small>
                    </div>

                    <h3 style="margin: 30px 0 20px; font-size: 1.2rem;">SEO (Opcional)</h3>

                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?php echo $politica['meta_title']; ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-paragraph"></i> Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3"><?php echo $politica['meta_description']; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?php echo $politica['meta_keywords']; ?>">
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
</div>

<script>
function inserirTag(tag) {
    const textarea = document.getElementById('conteudo');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + tag.length, start + tag.length);
}

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