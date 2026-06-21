<?php
$page_title = 'Newsletter';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$stats = getNewsletterStats();
$config = getNewsletterConfig();

// Busca últimas campanhas
$campanhas = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM newsletter_envios WHERE campanha_id = c.id) as total_enviados,
           (SELECT COUNT(*) FROM newsletter_envios WHERE campanha_id = c.id AND status = 'aberto') as total_abertos,
           (SELECT COUNT(*) FROM newsletter_envios WHERE campanha_id = c.id AND status = 'clicou') as total_cliques
    FROM newsletter_campanhas c
    ORDER BY c.created_at DESC LIMIT 10
");

// Busca últimos inscritos
$inscritos = $conn->query("
    SELECT * FROM newsletter_inscritos 
    ORDER BY created_at DESC LIMIT 10
");

// Templates para quick access (usando os mesmos cores dos templates)
$quick_templates = [
    ['nome' => 'Elegante', 'icone' => 'fa-envelope-open-text', 'cor' => '#4361ee', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #4b62a2 100%)', 'link' => 'criar_campanha.php?template=padrao'],
    ['nome' => 'Blog', 'icone' => 'fa-pen-fancy', 'cor' => '#f97316', 'gradient' => 'linear-gradient(135deg, #f97316 0%, #fbbf24 100%)', 'link' => 'criar_campanha.php?template=blog'],
    ['nome' => 'Oferta', 'icone' => 'fa-gift', 'cor' => '#10b981', 'gradient' => 'linear-gradient(135deg, #10b981 0%, #34d399 100%)', 'link' => 'criar_campanha.php?template=promocao'],
    ['nome' => 'Newsletter', 'icone' => 'fa-calendar-check', 'cor' => '#5c8df6', 'gradient' => 'linear-gradient(135deg, #5c78f6 0%, #a78bfa 100%)', 'link' => 'criar_campanha.php?template=newsletter'],
    ['nome' => 'Aviso', 'icone' => 'fa-bell', 'cor' => '#ef4444', 'gradient' => 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)', 'link' => 'criar_campanha.php?template=aviso'],
    ['nome' => 'Convite', 'icone' => 'fa-envelope-open-text', 'cor' => '#ec4899', 'gradient' => 'linear-gradient(135deg, #ec4899 0%, #f472b6 100%)', 'link' => 'criar_campanha.php?template=convite'],
];
?>

<style>
/* ===== RESET E VARIÁVEIS ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-primary: #f8faff;
    --bg-secondary: #ffffff;
    --text-primary: #333333;
    --text-secondary: #666666;
    --text-muted: #999999;
    --border: #eef2f6;
    --card-shadow: 0 20px 40px rgba(0,0,0,0.05);
    --card-shadow-hover: 0 30px 60px rgba(0,0,0,0.1);
}
body.dark {
    --card-bg: #1e1e2f;
    --card-border: #333;
    --card-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

/* ===== CONFIG BAR ===== */
.config-bar {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 25px 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    box-shadow: var(--card-shadow);
}

.config-info {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.config-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.config-item i {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--card-bg);
    color: #4361ee;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.config-badge {
    background: linear-gradient(135deg, #667eea 0%, #4b67a2 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 10px 20px rgba(102,126,234,0.2);
}

.config-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(102,126,234,0.3);
}

/* ===== QUICK TEMPLATES ===== */
.quick-templates {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 15px;
    margin-bottom: 40px;
}

.quick-template {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 20px 15px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: var(--card-shadow);
    color: inherit; /* mantém o texto de acordo com o body */
}

.quick-template:hover {
    transform: translateY(-5px);
    box-shadow: var(--card-shadow-hover);
    border-color: transparent;
}

.quick-template-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.quick-template:nth-child(1) .quick-template-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.quick-template:nth-child(2) .quick-template-icon { background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%); }
.quick-template:nth-child(3) .quick-template-icon { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.quick-template:nth-child(4) .quick-template-icon { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
.quick-template:nth-child(5) .quick-template-icon { background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); }
.quick-template:nth-child(6) .quick-template-icon { background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); }

.quick-template span {
    color: var(--text-primary);
    font-size: 0.9rem;
    font-weight: 500;
    display: block;
}

.quick-template small {
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-top: 5px;
    display: block;
}

/* ===== STATS CARDS ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 24px;
    padding: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card:nth-child(1)::before { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card:nth-child(2)::before { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
.stat-card:nth-child(3)::before { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.stat-card:nth-child(4)::before { background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%); }

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-shadow-hover);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 20px;
}

.stat-card:nth-child(1) .stat-icon { 
    background: rgba(102, 126, 234, 0.1); 
    color: #667eea;
}
.stat-card:nth-child(2) .stat-icon { 
    background: rgba(139, 92, 246, 0.1); 
    color: #5c92f6;
}
.stat-card:nth-child(3) .stat-icon { 
    background: rgba(16, 185, 129, 0.1); 
    color: #10b981;
}
.stat-card:nth-child(4) .stat-icon { 
    background: rgba(249, 115, 22, 0.1); 
    color: #f97316;
}

.stat-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.stat-trend {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
    padding-top: 15px;
    border-top: 1px solid var(--border);
}

.stat-trend.positive {
    color: #10b981;
}

.stat-trend i {
    font-size: 0.8rem;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 40px;
    flex-wrap: wrap;
    
}

.btn-action {
    padding: 14px 28px;
    border-radius: 50px;
    border: none;
    background: var(--card-bg);
    color: var(--text-primary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    font-size: 0.95rem;
    border: 1px solid var(--border);
    box-shadow: var(--card-shadow);
}

.btn-action i {
    color: #4361ee;
    font-size: 1.1rem;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow-hover);
    border-color: transparent;
    background: linear-gradient(135deg, #667eea 0%, #4b62a2 100%);
    color: white;
}

.btn-action:hover i {
    color: white;
}

.btn-action.primary {
    background: linear-gradient(135deg, #667eea 0%, #4b5fa2 100%);
    color: white;
    border: none;
}

.btn-action.primary i {
    color: white;
}

.btn-action.primary:hover {
    box-shadow: 0 20px 40px rgba(102,126,234,0.3);
}

/* ===== TABLES ===== */
.table-container {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    overflow: hidden;
    margin-top: 30px;
    box-shadow: var(--card-shadow);
}

.table-header {
    padding: 25px 30px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card-bg);
}

