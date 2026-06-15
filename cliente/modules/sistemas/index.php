<?php
$page_title = 'Meus Sistemas';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

$cid = (int)$_SESSION['cliente_id'];

// Busca sistemas (contratos assinados)
$sistemas = getSistemasCliente($cid);

// Estatísticas
$stats = [
    'total' => $sistemas->num_rows,
    'em_andamento' => 0,
    'concluido' => 0,
    'pendente' => 0
];

// Por enquanto, todos os sistemas assinados são "em andamento"
// Depois podemos adicionar uma tabela específica para progresso
$stats['em_andamento'] = $sistemas->num_rows;

// Filtros
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';
?>

<style>
/* ===== ESTILOS DO MÓDULO DE SISTEMAS ===== */

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--ac) 0%, var(--ac2) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--tx);
    margin-bottom: 5px;
    font-family: var(--mono);
}

.stat-label {
    color: var(--tx3);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Filtros */
.filtros-box {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
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
    font-size: 0.75rem;
    color: var(--tx3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filtro-item input,
.filtro-item select {
    padding: 12px 15px;
    border-radius: 12px;
    border: 1px solid var(--bdr);
    background: var(--surf2);
    color: var(--tx);
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.filtro-item input:focus,
.filtro-item select:focus {
    outline: none;
    border-color: var(--ac);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-filtro {
    padding: 12px 24px;
    border-radius: 30px;
    border: none;
    background: var(--ac);
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    height: 46px;
}

.btn-filtro:hover {
    background: var(--ac2);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-limpar {
    background: transparent;
    border: 1px solid var(--bdr2);
    color: var(--tx2);
}

.btn-limpar:hover {
    background: var(--surf2);
    border-color: var(--ac);
    color: var(--ac);
    transform: translateY(-2px);
    box-shadow: none;
}

/* Systems Grid */
.systems-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.system-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 24px;
    padding: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.system-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.system-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--ac), var(--ac2));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.system-card:hover::before {
    opacity: 1;
}

.system-header {
    display: flex;
    align-items: flex-start; /* era center */
    gap: 15px;
    margin-bottom: 15px;
    min-height: 60px;
}

.system-info {
    flex: 1;
    min-width: 0; /* evita overflow */
}

.system-name {
    font-weight: 700;
    color: var(--tx);
    margin-bottom: 5px;
    font-size: 1.1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;    /* máximo 2 linhas */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
    max-height: 2.6em;
}

.system-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0; /* ← impede o ícone de encolher */
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap; /* ← impede o badge de quebrar linha */
    flex-shrink: 0;
}



.system-plan {
    font-size: 0.8rem;
    color: var(--tx3);
    display: flex;
    align-items: center;
    gap: 5px;
}

.system-plan i {
    color: var(--ac);
    font-size: 0.7rem;
}

/* Status Chip */
.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
}

.status-chip i {
    font-size: 0.6rem;
}

.status-chip.andamento {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-chip.concluido {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-chip.pendente {
    background: rgba(67, 97, 238, 0.1);
    color: #4361ee;
}

/* Contrato Info */
.contrato-info {
    background: var(--surf2);
    border-radius: 12px;
    padding: 15px;
    margin: 15px 0;
    font-size: 0.8rem;
}

.contrato-info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.contrato-info-item:last-child {
    margin-bottom: 0;
}

.contrato-info-label {
    color: var(--tx3);
}

.contrato-info-value {
    font-weight: 600;
    color: var(--tx);
    font-family: var(--mono);
}

/* Progress Bar */
.progress-container {
    margin: 15px 0;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    font-size: 0.8rem;
    color: var(--tx3);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--surf3);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--ac), var(--ac2));
    border-radius: 4px;
    transition: width 0.6s ease;
}

/* Dates */
.dates-container {
    display: flex;
    justify-content: space-between;
    margin: 15px 0;
    padding: 10px 0;
    border-top: 1px dashed var(--bdr);
    border-bottom: 1px dashed var(--bdr);
    font-size: 0.75rem;
    color: var(--tx3);
}

.date-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.date-item i {
    color: var(--ac);
    font-size: 0.7rem;
}

/* Footer */
.system-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.system-price {
    font-weight: 700;
    color: var(--ac);
    font-family: var(--mono);
    font-size: 1rem;
}

.system-price small {
    font-weight: 400;
    color: var(--tx3);
    font-size: 0.7rem;
    font-family: var(--font);
    margin-left: 3px;
}

.btn-sm {
    padding: 8px 18px;
    border-radius: 30px;
    border: 1px solid var(--bdr2);
    background: transparent;
    color: var(--tx2);
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-sm:hover {
    background: var(--ac);
    border-color: var(--ac);
    color: white;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: var(--surf);
    border-radius: 24px;
    border: 1px solid var(--bdr);
}

.empty-state i {
    font-size: 4rem;
    color: var(--tx4);
    margin-bottom: 20px;
}

.empty-state h2 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: var(--tx);
}

.empty-state p {
    color: var(--tx3);
    font-size: 0.9rem;
    max-width: 400px;
    margin: 0 auto 25px;
}

.empty-state .btn {
    padding: 12px 30px;
    border-radius: 30px;
    background: var(--ac);
    color: white;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.empty-state .btn:hover {
    background: var(--ac2);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filtros-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-filtro {
        width: 100%;
        justify-content: center;
    }
    
    .systems-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .dates-container {
        flex-direction: column;
        gap: 8px;
    }
}

/* Responsive */
@media (max-width: 1024px) {
    .systems-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filtros-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-filtro {
        width: 100%;
        justify-content: center;
    }

    .systems-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .system-card {
        padding: 18px;
    }

    .system-header {
        flex-wrap: wrap;
        gap: 10px;
    }

    .system-icon {
        width: 48px;
        height: 48px;
        font-size: 1.3rem;
    }

    .system-name {
        font-size: 1rem;
    }

    .status-chip {
        font-size: 0.7rem;
        padding: 4px 10px;
    }

    .contrato-info {
        padding: 12px;
    }

    .system-footer {
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-sm {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .stat-card {
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 1.5rem;
        margin-bottom: 2px;
    }

    .dates-container {
        flex-direction: column;
        gap: 8px;
    }

    .system-header {
        gap: 10px;
    }

    .system-info {
        min-width: 0; /* evita overflow do texto */
    }

    .system-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .filtros-box {
        padding: 15px;
    }
}
</style>

<!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-cube"></i></div>
            <span class="top-pg-title">Meus Sistemas</span>
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

        <!-- Welcome Banner -->
        <div class="welcome" style="margin-bottom: 25px;">
            <div class="welcome-copy">
                <h1>Meus Sistemas</h1>
                <p>Acompanhe o desenvolvimento dos seus projetos</p>
            </div>
            <div class="welcome-emoji">💻</div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total de Sistemas</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="stat-value"><?php echo $stats['em_andamento']; ?></div>
                <div class="stat-label">Em Desenvolvimento</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Concluídos</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Nome do sistema ou plano..." 
                           value="<?php echo htmlspecialchars($busca); ?>">
                </div>

                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="em_andamento" <?php echo $status_filter == 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                        <option value="concluido" <?php echo $status_filter == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="/cliente/modules/sistemas/index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Sistemas -->
        <?php if ($sistemas->num_rows == 0): ?>
        <div class="empty-state">
            <i class="fas fa-cubes"></i>
            <h2>Nenhum sistema em desenvolvimento</h2>
            <p>Os sistemas aparecerão aqui após você assinar os contratos.</p>
            <a href="/cliente/modules/contratos/index.php" class="btn">
                <i class="fas fa-file-contract"></i> Ver Meus Contratos
            </a>
        </div>
        <?php else: ?>
        <div class="systems-grid">
            <?php 
            $sistemas->data_seek(0);
            while ($s = $sistemas->fetch_assoc()): 
                // Calcula progresso estimado (exemplo: baseado no tempo desde a assinatura)
                $dias_estimados = 45; // 45 dias para desenvolvimento
                $progresso = $s['percentual_concluido'] ?? 0;
                
                // Data prevista de entrega (data da assinatura + dias estimados)
                $data_inicio = new DateTime($s['data_contrato']);
                $data_previsao = clone $data_inicio;
                $data_previsao->modify("+{$dias_estimados} days");
                
                // Verifica se está próximo do prazo
                $hoje = new DateTime();
                $dias_restantes = $hoje->diff($data_previsao)->days;
                $prazo_class = '';
                if ($hoje > $data_previsao) {
                    $prazo_class = 'danger';
                } elseif ($dias_restantes <= 7) {
                    $prazo_class = 'warning';
                }
            ?>
            <div class="system-card">
                <div class="system-header">
                    <div class="system-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="system-info">
                        <div class="system-name"><?php echo htmlspecialchars($s['contrato_titulo'] ?? 'Sistema Personalizado'); ?></div>
                        <div class="system-plan">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($s['nome_plano'] ?? 'Plano Personalizado'); ?>
                        </div>
                    </div>
<?php
$status_labels = [
    'aguardando_inicio'  => ['label' => 'Aguardando Início', 'class' => 'pendente'],
    'desenvolvimento'    => ['label' => 'Em Desenvolvimento', 'class' => 'andamento'],
    'homologacao_cliente'=> ['label' => 'Homologação', 'class' => 'andamento'],
    'concluido'          => ['label' => 'Concluído', 'class' => 'concluido'],
    'pausado'            => ['label' => 'Pausado', 'class' => 'pendente'],
    'cancelado'          => ['label' => 'Cancelado', 'class' => 'pendente'],
];
$st = $status_labels[$s['status']] ?? ['label' => ucfirst($s['status']), 'class' => 'andamento'];
?>
<span class="status-chip <?php echo $st['class']; ?>">
    <i class="fas fa-circle"></i> <?php echo $st['label']; ?>
</span>
                </div>

                <!-- Informações do Contrato -->
                <div class="contrato-info">
                    <div class="contrato-info-item">
                        <span class="contrato-info-label">Contrato:</span>
                        <span class="contrato-info-value"><?php echo htmlspecialchars($s['numero_contrato']); ?></span>
                    </div>
                    <div class="contrato-info-item">
                        <span class="contrato-info-label">Assinado em:</span>
                        <span class="contrato-info-value"><?php echo date('d/m/Y', strtotime($s['data_contrato'])); ?></span>
                    </div>
                    <?php if ($s['valor_mensal']): ?>
                    <div class="contrato-info-item">
                        <span class="contrato-info-label">Mensalidade:</span>
                        <span class="contrato-info-value">R$ <?php echo number_format($s['valor_mensal'], 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Barra de Progresso -->
                <div class="progress-container">
                    <div class="progress-header">
                        <span>Progresso estimado</span>
                        <span><?php echo $progresso; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progresso; ?>%;"></div>
                    </div>
                </div>

                <!-- Datas -->
                <div class="dates-container">
                    <div class="date-item">
                        <i class="fas fa-play"></i> Início: <?php echo date('d/m/Y', strtotime($s['data_contrato'])); ?>
                    </div>
                    <div class="date-item <?php echo $prazo_class; ?>">
                        <i class="fas fa-flag-checkered"></i> Previsão: <?php echo $data_previsao->format('d/m/Y'); ?>
                        <?php if ($prazo_class == 'danger'): ?>
                        <span style="color: #ef4444; margin-left: 5px;">(Atrasado)</span>
                        <?php elseif ($prazo_class == 'warning'): ?>
                        <span style="color: #f97316; margin-left: 5px;">(<?php echo $dias_restantes; ?> dias)</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="system-footer">
                    <div class="system-price">
                        R$ <?php echo number_format($s['valor_total'] ?? 0, 2, ',', '.'); ?>
                        <small>total</small>
                    </div>
                    <a href="/cliente/modules/sistemas/visualizar.php?id=<?php echo $s['contrato_id']; ?>" class="btn-sm">
                        <i class="fas fa-eye"></i> Detalhes
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

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