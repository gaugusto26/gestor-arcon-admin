<?php
$page_title = 'Pagar Fatura';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once '../../includes/functions/pagamentos.php';

$cid = (int)$_SESSION['cliente_id'];
$fid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar fatura
$sql = "SELECT f.* FROM pagamento_faturas f 
        WHERE f.id = ? AND f.cliente_id = ? AND f.status = 'pendente'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $fid, $cid);
$stmt->execute();
$fatura = $stmt->get_result()->fetch_assoc();

if (!$fatura) {
    header("Location: index.php");
    exit;
}

$atrasada = strtotime($fatura['data_vencimento']) < time();
?>

<style>
.pagamento-container {
    max-width: 600px;
    margin: 0 auto;
}

.resumo-fatura {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
    text-align: center;
}

.resumo-fatura.pendente {
    border-left: 4px solid #f97316;
}

.resumo-fatura.atrasada {
    border-left: 4px solid #ef4444;
}

.resumo-label {
    font-size: 0.8rem;
    color: var(--tx3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.resumo-valor {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--ac);
    font-family: var(--mono);
    margin-bottom: 10px;
}

.resumo-vencimento {
    font-size: 0.9rem;
    color: var(--tx2);
}

.metodos-pagamento {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 25px;
}

.metodo-card {
    background: var(--surf);
    border: 2px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.metodo-card:hover {
    border-color: var(--ac);
    transform: translateY(-2px);
}

.metodo-card.active {
    border-color: var(--ac);
    background: var(--ac)10;
}

.metodo-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: var(--ac)15;
    color: var(--ac);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 12px;
}

.metodo-nome {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 4px;
}

.metodo-desc {
    font-size: 0.75rem;
    color: var(--tx3);
}

.pix-container {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-top: 20px;
}

.pix-qrcode-container {
    background: white;
    padding: 20px;
    border-radius: 16px;
    display: inline-block;
    margin: 20px auto;
}

.pix-copia-cola {
    background: var(--surf2);
    border: 1px solid var(--bdr);
    border-radius: 10px;
    padding: 15px;
    font-family: var(--mono);
    font-size: 0.8rem;
    word-break: break-all;
    margin: 15px 0;
    position: relative;
}

.btn-copiar {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--ac);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
}

