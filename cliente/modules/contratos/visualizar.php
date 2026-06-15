<?php
$page_title = 'Visualizar Contrato';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /cliente/modules/contratos/index.php');
    exit;
}

$cid = (int)$_SESSION['cliente_id'];
$contrato_id = (int)$_GET['id'];

// Busca contrato com dados completos do cliente
$stmt = $conn->prepare("
    SELECT c.*, 
           cl.nome as cliente_nome,
           cl.email as cliente_email,
           cl.telefone as cliente_telefone,
           cl.cpf_cnpj as cliente_documento,
           cl.endereco as cliente_endereco,
           cl.cidade as cliente_cidade,
           cl.estado as cliente_estado,
           cl.cep as cliente_cep,
           cl.empresa as cliente_empresa,
           pc.nome_plano,
           pc.valor_mensal as valor_mensal_plano,
           (SELECT id FROM cliente_assinaturas WHERE cliente_id = ? LIMIT 1) as tem_assinatura,
           (SELECT id FROM cliente_assinaturas_contratos WHERE contrato_id = c.id) as ja_assinado,
           (SELECT data_assinatura FROM cliente_assinaturas_contratos WHERE contrato_id = c.id) as data_assinatura_real
    FROM contratos c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
    WHERE c.id = ? AND c.cliente_id = ?
");
$stmt->bind_param("iii", $cid, $contrato_id, $cid);
$stmt->execute();
$contrato = $stmt->get_result()->fetch_assoc();

if (!$contrato) {
    header('Location: /cliente/modules/contratos/index.php');
    exit;
}

// Busca assinatura do cliente
$assinatura = $conn->query("SELECT * FROM cliente_assinaturas WHERE cliente_id = $cid ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// Verifica se já foi assinado
$ja_assinado = $conn->query("SELECT id FROM cliente_assinaturas_contratos WHERE contrato_id = $contrato_id")->num_rows > 0;

// Formata endereço completo
$endereco_completo = trim(
    ($contrato['cliente_endereco'] ?? '') . 
    ($contrato['cliente_cidade'] ? ', ' . $contrato['cliente_cidade'] : '') . 
    ($contrato['cliente_estado'] ? ' - ' . $contrato['cliente_estado'] : '') . 
    ($contrato['cliente_cep'] ? ' - CEP ' . $contrato['cliente_cep'] : '')
);
if (empty($endereco_completo)) $endereco_completo = '[ENDEREÇO NÃO CADASTRADO]';

// Processa o conteúdo do contrato substituindo as variáveis
$conteudo_processado = $contrato['conteudo'];

// Substitui dados do cliente
$conteudo_processado = str_replace(
    ['{nome_cliente}', '{cliente_nome}', '[NOME DO CLIENTE]'],
    $contrato['cliente_nome'] ?? '[NOME DO CLIENTE]',
    $conteudo_processado
);

$conteudo_processado = str_replace(
    ['{documento_cliente}', '{cliente_documento}', '[DOCUMENTO]'],
    $contrato['cliente_documento'] ?? '[DOCUMENTO NÃO CADASTRADO]',
    $conteudo_processado
);

$conteudo_processado = str_replace(
    ['{endereco_cliente}', '{cliente_endereco}', '[ENDEREÇO]'],
    $endereco_completo,
    $conteudo_processado
);

// Valores do contrato
$valor_plano = number_format($contrato['valor_total'] ?? 0, 2, ',', '.');
$valor_mensal = number_format($contrato['valor_mensal'] ?? $contrato['valor_mensal_plano'] ?? 0, 2, ',', '.');
$multa = number_format($contrato['multa_cancelamento'] ?? 0, 2, ',', '.');
$percentual_multa = $contrato['percentual_multa'] ?? 20;
$fidelidade = $contrato['prazo_fidelidade'] ?? 12;
$numero_parcelas = $contrato['numero_parcelas'] ?? 1;
$dia_vencimento = $contrato['dia_vencimento'] ?? 10;

// Datas formatadas
$data_primeira_parcela = '';
if (!empty($contrato['data_primeira_parcela']) && $contrato['data_primeira_parcela'] != '0000-00-00') {
    $data_primeira_parcela = date('d/m/Y', strtotime($contrato['data_primeira_parcela']));
} else {
    $data_primeira_parcela = '30 dias após assinatura';
}

$data_primeira_mensalidade = '';
if (!empty($contrato['data_primeira_mensalidade']) && $contrato['data_primeira_mensalidade'] != '0000-00-00') {
    $data_primeira_mensalidade = date('d/m/Y', strtotime($contrato['data_primeira_mensalidade']));
} else {
    $data_primeira_mensalidade = 'após entrega do projeto';
}

// Substitui as variáveis no conteúdo
$conteudo_processado = str_replace('{valor_plano}', $valor_plano, $conteudo_processado);
$conteudo_processado = str_replace('{valor_plano_extenso}', $valor_plano . ' reais', $conteudo_processado);
$conteudo_processado = str_replace('{valor_mensal}', $valor_mensal, $conteudo_processado);
$conteudo_processado = str_replace('{valor_mensal_extenso}', $valor_mensal . ' reais', $conteudo_processado);
$conteudo_processado = str_replace('{multa_cancelamento}', $multa, $conteudo_processado);
$conteudo_processado = str_replace('{multa_extenso}', $multa . ' reais', $conteudo_processado);
$conteudo_processado = str_replace('{percentual_multa}', $percentual_multa, $conteudo_processado);
$conteudo_processado = str_replace('{fidelidade}', $fidelidade, $conteudo_processado);
$conteudo_processado = str_replace('{prazo_desenvolvimento}', $contrato['prazo_desenvolvimento'] ?? 30, $conteudo_processado);
$conteudo_processado = str_replace('{numero_parcelas}', $numero_parcelas, $conteudo_processado);
$conteudo_processado = str_replace('{data_primeira_parcela}', $data_primeira_parcela, $conteudo_processado);
$conteudo_processado = str_replace('{dia_vencimento}', $dia_vencimento, $conteudo_processado);
$conteudo_processado = str_replace('{data_primeira_mensalidade}', $data_primeira_mensalidade, $conteudo_processado);

// Remove R$ duplicados (caso existam)
$conteudo_processado = preg_replace('/R\$ R\$ /', 'R$ ', $conteudo_processado);
$conteudo_processado = preg_replace('/R\$R\$/', 'R$', $conteudo_processado);

// Adiciona as linhas de assinatura se não existirem no conteúdo
if (strpos($conteudo_processado, 'assinaturas') === false) {
    $assinatura_html = '
    <div style="margin-top: 60px;">
        <div style="display: flex; justify-content: space-between; margin-top: 80px;">
            <div style="text-align: center; width: 45%;">
                <div style="border-top: 2px solid #000; width: 100%; margin: 30px 0 10px;"></div>
                <p style="font-weight: bold; margin: 0;">____________________________</p>
                <p style="margin: 5px 0 0;"><strong>CONTRATANTE</strong></p>
                <p style="font-size: 0.85rem; color: #666;">' . htmlspecialchars($contrato['cliente_nome'] ?? '') . '</p>
            </div>
            <div style="text-align: center; width: 45%;">
                <div style="border-top: 2px solid #000; width: 100%; margin: 30px 0 10px;"></div>
                <p style="font-weight: bold; margin: 0;">____________________________</p>
                <p style="margin: 5px 0 0;"><strong>CONTRATADA</strong></p>
                <p style="font-size: 0.85rem; color: #666;">GESTOR ARCON ADMIN</p>
            </div>
        </div>
        <p style="text-align: center; margin-top: 50px; font-style: italic; color: #666;">
            © - GESTOR ARCON ADMIN, ' . date('d') . ' de ' . 
            ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'][date('n')-1] . 
            ' de ' . date('Y') . '
        </p>
    </div>';
    
    // Adiciona antes do fechamento do body
    $conteudo_processado = str_replace('</body>', $assinatura_html . '</body>', $conteudo_processado);
}

$status_class = match($contrato['status']) {
    'rascunho' => 'status-rascunho',
    'enviado'  => 'status-enviado',
    'assinado' => 'status-assinado',
    'cancelado'=> 'status-cancelado',
    default    => 'status-rascunho'
};

$status_label = match($contrato['status']) {
    'rascunho' => 'Rascunho',
    'enviado'  => 'Aguardando assinatura',
    'assinado' => 'Assinado',
    'cancelado'=> 'Cancelado',
    default    => ucfirst($contrato['status'])
};
?>

<style>
/* ===== ESTILOS MODERNOS E MINIMALISTAS ===== */
.contrato-view {
    max-width: 900px;
    margin: 0 auto;
}

/* Header */
.contrato-header {
    margin-bottom: 24px;
}

.contrato-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 8px;
}

.contrato-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    color: var(--tx3);
    font-size: 0.9rem;
}

