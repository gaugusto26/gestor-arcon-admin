<?php
$page_title = 'Detalhes do Sistema';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /newsoftware/cliente/modules/sistemas/index.php');
    exit;
}

$cid = (int)$_SESSION['cliente_id'];
$contrato_id = (int)$_GET['id'];

// Busca detalhes incluindo status real
$stmt = $conn->prepare("
    SELECT c.*,
           c.created_at as data_contrato,
           pc.nome_plano,
           pc.descricao as plano_descricao,
           pc.valor_plano,
           s.status as status_real,
           s.percentual_concluido,
           s.proxima_etapa,
           s.ultima_atualizacao
    FROM contratos c
    LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
    LEFT JOIN cliente_sistemas s ON s.cliente_id = c.cliente_id 
                                 AND s.plano_contratado_id = c.plano_contratado_id
    WHERE c.id = ? AND c.cliente_id = ? AND c.status = 'assinado'
");
$stmt->bind_param("ii", $contrato_id, $cid);
$stmt->execute();
$sistema = $stmt->get_result()->fetch_assoc();

if (!$sistema) {
    header('Location: /newsoftware/cliente/modules/sistemas/index.php');
    exit;
}

// Usa progresso real do banco
$progresso = $sistema['percentual_concluido'] ?? 0;

// Datas
$data_contrato_str = $sistema['data_contrato'] ?? $sistema['created_at'] ?? date('Y-m-d');
$data_inicio = new DateTime($data_contrato_str);
$dias_estimados = 45;
$data_previsao = clone $data_inicio;
$data_previsao->modify("+{$dias_estimados} days");

$hoje = new DateTime();
$dias_restantes = max(0, (int)$hoje->diff($data_previsao)->days);
$atrasado = $hoje > $data_previsao;

$prazo_class = '';
$prazo_texto = '';
if ($atrasado) {
    $prazo_class = 'danger';
    $prazo_texto = 'Atrasado';
} elseif ($dias_restantes <= 7) {
    $prazo_class = 'warning';
    $prazo_texto = 'Próximo do prazo';
} else {
    $prazo_class = 'normal';
    $prazo_texto = 'No prazo';
}

// Status labels
$status_labels = [
    'aguardando_inicio'        => ['label' => 'Aguardando Início',      'class' => 'pendente',  'icon' => 'fa-clock'],
    'reuniao_inicial'          => ['label' => 'Reunião Inicial',         'class' => 'andamento', 'icon' => 'fa-users'],
    'levantamento_requisitos'  => ['label' => 'Levantamento',            'class' => 'andamento', 'icon' => 'fa-clipboard-list'],
    'design_aprovacao'         => ['label' => 'Design p/ Aprovação',     'class' => 'andamento', 'icon' => 'fa-paint-brush'],
    'design_aprovado'          => ['label' => 'Design Aprovado',         'class' => 'concluido', 'icon' => 'fa-check-circle'],
    'desenvolvimento'          => ['label' => 'Em Desenvolvimento',      'class' => 'andamento', 'icon' => 'fa-code'],
    'desenvolvimento_frontend' => ['label' => 'Frontend',                'class' => 'andamento', 'icon' => 'fa-window-maximize'],
    'desenvolvimento_backend'  => ['label' => 'Backend',                 'class' => 'andamento', 'icon' => 'fa-server'],
    'integracao_apis'          => ['label' => 'Integração de APIs',      'class' => 'andamento', 'icon' => 'fa-plug'],
    'testes_internos'          => ['label' => 'Testes Internos',         'class' => 'andamento', 'icon' => 'fa-vial'],
    'ambiente_teste'           => ['label' => 'Ambiente de Teste',       'class' => 'andamento', 'icon' => 'fa-flask'],
    'homologacao_cliente'      => ['label' => 'Homologação',             'class' => 'andamento', 'icon' => 'fa-user-check'],
    'ajustes_finais'           => ['label' => 'Ajustes Finais',          'class' => 'andamento', 'icon' => 'fa-tools'],
    'treinamento_cliente'      => ['label' => 'Treinamento',             'class' => 'andamento', 'icon' => 'fa-chalkboard-teacher'],
    'aguardando_aprovacao'     => ['label' => 'Aguardando Aprovação',    'class' => 'pendente',  'icon' => 'fa-hourglass-half'],
    'aprovado_cliente'         => ['label' => 'Aprovado pelo Cliente',   'class' => 'concluido', 'icon' => 'fa-thumbs-up'],
    'implantacao'              => ['label' => 'Implantação',             'class' => 'andamento', 'icon' => 'fa-rocket'],
    'concluido'                => ['label' => 'Concluído',               'class' => 'concluido', 'icon' => 'fa-check-double'],
    'manutencao'               => ['label' => 'Em Manutenção',           'class' => 'andamento', 'icon' => 'fa-tools'],
    'pausado'                  => ['label' => 'Pausado',                 'class' => 'pendente',  'icon' => 'fa-pause-circle'],
    'cancelado'                => ['label' => 'Cancelado',               'class' => 'cancelado', 'icon' => 'fa-ban'],
];
$status_real = $sistema['status_real'] ?? 'aguardando_inicio';
$st = $status_labels[$status_real] ?? ['label' => ucfirst($status_real), 'class' => 'andamento', 'icon' => 'fa-circle'];
?>

<style>
.detalhes-container {
    max-width: 1000px;
    margin: 0 auto;
}

.detalhes-header {
    margin-bottom: 30px;
}

.detalhes-titulo {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--tx);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.detalhes-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    color: var(--tx3);
    font-size: 0.9rem;
}