.cartao-container {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-top: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 0.8rem;
    color: var(--tx3);
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid var(--bdr);
    background: var(--surf2);
    color: var(--tx);
    font-size: 0.95rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--ac);
    box-shadow: 0 0 0 3px var(--ac)20;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.btn-processar {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 30px;
    background: var(--ac);
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.btn-processar:hover {
    background: var(--ac2);
    transform: translateY(-2px);
}

.btn-processar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-processar.atrasada {
    background: #ef4444;
}

.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .metodos-pagamento {
        grid-template-columns: 1fr;
    }
    
    .row {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="top-pg-ico"><i class="fas fa-credit-card"></i></div>
            <span class="top-pg-title">Pagar Fatura</span>
        </div>
        <div class="top-right">
            <div class="theme-tog" id="themeTog" title="Alternar tema">
                <div class="theme-thumb">
                    <i class="fas <?php echo $tema_atual === 'dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
                </div>
            </div>

            <div class="user-btn" id="userBtn">
                <div class="sb-av"><?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?></div>
                <span class="user-btn-n"><?php echo htmlspecialchars(explode(' ', $cliente['nome'])[0]); ?></span>
                <i class="fas fa-chevron-down user-chevron"></i>
                <div class="ddrop" id="userDrop">
                    <a href="/cliente/modules/perfil/index.php"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                    <a href="/cliente/modules/assinatura/index.php"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                    <hr>
                    <a href="/cliente/logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="pagamento-container">

            <!-- Resumo da Fatura -->
            <div class="resumo-fatura <?php echo $atrasada ? 'atrasada' : 'pendente'; ?>">
                <div class="resumo-label">Fatura <?php echo htmlspecialchars($fatura['numero_fatura']); ?></div>
                <div class="resumo-valor">R$ <?php echo number_format($fatura['valor_total'], 2, ',', '.'); ?></div>
                <div class="resumo-vencimento">
                    <i class="fas fa-calendar"></i> 
                    Vencimento: <?php echo date('d/m/Y', strtotime($fatura['data_vencimento'])); ?>
                    <?php if ($atrasada): ?>
                    <span style="color: #ef4444; margin-left: 8px;">
                        <i class="fas fa-exclamation-circle"></i> Atrasada
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Métodos de Pagamento -->
            <div class="metodos-pagamento">
                <div class="metodo-card active" id="metodoPix" onclick="selecionarMetodo('pix')">
                    <div class="metodo-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="metodo-nome">PIX</div>
                    <div class="metodo-desc">Pagamento instantâneo</div>
                </div>
                
                <div class="metodo-card" id="metodoCartao" onclick="selecionarMetodo('cartao')">
                    <div class="metodo-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="metodo-nome">Cartão de Crédito</div>
                    <div class="metodo-desc">Parcelamos em até 3x</div>
                </div>
            </div>

            <!-- Container PIX -->
            <div id="pixContainer" class="pix-container">
                <div style="text-align: center;">
                    <button class="btn-processar" onclick="gerarPIX()" id="btnGerarPix">
                        <i class="fas fa-qrcode"></i> Gerar QR Code PIX
                    </button>
                </div>

                <div id="pixGerado" style="display: none;">
                    <div style="text-align: center; margin: 20px 0;">
                        <h4>Escaneie o QR Code</h4>
                        <div class="pix-qrcode-container">
                            <img id="pixQrCode" src="" alt="QR Code PIX" style="width: 200px;">
                        </div>
                    </div>

                    <div class="pix-copia-cola">
                        <span id="pixCode"></span>
                        <button class="btn-copiar" onclick="copiarPIX()">Copiar</button>
                    </div>

                    <p style="color: var(--tx3); font-size: 0.8rem; text-align: center;">
                        <i class="fas fa-clock"></i> O PIX expira em 30 minutos
                    </p>

                    <div id="pixStatus" style="text-align: center; margin-top: 20px;">
                        <span class="badge pendente">Aguardando pagamento...</span>
                    </div>
                </div>
            </div>

            <!-- Container Cartão -->
            <div id="cartaoContainer" class="cartao-container" style="display: none;">
                <form id="formCartao" onsubmit="processarCartao(event)">
                    <div class="form-group">
                        <label class="form-label">Número do Cartão</label>
                        <input type="text" class="form-control" id="cardNumber" 
                               placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="form-label">Validade</label>
                            <input type="text" class="form-control" id="cardExpiry" 
                                   placeholder="MM/AA" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cardCVV" 
                                   placeholder="123" maxlength="4" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nome no Cartão</label>
                        <input type="text" class="form-control" id="cardName" 
                               placeholder="Como está gravado no cartão" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">CPF do Titular</label>
                        <input type="text" class="form-control" id="cardCPF" 
                               placeholder="000.000.000-00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Número de Parcelas</label>
                        <select class="form-control" id="cardParcelas">
                            <option value="1">1x de R$ <?php echo number_format($fatura['valor_total'], 2, ',', '.'); ?></option>
                            <?php if ($fatura['valor_total'] >= 50): ?>
                            <option value="2">2x de R$ <?php echo number_format($fatura['valor_total']/2, 2, ',', '.'); ?></option>
                            <?php endif; ?>
                            <?php if ($fatura['valor_total'] >= 100): ?>
                            <option value="3">3x de R$ <?php echo number_format($fatura['valor_total']/3, 2, ',', '.'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-processar <?php echo $atrasada ? 'atrasada' : ''; ?>" id="btnProcessar">
                        <i class="fas fa-lock"></i> Pagar R$ <?php echo number_format($fatura['valor_total'], 2, ',', '.'); ?>
                    </button>
                </form>
            </div>

        </div>
    </div>
</main>

<!-- Modal de Sucesso -->
<div class="modal" id="modalSucesso" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--surf); padding: 30px; border-radius: 20px; max-width: 400px; width: 90%; text-align: center;">
        <i class="fas fa-check-circle" style="font-size: 4rem; color: #10b981; margin-bottom: 20px;"></i>
        <h3 style="margin-bottom: 15px;">Pagamento Confirmado!</h3>
        <p style="color: var(--tx2); margin-bottom: 20px;" id="modalMensagem">Sua fatura foi paga com sucesso.</p>
        <button class="btn-processar" onclick="window.location.href='index.php'" style="width: auto; padding: 12px 30px;">OK</button>
    </div>
</div>

<script>
let metodoAtual = 'pix';
let checkInterval;

function selecionarMetodo(metodo) {
    metodoAtual = metodo;
    
    // Atualizar UI
    document.getElementById('metodoPix').classList.toggle('active', metodo === 'pix');
    document.getElementById('metodoCartao').classList.toggle('active', metodo === 'cartao');
    
    // Mostrar/esconder containers
    document.getElementById('pixContainer').style.display = metodo === 'pix' ? 'block' : 'none';
    document.getElementById('cartaoContainer').style.display = metodo === 'cartao' ? 'block' : 'none';
    
    // Limpar intervalo se existir
    if (checkInterval) {
        clearInterval(checkInterval);
        checkInterval = null;
    }
}

function gerarPIX() {
    const btn = document.getElementById('btnGerarPix');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Gerando...';
    
    fetch('ajax/gerar_pix.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            fatura_id: <?php echo $fid; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('pixQrCode').src = data.pix_qrcode;
            document.getElementById('pixCode').innerText = data.pix_copiaecola;
            document.getElementById('pixGerado').style.display = 'block';
            btn.style.display = 'none';
            
            // Iniciar verificação de status
            verificarStatusPIX(data.transacao_id);
        } else {
            alert('Erro ao gerar PIX: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-qrcode"></i> Gerar QR Code PIX';
        }
    });
}

function copiarPIX() {
    const pixCode = document.getElementById('pixCode').innerText;
    navigator.clipboard.writeText(pixCode).then(() => {
        alert('Código PIX copiado!');
    });
}

function verificarStatusPIX(transacaoId) {
    checkInterval = setInterval(() => {
        fetch('ajax/verificar_pix.php?transacao=' + transacaoId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'aprovado') {
                    clearInterval(checkInterval);
                    document.getElementById('pixStatus').innerHTML = 
                        '<span class="badge paga">Pagamento confirmado!</span>';
                    
                    document.getElementById('modalMensagem').innerText = 
                        'Pagamento PIX confirmado com sucesso!';
                    document.getElementById('modalSucesso').style.display = 'block';
                }
            });
    }, 3000);
}

