<?php
$page_title = 'Configurar Pagamentos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$config = getConfigPagamento();

// Carrega SDKs se existirem
$autoload = __DIR__ . '/../../../../vendor/autoload.php';
if (file_exists($autoload)) require_once $autoload;
$sdk_mp_loaded = class_exists('MercadoPago\\SDK') || class_exists('MercadoPago\\MercadoPagoConfig');
$sdk_pagbank_loaded = class_exists('PagBank\\PagBank') || class_exists('PagSeguro\\Library');

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gateway = limparInput($_POST['gateway']);
    $modo = limparInput($_POST['modo']);
    $public_key = limparInput($_POST['public_key']);
    $access_token = limparInput($_POST['access_token']);
    $client_id = limparInput($_POST['client_id']);
    $client_secret = limparInput($_POST['client_secret']);
    $webhook_secret = limparInput($_POST['webhook_secret']);
    $pix_key = limparInput($_POST['pix_key']);
    $pix_key_type = limparInput($_POST['pix_key_type']);
    $juros_mensal = floatval($_POST['juros_mensal']);
    $multa_atraso = floatval($_POST['multa_atraso']);
    $juros_dia = floatval($_POST['juros_dia']);
    
    // Gera webhook URL
    $webhook_url = SITE_URL . "/webhook/" . $gateway . ".php";
    
    $stmt = $conn->prepare("
        UPDATE pagamento_config SET 
            gateway = ?,
            modo = ?,
            public_key = ?,
            access_token = ?,
            client_id = ?,
            client_secret = ?,
            webhook_secret = ?,
            webhook_url = ?,
            pix_key = ?,
            pix_key_type = ?,
            juros_mensal = ?,
            multa_atraso = ?,
            juros_dia = ?
        WHERE id = 1
    ");
    
    $stmt->bind_param(
        "ssssssssssddd",
        $gateway, $modo, $public_key, $access_token, $client_id, $client_secret,
        $webhook_secret, $webhook_url, $pix_key, $pix_key_type,
        $juros_mensal, $multa_atraso, $juros_dia
    );
    
    if($stmt->execute()) {
        $sucesso = 'Configurações salvas com sucesso!';
        $config = getConfigPagamento();
    } else {
        $erros[] = 'Erro ao salvar: ' . $conn->error;
    }
}

// SDKs disponíveis
$sdks = [
    'mercadopago' => [
        'nome' => 'Mercado Pago',
        'site' => 'https://www.mercadopago.com.br/developers',
        'docs' => 'https://www.mercadopago.com.br/developers/pt/docs',
        'sdk' => 'mercadopago/sdk',
        'instalacao' => 'composer require mercadopago/sdk',
        'test_keys' => true
    ],
    'pagbank' => [
        'nome' => 'PagBank',
        'site' => 'https://pagseguro.uol.com.br/',
        'docs' => 'https://dev.pagbank.uol.com.br/',
        'sdk' => 'pagseguro/pagbank-php',
        'instalacao' => 'composer require pagseguro/pagbank-php',
        'test_keys' => false
    ]
];
?>

