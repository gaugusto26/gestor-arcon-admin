<?php
$page_title = 'Criar Novo Template';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$config = getNewsletterConfig();
$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = limparInput($_POST['nome']);
    $assunto = limparInput($_POST['assunto']);
    $conteudo = $_POST['conteudo'];
    
    if(empty($nome)) {
        $erros[] = 'Nome do template é obrigatório';
    }
    if(empty($conteudo)) {
        $erros[] = 'Conteúdo é obrigatório';
    }
    
    if(empty($erros)) {
        // Salva como uma campanha marcada como template
        $stmt = $conn->prepare("
            INSERT INTO newsletter_campanhas (titulo, assunto, conteudo, template, status, created_by) 
            VALUES (?, ?, ?, 'custom', 'rascunho', ?)
        ");
        $stmt->bind_param("sssi", $nome, $assunto, $conteudo, $_SESSION['admin_id']);
        
        if($stmt->execute()) {
            $sucesso = 'Template salvo com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'templates.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao salvar template: ' . $conn->error;
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
    border-radius: 16px;
    padding: 30px;
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
    color: var(--accent);
    margin-right: 5px;
    width: 20px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.95rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
}

textarea.form-control {
    min-height: 300px;
    font-family: monospace;
    line-height: 1.5;
    resize: vertical;
}

.variables-box {
    background: var(--bg-secondary);
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
    background: var(--accent-light);
    color: var(--accent);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    margin: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.variable-tag:hover {
    background: var(--accent);
    color: white;
    transform: translateY(-2px);
}

.btn {
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
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

.btn-preview {
    background: #8b5cf6;
    color: white;
    margin-right: 10px;
}

.btn-preview:hover {
    background: #7c3aed;
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

.alert-error {
    background: #ef444420;
    color: #ef4444;
    border: 1px solid #ef4444;
}

.preview-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.preview-content {
    background: white;
    border-radius: 16px;
    max-width: 700px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.preview-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
}

.preview-body {
    padding: 20px;
}

.preview-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
}

.form-row {
    display: flex;
    gap: 15px;
    align-items: center;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i>
            Criar Novo Template
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
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
                <!-- Variáveis disponíveis -->
                <div class="variables-box">
                    <h4><i class="fas fa-code"></i> Variáveis disponíveis:</h4>
                    <span class="variable-tag" onclick="inserirTag('{nome}')">{nome}</span>
                    <span class="variable-tag" onclick="inserirTag('{email}')">{email}</span>
                    <span class="variable-tag" onclick="inserirTag('{desinscrever_link}')">{desinscrever_link}</span>
                    <span class="variable-tag" onclick="inserirTag('{site_url}')">{site_url}</span>
                    <span class="variable-tag" onclick="inserirTag('{blog_url}')">{blog_url}</span>
                    <span class="variable-tag" onclick="inserirTag('{ano}')">{ano}</span>
                    <span class="variable-tag" onclick="inserirTag('{data}')">{data}</span>
                </div>

                <form method="POST" id="templateForm">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nome do Template *</label>
                        <input type="text" name="nome" class="form-control" required value="<?php echo $_POST['nome'] ?? ''; ?>" placeholder="Ex: Template de Promoção Natal">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Assunto Padrão (opcional)</label>
                        <input type="text" name="assunto" class="form-control" value="<?php echo $_POST['assunto'] ?? ''; ?>" placeholder="Ex: Novidades da Arcon - {nome}">
                        <small style="color: var(--text-muted);">Este será o assunto padrão quando usar o template</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-newspaper"></i> Conteúdo HTML *</label>
                        <textarea name="conteudo" class="form-control" required id="conteudo"><?php 
                            echo $_POST['conteudo'] ?? '<h1 style="color: #0d47a1;">Olá {nome}!</h1>

<p>Este é um template personalizado. Edite conforme sua necessidade.</p>

<h2>Seção 1</h2>
<p>Conteúdo da primeira seção...</p>

<h2>Seção 2</h2>
<p>Conteúdo da segunda seção...</p>

<a href="{site_url}" style="background: #0d47a1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Visite nosso site</a>';
                        ?></textarea>
                        <small style="color: var(--text-muted);">Você pode usar HTML para formatar seu e-mail</small>
                    </div>
                    
                    <div class="form-row" style="justify-content: space-between;">
                        <div>
                            <button type="button" class="btn btn-preview" onclick="previewTemplate()">
                                <i class="fas fa-eye"></i> Pré-visualizar
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="limparEditor()">
                                <i class="fas fa-undo"></i> Limpar
                            </button>
                        </div>
                        <div>
                            <a href="templates.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pré-visualização -->
<div id="previewModal" class="preview-modal">
    <div class="preview-content">
        <div class="preview-header">
            <h3>Pré-visualização do Template</h3>
            <span class="preview-close" onclick="fecharPreview()">&times;</span>
        </div>
        <div class="preview-body" id="previewBody">
            Carregando...
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

function previewTemplate() {
    const conteudo = document.getElementById('conteudo').value;
    const nome = document.getElementById('nome')?.value || 'Template';
    
    if(!conteudo.trim()) {
        alert('Digite algum conteúdo para pré-visualizar');
        return;
    }
    
    // Substitui variáveis por exemplos
    let preview = conteudo
        .replace(/{nome}/g, 'João Silva')
        .replace(/{email}/g, 'joao@email.com')
        .replace(/{desinscrever_link}/g, '#')
        .replace(/{site_url}/g, '<?php echo SITE_URL; ?>')
        .replace(/{blog_url}/g, '<?php echo SITE_URL; ?>/blog.php')
        .replace(/{ano}/g, new Date().getFullYear())
        .replace(/{data}/g, new Date().toLocaleDateString('pt-BR'));
    
    // Aplica template base completo
    const htmlCompleto = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pré-visualização</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;">
            <div style="max-width:600px; margin:20px auto; background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                <div style="background:linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); padding:30px; text-align:center;">
                    <img src="<?php echo SITE_URL; ?>/assets/image/logo.png" style="max-width:150px; margin-bottom:10px;">
                    <h1 style="color:white; margin:0; font-size:24px;">Gestor Arcon Admin</h1>
                </div>
                <div style="padding:30px;">
                    ${preview}
                </div>
                <div style="background:#f8f8f8; padding:20px; text-align:center; font-size:12px; color:#666;">
                    <p>&copy; ${new Date().getFullYear()} Gestor Arcon Admin</p>
                    <p style="margin:5px 0 0;">
                        <a href="#" style="color:#999;">Cancelar inscrição</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    document.getElementById('previewBody').innerHTML = htmlCompleto;
    document.getElementById('previewModal').style.display = 'flex';
}

function fecharPreview() {
    document.getElementById('previewModal').style.display = 'none';
}

function limparEditor() {
    if(confirm('Limpar o editor? Todo o conteúdo será apagado.')) {
        document.getElementById('conteudo').value = '';
    }
}

// Fecha modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('previewModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
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