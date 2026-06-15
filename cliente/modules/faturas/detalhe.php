<?php
$page_title = 'Detalhe da Fatura';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once '../../includes/functions/pagamentos.php';

$cid = (int)$_SESSION['cliente_id'];
$fid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar fatura
$sql = "SELECT f.*, c.nome, c.email, c.cpf_cnpj, c.empresa,
               pc.nome_plano, pc.valor_plano, pc.valor_mensal,
               ct.numero_contrato
        FROM pagamento_faturas f
        JOIN clientes c ON c.id = f.cliente_id
        LEFT JOIN planos_contratados pc ON pc.id = f.plano_contratado_id
        LEFT JOIN contratos ct ON ct.id = f.contrato_id
        WHERE f.id = ? AND f.cliente_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $fid, $cid);
$stmt->execute();
$fatura = $stmt->get_result()->fetch_assoc();

if (!$fatura) {
    header("Location: index.php");
    exit;
}

$status_real = $fatura['status'] == 'pendente' && strtotime($fatura['data_vencimento']) < time() ? 'atrasada' : $fatura['status'];
$dias_vencimento = (strtotime($fatura['data_vencimento']) - time()) / 86400;
?>

<style>
.detalhe-container {
    max-width: 800px;
    margin: 0 auto;
}

.fatura-header {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.fatura-header.pendente {
    border-left: 4px solid #f97316;
}

.fatura-header.atrasada {
    border-left: 4px solid #ef4444;
}

.fatura-header.paga {
    border-left: 4px solid #10b981;
}

.fatura-numero-grande {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--tx);
    font-family: var(--mono);
    margin-bottom: 10px;
}

