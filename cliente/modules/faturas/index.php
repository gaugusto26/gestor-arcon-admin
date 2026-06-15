<?php
$page_title = 'Minhas Faturas';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once '../../includes/functions/pagamentos.php';

$cid = (int)$_SESSION['cliente_id'];

// Filtros
$status_filter = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$periodo_filter = isset($_GET['periodo']) ? limparInput($_GET['periodo']) : '';

$filtros = [
    'status' => $status_filter,
    'periodo' => $periodo_filter
];

// Busca faturas
$faturas = buscarFaturasCliente($cid, $filtros, $conn);
$stats = estatisticasFaturasCliente($cid, $conn);
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

/* Alert Cards */
.alert-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.alert-card.warning {
    border-left: 4px solid #f97316;
    background: rgba(249, 115, 22, 0.05);
}

.alert-card.danger {
    border-left: 4px solid #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

.alert-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.alert-card.warning .alert-icon {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.alert-card.danger .alert-icon {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 4px;
}

.alert-text {
    color: var(--tx2);
    font-size: 0.85rem;
}

.alert-btn {
    padding: 8px 16px;
    border-radius: 30px;
    border: none;
    background: var(--ac);
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.alert-btn.warning {
    background: #f97316;
}

.alert-btn.danger {
    background: #ef4444;
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
    margin-top: 23px;
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

/* Tabs */
.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    border-bottom: 1px solid var(--bdr);
    padding-bottom: 10px;
}

.tab {
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--tx2);
    cursor: pointer;
    transition: all 0.3s ease;
    background: transparent;
    border: none;
}

.tab:hover {
    background: var(--surf2);
    color: var(--ac);
}

.tab.active {
    background: var(--ac);
    color: white;
}

/* Faturas Grid */
.faturas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.fatura-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.fatura-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.fatura-card.pendente::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #f97316;
}

.fatura-card.atrasada::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #ef4444;
    animation: pulse 2s infinite;
}

.fatura-card.paga::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #10b981;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
    100% {
        opacity: 1;
    }
}

.fatura-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.fatura-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
}

.fatura-info {
    flex: 1;
}

.fatura-numero {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 4px;
    font-size: 1rem;
    font-family: var(--mono);
}

.fatura-plano {
    font-size: 0.75rem;
    color: var(--tx3);
    margin-bottom: 2px;
}

.fatura-referencia {
    font-size: 0.7rem;
    color: var(--ac);
    font-family: var(--mono);
}

/* Info Row */
.fatura-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0;
    font-size: 0.8rem;
}

.fatura-data {
    color: var(--tx3);
    display: flex;
    align-items: center;
    gap: 4px;
}

.fatura-data i {
    width: 14px;
    color: var(--ac);
}

.fatura-valor {
    font-weight: 700;
    color: var(--ac);
    font-family: var(--mono);
    font-size: 1.2rem;
}