function processarCartao(event) {
    event.preventDefault();
    
    const btn = document.getElementById('btnProcessar');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Processando...';
    
    const dados = {
        fatura_id: <?php echo $fid; ?>,
        card_number: document.getElementById('cardNumber').value.replace(/\s/g, ''),
        card_expiry: document.getElementById('cardExpiry').value,
        card_cvv: document.getElementById('cardCVV').value,
        card_name: document.getElementById('cardName').value,
        card_cpf: document.getElementById('cardCPF').value.replace(/[^\d]/g, ''),
        parcelas: document.getElementById('cardParcelas').value
    };
    
    fetch('ajax/processar_cartao.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('modalMensagem').innerText = 
                'Pagamento aprovado! ID da transação: ' + data.transacao_id;
            document.getElementById('modalSucesso').style.display = 'block';
        } else {
            alert('Erro no pagamento: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Pagar';
        }
    });
}

// Formatação de cartão
document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value;
});

document.getElementById('cardExpiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0,2) + '/' + value.substring(2,4);
    }
    e.target.value = value;
});

document.getElementById('cardCPF').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    e.target.value = value;
});

(function() {
    const userBtn = document.getElementById('userBtn');
    const userDrop = document.getElementById('userDrop');
    if (userBtn && userDrop) {
        userBtn.addEventListener('click', e => {
            e.stopPropagation();
            userDrop.classList.toggle('open');
            userBtn.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            userDrop.classList.remove('open');
            userBtn.classList.remove('open');
        });
    }

    const themeTog = document.getElementById('themeTog');
    if (themeTog) {
        themeTog.addEventListener('click', () => {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const novo = isDark ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = novo === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            localStorage.setItem('theme', novo);
        });
    }
})();
</script>

<?php require_once '../../includes/footer.php'; ?>