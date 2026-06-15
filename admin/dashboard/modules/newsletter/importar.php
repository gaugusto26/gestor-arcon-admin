<?php
$page_title = 'Importar Inscritos';
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
    $origem = limparInput($_POST['origem'] ?? 'importacao');
    $confirmar_automatico = isset($_POST['confirmar_automatico']);
    
    if($arquivo['error'] != 0) {
        $erros[] = 'Erro no upload do arquivo';
    } else {
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        if($extensao == 'csv') {
            // Processa CSV
            $handle = fopen($arquivo['tmp_name'], 'r');
            $linha = 0;
            
            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $linha++;
                
                // Pula cabeçalho se existir
                if($linha == 1 && (strtolower($data[0]) == 'nome' || strtolower($data[0]) == 'email')) {
                    continue;
                }
                
                // CSV pode ter formatos diferentes
                if(count($data) >= 2) {
                    // Formato: Nome, Email
                    $nome = limparInput($data[0]);
                    $email = limparInput($data[1]);
                } elseif(count($data) == 1) {
                    // Só email
                    $nome = explode('@', $data[0])[0];
                    $email = limparInput($data[0]);
                } else {
                    $invalidos++;
                    continue;
                }
                
                // Valida email
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidos++;
                    continue;
                }
                
                // Verifica se já existe
                $stmt = $conn->prepare("SELECT id FROM newsletter_inscritos WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                    $duplicados++;
                    continue;
                }
                
                // Insere
                $token = gerarTokenUnico();
                $confirmado = $confirmar_automatico ? 1 : 0;
                $data_confirmacao = $confirmar_automatico ? date('Y-m-d H:i:s') : null;
                
                $stmt = $conn->prepare("
                    INSERT INTO newsletter_inscritos (nome, email, token, confirmado, data_confirmacao, origem, ip) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("sssisss", $nome, $email, $token, $confirmado, $data_confirmacao, $origem, $_SERVER['REMOTE_ADDR']);
                
                if($stmt->execute()) {
                    $importados++;
                }
            }
            
            fclose($handle);
            
        } elseif($extensao == 'txt') {
            // TXT: um email por linha
            $conteudo = file_get_contents($arquivo['tmp_name']);
            $linhas = explode("\n", $conteudo);
            
            foreach($linhas as $linha) {
                $linha = trim($linha);
                if(empty($linha)) continue;
                
                if(filter_var($linha, FILTER_VALIDATE_EMAIL)) {
                    $email = $linha;
                    $nome = explode('@', $email)[0];
                } else {
                    // Pode ser "Nome <email>"
                    if(preg_match('/(.*)<(.*)>/', $linha, $matches)) {
                        $nome = trim($matches[1]);
                        $email = trim($matches[2]);
                    } else {
                        $invalidos++;
                        continue;
                    }
                }
                
                // Valida email
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidos++;
                    continue;
                }
                
                // Verifica duplicata
                $stmt = $conn->prepare("SELECT id FROM newsletter_inscritos WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                    $duplicados++;
                    continue;
                }
                
                // Insere
                $token = gerarTokenUnico();
                $confirmado = $confirmar_automatico ? 1 : 0;
                $data_confirmacao = $confirmar_automatico ? date('Y-m-d H:i:s') : null;
                
                $stmt = $conn->prepare("
                    INSERT INTO newsletter_inscritos (nome, email, token, confirmado, data_confirmacao, origem, ip) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("sssisss", $nome, $email, $token, $confirmado, $data_confirmacao, $origem, $_SERVER['REMOTE_ADDR']);
                
                if($stmt->execute()) {
                    $importados++;
                }
            }
        } else {
            $erros[] = 'Formato de arquivo não suportado. Use CSV ou TXT';
        }
        
        if($importados > 0) {
            $sucesso = "Importação concluída: $importados inscritos adicionados, $duplicados duplicados ignorados, $invalidos inválidos ignorados.";
            registrarLog($conn, $_SESSION['admin_id'], "Importou $importados inscritos via arquivo");
        }
    }
}

// Busca origens para o select
$origens = $conn->query("SELECT DISTINCT origem FROM newsletter_inscritos WHERE origem IS NOT NULL UNION SELECT 'importacao'");
?>

<style>
.import-container {
    max-width: 800px;
    margin: 0 auto;
}

.import-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px;
    margin-bottom: 30px;
}

.import-icon {
    font-size: 4rem;
    color: var(--accent);
    text-align: center;
    margin-bottom: 20px;
}

.import-title {
    text-align: center;
    margin-bottom: 30px;
}

