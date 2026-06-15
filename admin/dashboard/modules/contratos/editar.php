<?php
$page_title = 'Editar Contrato';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca contrato
$stmt = $conn->prepare("SELECT * FROM contratos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$contrato = $stmt->get_result()->fetch_assoc();

if(!$contrato) {
    header('Location: index.php');
    exit;
}

// Busca clientes para select
$clientes = $conn->query("SELECT id, nome, email FROM clientes WHERE status = 'ativo' ORDER BY nome");

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = limparInput($_POST['titulo']);
    $conteudo = $_POST['conteudo'];
    $status = limparInput($_POST['status']);
    $observacoes = limparInput($_POST['observacoes']);
    
    if(empty($titulo)) {
        $erros[] = 'Título é obrigatório';
    }
    if(empty($conteudo)) {
        $erros[] = 'Conteúdo é obrigatório';
    }
    
    if(empty($erros)) {
        // Salva histórico antes de alterar
        $stmt_hist = $conn->prepare("
            INSERT INTO contrato_historico (contrato_id, acao, dados_anteriores, dados_novos, created_by, created_at)
            VALUES (?, 'editado', ?, ?, ?, NOW())
        ");
        $dados_anteriores = json_encode($contrato);
        $dados_novos = json_encode($_POST);
        $stmt_hist->bind_param("issi", $id, $dados_anteriores, $dados_novos, $_SESSION['admin_id']);
        $stmt_hist->execute();
        
        // Atualiza contrato
        $stmt = $conn->prepare("
            UPDATE contratos SET 
                titulo = ?, conteudo = ?, status = ?, observacoes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $titulo, $conteudo, $status, $observacoes, $id);
        
        if($stmt->execute()) {
            $sucesso = 'Contrato atualizado com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'visualizar.php?id=$id'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao atualizar: ' . $conn->error;
        }
    }
}
?>

<style>
/* Estilos similares aos do criar */
.form-container {
    max-width: 1000px;
    margin: 0 auto;
}

.form-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.form-card h2 {
    font-size: 1.3rem;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-card h2 i {
    color: #4361ee;
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

.form-group label i {
    color: #4361ee;
    margin-right: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 18px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #f8faff;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}

textarea.form-control {
    min-height: 400px;
    font-family: monospace;
    resize: vertical;
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
    padding: 14px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 1rem;
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
    box-shadow: 0 15px 30px rgba(102,126,234,0.3);
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

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-edit" style="color: #4361ee; margin-right: 10px;"></i>
            Editar Contrato
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="form-container">
            <a href="visualizar.php?id=<?php echo $id; ?>" class="btn btn-secondary" style="margin-bottom: 20px;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

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
                    <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Título do Contrato</label>
                        <input type="text" name="titulo" class="form-control" required value="<?php echo $contrato['titulo']; ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-info-circle"></i> Status</label>
                        <select name="status" class="form-control">
                            <option value="rascunho" <?php echo $contrato['status'] == 'rascunho' ? 'selected' : ''; ?>>Rascunho</option>
                            <option value="enviado" <?php echo $contrato['status'] == 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                            <option value="assinado" <?php echo $contrato['status'] == 'assinado' ? 'selected' : ''; ?>>Assinado</option>
                            <option value="cancelado" <?php echo $contrato['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-newspaper"></i> Conteúdo do Contrato</label>
                        <textarea name="conteudo" class="form-control" required><?php echo htmlspecialchars($contrato['conteudo']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"><?php echo $contrato['observacoes']; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="visualizar.php?id=<?php echo $id; ?>" class="btn btn-secondary">
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