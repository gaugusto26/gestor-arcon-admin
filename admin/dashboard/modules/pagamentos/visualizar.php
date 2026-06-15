<?php
$page_title = 'Detalhes da Transação';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';
require_once '../../../../config.php';
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca transação com dados do cliente
$stmt = $conn->prepare("
    SELECT t.*, 
           c.nome as cliente_nome, 
           c.email as cliente_email,
           c.empresa as cliente_empresa,
           c.cpf_cnpj,
           ct.numero_contrato,
           pc.nome_plano
    FROM pagamento_transacoes t
    LEFT JOIN clientes c ON t.cliente_id = c.id
    LEFT JOIN contratos ct ON t.contrato_id = ct.id
    LEFT JOIN planos_contratados pc ON t.plano_contratado_id = pc.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$transacao = $stmt->get_result()->fetch_assoc();

if(!$transacao) {
    header('Location: index.php');
    exit;
}

// Busca histórico de webhooks
$webhooks = $conn->query("
    SELECT * FROM pagamento_webhooks 
    WHERE transacao_id = '{$transacao['transacao_id']}' 
    ORDER BY data_recebimento DESC
");
?>

<style>
.view-container {
    max-width: 1000px;
    margin: 0 auto;
}

.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.status-bar {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.status-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.status-icon.aprovado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-icon.pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-icon.recusado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-info {
    flex: 1;
}

.status-info h2 {
    font-size: 1.5rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.status-info .transacao-id {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.status-badge-large {
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    display: inline-block;
}

.status-badge-large.aprovado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.status-badge-large.pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid #f97316;
}

.status-badge-large.recusado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.info-card h3 {
    font-size: 1.1rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card h3 i {
    color: #4361ee;
}

.info-row {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-label {
    width: 140px;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.info-value {
    flex: 1;
    color: var(--text-primary);
    font-weight: 500;
}

.pix-box {
    background: #f8faff;
    border: 2px dashed var(--border);
    border-radius: 16px;
    padding: 30px;
    text-align: center;
    margin-top: 20px;
}

.pix-qrcode {
    max-width: 250px;
    margin: 0 auto 20px;
    padding: 15px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.pix-copiaecola {
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    font-family: monospace;
    word-break: break-all;
    margin: 20px 0;
    border: 1px solid var(--border);
}

.copy-btn {
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.copy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(67,97,238,0.2);
}

.webhook-item {
    padding: 15px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.webhook-icon {
    width: 40px;
    height: 40px;
    background: #f8faff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
}

.webhook-info {
    flex: 1;
}

.webhook-evento {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.webhook-data {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.webhook-status {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.webhook-processado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.webhook-pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.btn {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #4361ee 0%, #667eea 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(67,97,238,0.2);
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
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-credit-card" style="color: #4361ee; margin-right: 10px;"></i>
            Detalhes da Transação
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="view-container">
            <div class="view-header">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <?php if($transacao['status'] == 'pendente' && $transacao['forma_pagamento'] == 'pix'): ?>
                <button class="btn btn-primary" onclick="verPIX(<?php echo $transacao['id']; ?>)">
                    <i class="fas fa-qrcode"></i> Ver PIX
                </button>
                <?php endif; ?>
            </div>

            <!-- Status Bar -->
            <div class="status-bar">
                <div class="status-icon <?php echo $transacao['status']; ?>">
                    <i class="fas fa-<?php 
                        echo $transacao['status'] == 'aprovado' ? 'check-circle' : 
                            ($transacao['status'] == 'pendente' ? 'clock' : 
                            ($transacao['status'] == 'recusado' ? 'times-circle' : 'ban')); 
                    ?>"></i>
                </div>
                <div class="status-info">
                    <h2>Transação #<?php echo $transacao['id']; ?></h2>
                    <div class="transacao-id">
                        <i class="fas fa-hashtag"></i> <?php echo $transacao['transacao_id']; ?>
                        <?php if($transacao['gateway']): ?>
                        <span class="gateway-tag" style="margin-left: 10px;">
                            <i class="fas fa-<?php echo $transacao['gateway'] == 'mercadopago' ? 'dollar-sign' : 'wallet'; ?>"></i>
                            <?php echo ucfirst($transacao['gateway']); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <span class="status-badge-large <?php echo $transacao['status']; ?>">
                        <?php echo strtoupper($transacao['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Informações da Transação -->
            <div class="info-grid">
                <!-- Dados do Cliente -->
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Dados do Cliente</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Nome</span>
                        <span class="info-value"><?php echo $transacao['cliente_nome']; ?></span>
                    </div>
                    
                    <?php if($transacao['cliente_empresa']): ?>
                    <div class="info-row">
                        <span class="info-label">Empresa</span>
                        <span class="info-value"><?php echo $transacao['cliente_empresa']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <span class="info-label">E-mail</span>
                        <span class="info-value"><?php echo $transacao['cliente_email']; ?></span>
                    </div>
                    
                    <?php if($transacao['cpf_cnpj']): ?>
                    <div class="info-row">
                        <span class="info-label">CPF/CNPJ</span>
                        <span class="info-value"><?php echo $transacao['cpf_cnpj']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['numero_contrato']): ?>
                    <div class="info-row">
                        <span class="info-label">Contrato</span>
                        <span class="info-value"><?php echo $transacao['numero_contrato']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Detalhes do Pagamento -->
                <div class="info-card">
                    <h3><i class="fas fa-dollar-sign"></i> Detalhes do Pagamento</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Valor Original</span>
                        <span class="info-value">R$ <?php echo number_format($transacao['valor_original'] ?? $transacao['valor'], 2, ',', '.'); ?></span>
                    </div>
                    
                    <?php if($transacao['desconto'] > 0): ?>
                    <div class="info-row">
                        <span class="info-label">Desconto</span>
                        <span class="info-value">- R$ <?php echo number_format($transacao['desconto'], 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['juros'] > 0): ?>
                    <div class="info-row">
                        <span class="info-label">Juros</span>
                        <span class="info-value">+ R$ <?php echo number_format($transacao['juros'], 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['multa'] > 0): ?>
                    <div class="info-row">
                        <span class="info-label">Multa</span>
                        <span class="info-value">+ R$ <?php echo number_format($transacao['multa'], 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row" style="border-top: 2px solid var(--border); padding-top: 15px; margin-top: 5px;">
                        <span class="info-label"><strong>Valor Total</strong></span>
                        <span class="info-value"><strong>R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?></strong></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Forma</span>
                        <span class="info-value">
                            <?php 
                            $forma = str_replace('_', ' ', $transacao['forma_pagamento']);
                            echo ucwords($forma);
                            if($transacao['parcelas'] > 1) echo " em {$transacao['parcelas']}x";
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Datas -->
                <div class="info-card">
                    <h3><i class="fas fa-clock"></i> Datas</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Criação</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($transacao['created_at'])); ?></span>
                    </div>
                    
                    <?php if($transacao['data_aprovacao']): ?>
                    <div class="info-row">
                        <span class="info-label">Aprovação</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($transacao['data_aprovacao'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['data_pagamento']): ?>
                    <div class="info-row">
                        <span class="info-label">Pagamento</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($transacao['data_pagamento'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['pix_expiracao']): ?>
                    <div class="info-row">
                        <span class="info-label">Expiração PIX</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($transacao['pix_expiracao'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['data_vencimento']): ?>
                    <div class="info-row">
                        <span class="info-label">Vencimento</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($transacao['data_vencimento'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Informações Adicionais -->
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Informações Adicionais</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Tipo</span>
                        <span class="info-value"><?php echo ucfirst($transacao['tipo']); ?></span>
                    </div>
                    
                    <?php if($transacao['nome_plano']): ?>
                    <div class="info-row">
                        <span class="info-label">Plano</span>
                        <span class="info-value"><?php echo $transacao['nome_plano']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <span class="info-label">IP</span>
                        <span class="info-value"><?php echo $transacao['ip'] ?: '—'; ?></span>
                    </div>
                    
                    <?php if($transacao['status_detalhe']): ?>
                    <div class="info-row">
                        <span class="info-label">Detalhe</span>
                        <span class="info-value"><?php echo $transacao['status_detalhe']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transacao['observacoes']): ?>
                    <div class="info-row">
                        <span class="info-label">Observações</span>
                        <span class="info-value"><?php echo nl2br($transacao['observacoes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PIX Info -->
            <?php if($transacao['forma_pagamento'] == 'pix' && $transacao['pix_qrcode']): ?>
            <div class="info-card">
                <h3><i class="fas fa-qrcode"></i> Pagamento PIX</h3>
                
                <div class="pix-box">
                    <div class="pix-qrcode">
                        <img src="<?php echo $transacao['pix_qrcode']; ?>" style="max-width: 100%;">
                    </div>
                    
                    <div class="pix-copiaecola" id="pixCode">
                        <?php echo $transacao['pix_copiaecola']; ?>
                    </div>
                    
                    <button class="copy-btn" onclick="copiarPIX()">
                        <i class="fas fa-copy"></i> Copiar código PIX
                    </button>
                    
                    <?php if($transacao['pix_expiracao']): ?>
                    <p style="margin-top: 15px; color: #f97316;">
                        <i class="fas fa-clock"></i> Expira em: <?php echo date('d/m/Y H:i', strtotime($transacao['pix_expiracao'])); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if($transacao['status'] == 'aprovado'): ?>
                    <p style="margin-top: 15px; color: #10b981;">
                        <i class="fas fa-check-circle"></i> PIX confirmado em <?php echo date('d/m/Y H:i', strtotime($transacao['data_aprovacao'])); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Webhooks -->
            <?php if($webhooks->num_rows > 0): ?>
            <div class="info-card">
                <h3><i class="fas fa-webhook"></i> Webhooks Recebidos</h3>
                
                <?php while($webhook = $webhooks->fetch_assoc()): ?>
                <div class="webhook-item">
                    <div class="webhook-icon">
                        <i class="fas fa-cloud-download-alt"></i>
                    </div>
                    <div class="webhook-info">
                        <div class="webhook-evento"><?php echo $webhook['evento']; ?></div>
                        <div class="webhook-data"><?php echo date('d/m/Y H:i:s', strtotime($webhook['data_recebimento'])); ?></div>
                    </div>
                    <div class="webhook-status <?php echo $webhook['processado'] ? 'webhook-processado' : 'webhook-pendente'; ?>">
                        <?php echo $webhook['processado'] ? 'Processado' : 'Pendente'; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copiarPIX() {
    const pixCode = document.getElementById('pixCode').innerText;
    navigator.clipboard.writeText(pixCode);
    alert('✅ Código PIX copiado!');
}

function verPIX(id) {
    window.location.href = 'get_pix.php?id=' + id;
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