.detalhes-meta i {
    color: var(--ac);
    margin-right: 5px;
}

.status-badge-large {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    border-radius: 40px;
    font-size: 0.9rem;
    font-weight: 600;
}

.status-badge-large.andamento {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid #f97316;
}

.status-badge-large.concluido {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.status-badge-large.pendente {
    background: rgba(67, 97, 238, 0.1);
    color: #4361ee;
    border: 1px solid #4361ee;
}

.status-badge-large.cancelado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 20px;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.info-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 12px;
}

.info-label {
    font-size: 0.7rem;
    color: var(--tx3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--tx);
    font-family: var(--mono);
}

.info-value small {
    font-size: 0.8rem;
    font-weight: 400;
    color: var(--tx3);
    font-family: var(--font);
    margin-left: 5px;
}

.progress-section {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.progress-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--tx);
    display: flex;
    align-items: center;
    gap: 8px;
}

.progress-title i { color: var(--ac); }

.progress-percent {
    font-size: 2rem;
    font-weight: 700;
    color: var(--ac);
    font-family: var(--mono);
}

.progress-bar-large {
    width: 100%;
    height: 12px;
    background: var(--surf3);
    border-radius: 6px;
    overflow: hidden;
    margin: 20px 0;
}

.progress-fill-large {
    height: 100%;
    background: linear-gradient(90deg, var(--ac), var(--ac2));
    border-radius: 6px;
    transition: width 0.6s ease;
}

.progress-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--bdr);
}

.progress-stat-item { text-align: center; }

.progress-stat-label {
    font-size: 0.75rem;
    color: var(--tx3);
    text-transform: uppercase;
    margin-bottom: 5px;
}

.progress-stat-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--tx);
}