<style>
.form-container {
    max-width: 900px;
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

.gateway-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.gateway-card {
    background: #f8faff;
    border: 2px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.gateway-card:hover {
    border-color: #4361ee;
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.gateway-card.selected {
    border-color: #10b981;
    background: #f0fdf4;
}

.gateway-icon {
    width: 70px;
    height: 70px;
    background: #ffffff;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 2rem;
    color: #4361ee;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.gateway-card h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.gateway-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    background: #f0fdf4;
    color: #10b981;
    border: 1px solid #10b981;
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

.info-box {
    background: #f8faff;
    border-left: 4px solid #4361ee;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.info-box h4 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: var(--text-primary);
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

.alert-warning {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid #f97316;
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
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    box-shadow: 0 10px 20px rgba(67,97,238,0.2);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(67,97,238,0.3);
}

.btn-secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}

.sdk-status {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.sdk-instalado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.sdk-nao-instalado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.webhook-box {
    background: #1e293b;
    color: #ffffff;
    padding: 15px;
    border-radius: 8px;
    font-family: monospace;
    font-size: 0.9rem;
    margin-top: 10px;
}

.copy-btn {
    background: transparent;
    border: 1px solid #ffffff;
    color: #ffffff;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 10px;
    font-size: 0.8rem;
}

.copy-btn:hover {
    background: #ffffff;
    color: #1e293b;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-credit-card" style="color: #4361ee; margin-right: 10px;"></i>
            Configurar Pagamentos
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

            <?php if(!$sdk_mp_loaded && !$sdk_pagbank_loaded): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>SDKs não encontrados!</strong> Execute os comandos abaixo para instalar:
                <pre style="margin-top: 10px; background: #1e293b; color: white; padding: 10px; border-radius: 8px;">
composer require mercadopago/sdk
composer require pagseguro/pagbank-php</pre>
            </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Seleção do Gateway -->
                <div class="form-card">
                    <h2><i class="fas fa-credit-card"></i> Gateway de Pagamento</h2>
                    
                    <div class="gateway-selector">
                        <div class="gateway-card <?php echo $config['gateway'] == 'mercadopago' ? 'selected' : ''; ?>" onclick="selecionarGateway('mercadopago')">
                            <div class="gateway-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3>Mercado Pago</h3>
                            <span class="gateway-badge">Cartão e PIX</span>
                        </div>
                        
                        <div class="gateway-card <?php echo $config['gateway'] == 'pagbank' ? 'selected' : ''; ?>" onclick="selecionarGateway('pagbank')">
                            <div class="gateway-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <h3>PagBank</h3>
                            <span class="gateway-badge">Cartão e PIX</span>
                        </div>
                        
                        <div class="gateway-card <?php echo $config['gateway'] == 'nenhum' ? 'selected' : ''; ?>" onclick="selecionarGateway('nenhum')">
                            <div class="gateway-icon">
                                <i class="fas fa-times"></i>
                            </div>
                            <h3>Nenhum</h3>
                            <span class="gateway-badge">Desativado</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="gateway" id="gateway_selecionado" value="<?php echo $config['gateway']; ?>">
                </div>

                <!-- Configurações Gerais -->
                <div class="form-card">
                    <h2><i class="fas fa-cog"></i> Configurações Gerais</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-globe"></i> Modo</label>
                            <select name="modo" class="form-control">
                                <option value="teste" <?php echo $config['modo'] == 'teste' ? 'selected' : ''; ?>>Teste / Sandbox</option>
                                <option value="producao" <?php echo $config['modo'] == 'producao' ? 'selected' : ''; ?>>Produção</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-percent"></i> Juros Mensal (%)</label>
                            <input type="number" step="0.01" name="juros_mensal" class="form-control" value="<?php echo $config['juros_mensal']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-exclamation-triangle"></i> Multa por Atraso (%)</label>
                            <input type="number" step="0.01" name="multa_atraso" class="form-control" value="<?php echo $config['multa_atraso']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-clock"></i> Juros por Dia (%)</label>
                            <input type="number" step="0.01" name="juros_dia" class="form-control" value="<?php echo $config['juros_dia']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Configurações PIX -->
                <div class="form-card">
                    <h2><i class="fas fa-qrcode"></i> Configurações PIX</h2>
                    
                    <div class="info-box">
                        <h4><i class="fas fa-info-circle"></i> Chave PIX</h4>
                        <p>Configure a chave PIX que aparecerá para seus clientes.</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Chave PIX</label>
                            <input type="text" name="pix_key" class="form-control" value="<?php echo $config['pix_key']; ?>" placeholder="CPF, CNPJ, Telefone, Email...">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Tipo de Chave</label>
                            <select name="pix_key_type" class="form-control">
                                <option value="cnpj" <?php echo $config['pix_key_type'] == 'cnpj' ? 'selected' : ''; ?>>CNPJ</option>
                                <option value="cpf" <?php echo $config['pix_key_type'] == 'cpf' ? 'selected' : ''; ?>>CPF</option>
                                <option value="telefone" <?php echo $config['pix_key_type'] == 'telefone' ? 'selected' : ''; ?>>Telefone</option>
                                <option value="email" <?php echo $config['pix_key_type'] == 'email' ? 'selected' : ''; ?>>E-mail</option>
                                <option value="aleatoria" <?php echo $config['pix_key_type'] == 'aleatoria' ? 'selected' : ''; ?>>Chave Aleatória</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Configurações Mercado Pago -->
                <div id="config-mercadopago" style="display: <?php echo $config['gateway'] == 'mercadopago' ? 'block' : 'none'; ?>;">
                    <div class="form-card">
                        <h2><i class="fab fa-mercadopago"></i> Credenciais Mercado Pago</h2>
                        
                        <div class="info-box">
                            <h4><i class="fas fa-info-circle"></i> Como obter suas credenciais</h4>
                            <ol>
                                <li>Acesse <a href="<?php echo $sdks['mercadopago']['site']; ?>" target="_blank"><?php echo $sdks['mercadopago']['site']; ?></a></li>
                                <li>Faça login na sua conta</li>
                                <li>Vá em "Credenciais" no menu</li>
                                <li>Copie as chaves de produção ou teste</li>
                            </ol>
                        </div>
                        
                        <?php if(!$sdk_mp_loaded): ?>
                        <div class="sdk-status sdk-nao-instalado">
                            <i class="fas fa-exclamation-circle"></i>
                            SDK do Mercado Pago não encontrado. Execute: <strong><?php echo $sdks['mercadopago']['instalacao']; ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Public Key</label>
                            <input type="text" name="public_key" class="form-control" value="<?php echo $config['public_key']; ?>" placeholder="TEST-... ou APP_USR-...">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Access Token</label>
                            <input type="password" name="access_token" class="form-control" value="<?php echo $config['access_token']; ?>" placeholder="TEST-... ou APP_USR-...">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-webhook"></i> Webhook Secret</label>
                            <input type="text" name="webhook_secret" class="form-control" value="<?php echo $config['webhook_secret']; ?>" placeholder="Secret para validar notificações">
                        </div>
                    </div>
                </div>

                <!-- Configurações PagBank -->
                <div id="config-pagbank" style="display: <?php echo $config['gateway'] == 'pagbank' ? 'block' : 'none'; ?>;">
                    <div class="form-card">
                        <h2><i class="fas fa-wallet"></i> Credenciais PagBank</h2>
                        
                        <div class="info-box">
                            <h4><i class="fas fa-info-circle"></i> Como obter suas credenciais</h4>
                            <ol>
                                <li>Acesse <a href="<?php echo $sdks['pagbank']['site']; ?>" target="_blank"><?php echo $sdks['pagbank']['site']; ?></a></li>
                                <li>Faça login na sua conta</li>
                                <li>Vá em "Minha Conta" > "Credenciais"</li>
                                <li>Copie o Client ID e Client Secret</li>
                            </ol>
                        </div>
                        
                        <?php if(!$sdk_pagbank_loaded): ?>
                        <div class="sdk-status sdk-nao-instalado">
                            <i class="fas fa-exclamation-circle"></i>
                            SDK do PagBank não encontrado. Execute: <strong><?php echo $sdks['pagbank']['instalacao']; ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> Client ID</label>
                                <input type="text" name="client_id" class="form-control" value="<?php echo $config['client_id']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Client Secret</label>
                                <input type="password" name="client_secret" class="form-control" value="<?php echo $config['client_secret']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-webhook"></i> Webhook Secret</label>
                            <input type="text" name="webhook_secret" class="form-control" value="<?php echo $config['webhook_secret']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Webhook URL -->
                <div class="form-card">
                    <h2><i class="fas fa-webhook"></i> Webhook URL</h2>
                    
                    <div class="info-box">
                        <h4><i class="fas fa-info-circle"></i> Configure esta URL no painel do gateway</h4>
                        <p>Esta URL receberá notificações automáticas sobre pagamentos.</p>
                        
                        <div class="webhook-box">
                            <code id="webhook-url"><?php echo SITE_URL; ?>/webhook/<?php echo $config['gateway']; ?>.php</code>
                            <button class="copy-btn" onclick="copiarWebhook()">Copiar</button>
                        </div>
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
function selecionarGateway(gateway) {
    document.getElementById('gateway_selecionado').value = gateway;
    
    // Atualiza cards
    document.querySelectorAll('.gateway-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    // Mostra configurações específicas
    document.getElementById('config-mercadopago').style.display = gateway === 'mercadopago' ? 'block' : 'none';
    document.getElementById('config-pagbank').style.display = gateway === 'pagbank' ? 'block' : 'none';
    
    // Atualiza webhook URL
    const webhookSpan = document.getElementById('webhook-url');
    webhookSpan.innerHTML = '<?php echo SITE_URL; ?>/webhook/' + gateway + '.php';
}

function copiarWebhook() {
    const webhook = document.getElementById('webhook-url').innerText;
    navigator.clipboard.writeText(webhook);
    alert('✅ Webhook URL copiada!');
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