.fatura-valor small {
    font-size: 0.7rem;
    color: var(--tx3);
    font-weight: normal;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.atrasada {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.badge.pendente {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid rgba(249, 115, 22, 0.2);
}

.badge.paga {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

/* Footer */
.fatura-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed var(--bdr);
}

.btn-fatura {
    padding: 8px 16px;
    border-radius: 30px;
    border: 1px solid var(--bdr2);
    background: transparent;
    color: var(--tx2);
    font-size: 0.8rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.btn-fatura:hover {
    background: var(--ac);
    color: white;
    border-color: var(--ac);
}

.btn-fatura.pagar {
    background: var(--ac);
    color: white;
    border-color: var(--ac);
}

.btn-fatura.pagar:hover {
    background: var(--ac2);
    transform: translateY(-2px);
}

.btn-fatura.atrasada {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.btn-fatura.atrasada:hover {
    background: #dc2626;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--surf);
    border-radius: 20px;
    grid-column: 1 / -1;
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

/* Progress Bar */
.progress-container {
    margin-top: 10px;
}

.progress-bar {
    height: 4px;
    background: var(--bdr);
    border-radius: 2px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--ac);
    border-radius: 2px;
    transition: width 0.3s ease;
}

/* Timeline */
.timeline {
    margin: 15px 0;
    padding-left: 20px;
    border-left: 2px solid var(--bdr);
}

.timeline-item {
    position: relative;
    padding-bottom: 10px;
    font-size: 0.75rem;
    color: var(--tx3);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 4px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--bdr2);
}

.timeline-item.completed::before {
    background: #10b981;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .faturas-grid {
        grid-template-columns: repeat(2, 1fr);
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
        margin-top: 0;
    }
    
    .faturas-grid {
        grid-template-columns: 1fr;
    }
    
    .alert-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-file-invoice-dollar"></i></div>
            <span class="top-pg-title">Minhas Faturas</span>
        </div>
        <div class="top-right">
            <div class="ico-btn" id="notifBtn" title="Faturas pendentes">
                <i class="fas fa-bell"></i>
                <?php if ($stats['pendentes'] > 0 || $stats['atrasadas'] > 0): ?>
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
                <h1>Minhas Faturas</h1>
                <p>Gerencie seus pagamentos e acompanhe vencimentos</p>
            </div>
            <div class="welcome-emoji">💰</div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #4361ee15; color: #4361ee;">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_faturas'] ?? 0; ?></div>
                <div class="stat-label">Total de Faturas</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #f9731615; color: #f97316;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $stats['pendentes'] ?? 0; ?></div>
                <div class="stat-label">Pendentes</div>
                <?php if (($stats['pendentes'] ?? 0) > 0): ?>
                <div class="stat-sub">R$ <?php echo number_format($stats['total_pendente'] ?? 0, 2, ',', '.'); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #10b98115; color: #10b981;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $stats['pagas'] ?? 0; ?></div>
                <div class="stat-label">Pagas</div>
                <div class="stat-sub">R$ <?php echo number_format($stats['total_pago'] ?? 0, 2, ',', '.'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ef444415; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?php echo $stats['atrasadas'] ?? 0; ?></div>
                <div class="stat-label">Atrasadas</div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (($stats['atrasadas'] ?? 0) > 0): ?>
        <div class="alert-card danger">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title">Você tem <?php echo $stats['atrasadas']; ?> fatura(s) atrasada(s)!</div>
                <div class="alert-text">Regularize sua situação para evitar bloqueios nos serviços.</div>
            </div>
            <a href="#atrasadas" class="alert-btn danger">Regularizar Agora</a>
        </div>
        <?php elseif (($stats['pendentes'] ?? 0) > 0): ?>
        <div class="alert-card warning">
            <div class="alert-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title">Você tem <?php echo $stats['pendentes']; ?> fatura(s) a vencer</div>
                <div class="alert-text">Próximo vencimento: <?php echo $stats['proxima_fatura'] ? date('d/m/Y', strtotime($stats['proxima_fatura'])) : '-'; ?></div>
            </div>
            <a href="#pendentes" class="alert-btn warning">Pagar Agora</a>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="pendente" <?php echo $status_filter == 'pendente' ? 'selected' : ''; ?>>Pendentes</option>
                        <option value="paga" <?php echo $status_filter == 'paga' ? 'selected' : ''; ?>>Pagas</option>
                        <option value="atrasada" <?php echo $status_filter == 'atrasada' ? 'selected' : ''; ?>>Atrasadas</option>
                    </select>
                </div>

                <div class="filtro-item">
                    <label><i class="fas fa-calendar"></i> Período</label>
                    <select name="periodo">
                        <option value="">Todos</option>
                        <option value="mes" <?php echo $periodo_filter == 'mes' ? 'selected' : ''; ?>>Este mês</option>
                        <option value="trimestre" <?php echo $periodo_filter == 'trimestre' ? 'selected' : ''; ?>>Últimos 3 meses</option>
                        <option value="ano" <?php echo $periodo_filter == 'ano' ? 'selected' : ''; ?>>Este ano</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="/cliente/modules/faturas/index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="window.location.href='?status='">Todas</button>
            <button class="tab" onclick="window.location.href='?status=pendente'">Pendentes</button>
            <button class="tab" onclick="window.location.href='?status=paga'">Pagas</button>
            <button class="tab" onclick="window.location.href='?status=atrasada'">Atrasadas</button>
        </div>

        <!-- Lista de Faturas -->
        <?php if (empty($faturas)): ?>
        <div class="empty-state">
            <i class="fas fa-file-invoice"></i>
            <h3>Nenhuma fatura encontrada</h3>
            <p>As faturas aparecerão aqui quando disponíveis.</p>
        </div>
        <?php else: ?>
        <div class="faturas-grid">
            <?php foreach ($faturas as $fatura): 
                $status_real = $fatura['status_real'] ?? $fatura['status'];
                $dias_vencimento = isset($fatura['dias_para_vencer']) ? (int)$fatura['dias_para_vencer'] : 0;
                $progresso = $status_real == 'paga' ? 100 : ($dias_vencimento < 0 ? 0 : max(0, 100 - ($dias_vencimento * 5)));
            ?>
            <div class="fatura-card <?php echo $status_real; ?>" onclick="window.location.href='detalhe.php?id=<?php echo $fatura['id']; ?>'">
                <div class="fatura-header">
                    <div class="fatura-icon" style="background: <?php 
                        echo $status_real == 'paga' ? '#10b981' : 
                             ($status_real == 'atrasada' ? '#ef4444' : '#f97316'); 
                    ?>;">
                        <i class="fas <?php 
                            echo $status_real == 'paga' ? 'fa-check-circle' : 
                                 ($status_real == 'atrasada' ? 'fa-exclamation-circle' : 'fa-clock'); 
                        ?>"></i>
                    </div>
                    <div class="fatura-info">
                        <div class="fatura-numero">
                            <?php echo htmlspecialchars($fatura['numero_fatura'] ?? 'Fatura #' . $fatura['id']); ?>
                        </div>
                        <?php if (!empty($fatura['nome_plano'])): ?>
                        <div class="fatura-plano">
                            <?php echo htmlspecialchars($fatura['nome_plano']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="fatura-referencia">
                            <i class="fas fa-calendar-alt"></i> 
                            <?php echo isset($fatura['mes_referencia']) ? date('m/Y', strtotime($fatura['mes_referencia'])) : date('m/Y'); ?>
                        </div>
                    </div>
                </div>

                <!-- Info Row -->
                <div class="fatura-info-row">
                    <div class="fatura-data">
                        <i class="fas fa-calendar-check"></i>
                        Vencimento: <?php echo date('d/m/Y', strtotime($fatura['data_vencimento'])); ?>
                    </div>
                    <div class="fatura-valor">
                        R$ <?php echo number_format($fatura['valor_total'], 2, ',', '.'); ?>
                    </div>
                </div>

                <!-- Status Badge -->
                <div style="margin: 8px 0;">
                    <span class="badge <?php echo $status_real; ?>">
                        <?php 
                        echo $status_real == 'paga' ? 'Pago em ' . (isset($fatura['data_pagamento']) ? date('d/m/Y', strtotime($fatura['data_pagamento'])) : '') : 
                             ($status_real == 'atrasada' ? 'Atrasada ' . abs($dias_vencimento) . ' dia(s)' : 
                             ($dias_vencimento . ' dia(s) para vencer')); 
                        ?>
                    </span>
                </div>

                <!-- Progress Bar (para pendentes) -->
                <?php if ($status_real == 'pendente' && $dias_vencimento > 0): ?>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progresso; ?>%;"></div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline (para pagas) -->
                <?php if ($status_real == 'paga' && isset($fatura['data_pagamento'])): ?>
                <div class="timeline">
                    <div class="timeline-item completed">
                        Fatura paga em <?php echo date('d/m/Y H:i', strtotime($fatura['data_pagamento'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="fatura-footer">
                    <span class="btn-fatura" onclick="event.stopPropagation(); window.location.href='detalhe.php?id=<?php echo $fatura['id']; ?>'">
                        <i class="fas fa-eye"></i> Detalhes
                    </span>
                    
                    <?php if ($status_real == 'pendente' || $status_real == 'atrasada'): ?>
                    <span class="btn-fatura <?php echo $status_real == 'atrasada' ? 'atrasada' : 'pagar'; ?>" 
                          onclick="event.stopPropagation(); window.location.href='pagar.php?id=<?php echo $fatura['id']; ?>'">
                        <i class="fas fa-credit-card"></i> 
                        <?php echo $status_real == 'atrasada' ? 'Regularizar' : 'Pagar'; ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($fatura['pdf_path'])): ?>
                    <span class="btn-fatura" onclick="event.stopPropagation(); window.open('<?php echo $fatura['pdf_path']; ?>', '_blank')">
                        <i class="fas fa-download"></i> PDF
                    </span>
                    <?php endif; ?>
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
            window.location.href = '/cliente/modules/faturas/index.php?status=pendente';
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

    // Marcar tab ativa
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
        if ((!status && tab.textContent.trim() === 'Todas') ||
            (status && tab.textContent.trim().toLowerCase() === status)) {
            tab.classList.add('active');
        }
    });
})();
</script>

<?php require_once '../../includes/footer.php'; ?>