.contrato-meta i {
    margin-right: 4px;
    color: var(--ac);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge i {
    font-size: 0.8rem;
}

.status-badge.rascunho {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-badge.enviado {
    background: rgba(67, 97, 238, 0.1);
    color: #4361ee;
}

.status-badge.assinado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-badge.cancelado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Info Cards - Minimalistas */
.info-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin: 24px 0;
}

.info-card-mini {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 12px;
    padding: 16px;
}

.info-card-mini .label {
    font-size: 0.75rem;
    color: var(--tx3);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.info-card-mini .value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--tx);
    font-family: var(--mono);
}

.info-card-mini .value small {
    font-size: 0.8rem;
    font-weight: 400;
    color: var(--tx3);
    margin-left: 4px;
}

/* Datas Grid */
.datas-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin: 20px 0;
}

.data-card {
    background: var(--surf2);
    border: 1px solid var(--bdr);
    border-radius: 10px;
    padding: 12px 15px;
}

.data-label {
    font-size: 0.7rem;
    color: var(--tx3);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.data-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--tx);
}

/* Content Box */
.contrato-content-box {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 32px;
    margin: 24px 0;
    font-family: 'Times New Roman', serif;
    line-height: 1.8;
    color: var(--tx2);
}

.contrato-content-box h1 {
    font-size: 1.8rem;
    text-align: center;
    margin-bottom: 32px;
    color: var(--tx);
    font-weight: 600;
}

