<?php
$page_title = 'Editar Cliente';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if(!$cliente) {
    header('Location: index.php');
    exit;
}

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = limparInput($_POST['nome']);
    $email = limparInput($_POST['email']);
    $telefone = limparInput($_POST['telefone']);
    $celular = limparInput($_POST['celular']);
    $cpf_cnpj = limparInput($_POST['cpf_cnpj']);
    $rg_ie = limparInput($_POST['rg_ie']);
    $data_nascimento = $_POST['data_nascimento'] ?: null;
    $empresa = limparInput($_POST['empresa']);
    $cargo = limparInput($_POST['cargo']);
    $endereco = limparInput($_POST['endereco']);
    $cidade = limparInput($_POST['cidade']);
    $estado = limparInput($_POST['estado']);
    $cep = limparInput($_POST['cep']);
    $tipo = limparInput($_POST['tipo']);
    $status = limparInput($_POST['status']);
    $observacoes = limparInput($_POST['observacoes']);
    
    if(empty($nome)) {
        $erros[] = 'Nome é obrigatório';
    }
    if(empty($email)) {
        $erros[] = 'E-mail é obrigatório';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido';
    }
    
    // Verifica se email já existe (exceto este cliente)
    $check = $conn->query("SELECT id FROM clientes WHERE email = '$email' AND id != $id");
    if($check->num_rows > 0) {
        $erros[] = 'Este e-mail já está cadastrado para outro cliente';
    }
    
    if(empty($erros)) {
        $stmt = $conn->prepare("
            UPDATE clientes SET 
                nome = ?, email = ?, telefone = ?, celular = ?, cpf_cnpj = ?, 
                rg_ie = ?, data_nascimento = ?, empresa = ?, cargo = ?, 
                endereco = ?, cidade = ?, estado = ?, cep = ?, 
                tipo = ?, status = ?, observacoes = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "sssssssssssssssi",
            $nome, $email, $telefone, $celular, $cpf_cnpj, $rg_ie, $data_nascimento,
            $empresa, $cargo, $endereco, $cidade, $estado, $cep,
            $tipo, $status, $observacoes, $id
        );
        
        if($stmt->execute()) {
            registrarLogCliente($id, "Dados atualizados pelo admin");
            $sucesso = 'Cliente atualizado com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'visualizar.php?id=$id'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao atualizar: ' . $conn->error;
        }
    }
}

// Estados brasileiros
$estados = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS',
    'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC',
    'SP', 'SE', 'TO'
];
?>

<style>
/* Mesmos estilos do criar.php */
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

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
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
    border-color: #4361ee;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}

textarea.form-control {
    min-height: 100px;
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

.radio-group {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.radio-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.radio-item input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-edit" style="color: #4361ee; margin-right: 10px;"></i>
            Editar Cliente: <?php echo $cliente['nome']; ?>
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

            <form method="POST">
                <!-- Informações Pessoais -->
                <div class="form-card">
                    <h2><i class="fas fa-user"></i> Informações Pessoais</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" required value="<?php echo $cliente['nome']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> E-mail *</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo $cliente['email']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?php echo $cliente['telefone']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-mobile-alt"></i> Celular</label>
                            <input type="text" name="celular" class="form-control" value="<?php echo $cliente['celular']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> CPF/CNPJ</label>
                            <input type="text" name="cpf_cnpj" class="form-control" value="<?php echo $cliente['cpf_cnpj']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> RG/IE</label>
                            <input type="text" name="rg_ie" class="form-control" value="<?php echo $cliente['rg_ie']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control" value="<?php echo $cliente['data_nascimento']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Tipo</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="tipo" value="cliente" <?php echo $cliente['tipo'] == 'cliente' ? 'checked' : ''; ?>>
                                    <label>Cliente</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="tipo" value="admin" <?php echo $cliente['tipo'] == 'admin' ? 'checked' : ''; ?>>
                                    <label>Admin</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="tipo" value="parceiro" <?php echo $cliente['tipo'] == 'parceiro' ? 'checked' : ''; ?>>
                                    <label>Parceiro</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Profissionais -->
                <div class="form-card">
                    <h2><i class="fas fa-briefcase"></i> Informações Profissionais</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Empresa</label>
                            <input type="text" name="empresa" class="form-control" value="<?php echo $cliente['empresa']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user-tie"></i> Cargo</label>
                            <input type="text" name="cargo" class="form-control" value="<?php echo $cliente['cargo']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="form-card">
                    <h2><i class="fas fa-map-marker-alt"></i> Endereço</h2>
                    
                    <div class="form-group">
                        <label><i class="fas fa-road"></i> Endereço</label>
                        <input type="text" name="endereco" class="form-control" value="<?php echo $cliente['endereco']; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-city"></i> Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?php echo $cliente['cidade']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-map"></i> Estado</label>
                            <select name="estado" class="form-control">
                                <option value="">Selecione</option>
                                <?php foreach($estados as $uf): ?>
                                <option value="<?php echo $uf; ?>" <?php echo $cliente['estado'] == $uf ? 'selected' : ''; ?>>
                                    <?php echo $uf; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-mail-bulk"></i> CEP</label>
                            <input type="text" name="cep" class="form-control" value="<?php echo $cliente['cep']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-circle"></i> Status</label>
                            <select name="status" class="form-control">
                                <option value="ativo" <?php echo $cliente['status'] == 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?php echo $cliente['status'] == 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                <option value="bloqueado" <?php echo $cliente['status'] == 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="form-card">
                    <h2><i class="fas fa-comment"></i> Observações</h2>
                    
                    <div class="form-group">
                        <textarea name="observacoes" class="form-control" rows="4" placeholder="Observações sobre o cliente..."><?php echo $cliente['observacoes']; ?></textarea>
                    </div>
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