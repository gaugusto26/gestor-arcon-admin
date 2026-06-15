<?php
$page_title = 'Relatórios';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Período para análise
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '30dias';

switch($periodo) {
    case '7dias':
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
        $label_periodo = 'Últimos 7 dias';
        break;
    case '30dias':
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        $label_periodo = 'Últimos 30 dias';
        break;
    case '90dias':
        $data_inicio = date('Y-m-d', strtotime('-90 days'));
        $label_periodo = 'Últimos 90 dias';
        break;
    case 'ano':
        $data_inicio = date('Y-01-01');
        $label_periodo = 'Este ano';
        break;
    default:
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        $label_periodo = 'Últimos 30 dias';
}

// Estatísticas gerais
$stats = [
    'total_inscritos' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos")->fetch_assoc()['total'],
    'novos_periodo' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE DATE(created_at) >= '$data_inicio'")->fetch_assoc()['total'],
    'total_campanhas' => $conn->query("SELECT COUNT(*) as total FROM newsletter_campanhas")->fetch_assoc()['total'],
    'campanhas_periodo' => $conn->query("SELECT COUNT(*) as total FROM newsletter_campanhas WHERE DATE(created_at) >= '$data_inicio'")->fetch_assoc()['total'],
    'total_envios' => $conn->query("SELECT COUNT(*) as total FROM newsletter_envios")->fetch_assoc()['total'],
    'total_abertos' => $conn->query("SELECT COUNT(*) as total FROM newsletter_envios WHERE status = 'aberto'")->fetch_assoc()['total'],
    'total_cliques' => $conn->query("SELECT COUNT(*) as total FROM newsletter_envios WHERE status = 'clicou'")->fetch_assoc()['total'],
];

$stats['taxa_abertura'] = $stats['total_envios'] > 0 ? round(($stats['total_abertos'] / $stats['total_envios']) * 100, 2) : 0;
$stats['taxa_cliques'] = $stats['total_abertos'] > 0 ? round(($stats['total_cliques'] / $stats['total_abertos']) * 100, 2) : 0;