.import-title h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.import-title p {
    color: var(--text-muted);
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

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.form-check input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-check label {
    margin-bottom: 0;
    cursor: pointer;
}

.file-upload {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    background: var(--bg-secondary);
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.file-upload:hover {
    border-color: var(--accent);
    background: var(--accent-light);
}

.file-upload i {
    font-size: 3rem;
    color: var(--accent);
    margin-bottom: 15px;
}

.file-upload p {
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.file-upload small {
    color: var(--text-muted);
}

.file-name {
    margin-top: 10px;
    font-weight: 600;
    color: var(--accent);
}

.btn {
    padding: 14px 30px;
    border-radius: 8px;
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
    background: var(--accent);
    color: white;
    width: 100%;
    justify-content: center;
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

.example-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-top: 30px;
}

.example-box h4 {
    margin-bottom: 15px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.example-box pre {
    background: var(--card-bg);
    padding: 15px;
    border-radius: 8px;
    color: var(--text-secondary);
    font-family: monospace;
    font-size: 0.9rem;
    border: 1px solid var(--border);
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
    background: var(--bg-secondary);
    border-radius: 12px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent);
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-top: 5px;
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

.alert-warning {
    background: #f59e0b20;
    color: #f59e0b;
    border: 1px solid #f59e0b;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-import"></i>
            Importar Inscritos
        </h1>
    </div>

    <div class="content-area">
        <div class="import-container">
            <?php if(!empty($erros)): ?>
                <?php foreach($erros as $erro): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
            
            <div class="stats-box">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $importados; ?></div>
                    <div class="stat-label">Importados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $duplicados; ?></div>
                    <div class="stat-label">Duplicados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $invalidos; ?></div>
                    <div class="stat-label">Inválidos</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="import-box">
                <div class="import-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                
                <div class="import-title">
                    <h2>Importar Lista de Contatos</h2>
                    <p>Envie um arquivo CSV ou TXT com os e-mails que deseja adicionar</p>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="importForm">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Origem dos contatos</label>
                        <select name="origem" class="form-control" required>
                            <option value="">Selecione a origem</option>
                            <?php 
                            $origens->data_seek(0);
                            while($origem = $origens->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $origem['origem']; ?>">
                                <?php echo ucfirst($origem['origem']); ?>
                            </option>
                            <?php endwhile; ?>
                            <option value="evento">Evento</option>
                            <option value="parceria">Parceria</option>
                            <option value="compra">Compra</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="confirmar_automatico" id="confirmar_automatico" value="1" checked>
                        <label for="confirmar_automatico">Confirmar automaticamente (não enviar e-mail de confirmação)</label>
                    </div>
                    
                    <div class="file-upload" id="fileUpload" onclick="document.getElementById('arquivo').click()">
                        <i class="fas fa-file-csv"></i>
                        <p><strong>Clique para selecionar o arquivo</strong></p>
                        <p>ou arraste e solte aqui</p>
                        <small>Formatos aceitos: CSV, TXT (até 5MB)</small>
                        <div id="fileName" class="file-name"></div>
                    </div>
                    
                    <input type="file" name="arquivo" id="arquivo" accept=".csv,.txt" style="display: none;" required>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Importar Contatos
                    </button>
                </form>
                
                <div class="example-box">
                    <h4><i class="fas fa-info-circle"></i> Formatos aceitos:</h4>
                    
                    <p><strong>CSV (separado por vírgula):</strong></p>
                    <pre>Nome,Email
João Silva,joao@email.com
Maria Santos,maria@email.com</pre>
                    
                    <p><strong>TXT (um por linha):</strong></p>
                    <pre>joao@email.com
maria@email.com
"João Silva" <joao@email.com></pre>
                    
                    <p><small>O sistema detecta automaticamente o formato e ignora cabeçalhos.</small></p>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="inscritos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar para lista
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Mostra nome do arquivo selecionado
document.getElementById('arquivo').addEventListener('change', function(e) {
    const fileName = document.getElementById('fileName');
    if(this.files.length > 0) {
        fileName.innerHTML = '<i class="fas fa-check-circle"></i> ' + this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(2) + ' KB)';
    } else {
        fileName.innerHTML = '';
    }
});

// Drag and drop
const fileUpload = document.getElementById('fileUpload');
const fileInput = document.getElementById('arquivo');

fileUpload.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileUpload.style.borderColor = 'var(--accent)';
    fileUpload.style.background = 'var(--accent-light)';
});

fileUpload.addEventListener('dragleave', (e) => {
    e.preventDefault();
    fileUpload.style.borderColor = 'var(--border)';
    fileUpload.style.background = 'var(--bg-secondary)';
});

fileUpload.addEventListener('drop', (e) => {
    e.preventDefault();
    fileUpload.style.borderColor = 'var(--border)';
    fileUpload.style.background = 'var(--bg-secondary)';
    
    const files = e.dataTransfer.files;
    if(files.length > 0) {
        fileInput.files = files;
        const event = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(event);
    }
});

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