.contrato-content-box h2 {
    font-size: 1.3rem;
    margin: 32px 0 16px;
    color: var(--tx);
    font-weight: 500;
    border-bottom: 1px solid var(--bdr);
    padding-bottom: 8px;
}

.contrato-content-box p {
    margin-bottom: 16px;
    text-align: justify;
}

.contrato-content-box strong {
    color: var(--ac);
}

.contrato-content-box ul, 
.contrato-content-box ol {
    margin: 16px 0 16px 24px;
}

.contrato-content-box li {
    margin-bottom: 8px;
}

/* Assinaturas */
.assinaturas-container {
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px dashed var(--bdr2);
}

.assinaturas-grid {
    display: flex;
    justify-content: space-between;
    gap: 40px;
    margin: 40px 0;
}

.assinatura-col {
    flex: 1;
    text-align: center;
}

.assinatura-linha {
    border-top: 2px solid var(--tx);
    margin: 30px 0 15px;
    width: 100%;
    position: relative;
}

.assinatura-placeholder {
    color: var(--tx3);
    font-size: 0.8rem;
    margin-top: -25px;
    margin-bottom: 15px;
    font-style: italic;
}

.assinatura-nome {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 5px;
}

.assinatura-data {
    font-size: 0.75rem;
    color: var(--tx3);
}

.assinatura-badge {
    display: inline-block;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 8px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--bdr2);
    color: var(--tx2);
}

.btn-outline:hover {
    background: var(--surf2);
    border-color: var(--ac);
    color: var(--ac);
}

.btn-primary {
    background: var(--ac);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0d9488;
    transform: translateY(-1px);
}

