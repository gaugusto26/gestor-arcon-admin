<?php
$page_title = 'Detalhes do Documento';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /cliente/modules/documentos/index.php');
    exit;
}

$cid = (int)$_SESSION['cliente_id'];
$doc_id = (int)$_GET['id'];

$doc = getDocumentoDetalhes($doc_id, $cid);

if (!$doc) {
    header('Location: /cliente/modules/documentos/index.php');
    exit;
}

$icone = getIconePorTipo($doc['tipo']);
$cor = getCorPorTipo($doc['tipo']);
$tipo_nome = getNomeTipo($doc['tipo']);
$tamanho = formatarTamanhoArquivo($doc['arquivo_tamanho']);

// Verifica vencimento
$vencimento_class = '';
$vencimento_text = '';
if ($doc['data_vencimento']) {
    $hoje = new DateTime();
    $vencimento = new DateTime($doc['data_vencimento']);
    if ($vencimento < $hoje) {
        $vencimento_class = 'badge-vencido';
        $vencimento_text = 'Vencido';
    } elseif ($vencimento->diff($hoje)->days <= 7) {
        $vencimento_class = 'badge-pendente';
        $vencimento_text = 'Próximo ao vencimento';
    }
}
?>

<style>
/* ===== ESTILOS DA PÁGINA DE DETALHES ===== */
.detalhes-container {
    max-width: 800px;
    margin: 0 auto;
}

.documento-header {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 25px;
    flex-wrap: wrap;
}

.documento-icon-large {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    flex-shrink: 0;
}

.documento-title h1 {
    font-size: 1.5rem;
    margin-bottom: 5px;
    color: var(--tx);
}

.documento-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    color: var(--tx3);
    font-size: 0.85rem;
}

.documento-meta i {
    color: var(--ac);
    margin-right: 5px;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.info-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
}

.info-card h3 {
    font-size: 0.9rem;
    color: var(--tx3);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card h3 i {
    color: var(--ac);
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--bdr);
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-label {
    color: var(--tx3);
    font-size: 0.8rem;
}

.info-value {
    font-weight: 600;
    color: var(--tx);
}

.info-value.valor {
    color: var(--ac);
    font-size: 1.1rem;
}

/* Preview */
.preview-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 25px;
    text-align: center;
}

.preview-icon {
    font-size: 4rem;
    color: <?php echo $cor; ?>;
    margin-bottom: 15px;
}

.preview-name {
    font-size: 0.9rem;
    color: var(--tx3);
    margin-bottom: 20px;
}

.preview-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 25px;
    border-radius: 30px;
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
    background: <?php echo $cor; ?>;
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
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

/* Responsive */
@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .documento-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-file-pdf"></i></div>
            <span class="top-pg-title">Detalhes do Documento</span>
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
    </div><!-- /topbar -->

    <div class="content">
        <div class="detalhes-container">

            <!-- Botão Voltar -->
            <div style="margin-bottom: 20px;">
                <a href="/cliente/modules/documentos/index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <!-- Header do Documento -->
            <div class="documento-header">
                <div class="documento-icon-large" style="background: <?php echo $cor; ?>;">
                    <i class="fas <?php echo $icone; ?>"></i>
                </div>
                <div class="documento-title">
                    <h1><?php echo htmlspecialchars($doc['titulo']); ?></h1>
                    <div class="documento-meta">
                        <span><i class="fas fa-tag"></i> <?php echo $tipo_nome; ?></span>
                        <?php if ($doc['referencia_numero']): ?>
                        <span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($doc['referencia_numero']); ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-hdd"></i> <?php echo $tamanho; ?></span>
                    </div>
                </div>
            </div>

            <!-- Grid de Informações -->
            <div class="info-grid">
                <!-- Detalhes -->
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Detalhes</h3>
                    
                    <?php if ($doc['data_documento']): ?>
                    <div class="info-row">
                        <span class="info-label">Data do Documento</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($doc['data_documento'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($doc['data_vencimento']): ?>
                    <div class="info-row">
                        <span class="info-label">Data de Vencimento</span>
                        <span class="info-value" style="color: <?php echo $vencimento_class == 'badge-vencido' ? '#ef4444' : ($vencimento_class == 'badge-pendente' ? '#f97316' : 'inherit'); ?>">
                            <?php echo date('d/m/Y', strtotime($doc['data_vencimento'])); ?>
                            <?php if ($vencimento_class): ?>
                            <span class="documento-badge <?php echo $vencimento_class; ?>" style="margin-left: 8px;"><?php echo $vencimento_text; ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($doc['valor']): ?>
                    <div class="info-row">
                        <span class="info-label">Valor</span>
                        <span class="info-value valor">R$ <?php echo number_format($doc['valor'], 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <span class="info-label">Upload em</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?></span>
                    </div>
                </div>

                <!-- Referência -->
                <div class="info-card">
                    <h3><i class="fas fa-link"></i> Referência</h3>
                    
                    <?php if ($doc['referencia_numero']): ?>
                    <div class="info-row">
                        <span class="info-label">Número</span>
                        <span class="info-value"><?php echo htmlspecialchars($doc['referencia_numero']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($doc['tipo'] == 'contrato' && $doc['referencia_id']): ?>
                    <div style="margin-top: 15px;">
                        <a href="/cliente/modules/contratos/visualizar.php?id=<?php echo $doc['referencia_id']; ?>" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            <i class="fas fa-file-contract"></i> Ver Contrato
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($doc['tipo'] == 'fatura' && $doc['referencia_id']): ?>
                    <div style="margin-top: 15px;">
                        <a href="/cliente/modules/faturas/visualizar.php?id=<?php echo $doc['referencia_id']; ?>" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            <i class="fas fa-file-invoice-dollar"></i> Ver Fatura
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Descrição -->
            <?php if (!empty($doc['descricao'])): ?>
            <div class="info-card" style="grid-column: 1/-1;">
                <h3><i class="fas fa-align-left"></i> Descrição</h3>
                <p style="color: var(--tx2); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($doc['descricao'])); ?></p>
            </div>
            <?php endif; ?>

            <!-- Preview e Download -->
            <div class="preview-card">
                <div class="preview-icon">
                    <i class="fas <?php echo $icone; ?>"></i>
                </div>
                <div class="preview-name"><?php echo htmlspecialchars($doc['arquivo_nome']); ?></div>
                
                <div class="preview-actions">
                    <a href="/cliente/uploads/<?php echo $doc['arquivo_path']; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-eye"></i> Visualizar
                    </a>
                    <a href="/cliente/uploads/<?php echo $doc['arquivo_path']; ?>" class="btn btn-outline" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>

        </div><!-- /detalhes-container -->
    </div><!-- /content -->
</main><!-- /main -->

<script>
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

    const notifBtn = document.getElementById('notifBtn');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            window.location.href = '/cliente/modules/faturas/index.php';
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