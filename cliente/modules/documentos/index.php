<?php
$page_title = 'Meus Documentos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$cid = (int)$_SESSION['cliente_id'];

// Filtros
$tipo_filter = isset($_GET['tipo']) ? limparInput($_GET['tipo']) : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

$filtros = [
    'tipo' => $tipo_filter,
    'busca' => $busca
];

// Busca documentos
$documentos = getDocumentosCliente($cid, $filtros);
$stats = getEstatisticasDocumentos($cid);
?>

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s ease;
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin: 0 auto 12px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--tx);
    margin-bottom: 5px;
    font-family: var(--mono);
}

.stat-label {
    color: var(--tx3);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-sub {
    font-size: 0.7rem;
    color: var(--tx3);
    margin-top: 5px;
}

/* Filtros */
.filtros-box {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: 1fr 200px auto;
    gap: 15px;
}

.filtro-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtro-item label {
    font-size: 0.7rem;
    color: var(--tx3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filtro-item input,
.filtro-item select {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid var(--bdr);
    background: var(--surf2);
    color: var(--tx);
    font-size: 0.85rem;
}

.btn-filtro {
    padding: 10px 20px;
    border-radius: 30px;
    border: none;
    background: var(--ac);
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 42px;
}

.btn-filtro:hover {
    background: var(--ac2);
    transform: translateY(-2px);
}

.btn-limpar {
    background: transparent;
    border: 1px solid var(--bdr2);
    color: var(--tx2);
}

/* Documentos Grid */
.documentos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.documento-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.documento-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.documento-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.documento-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
}

.documento-info {
    flex: 1;
}

.documento-titulo {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 4px;
    font-size: 1rem;
}

.documento-referencia {
    font-size: 0.7rem;
    color: var(--ac);
    font-family: var(--mono);
}

.documento-tipo {
    font-size: 0.7rem;
    color: var(--tx3);
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 0.65rem;
    font-weight: 600;
}

.badge-vencido {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.badge-pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.badge-pago {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

/* Info Row */
.documento-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0;
    font-size: 0.8rem;
}

.documento-data {
    color: var(--tx3);
    display: flex;
    align-items: center;
    gap: 4px;
}

.documento-valor {
    font-weight: 600;
    color: var(--ac);
    font-family: var(--mono);
}

/* Footer */
.documento-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed var(--bdr);
}

.btn-documento {
    padding: 6px 14px;
    border-radius: 30px;
    border: 1px solid var(--bdr2);
    background: transparent;
    color: var(--tx2);
    font-size: 0.75rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-documento:hover {
    background: var(--ac);
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--surf);
    border-radius: 20px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--tx4);
    margin-bottom: 15px;
}

.empty-state h3 {
    font-size: 1.1rem;
    margin-bottom: 8px;
    color: var(--tx);
}

.empty-state p {
    color: var(--tx3);
    font-size: 0.85rem;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filtros-grid {
        grid-template-columns: 1fr;
    }
    
    .documentos-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-file-pdf"></i></div>
            <span class="top-pg-title">Meus Documentos</span>
        </div>
        <div class="top-right">
            <div class="ico-btn" id="notifBtn" title="Faturas pendentes">
                <i class="fas fa-bell"></i>
                <?php if ($stats['pendentes'] > 0 || $stats['vencidos'] > 0): ?>
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
    </div>

    <div class="content">

        <!-- Welcome Banner -->
        <div class="welcome" style="margin-bottom: 25px;">
            <div class="welcome-copy">
                <h1>Meus Documentos</h1>
                <p>Consulte todos os seus documentos</p>
            </div>
            <div class="welcome-emoji">📄</div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #4361ee15; color: #4361ee;">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-value"><?php echo $stats['contratos']; ?></div>
                <div class="stat-label">Contratos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #10b98115; color: #10b981;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-value"><?php echo $stats['faturas']; ?></div>
                <div class="stat-label">Faturas</div>
                <?php if ($stats['pendentes'] > 0): ?>
                <div class="stat-sub"><?php echo $stats['pendentes']; ?> a vencer</div>
                <?php endif; ?>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #f9731615; color: #f97316;">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div class="stat-value"><?php echo $stats['propostas']; ?></div>
                <div class="stat-label">Propostas</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--ac)15; color: var(--ac);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?php echo number_format($stats['total_valor'], 2, ',', '.'); ?></div>
                <div class="stat-label">Valor Total</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Título ou número..." 
                           value="<?php echo htmlspecialchars($busca); ?>">
                </div>

                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Tipo</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <option value="contrato" <?php echo $tipo_filter == 'contrato' ? 'selected' : ''; ?>>Contratos</option>
                        <option value="fatura" <?php echo $tipo_filter == 'fatura' ? 'selected' : ''; ?>>Faturas</option>
                        <option value="proposta" <?php echo $tipo_filter == 'proposta' ? 'selected' : ''; ?>>Propostas</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="/cliente/modules/documentos/index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Documentos -->
        <?php if (empty($documentos)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>Nenhum documento encontrado</h3>
            <p>Os documentos aparecerão aqui quando estiverem disponíveis.</p>
        </div>
        <?php else: ?>
        <div class="documentos-grid">
            <?php foreach ($documentos as $doc): 
                $icone = getIconePorTipo($doc['tipo']);
                $cor = getCorPorTipo($doc['tipo']);
                $tipo_nome = getNomeTipo($doc['tipo']);
                list($badge_class, $badge_text) = getStatusBadge($doc['status'] ?? '');
            ?>
            <div class="documento-card" onclick="window.location.href='visualizar.php?id=<?php echo $doc['id']; ?>'">
                <div class="documento-header">
                    <div class="documento-icon" style="background: <?php echo $cor; ?>;">
                        <i class="fas <?php echo $icone; ?>"></i>
                    </div>
                    <div class="documento-info">
                        <div class="documento-titulo">
                            <?php echo htmlspecialchars($doc['titulo']); ?>
                        </div>
                        <div class="documento-referencia">
                            #<?php echo htmlspecialchars($doc['referencia'] ?? ''); ?>
                        </div>
                        <div class="documento-tipo">
                            <i class="fas <?php echo $icone; ?>" style="color: <?php echo $cor; ?>;"></i>
                            <?php echo $tipo_nome; ?>
                        </div>
                    </div>
                </div>

                <!-- Descrição -->
                <?php if (!empty($doc['descricao'])): ?>
                <p style="color: var(--tx2); font-size: 0.8rem; margin-bottom: 10px;">
                    <?php echo htmlspecialchars(is_string($doc['descricao']) ? substr($doc['descricao'], 0, 100) . (strlen($doc['descricao']) > 100 ? '...' : '') : ''); ?>
                </p>
                <?php endif; ?>

                <!-- Data e Valor -->
                <div class="documento-info-row">
                    <?php if (!empty($doc['data_emissao'])): ?>
                    <div class="documento-data">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('d/m/Y', strtotime($doc['data_emissao'])); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($doc['valor'])): ?>
                    <div class="documento-valor">
                        R$ <?php echo number_format($doc['valor'], 2, ',', '.'); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status Badge -->
                <?php if (!empty($badge_class)): ?>
                <div style="margin: 8px 0;">
                    <span class="badge <?php echo $badge_class; ?>">
                        <?php echo $badge_text; ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="documento-footer">
                    <span class="btn-documento" onclick="event.stopPropagation(); window.location.href='visualizar.php?id=<?php echo $doc['id']; ?>'">
                        <i class="fas fa-eye"></i> Visualizar
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</main>

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
        });
    }
})();
</script>

<?php require_once '../../includes/footer.php'; ?>