/* Observações */
.observacoes-box {
    background: rgba(102, 126, 234, 0.03);
    border: 1px solid var(--bdr);
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.observacoes-box h4 {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.observacoes-box h4 i {
    color: var(--ac);
}

.observacoes-box p {
    color: var(--tx2);
    font-size: 0.9rem;
    line-height: 1.6;
    white-space: pre-line;
}

/* Responsive */
@media (max-width: 768px) {
    .info-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .contrato-content-box {
        padding: 20px;
    }
    
    .datas-grid {
        grid-template-columns: 1fr;
    }
    
    .assinaturas-grid {
        flex-direction: column;
        gap: 30px;
    }
}

@media (max-width: 480px) {
    .info-cards {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-file-contract"></i></div>
            <span class="top-pg-title">Contrato #<?php echo htmlspecialchars($contrato['numero_contrato']); ?></span>
        </div>
        <div class="top-right">
            <div class="ico-btn" id="notifBtn" title="Faturas pendentes">
                <i class="fas fa-bell"></i>
                <?php if ($faturas_pendentes['total'] > 0): ?>
                <span class="ico-dot"></span>
                <?php endif; ?>
            </div>

            <div class="theme-tog" id="themeTog" title="Alternar tema">
                <div class="theme-thumb">
                    <i class="fas <?php echo $tema_atual==='dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
                </div>
            </div>

            <div class="user-btn" id="userBtn">
                <div class="sb-av"><?php echo strtoupper(substr($cliente['nome'],0,1)); ?></div>
                <span class="user-btn-n"><?php echo htmlspecialchars(explode(' ',$cliente['nome'])[0]); ?></span>
                <i class="fas fa-chevron-down user-chevron"></i>
                <div class="ddrop" id="userDrop">
                    <a href="/cliente/modules/perfil/index.php"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                    <a href="/cliente/modules/assinatura/index.php"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                    <hr>
                    <a href="/cliente/logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </div><!-- /topbar -->

    <div class="content">
        <div class="contrato-view">

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="/cliente/modules/contratos/index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <?php if ($contrato['status'] == 'enviado' && !$ja_assinado && $assinatura): ?>
                <a href="/cliente/modules/contratos/assinar.php?id=<?php echo $contrato['id']; ?>" class="btn btn-success">
                    <i class="fas fa-pen-fancy"></i> Assinar Contrato
                </a>
                <?php endif; ?>
                
                <a href="javascript:window.print()" class="btn btn-outline">
                    <i class="fas fa-print"></i> Imprimir
                </a>
                
                <?php if ($ja_assinado): ?>
                <a href="/cliente/modules/contratos/pdf.php?id=<?php echo $contrato['id']; ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> Baixar PDF
                </a>
                <?php endif; ?>
            </div>

            <!-- Header -->
            <div class="contrato-header">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <h1 class="contrato-title"><?php echo htmlspecialchars($contrato['titulo']); ?></h1>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <i class="fas fa-<?php 
                            echo $contrato['status'] == 'assinado' ? 'check-circle' : 
                                ($contrato['status'] == 'enviado' ? 'paper-plane' : 
                                ($contrato['status'] == 'cancelado' ? 'ban' : 'clock')); 
                        ?>"></i>
                        <?php echo $status_label; ?>
                    </span>
                </div>
                
                <div class="contrato-meta">
                    <span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($contrato['numero_contrato']); ?></span>
                    <span><i class="fas fa-calendar"></i> Criado em <?php echo date('d/m/Y', strtotime($contrato['created_at'])); ?></span>
                    <?php if ($contrato['versao']): ?>
                    <span><i class="fas fa-code-branch"></i> Versão <?php echo $contrato['versao']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Plano Contratado -->
<?php if (!empty($contrato['nome_plano'])): ?>
<div style="background: var(--surf); border: 1px solid var(--bdr); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(102,126,234,0.1); display: flex; align-items: center; justify-content: center; color: var(--ac); flex-shrink: 0;">
        <i class="fas fa-crown"></i>
    </div>
    <div>
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--tx3); letter-spacing: 0.3px; margin-bottom: 3px;">Plano Contratado</div>
        <div style="font-weight: 600; color: var(--tx); font-size: 1rem;"><?php echo htmlspecialchars($contrato['nome_plano']); ?></div>
    </div>
</div>
<?php endif; ?>

            <!-- Info Cards -->
            <div class="info-cards">
                <div class="info-card-mini">
                    <div class="label">Valor do Desenvolvimento</div>
                    <div class="value">R$ <?php echo number_format($contrato['valor_total'] ?? 0, 2, ',', '.'); ?></div>
                </div>
                
                <div class="info-card-mini">
                    <div class="label">Mensalidade</div>
                    <div class="value">R$ <?php echo number_format($contrato['valor_mensal'] ?? $contrato['valor_mensal_plano'] ?? 0, 2, ',', '.'); ?> <small>/mês</small></div>
                </div>
                
                <div class="info-card-mini">
                    <div class="label">Fidelidade</div>
                    <div class="value"><?php echo $contrato['prazo_fidelidade'] ?? 0; ?> <small>meses</small></div>
                </div>
                
                <div class="info-card-mini">
                    <div class="label">Multa Cancelamento</div>
                    <div class="value">R$ <?php echo number_format($contrato['multa_cancelamento'] ?? 0, 2, ',', '.'); ?></div>
                </div>
            </div>

            <!-- Datas de Pagamento -->
            <div class="datas-grid">
                <?php if (!empty($contrato['data_primeira_parcela']) && $contrato['data_primeira_parcela'] != '0000-00-00'): ?>
                <div class="data-card">
                    <div class="data-label">Primeira Parcela</div>
                    <div class="data-value"><?php echo date('d/m/Y', strtotime($contrato['data_primeira_parcela'])); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contrato['data_primeira_mensalidade']) && $contrato['data_primeira_mensalidade'] != '0000-00-00'): ?>
                <div class="data-card">
                    <div class="data-label">Primeira Mensalidade</div>
                    <div class="data-value"><?php echo date('d/m/Y', strtotime($contrato['data_primeira_mensalidade'])); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($contrato['dia_vencimento']): ?>
                <div class="data-card">
                    <div class="data-label">Dia de Vencimento</div>
                    <div class="data-value">Dia <?php echo $contrato['dia_vencimento']; ?> de cada mês</div>
                </div>
                <?php endif; ?>
                
                <?php if ($contrato['numero_parcelas'] > 1): ?>
                <div class="data-card">
                    <div class="data-label">Parcelamento</div>
                    <div class="data-value"><?php echo $contrato['numero_parcelas']; ?>x de R$ <?php echo number_format($contrato['valor_total'] / $contrato['numero_parcelas'], 2, ',', '.'); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Cliente Info -->
            <?php if ($contrato['cliente_nome']): ?>
            <div style="background: var(--surf); border: 1px solid var(--bdr); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <i class="fas fa-user-circle" style="font-size: 1.5rem; color: var(--ac);"></i>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></span>
                    <?php if ($contrato['cliente_empresa']): ?>
                    <span style="color: var(--tx3);">(<?php echo htmlspecialchars($contrato['cliente_empresa']); ?>)</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 0.85rem; color: var(--tx3);">
                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contrato['cliente_email']); ?></span>
                    <?php if ($contrato['cliente_telefone']): ?>
                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contrato['cliente_telefone']); ?></span>
                    <?php endif; ?>
                    <?php if ($contrato['cliente_documento']): ?>
                    <span><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($contrato['cliente_documento']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Conteúdo do Contrato -->
            <div class="contrato-content-box">
                <?php echo $conteudo_processado; ?>
                
                <!-- Linhas de Assinatura (garantido) -->
                <!-- Linhas de Assinatura (garantido) -->
<div class="assinaturas-container">
    <div class="assinaturas-grid">

        <!-- Coluna CONTRATANTE -->
        <div class="assinatura-col">

            <?php if ($ja_assinado): ?>
                <!-- ✅ Já assinou: mostra imagem da assinatura -->
                <?php if (!empty($assinatura['assinatura_base64'])): ?>
                <div style="background: white; border-radius: 10px; padding: 10px; display: inline-block; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 10px;">
                    <img src="<?php echo $assinatura['assinatura_base64']; ?>" 
                         style="max-width: 220px; max-height: 80px; object-fit: contain; display: block;">
                </div>
                <?php endif; ?>
                <div class="assinatura-linha"></div>
                <div class="assinatura-nome"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></div>
                <div class="assinatura-data">
                    Assinado em: <?php echo date('d/m/Y H:i', strtotime($contrato['data_assinatura_real'] ?? $contrato['data_assinatura'] ?? 'now')); ?>
                </div>
                <span class="assinatura-badge">
                    <i class="fas fa-check-circle"></i> Assinado digitalmente
                </span>

            <?php elseif ($contrato['status'] == 'enviado' && $assinatura): ?>
                <!-- ✍️ Pode assinar: mostra botão -->
                <div style="margin-bottom: 20px;">
                    <div style="border: 2px dashed var(--bdr2); border-radius: 12px; padding: 25px 20px; background: var(--surf2); position: relative;">
                        <p style="color: var(--tx3); font-size: 0.8rem; margin-bottom: 14px; font-style: italic;">
                            Clique para assinar com sua assinatura digital
                        </p>
                        <a href="/cliente/modules/contratos/assinar.php?id=<?php echo $contrato['id']; ?>" 
                           class="btn btn-success" 
                           style="padding: 10px 22px; font-size: 0.9rem;">
                            <i class="fas fa-pen-fancy"></i> Assinar Contrato
                        </a>
                    </div>
                </div>
                <div class="assinatura-linha"></div>
                <div class="assinatura-nome"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></div>
                <div class="assinatura-data" style="color: #f59e0b;">
                    <i class="fas fa-clock"></i> Aguardando assinatura
                </div>

            <?php elseif ($contrato['status'] == 'enviado' && !$assinatura): ?>
                <!-- ⚠️ Sem assinatura cadastrada -->
                <div style="margin-bottom: 20px;">
                    <div style="border: 2px dashed #f59e0b; border-radius: 12px; padding: 20px; background: rgba(245,158,11,0.05);">
                        <p style="color: #f59e0b; font-size: 0.8rem; margin-bottom: 12px;">
                            <i class="fas fa-exclamation-triangle"></i> Você precisa criar sua assinatura antes de assinar
                        </p>
                        <a href="/cliente/modules/assinatura/index.php" 
                           class="btn btn-primary" 
                           style="padding: 8px 18px; font-size: 0.85rem;">
                            <i class="fas fa-pen"></i> Criar Assinatura
                        </a>
                    </div>
                </div>
                <div class="assinatura-linha"></div>
                <div class="assinatura-nome"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></div>
                <div class="assinatura-data" style="color: var(--tx3);">Aguardando assinatura</div>

            <?php else: ?>
                <!-- Status rascunho/cancelado -->
                <div class="assinatura-linha"></div>
                <div class="assinatura-placeholder">(assine aqui)</div>
                <div class="assinatura-nome"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></div>
            <?php endif; ?>

        </div>

        <!-- Coluna CONTRATADA -->
        <div class="assinatura-col">
            <div class="assinatura-linha"></div>
            <div class="assinatura-placeholder">(assinatura da contratada)</div>
            <div class="assinatura-nome">GESTOR ARCON ADMIN</div>
            <div class="assinatura-data">Documento gerado eletronicamente</div>
        </div>

    </div>


                    
                    <p style="text-align: center; margin-top: 30px; font-style: italic; color: var(--tx3); font-size: 0.8rem;">
                        © - GESTOR ARCON ADMIN, <?php echo date('d') . ' de ' . 
                            ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'][date('n')-1] . 
                            ' de ' . date('Y'); ?>
                    </p>
                </div>
            </div>

            <!-- Observações -->
            <?php if (!empty($contrato['observacoes'])): ?>
            <div class="observacoes-box">
                <h4><i class="fas fa-clipboard-list"></i> Observações</h4>
                <p><?php echo nl2br(htmlspecialchars($contrato['observacoes'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Status da Assinatura (resumo) -->
            <?php if (!$ja_assinado && $contrato['status'] == 'enviado'): ?>
            <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 12px; padding: 20px; margin-top: 24px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 1.3rem;">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <div style="flex: 1;">
                    <h4 style="font-weight: 600; margin-bottom: 4px;">Assine este contrato</h4>
                    <p style="color: var(--tx3); font-size: 0.85rem; margin-bottom: 10px;">
                        Utilize sua assinatura digital para assinar este documento.
                    </p>
                    <?php if (!$assinatura): ?>
                        <a href="/cliente/modules/assinatura/index.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.8rem;">
                            <i class="fas fa-pen"></i> Criar Assinatura
                        </a>
                    <?php else: ?>
                        <a href="/cliente/modules/contratos/assinar.php?id=<?php echo $contrato['id']; ?>" class="btn btn-success" style="padding: 8px 16px; font-size: 0.8rem;">
                            <i class="fas fa-pen-fancy"></i> Assinar Agora
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($ja_assinado): ?>
            <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid #10b981; border-radius: 12px; padding: 20px; margin-top: 24px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 1.3rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h4 style="font-weight: 600; margin-bottom: 4px;">Contrato Assinado</h4>
                    <p style="color: var(--tx3); font-size: 0.85rem;">
                        Este contrato foi assinado digitalmente em <?php echo date('d/m/Y \à\s H:i', strtotime($contrato['data_assinatura_real'] ?? $contrato['data_assinatura'] ?? 'now')); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /contrato-view -->
    </div><!-- /content -->
</main><!-- /main -->

<script>
(function() {
    // User dropdown
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

    // Notificação
    const notifBtn = document.getElementById('notifBtn');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            window.location.href = '/cliente/modules/faturas/index.php';
        });
    }

    // Theme toggle
    const themeTog = document.getElementById('themeTog');
    if (themeTog) {
        themeTog.addEventListener('click', () => {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const novo = isDark ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = novo === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            
            const fd = new FormData();
            fd.append('action', 'toggle_tema');
            fd.append('tema', novo);
            fetch(window.location.pathname, { method: 'POST', body: fd })
                .catch(() => {});
        });
    }
})();
</script>

<?php require_once '../../includes/footer.php'; ?>