<?php
$page_title = 'Configurar Indicações';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Busca configuração atual
$config = $conn->query("SELECT * FROM indicacoes_config WHERE id = 1")->fetch_assoc();

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $percentual_desconto = floatval($_POST['percentual_desconto']);
    $dias_validade = intval($_POST['dias_validade']);
    $limite_indicacoes = intval($_POST['limite_indicacoes']);
    $desconto_acumulativo = isset($_POST['desconto_acumulativo']) ? 1 : 0;
    $mensagem_whatsapp = limparInput($_POST['mensagem_whatsapp']);
    $regras = limparInput($_POST['regras']);
    
    if($percentual_desconto <= 0 || $percentual_desconto > 100) {
        $erros[] = 'Percentual de desconto deve ser entre 1 e 100';
    }
    if($dias_validade <= 0) {
        $erros[] = 'Dias de validade deve ser maior que zero';
    }
    
    if(empty($erros)) {
        if($config) {
            // Atualiza
            $stmt = $conn->prepare("
                UPDATE indicacoes_config SET 
                    percentual_desconto = ?,
                    dias_validade = ?,
                    limite_indicacoes = ?,
                    desconto_acumulativo = ?,
                    mensagem_whatsapp = ?,
                    regras = ?
                WHERE id = 1
            ");
            $stmt->bind_param("diiiss", 
                $percentual_desconto, $dias_validade, $limite_indicacoes, 
                $desconto_acumulativo, $mensagem_whatsapp, $regras
            );
        } else {
            // Insere
            $stmt = $conn->prepare("
                INSERT INTO indicacoes_config 
                (percentual_desconto, dias_validade, limite_indicacoes, desconto_acumulativo, mensagem_whatsapp, regras)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("diiiss", 
                $percentual_desconto, $dias_validade, $limite_indicacoes, 
                $desconto_acumulativo, $mensagem_whatsapp, $regras
            );
        }
        
        if($stmt->execute()) {
            $sucesso = 'Configurações salvas com sucesso!';
            $config = $conn->query("SELECT * FROM indicacoes_config WHERE id = 1")->fetch_assoc();
        } else {
            $erros[] = 'Erro ao salvar: ' . $conn->error;
        }
    }
}
?>

<style>
.form-container {
    max-width: 800px;
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
    min-height: 120px;
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
    margin: 15px 0;
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

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}

.info-box {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.variable-tag {
    display: inline-block;
    background: #ffffff;
    border: 1px solid #10b981;
    color: #10b981;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.85rem;
    margin: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.variable-tag:hover {
    background: #10b981;
    color: white;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-cog" style="color: #10b981; margin-right: 10px;"></i>
            Configurar Indicações
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

            <form method="POST">
                <div class="form-card">
                    <h2><i class="fas fa-percent"></i> Configurações Gerais</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-percent"></i> Percentual de Desconto (%)</label>
                            <input type="number" step="0.1" min="1" max="100" name="percentual_desconto" class="form-control" required value="<?php echo $config['percentual_desconto'] ?? 10; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-clock"></i> Validade (dias)</label>
                            <input type="number" min="1" name="dias_validade" class="form-control" required value="<?php echo $config['dias_validade'] ?? 90; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-infinity"></i> Limite de Indicações</label>
                            <input type="number" min="0" name="limite_indicacoes" class="form-control" value="<?php echo $config['limite_indicacoes'] ?? 0; ?>" placeholder="0 = ilimitado">
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="desconto_acumulativo" id="desconto_acumulativo" value="1" <?php echo ($config['desconto_acumulativo'] ?? 0) ? 'checked' : ''; ?>>
                                <label for="desconto_acumulativo">Permitir descontos acumulativos</label>
                            </div>
                            <small style="color: var(--text-muted);">Se ativado, múltiplas indicações podem acumular descontos</small>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <h2><i class="fas fa-whatsapp"></i> Mensagem Padrão (WhatsApp)</h2>
                    
                    <div class="info-box">
                        <p><strong>Variáveis disponíveis:</strong></p>
                        <span class="variable-tag" onclick="inserirTag('{codigo}')">{codigo}</span>
                        <span class="variable-tag" onclick="inserirTag('{indicador}')">{indicador}</span>
                        <span class="variable-tag" onclick="inserirTag('{site_url}')">{site_url}</span>
                        <span class="variable-tag" onclick="inserirTag('{desconto}')">{desconto}</span>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp"></i> Mensagem</label>
                        <textarea name="mensagem_whatsapp" class="form-control" id="mensagem"><?php echo $config['mensagem_whatsapp'] ?? 'Olá! Gostaria de indicar a NTW para você. Use meu código {codigo} e ganhe {desconto}% de desconto! Acesse: {site_url}'; ?></textarea>
                    </div>
                </div>

                <div class="form-card">
                    <h2><i class="fas fa-file-alt"></i> Regras do Programa</h2>
                    
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> Regras</label>
                        <textarea name="regras" class="form-control" rows="8"><?php echo $config['regras'] ?? "1. O desconto de 10% é aplicado na mensalidade do indicado por 3 meses\n2. O indicador ganha 10% de desconto na sua própria mensalidade por 3 meses\n3. A indicação só é válida após a confirmação do primeiro pagamento\n4. O código tem validade de 90 dias\n5. Para acumular descontos, o cliente deve continuar indicando novos clientes"; ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function inserirTag(tag) {
    const textarea = document.getElementById('mensagem');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + tag.length, start + tag.length);
}

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