.fatura-status {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.fatura-status.pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.fatura-status.atrasada {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.fatura-status.paga {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
}

.info-label {
    font-size: 0.7rem;
    color: var(--tx3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.info-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--tx);
}

.info-value small {
    font-size: 0.8rem;
    color: var(--tx3);
    font-weight: normal;
}

.valor-destaque {
    font-size: 2rem;
    font-weight: 700;
    color: var(--ac);
    font-family: var(--mono);
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, var(--ac)10, transparent);
    border-radius: 16px;
}

.timeline-detalhe {
    margin: 30px 0;
    padding: 20px;
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
}

.timeline-item-detalhe {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid var(--bdr);
}

.timeline-item-detalhe:last-child {
    border-bottom: none;
}

.timeline-icon-detalhe {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.timeline-icon-detalhe.gerada {
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
}

.timeline-icon-detalhe.paga {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.timeline-icon-detalhe.atrasada {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.timeline-content-detalhe {
    flex: 1;
}

.timeline-title {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 4px;
}

.timeline-date {
    font-size: 0.8rem;
    color: var(--tx3);
}

.pix-box {
    background: var(--surf2);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.pix-qrcode {
    max-width: 250px;
    margin: 20px auto;
    padding: 15px;
    background: white;
    border-radius: 16px;
}

.pix-copia-cola {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 10px;
    padding: 15px;
    font-family: var(--mono);
    font-size: 0.8rem;
    word-break: break-all;
    margin: 15px 0;
    position: relative;
}

.pix-copia-cola button {
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

.btn-pagar {
    display: block;
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 30px;
    background: var(--ac);
    color: white;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    margin: 20px 0;
}

.btn-pagar:hover {
    background: var(--ac2);
    transform: translateY(-2px);
}

.btn-pagar.atrasada {
    background: #ef4444;
}

.btn-pagar.atrasada:hover {
    background: #dc2626;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .valor-destaque {
        font-size: 1.5rem;
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
            <div class="top-pg-ico"><i class="fas fa-file-invoice-dollar"></i></div>
            <span class="top-pg-title">Detalhe da Fatura</span>
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
                    <a href="/newsoftware/cliente/modules/perfil/index.php"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                    <a href="/newsoftware/cliente/modules/assinatura/index.php"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                    <hr>
                    <a href="/newsoftware/cliente/logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="detalhe-container">

            <!-- Header da Fatura -->
            <div class="fatura-header <?php echo $status_real; ?>">
                <div class="fatura-numero-grande">
                    <?php echo htmlspecialchars($fatura['numero_fatura']); ?>
                </div>
                
                <span class="fatura-status <?php echo $status_real; ?>">
                    <?php 
                    echo $status_real == 'paga' ? 'PAGA' : 
                         ($status_real == 'atrasada' ? 'ATRASADA' : 'PENDENTE'); 
                    ?>
                </span>

                <div style="margin-top: 15px;">
                    <div style="color: var(--tx3); font-size: 0.9rem; margin-bottom: 5px;">
                        <i class="fas fa-calendar"></i> Emissão: <?php echo date('d/m/Y', strtotime($fatura['created_at'])); ?>
                    </div>
                    <div style="color: var(--tx3); font-size: 0.9rem;">
                        <i class="fas fa-calendar-check"></i> Vencimento: <?php echo date('d/m/Y', strtotime($fatura['data_vencimento'])); ?>
                    </div>
                </div>
            </div>

            <!-- Valor -->
            <div class="valor-destaque">
                R$ <?php echo number_format($fatura['valor_total'], 2, ',', '.'); ?>
            </div>

            <!-- Informações Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Cliente</div>
                    <div class="info-value"><?php echo htmlspecialchars($fatura['nome']); ?></div>
                    <div style="font-size: 0.85rem; color: var(--tx2); margin-top: 5px;">
                        <?php echo htmlspecialchars($fatura['empresa'] ?: 'Pessoa Física'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">CPF/CNPJ</div>
                    <div class="info-value"><?php echo htmlspecialchars($fatura['cpf_cnpj'] ?: 'Não informado'); ?></div>
                </div>

                <?php if (!empty($fatura['nome_plano'])): ?>
                <div class="info-item">
                    <div class="info-label">Plano</div>
                    <div class="info-value"><?php echo htmlspecialchars($fatura['nome_plano']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($fatura['numero_contrato'])): ?>
                <div class="info-item">
                    <div class="info-label">Contrato</div>
                    <div class="info-value"><?php echo htmlspecialchars($fatura['numero_contrato']); ?></div>
                </div>
                <?php endif; ?>

                <div class="info-item">
                    <div class="info-label">Mês Referência</div>
                    <div class="info-value">
                        <?php echo isset($fatura['mes_referencia']) ? date('m/Y', strtotime($fatura['mes_referencia'])) : date('m/Y'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Tipo</div>
                    <div class="info-value">Mensalidade</div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="timeline-detalhe">
                <h4 style="margin-bottom: 20px;">Histórico</h4>
                
                <div class="timeline-item-detalhe">
                    <div class="timeline-icon-detalhe gerada">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="timeline-content-detalhe">
                        <div class="timeline-title">Fatura gerada</div>
                        <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($fatura['created_at'])); ?></div>
                    </div>
                </div>

                <?php if ($fatura['status'] == 'paga' && !empty($fatura['data_pagamento'])): ?>
                <div class="timeline-item-detalhe">
                    <div class="timeline-icon-detalhe paga">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="timeline-content-detalhe">
                        <div class="timeline-title">Pagamento confirmado</div>
                        <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($fatura['data_pagamento'])); ?></div>
                    </div>
                </div>
                <?php elseif ($status_real == 'atrasada'): ?>
                <div class="timeline-item-detalhe">
                    <div class="timeline-icon-detalhe atrasada">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="timeline-content-detalhe">
                        <div class="timeline-title">Vencimento ultrapassado</div>
                        <div class="timeline-date">Há <?php echo abs(floor($dias_vencimento)); ?> dia(s)</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Ações -->
            <?php if ($fatura['status'] == 'pendente'): ?>
                <?php if (isset($fatura['pix_qrcode']) && !empty($fatura['pix_qrcode'])): ?>
                <!-- PIX já gerado -->
                <div class="pix-box">
                    <h5>Pagamento via PIX</h5>
                    
                    <div class="pix-qrcode">
                        <img src="<?php echo $fatura['pix_qrcode']; ?>" alt="QR Code PIX" style="width: 100%;">
                    </div>
                    
                    <div class="pix-copia-cola">
                        <span id="pixCode"><?php echo $fatura['pix_copiaecola']; ?></span>
                        <button onclick="copiarPix()">Copiar</button>
                    </div>
                    
                    <p style="color: var(--tx3); font-size: 0.8rem;">
                        <i class="fas fa-clock"></i> O código PIX expira em 30 minutos
                    </p>
                </div>
                <?php else: ?>
                <!-- Botão para pagar -->
                <a href="pagar.php?id=<?php echo $fatura['id']; ?>" class="btn-pagar <?php echo $status_real == 'atrasada' ? 'atrasada' : ''; ?>">
                    <i class="fas fa-credit-card"></i> 
                    <?php echo $status_real == 'atrasada' ? 'Regularizar Pagamento' : 'Pagar Agora'; ?>
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($fatura['pdf_path'])): ?>
            <a href="<?php echo $fatura['pdf_path']; ?>" target="_blank" class="btn-fatura" style="display: block; text-align: center; margin-top: 15px;">
                <i class="fas fa-file-pdf"></i> Baixar PDF da Fatura
            </a>
            <?php endif; ?>

        </div>
    </div>
</main>

<script>
function copiarPix() {
    const pixCode = document.getElementById('pixCode').innerText;
    navigator.clipboard.writeText(pixCode).then(() => {
        alert('Código PIX copiado para a área de transferência!');
    });
}

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