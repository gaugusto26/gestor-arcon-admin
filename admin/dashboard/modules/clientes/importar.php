<?php
$page_title = 'Importar Clientes';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$erros = [];
$sucesso = '';
$importados = 0;
$duplicados = 0;
$invalidos = 0;

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    
    if($arquivo['error'] != 0) {
        $erros[] = 'Erro no upload do arquivo';
    } else {
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        if($extensao == 'csv') {
            $handle = fopen($arquivo['tmp_name'], 'r');
            $linha = 0;
            
            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $linha++;
                
                if($linha == 1) continue; // Pula cabeçalho
                
                if(count($data) >= 3) {
                    $nome = limparInput($data[0]);
                    $email = limparInput($data[1]);
                    $telefone = limparInput($data[2] ?? '');
                    $empresa = limparInput($data[3] ?? '');
                    
                    if(empty($nome) || empty($email)) {
                        $invalidos++;
                        continue;
                    }
                    
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invalidos++;
                        continue;
                    }
                    
                    // Verifica duplicata
                    $check = $conn->query("SELECT id FROM clientes WHERE email = '$email'");
                    if($check->num_rows > 0) {
                        $duplicados++;
                        continue;
                    }
                    
                    // Gera username e senha
                    $username = gerarUsername($nome);
                    $senha = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                    $password_hash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("
                        INSERT INTO clientes (nome, email, telefone, empresa, username, password_hash, tipo, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'cliente', 'ativo')
                    ");
                    $stmt->bind_param("ssssss", $nome, $email, $telefone, $empresa, $username, $password_hash);
                    
                    if($stmt->execute()) {
                        $cliente_id = $conn->insert_id;
                        enviarEmailBoasVindas($cliente_id, $senha);
                        $importados++;
                    }
                }
            }
            fclose($handle);
            
            $sucesso = "Importação concluída: $importados clientes adicionados, $duplicados duplicados ignorados, $invalidos inválidos ignorados.";
            registrarLogCliente(0, "Importou $importados clientes via CSV");
            
        } else {
            $erros[] = 'Formato de arquivo não suportado. Use CSV';
        }
    }
}
?>

<style>
.import-container {
    max-width: 800px;
    margin: 0 auto;
}

.import-box {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 40px;
    text-align: center;
}

.import-icon {
    font-size: 4rem;
    color: #4361ee;
    margin-bottom: 20px;
}

.import-title h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.import-title p {
    color: var(--text-muted);
    margin-bottom: 30px;
}

.file-upload {
    border: 2px dashed var(--border);
    border-radius: 16px;
    padding: 40px;
    background: #f8faff;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.file-upload:hover {
    border-color: #4361ee;
    background: #ffffff;
}

.file-upload i {
    font-size: 3rem;
    color: #4361ee;
    margin-bottom: 15px;
}

.example-box {
    background: #f8faff;
    border-radius: 12px;
    padding: 20px;
    margin-top: 30px;
    text-align: left;
}

.example-box pre {
    background: #ffffff;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-top: 10px;
}

.btn {
    padding: 14px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 1rem;
}

.stats-box {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 30px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8faff;
    border-radius: 12px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #4361ee;
}

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-import" style="color: #4361ee; margin-right: 10px;"></i>
            Importar Clientes
        </h1>
    </div>

    <div class="content-area">
        <div class="import-container">
            <?php if(!empty($erros)): ?>
                <?php foreach($erros as $erro): ?>
                <div class="alert alert-error"><?php echo $erro; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
            <div class="stats-box">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $importados; ?></div>
                    <div>Importados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $duplicados; ?></div>
                    <div>Duplicados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $invalidos; ?></div>
                    <div>Inválidos</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="import-box">
                <div class="import-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                
                <div class="import-title">
                    <h2>Importar Clientes em Massa</h2>
                    <p>Envie um arquivo CSV com os dados dos clientes</p>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="file-upload" onclick="document.getElementById('arquivo').click()">
                        <i class="fas fa-file-csv"></i>
                        <p><strong>Clique para selecionar o arquivo</strong></p>
                        <p>ou arraste e solte aqui</p>
                        <small>Formato aceito: CSV</small>
                        <div id="file-name" style="margin-top: 10px; color: #4361ee;"></div>
                    </div>
                    
                    <input type="file" name="arquivo" id="arquivo" accept=".csv" style="display: none;" required>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-cloud-upload-alt"></i> Importar Clientes
                    </button>
                </form>
                
                <div class="example-box">
                    <h4>Formato do arquivo CSV:</h4>
                    <pre>nome,email,telefone,empresa
João Silva,joao@email.com,(11)99999-9999,Empresa ABC
Maria Santos,maria@email.com,(11)88888-8888,</pre>
                    <p><small>O cabeçalho é obrigatório. Apenas nome e email são obrigatórios.</small></p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('arquivo').addEventListener('change', function(e) {
    const fileName = document.getElementById('file-name');
    if(this.files.length > 0) {
        fileName.innerHTML = '<i class="fas fa-check-circle"></i> ' + this.files[0].name;
    }
});

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