.table-title {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-title i {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--card-bg);
    color: #4361ee;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-view-all {
    padding: 10px 20px;
    border-radius: 50px;
    background: #f8faff;
    color: #4361ee;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: 1px solid var(--border);
}

.btn-view-all:hover {
    background: linear-gradient(135deg, #667eea 0%, #4b82a2 100%);
    color: white;
    border-color: transparent;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: var(--card-bg);
}

th {
    text-align: left;
    padding: 16px 30px;
    background: var(--card-bg);
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9rem;
    border-bottom: 1px solid var(--border);
}

td {
    padding: 20px 30px;
    border-bottom: 1px solid var(--border);
    color: var(--text-primary);
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background: #f8faff;
}

/* ===== STATUS BADGES ===== */
.status-badge {
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-ativo, .status-enviado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-inativo, .status-cancelada {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-pendente, .status-agendada {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-rascunho {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.status-enviando {
    background: rgba(139, 92, 246, 0.1);
    color: #5c9ff6;
}

/* ===== PROGRESS BAR ===== */
.progress-bar {
    width: 100%;
    height: 6px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #4b6ea2 100%);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* ===== BUTTONS ===== */
.btn-icon {
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0 3px;
    text-decoration: none;
    display: inline-block;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #4b67a2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

/* ===== EMPTY STATES ===== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 20px;
}

/* ===== INFO CARDS ===== */
.info-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 40px;
}

.info-card {
    background: var(--card-bg);;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--card-shadow);
}

.info-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    font-size: 1.3rem;
}

.info-card:nth-child(1) .info-card-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.info-card:nth-child(2) .info-card-icon {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.info-card:nth-child(3) .info-card-icon {
    background: rgba(139, 92, 246, 0.1);
    color: #5c8df6;
}

.info-card h4 {
    color: var(--text-primary);
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.info-card p {
    color: var(--text-muted);
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .quick-templates {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .info-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .quick-templates {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
    
    .config-info {
        flex-direction: column;
        gap: 15px;
    }
    
    .info-cards {
        grid-template-columns: 1fr;
    }
    
    th, td {
        padding: 15px 20px;
    }
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-envelope-open-text" style="color: #4361ee; margin-right: 10px;"></i>
            Newsletter
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Config Bar -->
        <div class="config-bar">
            <div class="config-info">
                <div class="config-item">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo $config['remetente_nome'] ?? 'Não configurado'; ?> &lt;<?php echo $config['remetente_email'] ?? '—'; ?>&gt;</span>
                </div>
                <div class="config-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span><?php echo $config['limite_por_minuto'] ?? '—'; ?>/min • <?php echo $config['limite_por_hora'] ?? '—'; ?>/h</span>
                </div>
                <div class="config-item">
                    <i class="fas fa-database"></i>
                    <span><?php echo number_format($stats['total_inscritos']); ?> inscritos</span>
                </div>
            </div>
            <a href="config_smtp.php" class="config-badge">
                <i class="fas fa-cog"></i> Configurar SMTP
            </a>
        </div>

        <!-- Quick Templates (igual ao design dos templates) -->
        <div class="quick-templates">
            <?php foreach($quick_templates as $template): ?>
            <a href="<?php echo $template['link']; ?>" class="quick-template" title="Usar <?php echo $template['nome']; ?>">
                <div class="quick-template-icon">
                    <i class="fas <?php echo $template['icone']; ?>"></i>
                </div>
                <span><?php echo $template['nome']; ?></span>
                <small>Template</small>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_inscritos']); ?></div>
                <div class="stat-label">Total de Inscritos</div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i> +<?php echo $stats['hoje'] ?? 0; ?> hoje
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['confirmados'] ?? 0); ?></div>
                <div class="stat-label">Confirmados</div>
                <div class="stat-trend" style="color: #f97316;">
                    <i class="fas fa-clock"></i> <?php echo $stats['nao_confirmados'] ?? 0; ?> pendentes
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_campanhas']); ?></div>
                <div class="stat-label">Campanhas</div>
                <div class="stat-trend" style="color: #8b5cf6;">
                    <i class="fas fa-calendar"></i> <?php echo $stats['campanhas_mes'] ?? 0; ?> este mês
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?php echo $stats['taxa_abertura']; ?>%</div>
                <div class="stat-label">Taxa de Abertura</div>
                <div class="stat-trend" style="color: #10b981;">
                    <i class="fas fa-eye"></i> <?php echo number_format($stats['total_envios'] ?? 0); ?> envios
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="criar_campanha.php" class="btn-action primary">
                <i class="fas fa-plus"></i> Nova Campanha
            </a>
            <a href="inscritos.php" class="btn-action">
                <i class="fas fa-list"></i> Gerenciar Inscritos
            </a>
            <a href="importar.php" class="btn-action">
                <i class="fas fa-file-import"></i> Importar Lista
            </a>
            <a href="templates.php" class="btn-action">
                <i class="fas fa-paint-brush"></i> Templates
            </a>
            <a href="relatorios.php" class="btn-action">
                <i class="fas fa-chart-bar"></i> Relatórios
            </a>
        </div>

        <!-- Últimas Campanhas -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-bullhorn"></i> Últimas Campanhas
                </h3>
                <a href="campanhas.php" class="btn-view-all">
                    Ver todas <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Campanha</th>
                        <th>Status</th>
                        <th>Enviados</th>
                        <th>Abertos</th>
                        <th>Cliques</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($campanhas->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Nenhuma campanha criada ainda</p>
                            <a href="criar_campanha.php" class="btn-action primary" style="display: inline-block; padding: 12px 30px;">
                                <i class="fas fa-plus"></i> Criar Primeira Campanha
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($camp = $campanhas->fetch_assoc()): 
                            $status_class = '';
                            $status_text = '';
                            switch($camp['status']) {
                                case 'rascunho': 
                                    $status_class = 'status-rascunho'; 
                                    $status_text = 'Rascunho';
                                    break;
                                case 'enviando': 
                                    $status_class = 'status-enviando'; 
                                    $status_text = 'Enviando';
                                    break;
                                case 'enviada': 
                                    $status_class = 'status-enviado'; 
                                    $status_text = 'Enviada';
                                    break;
                                case 'agendada': 
                                    $status_class = 'status-pendente'; 
                                    $status_text = 'Agendada';
                                    break;
                                case 'cancelada': 
                                    $status_class = 'status-inativo'; 
                                    $status_text = 'Cancelada';
                                    break;
                            }
                            $taxa_abertura = $camp['total_envios'] > 0 ? round(($camp['total_abertos'] / $camp['total_envios']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--text-primary); font-size: 1rem;"><?php echo $camp['titulo']; ?></strong><br>
                                <span style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $camp['assunto']; ?></span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                                <?php if($camp['status'] == 'agendada' && $camp['data_agendamento']): ?>
                                <br><small style="color: var(--text-muted); font-size: 0.7rem;">
                                    <?php echo date('d/m H:i', strtotime($camp['data_agendamento'])); ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo number_format($camp['total_enviados']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo number_format($camp['total_abertos']); ?></strong>
                                <br><small style="color: var(--text-muted);"><?php echo $taxa_abertura; ?>%</small>
                                <?php if($camp['status'] == 'enviando'): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $taxa_abertura; ?>%"></div>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo number_format($camp['total_cliques']); ?></strong>
                            </td>
                            <td>
                                <span style="font-weight: 500;"><?php echo date('d/m/Y', strtotime($camp['created_at'])); ?></span><br>
                                <span style="color: var(--text-muted); font-size: 0.85rem;"><?php echo date('H:i', strtotime($camp['created_at'])); ?></span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="visualizar_campanha.php?id=<?php echo $camp['id']; ?>" class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if($camp['status'] == 'rascunho'): ?>
                                    <a href="editar_campanha.php?id=<?php echo $camp['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="enviar_campanha.php?id=<?php echo $camp['id']; ?>" class="btn-icon" title="Enviar" style="color: #10b981;">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                    <a href="excluir_campanha.php?id=<?php echo $camp['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta campanha?')" style="color: #ef4444;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if($camp['status'] == 'enviada'): ?>
                                    <a href="relatorio_campanha.php?id=<?php echo $camp['id']; ?>" class="btn-icon" title="Relatório">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Últimos Inscritos -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-user-plus"></i> Últimos Inscritos
                </h3>
                <a href="inscritos.php" class="btn-view-all">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Confirmação</th>
                        <th>Origem</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($inscritos->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>Nenhum inscrito ainda</p>
                            <p style="font-size: 0.9rem; color: var(--text-muted);">Os formulários de newsletter irão aparecer aqui</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($insc = $inscritos->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo $insc['nome']; ?></strong>
                            </td>
                            <td>
                                <span style="color: #4361ee;"><?php echo $insc['email']; ?></span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $insc['status'] == 'ativo' ? 'status-ativo' : 'status-inativo'; ?>">
                                    <?php echo ucfirst($insc['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $insc['confirmado'] ? 'status-ativo' : 'status-pendente'; ?>">
                                    <?php echo $insc['confirmado'] ? 'Confirmado' : 'Pendente'; ?>
                                </span>
                                <?php if($insc['data_confirmacao']): ?>
                                <br><small style="color: var(--text-muted);"><?php echo date('d/m/Y', strtotime($insc['data_confirmacao'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="background: #f8faff; color: #4361ee; padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 500;">
                                    <?php echo ucfirst($insc['origem']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 500;"><?php echo date('d/m/Y', strtotime($insc['created_at'])); ?></span><br>
                                <span style="color: var(--text-muted); font-size: 0.85rem;"><?php echo date('H:i', strtotime($insc['created_at'])); ?></span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="editar_inscrito.php?id=<?php echo $insc['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="excluir_inscrito.php?id=<?php echo $insc['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este inscrito?')" style="color: #ef4444;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Info Cards (Dicas) -->
        <div class="info-cards">
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h4>Use Templates Modernos</h4>
                <p>Nossos templates clean aumentam o engajamento em até 40% e melhoram a experiência do leitor.</p>
            </div>
            
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4>Acompanhe Resultados</h4>
                <p>Monitore aberturas e cliques para otimizar suas campanhas e entender melhor seu público.</p>
            </div>
            
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h4>Melhor Horário</h4>
                <p>Terças e quintas às 10h têm as melhores taxas de abertura. Programe suas campanhas!</p>
            </div>
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
        
        // Animação
        themeToggle.style.transform = 'scale(1.1)';
        setTimeout(() => {
            themeToggle.style.transform = 'scale(1)';
        }, 200);
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>