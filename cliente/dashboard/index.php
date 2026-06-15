<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';
require_once '../includes/menu.php';
require_once '../config.php';

global $conn;
$cid = (int)$_SESSION['cliente_id'];

// ── Dados ─────────────────────────────────────────────────────────────────────
$sistemas  = getSistemasCliente($cid);
$contratos = getContratosCliente($cid);

$total_gasto = (float)($conn->query("
    SELECT COALESCE(SUM(valor),0) AS t FROM pagamento_transacoes
    WHERE cliente_id=$cid AND status='aprovado'
")->fetch_assoc()['t'] ?? 0);

$proximos_venc = $conn->query("
    SELECT * FROM cliente_faturas
    WHERE cliente_id=$cid AND status='pendente'
      AND data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY data_vencimento ASC
    LIMIT 5
");

// Gastos mensais (últimos 6 meses)
$gastos_q = $conn->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS mes, SUM(valor) AS total
    FROM pagamento_transacoes
    WHERE cliente_id=$cid AND status='aprovado'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY mes ASC
");

$meses = []; $valores = [];
while ($r = $gastos_q->fetch_assoc()) {
    $meses[]   = date('M/y', strtotime($r['mes'].'-01'));
    $valores[] = (float)$r['total'];
}

// Stats — todos os contratos assinados são "em_andamento" por enquanto
$stats = ['em_andamento' => 0, 'concluido' => 0, 'pendente' => 0, 'cancelado' => 0];
if ($sistemas && $sistemas->num_rows > 0) {
    $sistemas->data_seek(0);
    while ($s = $sistemas->fetch_assoc()) {
        // status no banco é 'assinado', mapeamos para em_andamento
        $stats['em_andamento']++;
    }
    $sistemas->data_seek(0);
}
?>

    <!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
    <main class="main" id="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="top-left">
                <div class="top-pg-ico"><i class="fas fa-home"></i></div>
                <span class="top-pg-title">Dashboard</span>
            </div>
            <div class="top-right">

                <!-- Notificação -->
                <div class="ico-btn" id="notifBtn" title="Faturas pendentes">
                    <i class="fas fa-bell"></i>
                    <?php if ($faturas_pendentes['total'] > 0): ?>
                    <span class="ico-dot"></span>
                    <?php endif; ?>
                </div>

                <!-- Toggle de tema (dark/light) — salvo no banco -->
                <div class="theme-tog" id="themeTog" title="Alternar tema">
                    <div class="theme-thumb">
                        <i class="fas <?php echo $tema_atual==='dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
                    </div>
                </div>

                <!-- Usuário -->
                <div class="user-btn" id="userBtn">
                    <div class="sb-av"><?php echo strtoupper(substr($cliente['nome'],0,1)); ?></div>
                    <span class="user-btn-n"><?php echo htmlspecialchars(explode(' ',$cliente['nome'])[0]); ?></span>
                    <i class="fas fa-chevron-down user-chevron"></i>
                    <div class="ddrop" id="userDrop">
                        <a href="?mod=perfil"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                        <a href="?mod=assinatura"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                        <hr>
                        <a href="logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </div>
                </div>

            </div>
        </div><!-- /topbar -->

        <!-- Content -->
        <div class="content">
<style>
    .welcome-emoji i {
    font-size: 40px;
    background: linear-gradient(135deg, #b493f3, #a3d6ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
            <!-- Welcome -->
            <div class="welcome">
                <div class="welcome-copy">
                    <h1>Olá, <?php echo htmlspecialchars(explode(' ',$cliente['nome'])[0]); ?>! 👋</h1>
                    <p>Aqui está o resumo da sua conta</p>
                </div>
                <div class="welcome-emoji">
    <i class="fas fa-chart-line"></i>
</div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-ico" style="background:rgba(79,110,247,.1);color:var(--ac)">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="stat-val"><?php echo $sistemas->num_rows; ?></div>
                    <div class="stat-lbl">Sistemas Ativos</div>
                    <div class="stat-sub" style="color:var(--ac)">
                        <i class="fas fa-circle-dot"></i>
                        <?php echo $stats['em_andamento']; ?> em andamento
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-ico" style="background:rgba(5,150,105,.1);color:var(--ac3)">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="stat-val"><?php echo $contratos->num_rows; ?></div>
                    <div class="stat-lbl">Contratos</div>
                    <div class="stat-sub" style="color:var(--ac3)">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $stats['concluido']; ?> concluídos
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-ico" style="background:rgba(124,58,237,.1);color:var(--ac2)">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-val sm">
                        R$&nbsp;<?php echo number_format($total_gasto,2,',','.'); ?>
                    </div>
                    <div class="stat-lbl">Total Investido</div>
                    <div class="stat-sub" style="color:var(--tx3)">
                        <i class="fas fa-calendar-alt"></i> Últimos 6 meses
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-ico" style="background:rgba(220,38,38,.09);color:var(--danger)">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-val"><?php echo (int)$faturas_pendentes['total']; ?></div>
                    <div class="stat-lbl">Faturas Pendentes</div>
                    <div class="stat-sub" style="color:var(--danger)">
                        <i class="fas fa-triangle-exclamation"></i>
                        R$&nbsp;<?php echo number_format((float)$faturas_pendentes['total_valor'],2,',','.'); ?>
                    </div>
                </div>

            </div><!-- /stats-grid -->

            <!-- ════════════════════════════════════════════════════════
                 CHARTS — bug corrigido: chart-box com overflow:hidden
                 e canvas position:absolute preenchendo 100%
            ════════════════════════════════════════════════════════ -->
            <div class="charts-grid">

                <div class="chart-card">
                    <div class="chart-hd">
                        <div class="chart-ttl">
                            <i class="fas fa-chart-line"></i> Gastos Mensais
                        </div>
                    </div>
                    <!-- ✅ Wrapper contido -->
                    <div class="chart-box">
                        <canvas id="gastosChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-hd">
                        <div class="chart-ttl">
                            <i class="fas fa-chart-pie"></i> Status dos Sistemas
                        </div>
                    </div>
                    <div class="chart-box">
                        <canvas id="sistemasChart"></canvas>
                    </div>
                </div>

            </div><!-- /charts-grid -->

            <!-- Ações rápidas -->
            <div class="actions-grid">
                <a href="?mod=contratos" class="act-card">
                    <div class="act-ico" style="background:rgba(79,110,247,.1);color:var(--ac)">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <span class="act-lbl">Contratos</span>
                </a>
                <a href="?mod=faturas" class="act-card">
                    <div class="act-ico" style="background:rgba(220,38,38,.09);color:var(--danger)">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span class="act-lbl">Faturas</span>
                </a>
                <a href="?mod=assinatura" class="act-card">
                    <div class="act-ico" style="background:rgba(124,58,237,.1);color:var(--ac2)">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <span class="act-lbl">Criar Assinatura</span>
                </a>
                <a href="?mod=suporte" class="act-card">
                    <div class="act-ico" style="background:rgba(5,150,105,.1);color:var(--ac3)">
                        <i class="fas fa-headset"></i>
                    </div>
                    <span class="act-lbl">Suporte</span>
                </a>
            </div>

         <?php if ($sistemas && $sistemas->num_rows > 0): ?>
<div class="sec-ttl"><i class="fas fa-cube"></i> Meus Sistemas</div>
<div class="systems-grid">
    <?php 
    $sistemas->data_seek(0);
    while ($sys = $sistemas->fetch_assoc()):
        // Status sempre 'assinado' vindo do banco — mapeia para exibição
        $chip  = 'chip-warn';
        $label = 'Em Andamento';

        // Calcula progresso por tempo (igual ao módulo de sistemas)
        $dias_estimados = 45;
        $pct = calcularProgressoSistema($sys['data_contrato'], $dias_estimados);

        $nome = $sys['nome_sistema'] ?? $sys['contrato_titulo'] ?? 'Sistema Personalizado';
    ?>
    <div class="sys-card">
        <div class="sys-hd">
            <div class="sys-ico" style="background:rgba(79,110,247,.1);color:var(--ac)">
                <i class="fas fa-code"></i>
            </div>
            <div>
                <div class="sys-name"><?php echo htmlspecialchars($nome); ?></div>
                <span class="chip <?php echo $chip; ?>"><?php echo $label; ?></span>
            </div>
        </div>
        <div class="prog-track">
            <div class="prog-fill" style="width:<?php echo $pct; ?>%"></div>
        </div>
        <div class="prog-lbls">
            <span>Progresso</span>
            <span><?php echo $pct; ?>%</span>
        </div>
        <div class="sys-foot">
            <div class="sys-price">
                R$&nbsp;<?php echo number_format((float)($sys['valor_mensal'] ?? 0), 2, ',', '.'); ?>
                <small>/mês</small>
            </div>
            <a href="../modules/sistemas/visualizar.php?id=<?php echo (int)$sys['contrato_id']; ?>" class="btn-sm">
                Detalhes <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>
            <!-- Próximos vencimentos -->
            <?php if ($proximos_venc->num_rows > 0): ?>
            <div class="sec-ttl"><i class="fas fa-clock"></i> Próximos Vencimentos</div>
            <div class="alert-wrap">
                <?php while ($fat = $proximos_venc->fetch_assoc()):
                    $dias = (strtotime($fat['data_vencimento']) - time()) / 86400;
                    if ($dias <= 0) {
                        $ico_bg = 'rgba(220,38,38,.1)'; $ico_c = 'var(--danger)';
                        $val_c  = 'var(--danger)';
                        $quando = 'Hoje';
                    } elseif ($dias <= 7) {
                        $ico_bg = 'rgba(180,83,9,.1)'; $ico_c = 'var(--warn)';
                        $val_c  = 'var(--warn)';
                        $quando = $dias <= 1 ? 'Amanhã' : 'em '.round($dias).' dias';
                    } else {
                        $ico_bg = 'rgba(5,150,105,.1)'; $ico_c = 'var(--ac3)';
                        $val_c  = 'var(--ac3)';
                        $quando = 'em '.round($dias).' dias';
                    }
                ?>
                <div class="alert-row">
                    <div class="alert-ico" style="background:<?php echo $ico_bg; ?>;color:<?php echo $ico_c; ?>">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="alert-info">
                        <div class="alert-ttl">Fatura #<?php echo htmlspecialchars($fat['numero_fatura']); ?></div>
                        <div class="alert-dt">
                            <?php echo date('d/m/Y', strtotime($fat['data_vencimento'])); ?>
                            &bull; <?php echo $quando; ?>
                        </div>
                    </div>
                    <div class="alert-val" style="color:<?php echo $val_c; ?>">
                        R$&nbsp;<?php echo number_format((float)$fat['valor_total'],2,',','.'); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

        </div><!-- /content -->
    </main><!-- /main -->

<!-- ════════════════════════════════════════════════════════════════════
     CHARTS JS — cores dinâmicas por tema + rebuildCharts()
════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
    // ── Dados vindos do PHP ──────────────────────────────────────────
    const meses  = <?php echo json_encode($meses); ?>;
    const valores = <?php echo json_encode($valores); ?>;
    const statsData = [
        <?php echo $stats['em_andamento'].','.
                        $stats['concluido'].','.
                        $stats['pendente']; ?>
    ];

    // ── Detecta tema atual ───────────────────────────────────────────
    function isDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    // ── Paleta por tema ──────────────────────────────────────────────
    function palette() {
        const d = isDark();
        return {
            line: d ? '#6b85ff' : '#4f6ef7',
            lineAlpha: d ? 'rgba(107,133,255,0.10)' : 'rgba(79,110,247,0.10)',
            grid: d ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)',
            tick: d ? '#545f7e' : '#9ca3af',
            tooltip: {
                bg: d ? '#1c2338' : '#ffffff',
                border: d ? 'rgba(255,255,255,0.1)' : 'rgba(79,110,247,0.12)',
                title: d ? '#9aa5c4' : '#6b7a9f',
                body:  d ? '#dde3f5' : '#0f1730',
            },
            doughnut: ['#fbbf24','#34d399','#6b85ff'],
            legendColor: d ? '#9aa5c4' : '#6b7a9f',
        };
    }

    // ── Shared chart defaults ────────────────────────────────────────
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    function tooltipOptions(p) {
        return {
            backgroundColor: p.tooltip.bg,
            borderColor:     p.tooltip.border,
            borderWidth:     1,
            titleColor:      p.tooltip.title,
            bodyColor:       p.tooltip.body,
            padding:         10,
            cornerRadius:    8,
        };
    }

    // ── Build Line chart ─────────────────────────────────────────────
    let gastosChart, sistemasChart;

    function buildGastos(p) {
        const ctx = document.getElementById('gastosChart').getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Gastos',
                    data: valores,
                    borderColor: p.line,
                    backgroundColor: p.lineAlpha,
                    borderWidth: 2.5,
                    pointBackgroundColor: p.line,
                    pointBorderColor: isDark() ? '#0e1220' : '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.38,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipOptions(p),
                        callbacks: {
                            label: ctx => ' R$ ' + ctx.raw.toFixed(2).replace('.', ',')
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: p.grid },
                        ticks: { color: p.tick, font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: p.grid },
                        ticks: {
                            color: p.tick, font: { size: 11 },
                            callback: v => 'R$ ' + v
                        }
                    }
                }
            }
        });
    }

    function buildSistemas(p) {
        const ctx = document.getElementById('sistemasChart').getContext('2d');
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Em Andamento', 'Concluído', 'Pendente'],
                datasets: [{
                    data: statsData,
                    backgroundColor: p.doughnut,
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: p.legendColor,
                            font: { size: 12 },
                            boxWidth: 10, boxHeight: 10,
                            padding: 14,
                        }
                    },
                    tooltip: tooltipOptions(p),
                }
            }
        });
    }

    // ── Inicializar ──────────────────────────────────────────────────
    let p = palette();
    gastosChart   = buildGastos(p);
    sistemasChart = buildSistemas(p);

    // ── Rebuild ao trocar tema (chamado pelo footer.js) ───────────────
    window.rebuildCharts = function (novTema) {
        // Atualiza ícone do toggle
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = novTema === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }

        // Destrói e recria com novo palette
        gastosChart.destroy();
        sistemasChart.destroy();
        const np = palette();
        gastosChart   = buildGastos(np);
        sistemasChart = buildSistemas(np);
    };

})();
</script>

<?php require_once '../includes/footer.php'; ?>
