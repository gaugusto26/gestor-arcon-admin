<?php
$page_title = 'Nova Campanha';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$config = getNewsletterConfig();
$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = limparInput($_POST['titulo']);
    $assunto = limparInput($_POST['assunto']);
    $conteudo = $_POST['conteudo']; // Não usar limparInput pq tem HTML
    $template = limparInput($_POST['template']);
    $status = limparInput($_POST['status']);
    $data_agendamento = !empty($_POST['data_agendamento']) ? $_POST['data_agendamento'] : null;
    
    if(empty($titulo)) {
        $erros[] = 'Título é obrigatório';
    }
    if(empty($assunto)) {
        $erros[] = 'Assunto é obrigatório';
    }
    if(empty($conteudo)) {
        $erros[] = 'Conteúdo é obrigatório';
    }
    
    if(empty($erros)) {
        $stmt = $conn->prepare("
            INSERT INTO newsletter_campanhas 
            (titulo, assunto, conteudo, template, status, data_agendamento, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssi", $titulo, $assunto, $conteudo, $template, $status, $data_agendamento, $_SESSION['admin_id']);
        
        if($stmt->execute()) {
            $campanha_id = $conn->insert_id;
            $sucesso = 'Campanha criada com sucesso!';
            
            if($status == 'enviar_agora') {
                header("Location: enviar_campanha.php?id=$campanha_id");
                exit;
            }
            
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao criar campanha: ' . $conn->error;
        }
    }
}
?>

<style>
.form-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    max-width: 1000px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
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
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.btn {
    padding: 12px 30px;
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
    background: #0a4fe0;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

.template-preview {
    margin-top: 20px;
    padding: 20px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    max-height: 400px;
    overflow-y: auto;
}

.variables-list {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.variable-tag {
    display: inline-block;
    background: var(--accent-light);
    color: var(--accent);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    margin: 3px;
    cursor: pointer;
}

.variable-tag:hover {
    background: var(--accent);
    color: white;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i>
            Nova Campanha de Newsletter
        </h1>
    </div>

    <div class="content-area">
        <div class="form-container">
            <?php if(!empty($erros)): ?>
                <div class="alert alert-danger">
                    <?php foreach($erros as $erro): ?>
                        <div><?php echo $erro; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
                <div class="alert alert-success">
                    <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="campanhaForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Título da Campanha</label>
                        <input type="text" name="titulo" class="form-control" required value="<?php echo $_POST['titulo'] ?? ''; ?>">
                        <small>Nome interno para identificação</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Assunto do E-mail</label>
                        <input type="text" name="assunto" class="form-control" required value="<?php echo $_POST['assunto'] ?? ''; ?>">
                        <small>Será o assunto que os inscritos verão</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Template</label>
                        <select name="template" class="form-control" id="templateSelect">
                            <option value="padrao">Padrão</option>
                            <option value="blog">Blog - Novos Posts</option>
                            <option value="promocao">Promoção</option>
                            <option value="aviso">Aviso</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="rascunho">Salvar como Rascunho</option>
                            <option value="agendada">Agendar para depois</option>
                            <option value="enviar_agora">Enviar Agora</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" id="agendamentoGroup" style="display: none;">
                    <label>Data de Agendamento</label>
                    <input type="datetime-local" name="data_agendamento" class="form-control">
                </div>
                
                <div class="variables-list">
                    <strong>Variáveis disponíveis:</strong><br>
                    <span class="variable-tag" onclick="inserirTag('{nome}')">{nome}</span>
                    <span class="variable-tag" onclick="inserirTag('{email}')">{email}</span>
                    <span class="variable-tag" onclick="inserirTag('{desinscrever_link}')">{desinscrever_link}</span>
                    <span class="variable-tag" onclick="inserirTag('{site_url}')">{site_url}</span>
                    <span class="variable-tag" onclick="inserirTag('{blog_url}')">{blog_url}</span>
                    <span class="variable-tag" onclick="inserirTag('{ano}')">{ano}</span>
                </div>
                
                <div class="form-group">
                    <label>Conteúdo do E-mail (HTML)</label>
                    <textarea name="conteudo" class="form-control" id="conteudo" required><?php 
                        echo $_POST['conteudo'] ?? '<h1>Olá {nome}!</h1>
                        
<p>Fique por dentro das novidades da Arcon:</p>

<h2>Últimos posts do blog:</h2>
<ul>
    <li><a href="{blog_url}/post-exemplo">Título do Post</a></li>
</ul>

<p>Confira nossos <a href="{site_url}/planos">planos</a> e soluções!</p>

<hr>

<p style="font-size: 12px; color: #666;">
    Se não quiser mais receber nossos e-mails, <a href="{desinscrever_link}">clique aqui</a>.
</p>';
                    ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-secondary" onclick="previewTemplate()">
                        <i class="fas fa-eye"></i> Pré-visualizar
                    </button>
                </div>
                
                <div id="previewArea" class="template-preview" style="display: none;"></div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Campanha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mostrar/esconder campo de agendamento
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const agendamentoGroup = document.getElementById('agendamentoGroup');
    agendamentoGroup.style.display = this.value === 'agendada' ? 'block' : 'none';
});

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
    const template = document.getElementById('templateSelect').value;
    const conteudo = document.getElementById('conteudo').value;
    const previewArea = document.getElementById('previewArea');
    
    // Substitui variáveis por exemplos
    let preview = conteudo
        .replace(/{nome}/g, 'João Silva')
        .replace(/{email}/g, 'joao@email.com')
        .replace(/{desinscrever_link}/g, '#')
        .replace(/{site_url}/g, '<?php echo SITE_URL; ?>')
        .replace(/{blog_url}/g, '<?php echo SITE_URL; ?>/blog.php')
        .replace(/{ano}/g, new Date().getFullYear());
    
    // Aplica template base
    preview = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="<?php echo $config['logo_url']; ?>" style="max-width: 150px;">
                <h1 style="color: #0b5cff; margin-top: 10px;">Digital Five</h1>
            </div>
            
            ${preview}
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 12px;">
                <p>
                    <a href="<?php echo $config['instagram_url']; ?>" style="color: #0b5cff; text-decoration: none; margin: 0 10px;">Instagram</a> |
                    <a href="https://wa.me/<?php echo $config['whatsapp_numero']; ?>" style="color: #0b5cff; text-decoration: none; margin: 0 10px;">WhatsApp</a>
                </p>
                <p>&copy; <?php echo date('Y'); ?> Digital Five. Todos os direitos reservados.</p>
            </div>
        </div>
    `;
    
    previewArea.innerHTML = preview;
    previewArea.style.display = 'block';
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