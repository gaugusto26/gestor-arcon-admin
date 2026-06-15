<?php
$page_title = 'Gerenciar Status dos Sistemas';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';
require_once '../../../../config.php';

$status_list = getTodosStatus();

// Filtros
$filtro_status = isset($_GET['status']) ? limparInput($_GET['status']) : '';
$filtro_cliente = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

$filtros = [
    'status' => $filtro_status,
    'cliente_id' => $filtro_cliente,
    'busca' => $busca
];

$sistemas = getSistemasParaGerenciar($filtros);

// Estatísticas rápidas
$stats = [
    'total' => 0,
    'aguardando' => 0,
    'desenvolvimento' => 0,
    'teste' => 0,
    'homologacao' => 0,
    'concluido' => 0
];

$sistemas->data_seek(0);
while ($s = $sistemas->fetch_assoc()) {
    $stats['total']++;
    if (in_array($s['status'], ['aguardando_inicio', 'reuniao_inicial', 'levantamento_requisitos'])) {
        $stats['aguardando']++;
    } elseif (in_array($s['status'], ['desenvolvimento', 'desenvolvimento_frontend', 'desenvolvimento_backend', 'integracao_apis'])) {
        $stats['desenvolvimento']++;
    } elseif (in_array($s['status'], ['testes_internos', 'ambiente_teste'])) {
        $stats['teste']++;
    } elseif (in_array($s['status'], ['homologacao_cliente', 'ajustes_finais', 'aguardando_aprovacao'])) {
        $stats['homologacao']++;
    } elseif ($s['status'] == 'concluido') {
        $stats['concluido']++;
    }
}
$sistemas->data_seek(0);
?>

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    transition: all 0.3s ease;
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent);
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin: 0 auto 12px;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Filtros */
.filtros-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: 1fr 200px 200px auto;
    gap: 15px;
}

.filtro-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtro-item label {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

.filtro-item select, .filtro-item input {
    padding: 10px 15px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-primary);
}

.btn-filtro {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    background: var(--accent);
    color: white;
    cursor: pointer;
    font-weight: 600;
    height: 42px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-limpar {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

/* Tabela */
.table-container {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    margin-top: 30px;
}

.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    color: var(--accent);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 16px 25px;
    background: var(--hover);
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9rem;
}

td {
    padding: 16px 25px;
    border-bottom: 1px solid var(--border);
    color: var(--text-primary);
}

tr:hover td {
    background: var(--hover);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge i {
    font-size: 0.7rem;
}

/* Progresso */
.progress-container {
    width: 100%;
    min-width: 150px;
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
    background: var(--accent-gradient);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 2px;
}

/* Ações */
.btn-icon {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-primary);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-icon:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--bg-secondary);
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.2rem;
    color: var(--text-primary);
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.status-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 5px;
}

.status-option {
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.status-option:hover {
    border-color: var(--accent);
    background: var(--hover);
}

.status-option.selected {
    border-color: var(--accent);
    background: var(--accent-light);
}

.status-option .status-nome {
    font-weight: 600;
    margin-bottom: 3px;
}

.status-option .status-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Timeline */
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
    background: var(--accent-gradient);
    opacity: 0.3;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-dot {
    position: absolute;
    left: -30px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--bg-secondary);
    border: 3px solid var(--accent);
}

.timeline-content {
    background: var(--hover);
    border-radius: 10px;
    padding: 15px;
}

.timeline-date {
    font-size: 0.8rem;
    color: var(--accent);
    margin-bottom: 5px;
}