.progress-stat-value.warning { color: #f97316; }
.progress-stat-value.danger  { color: #ef4444; }

/* Próxima etapa */
.proxima-etapa-box {
    background: rgba(102, 126, 234, 0.05);
    border: 1px solid var(--ac);
    border-radius: 12px;
    padding: 15px 20px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.proxima-etapa-box i {
    color: var(--ac);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.proxima-etapa-box span {
    font-size: 0.9rem;
    color: var(--tx2);
}

.proxima-etapa-box strong {
    color: var(--tx);
}

.timeline-section {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.timeline-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.timeline-title i { color: var(--ac); }

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, var(--ac) 0%, var(--ac2) 100%);
    opacity: 0.3;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item:last-child { padding-bottom: 0; }

.timeline-dot {
    position: absolute;
    left: -30px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--surf);
    border: 3px solid var(--ac);
    z-index: 2;
}

.timeline-dot.completed {
    background: var(--ac);
    border-color: var(--ac);
}

.timeline-content {
    background: var(--surf2);
    border-radius: 12px;
    padding: 15px 20px;
}

.timeline-date {
    font-size: 0.8rem;
    color: var(--ac);
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-event {
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 5px;
}

.timeline-description {
    font-size: 0.85rem;
    color: var(--tx2);
}

.contrato-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.contrato-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.contrato-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--tx);
    display: flex;
    align-items: center;
    gap: 8px;
}

.contrato-header h3 i { color: var(--ac); }

.contrato-link {
    padding: 8px 16px;
    border-radius: 30px;
    background: transparent;
    border: 1px solid var(--bdr2);
    color: var(--tx2);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.contrato-link:hover {
    background: var(--ac);
    border-color: var(--ac);
    color: white;
}

.contrato-detalhes {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.contrato-detalhe-item {
    padding: 12px;
    background: var(--surf2);
    border-radius: 12px;
}

.contrato-detalhe-label {
    font-size: 0.7rem;
    color: var(--tx3);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.contrato-detalhe-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--tx);
}

.acesso-card {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%);
    border: 1px solid var(--ac);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.acesso-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.acesso-header i {
    font-size: 1.5rem;
    color: var(--ac);
}

.acesso-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--tx);
}

.acesso-botoes {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-acesso {
    padding: 12px 25px;
    border-radius: 40px;
    background: var(--ac);
    color: white;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-acesso:hover {
    background: var(--ac2);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

.btn-acesso-outline {
    background: transparent;
    border: 1px solid var(--ac);
    color: var(--ac);
}

.btn-acesso-outline:hover {
    background: var(--ac);
    color: white;
}

.observacoes-box {
    background: rgba(102, 126, 234, 0.03);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
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

.observacoes-box h4 i { color: var(--ac); }

.observacoes-box p {
    color: var(--tx2);
    font-size: 0.9rem;
    line-height: 1.6;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 22px;
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
    transform: translateY(-2px);
}

.btn-primary {
    background: var(--ac);
    color: white;
}

.btn-primary:hover {
    background: var(--ac2);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .info-grid          { grid-template-columns: repeat(2, 1fr); }
    .progress-stats     { grid-template-columns: 1fr; gap: 10px; }
    .contrato-detalhes  { grid-template-columns: 1fr; }
    .acesso-botoes      { flex-direction: column; }
    .btn-acesso         { width: 100%; justify-content: center; }
    .detalhes-titulo    { font-size: 1.4rem; }
}

@media (max-width: 480px) {
    .info-grid       { grid-template-columns: 1fr; }
    .action-buttons  { flex-direction: column; }
    .btn             { width: 100%; justify-content: center; }
}
</style>

<main class="main" id="main">

    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-cube"></i></div>
            <span class="top-pg-title">Detalhes do Sistema</span>
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
                    <a href="/newsoftware/cliente/modules/perfil/index.php"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                    <a href="/newsoftware/cliente/modules/assinatura/index.php"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                    <hr>
                    <a href="/newsoftware/cliente/logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="detalhes-container">

            <div class="action-buttons">
                <a href="/newsoftware/cliente/modules/sistemas/index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="javascript:window.print()" class="btn btn-outline">
                    <i class="fas fa-print"></i> Imprimir
                </a>
            </div>

            <!-- Header -->
            <div class="detalhes-header">
                <div class="detalhes-titulo">
                    <?php echo htmlspecialchars($sistema['titulo'] ?? 'Sistema Personalizado'); ?>
                    <span class="status-badge-large <?php echo $st['class']; ?>">
                        <i class="fas <?php echo $st['icon']; ?>"></i>
                        <?php echo $st['label']; ?>
                    </span>
                </div>
                <div class="detalhes-meta">
                    <span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($sistema['numero_contrato']); ?></span>
                    <span><i class="fas fa-calendar"></i> Início: <?php echo date('d/m/Y', strtotime($data_contrato_str)); ?></span>
                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($sistema['nome_plano'] ?? 'Plano Personalizado'); ?></span>
                    <?php if ($sistema['ultima_atualizacao']): ?>
                    <span><i class="fas fa-sync-alt"></i> Atualizado: <?php echo date('d/m/Y H:i', strtotime($sistema['ultima_atualizacao'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="info-label">Valor do Desenvolvimento</div>
                    <div class="info-value">R$ <?php echo number_format($sistema['valor_plano'] ?? $sistema['valor_total'] ?? 0, 2, ',', '.'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="info-label">Mensalidade</div>
                    <div class="info-value">R$ <?php echo number_format($sistema['valor_mensal'] ?? 0, 2, ',', '.'); ?> <small>/mês</small></div>
                </div>
                <div class="info-card">
                    <div class="info-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-label">Previsão de Entrega</div>
                    <div class="info-value"><?php echo $data_previsao->format('d/m/Y'); ?></div>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="progress-section">
                <div class="progress-header">
                    <div class="progress-title">
                        <i class="fas fa-chart-line"></i> Progresso do Desenvolvimento
                    </div>
                    <div class="progress-percent"><?php echo $progresso; ?>%</div>
                </div>

                <div class="progress-bar-large">
                    <div class="progress-fill-large" style="width: <?php echo $progresso; ?>%;"></div>
                </div>

                <div class="progress-stats">
                    <div class="progress-stat-item">
                        <div class="progress-stat-label">Tempo decorrido</div>
                        <div class="progress-stat-value"><?php echo $data_inicio->diff($hoje)->days; ?> dias</div>
                    </div>
                    <div class="progress-stat-item">
                        <div class="progress-stat-label">Dias restantes</div>
                        <div class="progress-stat-value <?php echo $prazo_class; ?>"><?php echo $dias_restantes; ?> dias</div>
                    </div>
                    <div class="progress-stat-item">
                        <div class="progress-stat-label">Status do prazo</div>
                        <div class="progress-stat-value <?php echo $prazo_class; ?>"><?php echo $prazo_texto; ?></div>
                    </div>
                </div>

                <?php if (!empty($sistema['proxima_etapa'])): ?>
                <div class="proxima-etapa-box">
                    <i class="fas fa-arrow-right"></i>
                    <span><strong>Próxima etapa:</strong> <?php echo htmlspecialchars($sistema['proxima_etapa']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Timeline -->
            <div class="timeline-section">
                <h3 class="timeline-title">
                    <i class="fas fa-history"></i> Linha do Tempo
                </h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot completed"></div>
                        <div class="timeline-content">
                            <div class="timeline-date"><?php echo date('d/m/Y', strtotime($data_contrato_str)); ?></div>
                            <div class="timeline-event">Contrato Assinado</div>
                            <div class="timeline-description">O contrato foi assinado e o desenvolvimento foi iniciado.</div>
                        </div>
                    </div>

                    <?php if ($sistema['ultima_atualizacao']): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot completed"></div>
                        <div class="timeline-content">
                            <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($sistema['ultima_atualizacao'])); ?></div>
                            <div class="timeline-event">Última atualização de status</div>
                            <div class="timeline-description">
                                Status atual: <strong><?php echo $st['label']; ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="timeline-item">
                        <div class="timeline-dot <?php echo $progresso >= 100 ? 'completed' : ''; ?>"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">Previsão: <?php echo $data_previsao->format('d/m/Y'); ?></div>
                            <div class="timeline-event">Entrega do Sistema</div>
                            <div class="timeline-description">Previsão de conclusão e entrega do sistema.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalhes do Contrato -->
            <div class="contrato-card">
                <div class="contrato-header">
                    <h3><i class="fas fa-file-contract"></i> Detalhes do Contrato</h3>
                    <a href="/newsoftware/cliente/modules/contratos/visualizar.php?id=<?php echo $sistema['id']; ?>" class="contrato-link">
                        <i class="fas fa-eye"></i> Ver Contrato
                    </a>
                </div>
                <div class="contrato-detalhes">
                    <div class="contrato-detalhe-item">
                        <div class="contrato-detalhe-label">Número do Contrato</div>
                        <div class="contrato-detalhe-value"><?php echo htmlspecialchars($sistema['numero_contrato']); ?></div>
                    </div>
                    <div class="contrato-detalhe-item">
                        <div class="contrato-detalhe-label">Plano Contratado</div>
                        <div class="contrato-detalhe-value"><?php echo htmlspecialchars($sistema['nome_plano'] ?? 'Personalizado'); ?></div>
                    </div>
                    <div class="contrato-detalhe-item">
                        <div class="contrato-detalhe-label">Valor Total</div>
                        <div class="contrato-detalhe-value">R$ <?php echo number_format($sistema['valor_total'] ?? 0, 2, ',', '.'); ?></div>
                    </div>
                    <div class="contrato-detalhe-item">
                        <div class="contrato-detalhe-label">Mensalidade</div>
                        <div class="contrato-detalhe-value">R$ <?php echo number_format($sistema['valor_mensal'] ?? 0, 2, ',', '.'); ?></div>
                    </div>
                </div>

                <?php if (!empty($sistema['plano_descricao'])): ?>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--bdr);">
                    <p style="color: var(--tx2); font-size: 0.9rem; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($sistema['plano_descricao'])); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Acesso (só aparece quando concluído) -->
            <?php if ($progresso >= 100 || $status_real === 'concluido'): ?>
            <div class="acesso-card">
                <div class="acesso-header">
                    <i class="fas fa-rocket"></i>
                    <h3>Sistema Disponível!</h3>
                </div>
                <p style="color: var(--tx2); margin-bottom: 20px;">
                    Seu sistema está pronto para acesso.
                </p>
                <div class="acesso-botoes">
                    <a href="#" class="btn-acesso">
                        <i class="fas fa-external-link-alt"></i> Acessar Sistema
                    </a>
                    <button class="btn-acesso btn-acesso-outline" onclick="alert('Credenciais enviadas para seu e-mail')">
                        <i class="fas fa-envelope"></i> Lembrar Credenciais
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sistema['observacoes'])): ?>
            <div class="observacoes-box">
                <h4><i class="fas fa-clipboard-list"></i> Observações</h4>
                <p><?php echo nl2br(htmlspecialchars($sistema['observacoes'])); ?></p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<script>
(function() {
    const userBtn  = document.getElementById('userBtn');
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
            window.location.href = '/newsoftware/cliente/modules/faturas/index.php';
        });
    }

    const themeTog = document.getElementById('themeTog');
    if (themeTog) {
        themeTog.addEventListener('click', () => {
            const html  = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const novo  = isDark ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = novo === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            const fd = new FormData();
            fd.append('action', 'toggle_tema');
            fd.append('tema', novo);
            fetch(window.location.pathname, { method: 'POST', body: fd }).catch(() => {});
        });
    }
})();
</script>

<?php require_once '../../includes/footer.php'; ?>