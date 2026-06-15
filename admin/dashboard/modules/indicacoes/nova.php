<?php
$page_title = 'Nova Indicação';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Busca clientes para select (indicadores)
$indicadores = $conn->query("SELECT id, nome, empresa, codigo_indicacao FROM clientes WHERE status = 'ativo' ORDER BY nome");

// Busca configuração
$config = $conn->query("SELECT * FROM indicacoes_config WHERE id = 1")->fetch_assoc();

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $indicador_id = (int)$_POST['indicador_id'];
    $nome_indicado = limparInput($_POST['nome_indicado']);
    $email_indicado = limparInput($_POST['email_indicado']);
    $telefone_indicado = limparInput($_POST['telefone_indicado']);
    $observacoes = limparInput($_POST['observacoes']);
    
    if(empty($indicador_id)) {
        $erros[] = 'Selecione quem está indicando';
    }
    if(empty($nome_indicado)) {
        $erros[] = 'Nome do indicado é obrigatório';
    }
    if(empty($email_indicado)) {
        $erros[] = 'E-mail do indicado é obrigatório';
    } elseif(!filter_var($email_indicado, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail do indicado inválido';
    }
    
    if(empty($erros)) {
        // Busca código do indicador
        $result = $conn->query("SELECT codigo_indicacao FROM clientes WHERE id = $indicador_id");
        $indicador = $result->fetch_assoc();
        $codigo = $indicador['codigo_indicacao'];
        
        // Calcula data de expiração
        $data_expiracao = date('Y-m-d H:i:s', strtotime("+{$config['dias_validade']} days"));
        
        // Insere indicação
        $stmt = $conn->prepare("
            INSERT INTO indicacoes (
                indicador_id, codigo_indicacao, nome_indicado, email_indicado, 
                telefone_indicado, status, data_expiracao, observacoes
            ) VALUES (?, ?, ?, ?, ?, 'pendente', ?, ?)
        ");
        $stmt->bind_param("issssss", 
            $indicador_id, $codigo, $nome_indicado, $email_indicado, 
            $telefone_indicado, $data_expiracao, $observacoes
        );
        
        if($stmt->execute()) {
            $indicacao_id = $conn->insert_id;
            
            // Registra histórico
            registrarHistoricoIndicacao($indicacao_id, 'criada', 'Indicação criada manualmente pelo admin');
            
            $sucesso = 'Indicação registrada com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao salvar: ' . $conn->error;
        }
    }
}
?>

<style>
.form-container {
    max-width: 700px;
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
    color: #10b981;
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
    color: #10b981;
    margin-right: 8px;
    width: 20px;
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
    border-color: #10b981;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.info-box {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.info-box h4 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #10b981;
}

.info-box ul {
    padding-left: 20px;
    color: var(--text-secondary);
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
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
    box-shadow: 0 10px 20px rgba(16,185,129,0.2);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(16,185,129,0.3);
}

.btn-secondary {
    background: #f8faff;
    color: #10b981;
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: #ffffff;
    border-color: #10b981;
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
            <i class="fas fa-plus-circle" style="color: #10b981; margin-right: 10px;"></i>
            Nova Indicação
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="form-container">
            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px;">
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
                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Sobre o programa de indicações</h4>
                    <ul>
                        <li>O indicador ganha <strong><?php echo $config['percentual_desconto']; ?>% de desconto</strong> na mensalidade por 3 meses</li>
                        <li>O indicado também ganha <strong><?php echo $config['percentual_desconto']; ?>% de desconto</strong> na primeira mensalidade</li>
                        <li>O código tem validade de <strong><?php echo $config['dias_validade']; ?> dias</strong></li>
                    </ul>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user-tie"></i> Quem está indicando *</label>
                        <select name="indicador_id" class="form-control" required>
                            <option value="">Selecione o cliente</option>
                            <?php while($ind = $indicadores->fetch_assoc()): ?>
                            <option value="<?php echo $ind['id']; ?>" <?php echo ($_POST['indicador_id'] ?? '') == $ind['id'] ? 'selected' : ''; ?>>
                                <?php echo $ind['nome']; ?> - <?php echo $ind['empresa'] ?: $ind['email']; ?> 
                                (Código: <?php echo $ind['codigo_indicacao']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <h3 style="margin: 30px 0 20px; color: var(--text-primary);">Dados do Indicado</h3>

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nome do Indicado *</label>
                        <input type="text" name="nome_indicado" class="form-control" required value="<?php echo $_POST['nome_indicado'] ?? ''; ?>">
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> E-mail *</label>
                            <input type="email" name="email_indicado" class="form-control" required value="<?php echo $_POST['email_indicado'] ?? ''; ?>">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Telefone</label>
                            <input type="text" name="telefone_indicado" class="form-control" value="<?php echo $_POST['telefone_indicado'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"><?php echo $_POST['observacoes'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Indicação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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