.timeline-status {
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-obs {
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .filtros-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-chart-line" style="margin-right: 10px;"></i>
            Gerenciar Status dos Sistemas
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
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
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $stats['aguardando']; ?></div>
                <div class="stat-label">Aguardando Início</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-code"></i>
                </div>
                <div class="stat-value"><?php echo $stats['desenvolvimento']; ?></div>
                <div class="stat-label">Em Desenvolvimento</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="stat-value"><?php echo $stats['teste']; ?></div>
                <div class="stat-label">Em Teste</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?php echo $stats['homologacao']; ?></div>
                <div class="stat-label">Homologação</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-value"><?php echo $stats['concluido']; ?></div>
                <div class="stat-label">Concluídos</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="busca" placeholder="Cliente, empresa ou sistema" value="<?php echo $busca; ?>">
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Status</label>
                    <select name="status">
                        <option value="">Todos os status</option>
                        <?php foreach ($status_list as $key => $status): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filtro_status == $key ? 'selected' : ''; ?>>
                            <?php echo $status['nome']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-building"></i> Cliente ID</label>
                    <input type="number" name="cliente_id" placeholder="ID do cliente" value="<?php echo $filtro_cliente ?: ''; ?>">
                </div>
                
                <div class="filtro-item" style="display: flex; flex-direction: row; gap: 10px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Sistemas -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i> Sistemas em Desenvolvimento
                </h3>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Sistema / Plano</th>
                        <th>Status</th>
                        <th>Progresso</th>
                        <th>Contrato</th>
                        <th>Última Atualização</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sistemas->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px;">
                            <i class="fas fa-cubes" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
                            <h3>Nenhum sistema encontrado</h3>
                            <p style="color: var(--text-muted);">Os sistemas aparecerão aqui quando clientes assinarem contratos.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while ($s = $sistemas->fetch_assoc()): 
                            $status_info = $status_list[$s['status']] ?? [
                                'nome' => 'Status desconhecido',
                                'cor' => '#64748b',
                                'icone' => 'fa-question-circle'
                            ];
                            
                            // Calcula progresso baseado no status
                            $etapa_atual = $status_info['etapa'] ?? 0;
                            $total_etapas = count($status_list);
                            $progresso = round(($etapa_atual / $total_etapas) * 100);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($s['cliente_nome']); ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo htmlspecialchars($s['cliente_empresa'] ?: '—'); ?></small>
                            </td>
                            <td>
    <strong><?php echo htmlspecialchars($s['nome_sistema']); ?></strong><br>
    <small style="color: var(--text-muted);">
        <?php echo htmlspecialchars($s['nome_plano'] ?? 'Sem plano'); ?>
    </small>
</td>
                            <td>
                                <span class="status-badge" style="background: <?php echo $status_info['cor']; ?>20; color: <?php echo $status_info['cor']; ?>; border: 1px solid <?php echo $status_info['cor']; ?>;">
                                    <i class="fas <?php echo $status_info['icone']; ?>"></i>
                                    <?php echo $status_info['nome']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="progress-container">
                                    <div class="progress-text">
                                        <span>Progresso</span>
                                        <span><?php echo $progresso; ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progresso; ?>%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($s['numero_contrato'] ?? '—'); ?>
                                <small style="color: var(--text-muted);"><?php echo date('d/m/Y', strtotime($s['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php 
                                if ($s['ultima_atualizacao']) {
                                    echo date('d/m/Y H:i', strtotime($s['ultima_atualizacao']));
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="editar.php?id=<?php echo $s['id']; ?>" class="btn-icon" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-icon" title="Atualizar Status" onclick="abrirModalStatus(<?php echo $s['id']; ?>, '<?php echo $s['status']; ?>')">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <a href="historico.php?id=<?php echo $s['id']; ?>" class="btn-icon" title="Histórico">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <a href="detalhes.php?id=<?php echo $s['id']; ?>" class="btn-icon" title="Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Atualizar Status -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-sync-alt"></i> Atualizar Status do Sistema</h3>
            <span class="modal-close" onclick="fecharModalStatus()">&times;</span>
        </div>
        <form id="statusForm" method="POST" action="atualizar_status.php">
            <div class="modal-body">
                <input type="hidden" name="sistema_id" id="sistema_id">
                <input type="hidden" name="status_anterior" id="status_anterior">
                
                <div class="form-group">
                    <label>Novo Status</label>
                    <div class="status-options" id="statusOptions">
                        <?php foreach ($status_list as $key => $status): ?>
                        <div class="status-option" data-value="<?php echo $key; ?>" onclick="selecionarStatus(this)">
                            <div class="status-nome" style="color: <?php echo $status['cor']; ?>;">
                                <i class="fas <?php echo $status['icone']; ?>"></i>
                                <?php echo $status['nome']; ?>
                            </div>
                            <div class="status-desc"><?php echo $status['descricao']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="status_novo" id="status_novo" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Observação (opcional)</label>
                    <textarea name="observacao" class="form-control" placeholder="Motivo da alteração, observações sobre o andamento..."></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-percent"></i> Percentual de Conclusão</label>
                    <input type="number" name="percentual_concluido" class="form-control" min="0" max="100" value="0" id="percentual">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Próxima Etapa</label>
                    <input type="text" name="proxima_etapa" class="form-control" placeholder="Ex: Aguardando aprovação do layout" id="proxima_etapa">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-icon" onclick="fecharModalStatus()">Cancelar</button>
                <button type="submit" class="btn-filtro">Atualizar Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalStatus(id, statusAtual) {
    document.getElementById('sistema_id').value = id;
    document.getElementById('status_anterior').value = statusAtual;
    document.getElementById('status_novo').value = '';
    
    // Remove seleção anterior
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    document.getElementById('statusModal').style.display = 'flex';
}

function fecharModalStatus() {
    document.getElementById('statusModal').style.display = 'none';
}

function selecionarStatus(elemento) {
    // Remove seleção de todos
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Adiciona seleção no clicado
    elemento.classList.add('selected');
    
    // Atualiza o input hidden
    const valor = elemento.getAttribute('data-value');
    document.getElementById('status_novo').value = valor;
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

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