// Crescimento por mês (últimos 6 meses)
$crescimento = [];
for($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $ano = date('Y', strtotime("-$i months"));
    $mes_num = date('m', strtotime("-$i months"));
    
    $total = $conn->query("
        SELECT COUNT(*) as total 
        FROM newsletter_inscritos 
        WHERE YEAR(created_at) = $ano AND MONTH(created_at) = $mes_num
    ")->fetch_assoc()['total'];
    
    $crescimento[] = [
        'mes' => date('M/Y', strtotime("-$i months")),
        'total' => $total
    ];
}

// Top 5 campanhas com melhores taxas
$top_campanhas = $conn->query("
    SELECT c.*, 
           COUNT(e.id) as total_envios,
           SUM(CASE WHEN e.status = 'aberto' THEN 1 ELSE 0 END) as total_abertos,
           SUM(CASE WHEN e.status = 'clicou' THEN 1 ELSE 0 END) as total_cliques
    FROM newsletter_campanhas c
    LEFT JOIN newsletter_envios e ON c.id = e.campanha_id
    WHERE c.status = 'enviada'
    GROUP BY c.id
    HAVING total_envios > 0
    ORDER BY (total_abertos / total_envios) DESC
    LIMIT 5
");

// Origem dos inscritos
$origens = $conn->query("
    SELECT origem, COUNT(*) as total 
    FROM newsletter_inscritos 
    WHERE origem IS NOT NULL 
    GROUP BY origem 
    ORDER BY total DESC
");
?>

<style>
.relatorios-header {
    margin-bottom: 30px;
}

.periodo-selector {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.btn-periodo {
    padding: 8px 20px;
    border-radius: 30px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.btn-periodo:hover {
    background: var(--hover);
}

.btn-periodo.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.stat-trend {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.trend-up {
    color: #22c55e;
}

.trend-down {
    color: #ef4444;
}

.chart-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
}

.chart-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.chart-title i {
    color: var(--accent);
    margin-right: 8px;
}

.chart-bar {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.chart-label {
    width: 80px;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.chart-bar-container {
    flex: 1;
    height: 30px;
    background: var(--border);
    border-radius: 6px;
    overflow: hidden;
    margin: 0 15px;
}

.chart-bar-fill {
    height: 100%;
    background: var(--accent-gradient);
    border-radius: 6px;
    transition: width 0.3s ease;
}

.chart-value {
    width: 50px;
    color: var(--text-primary);
    font-weight: 600;
}

.double-chart-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.table-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    margin-top: 30px;
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
}

.table-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-primary);
}

.table-title i {
    color: var(--accent);
    margin-right: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 16px 20px;
    background: var(--hover);
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9rem;
}

td {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    color: var(--text-primary);
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 5px;
}

.progress-fill {
    height: 100%;
    background: var(--accent);
    border-radius: 3px;
}

.porcentagem-badge {
    background: var(--accent-light);
    color: var(--accent);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.export-btn {
    padding: 10px 20px;
    background: var(--hover);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.export-btn:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-chart-bar"></i>
            Relatórios
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
            <a href="exportar_relatorio.php?periodo=<?php echo $periodo; ?>" class="export-btn">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
        </div>
    </div>

    <div class="content-area">
        <!-- Seletor de Período -->
        <div class="periodo-selector">
            <a href="?periodo=7dias" class="btn-periodo <?php echo $periodo == '7dias' ? 'active' : ''; ?>">7 dias</a>
            <a href="?periodo=30dias" class="btn-periodo <?php echo $periodo == '30dias' ? 'active' : ''; ?>">30 dias</a>
            <a href="?periodo=90dias" class="btn-periodo <?php echo $periodo == '90dias' ? 'active' : ''; ?>">90 dias</a>
            <a href="?periodo=ano" class="btn-periodo <?php echo $periodo == 'ano' ? 'active' : ''; ?>">Este ano</a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_inscritos']); ?></div>
                <div class="stat-label">Total de Inscritos</div>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +<?php echo $stats['novos_periodo']; ?> no período
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_campanhas']); ?></div>
                <div class="stat-label">Campanhas</div>
                <div class="stat-trend">
                    <i class="fas fa-calendar"></i> <?php echo $stats['campanhas_periodo']; ?> no período
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="stat-value"><?php echo $stats['taxa_abertura']; ?>%</div>
                <div class="stat-label">Taxa de Abertura</div>
                <div class="stat-trend">
                    <i class="fas fa-eye"></i> <?php echo number_format($stats['total_abertos']); ?> abertos
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="stat-value"><?php echo $stats['taxa_cliques']; ?>%</div>
                <div class="stat-label">Taxa de Cliques</div>
                <div class="stat-trend">
                    <i class="fas fa-hand-pointer"></i> <?php echo number_format($stats['total_cliques']); ?> cliques
                </div>
            </div>
        </div>

        <!-- Gráfico de Crescimento -->
        <div class="chart-container">
            <h3 class="chart-title">
                <i class="fas fa-chart-line"></i> Crescimento de Inscritos (últimos 6 meses)
            </h3>
            
            <?php 
            $max_crescimento = max(array_column($crescimento, 'total'));
            foreach($crescimento as $item): 
                $porcentagem = $max_crescimento > 0 ? ($item['total'] / $max_crescimento) * 100 : 0;
            ?>
            <div class="chart-bar">
                <span class="chart-label"><?php echo $item['mes']; ?></span>
                <div class="chart-bar-container">
                    <div class="chart-bar-fill" style="width: <?php echo $porcentagem; ?>%;"></div>
                </div>
                <span class="chart-value"><?php echo $item['total']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Origem dos Inscritos -->
        <div class="double-chart-grid">
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-map-pin"></i> Origem dos Inscritos
                </h3>
                
                <?php 
                $total_origens = 0;
                $origens_array = [];
                while($origem = $origens->fetch_assoc()) {
                    $origens_array[] = $origem;
                    $total_origens += $origem['total'];
                }
                
                foreach($origens_array as $origem): 
                    $porcentagem = $total_origens > 0 ? round(($origem['total'] / $total_origens) * 100, 1) : 0;
                ?>
                <div class="chart-bar">
                    <span class="chart-label"><?php echo ucfirst($origem['origem']); ?></span>
                    <div class="chart-bar-container">
                        <div class="chart-bar-fill" style="width: <?php echo $porcentagem; ?>%;"></div>
                    </div>
                    <span class="chart-value"><?php echo $porcentagem; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Engajamento -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-heart"></i> Engajamento
                </h3>
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--accent);">
                        <?php echo $stats['taxa_abertura']; ?>%
                    </div>
                    <div style="color: var(--text-muted);">Taxa Média de Abertura</div>
                </div>
                
                <div style="display: flex; justify-content: space-around; margin-top: 30px;">
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">
                            <?php echo number_format($stats['total_envios']); ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">Total Enviados</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">
                            <?php echo number_format($stats['total_abertos']); ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">Total Abertos</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">
                            <?php echo number_format($stats['total_cliques']); ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">Total Cliques</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Campanhas -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-trophy"></i> Top 5 Campanhas com Melhor Engajamento
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Campanha</th>
                        <th>Data</th>
                        <th>Enviados</th>
                        <th>Abertos</th>
                        <th>Taxa</th>
                        <th>Cliques</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($top_campanhas->num_rows == 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 10px; display: block;"></i>
                            Nenhuma campanha enviada ainda
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($camp = $top_campanhas->fetch_assoc()): 
                            $taxa_abertura = $camp['total_envios'] > 0 ? round(($camp['total_abertos'] / $camp['total_envios']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $camp['titulo']; ?></strong>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($camp['data_envio'])); ?></td>
                            <td><?php echo number_format($camp['total_envios']); ?></td>
                            <td><?php echo number_format($camp['total_abertos']); ?></td>
                            <td>
                                <span class="porcentagem-badge"><?php echo $taxa_abertura; ?>%</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $taxa_abertura; ?>%;"></div>
                                </div>
                            </td>
                            <td><?php echo number_format($camp['total_cliques']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
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