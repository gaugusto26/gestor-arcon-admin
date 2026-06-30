<?php
$page_title = 'Detalhes do Cliente';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';
require_once '../../../../admin/api/arcon-push.php';

// Garante colunas de integração Arcon
$conn->query("ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS arcon_empresa_id bigint DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS arcon_plano_saas varchar(50) DEFAULT 'free',
    ADD COLUMN IF NOT EXISTS arcon_status     varchar(30) DEFAULT 'pendente',
    ADD COLUMN IF NOT EXISTS arcon_sync_em    datetime DEFAULT NULL
");

$arcon = new ArconPush();

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if(!$cliente) {
    header('Location: index.php');
    exit;
}

// Busca contratos do cliente
$contratos = $conn->query("
    SELECT c.*, pc.nome_plano, pc.valor_plano, pc.valor_mensal
    FROM contratos c
    LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
    WHERE c.cliente_id = $id
    ORDER BY c.created_at DESC
");

// Busca planos contratados
$planos = $conn->query("
    SELECT * FROM planos_contratados 
    WHERE cliente_id = $id 
    ORDER BY created_at DESC
");

// Busca logs do cliente
$logs = $conn->query("
    SELECT * FROM cliente_logs 
    WHERE cliente_id = $id 
    ORDER BY data_hora DESC 
    LIMIT 10
");
?>

<style>
.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.cliente-profile {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
    color: white;
    box-shadow: 0 20px 40px rgba(102,126,234,0.3);
}



.profile-info {
    flex: 1;
}

.profile-info h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.profile-info .empresa {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 15px;
}

.profile-meta {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.profile-meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-meta-item i {
    font-size: 1.2rem;
    opacity: 0.8;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #4361ee;
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.info-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
}

.info-card h3 {
    font-size: 1.2rem;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-card h3 i {
    color: #4361ee;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 0.85rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.1rem;
    color: var(--text-primary);
    font-weight: 500;
}

.table-container {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 30px;
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
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-title i {
    color: #4361ee;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 16px 25px;
    background: #f8faff;
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9rem;
}

td {
    padding: 16px 25px;
    border-bottom: 1px solid var(--border);
    color: var(--text-primary);
}

.status-badge {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-ativo {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-inativo {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-bloqueado {
    background: rgba(100, 116, 139, 0.1);
    color: #64748b;
}

.btn {
    padding: 10px 20px;
    border-radius: 10px;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: #ffffff;
    border-color: #4361ee;
}

.btn-icon {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.log-item {
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.log-icon {
    width: 40px;
    height: 40px;
    background: #f8faff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
}

.log-content {
    flex: 1;
}

.log-acao {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.log-data {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.log-ip {
    font-size: 0.8rem;
    color: var(--text-muted);
    background: #f8faff;
    padding: 2px 8px;
    border-radius: 20px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-user" style="color: #4361ee; margin-right: 10px;"></i>
            Detalhes do Cliente
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Header com ações -->
        <div class="view-header">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <div class="action-buttons">
                <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="enviar-email.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i> Enviar E-mail
                </a>
                <a href="../contratos/criar.php?cliente_id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="fas fa-file-contract"></i> Novo Contrato
                </a>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="cliente-profile">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1><?php echo $cliente['nome']; ?></h1>
                <?php if($cliente['empresa']): ?>
                <div class="empresa">
                    <i class="fas fa-building"></i> <?php echo $cliente['empresa']; ?>
                    <?php if($cliente['cargo']): ?> - <?php echo $cliente['cargo']; ?><?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="profile-meta">
                    <div class="profile-meta-item">
                        <i class="fas fa-envelope"></i> <?php echo $cliente['email']; ?>
                    </div>
                    <?php if($cliente['telefone']): ?>
                    <div class="profile-meta-item">
                        <i class="fas fa-phone"></i> <?php echo $cliente['telefone']; ?>
                    </div>
                    <?php endif; ?>
                    <?php if($cliente['celular']): ?>
                    <div class="profile-meta-item">
                        <i class="fas fa-mobile-alt"></i> <?php echo $cliente['celular']; ?>
                    </div>
                    <?php endif; ?>
                    <div class="profile-meta-item">
                        <i class="fas fa-user"></i> Usuário: <?php echo $cliente['username']; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $planos->num_rows; ?></div>
                <div class="stat-label">Planos Contratados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $contratos->num_rows; ?></div>
                <div class="stat-label">Contratos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    $ativos = 0;
                    while($p = $planos->fetch_assoc()) {
                        if($p['status'] == 'ativo') $ativos++;
                    }
                    echo $ativos;
                    ?>
                </div>
                <div class="stat-label">Planos Ativos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php echo $cliente['ultimo_acesso'] ? date('d/m', strtotime($cliente['ultimo_acesso'])) : '—'; ?>
                </div>
                <div class="stat-label">Último Acesso</div>
            </div>
        </div>

        <!-- Informações Pessoais -->
        <div class="info-card">
            <h3><i class="fas fa-id-card"></i> Informações Pessoais</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">CPF/CNPJ</span>
                    <span class="info-value"><?php echo $cliente['cpf_cnpj'] ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">RG/IE</span>
                    <span class="info-value"><?php echo $cliente['rg_ie'] ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Data Nascimento</span>
                    <span class="info-value"><?php echo $cliente['data_nascimento'] ? date('d/m/Y', strtotime($cliente['data_nascimento'])) : '—'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tipo</span>
                    <span class="info-value">
                        <span class="status-badge <?php echo $cliente['tipo'] == 'admin' ? 'status-ativo' : ($cliente['tipo'] == 'parceiro' ? 'status-bloqueado' : ''); ?>">
                            <?php echo ucfirst($cliente['tipo']); ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo $cliente['status']; ?>">
                            <?php echo ucfirst($cliente['status']); ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cliente desde</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Endereço -->
        <?php if($cliente['endereco'] || $cliente['cidade']): ?>
        <div class="info-card">
            <h3><i class="fas fa-map-marker-alt"></i> Endereço</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Endereço</span>
                    <span class="info-value"><?php echo $cliente['endereco'] ?: '—'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cidade</span>
                    <span class="info-value"><?php echo $cliente['cidade'] ?: '—'; ?> <?php echo $cliente['estado'] ? '/ ' . $cliente['estado'] : ''; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">CEP</span>
                    <span class="info-value"><?php echo $cliente['cep'] ?: '—'; ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contratos -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-file-contract"></i> Contratos
                </h3>
                <a href="../contratos/criar.php?cliente_id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Novo Contrato
                </a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Nº Contrato</th>
                        <th>Tipo</th>
                        <th>Plano</th>
                        <th>Valores</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($contratos->num_rows == 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fas fa-file-contract" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 10px;"></i>
                            <p>Nenhum contrato encontrado</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($cont = $contratos->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $cont['numero_contrato']; ?></strong></td>
                            <td><?php echo ucfirst($cont['tipo_contrato']); ?></td>
                            <td><?php echo $cont['nome_plano'] ?: 'Personalizado'; ?></td>
                            <td>
                                R$ <?php echo number_format($cont['valor_total'] ?? 0, 2, ',', '.'); ?>
                                <?php if($cont['valor_mensal']): ?>
                                <br><small>Mensal: R$ <?php echo number_format($cont['valor_mensal'], 2, ',', '.'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $cont['status']; ?>">
                                    <?php echo ucfirst($cont['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($cont['created_at'])); ?></td>
                            <td>
                                <a href="../contratos/visualizar.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Ver contrato">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Observações -->
        <?php if($cliente['observacoes']): ?>
        <div class="info-card">
            <h3><i class="fas fa-comment"></i> Observações</h3>
            <p style="color: var(--text-secondary); line-height: 1.6;"><?php echo nl2br($cliente['observacoes']); ?></p>
        </div>
        <?php endif; ?>

        <!-- ── Integração Arcon ───────────────────────────────── -->
        <div class="info-card" id="arcon-card">
            <h3 style="display:flex;align-items:center;justify-content:space-between;">
                <span><i class="fas fa-plug"></i> Integração Arcon (SaaS)</span>
                <?php if ($arcon->isEnabled()): ?>
                <span style="font-size:.75rem;font-weight:600;color:#10b981;background:rgba(16,185,129,.1);padding:3px 10px;border-radius:20px;">
                    <i class="fas fa-circle" style="font-size:.5rem;"></i> Conectado
                </span>
                <?php else: ?>
                <span style="font-size:.75rem;font-weight:600;color:#f59e0b;background:rgba(245,158,11,.1);padding:3px 10px;border-radius:20px;">
                    Supabase não configurado
                </span>
                <?php endif; ?>
            </h3>

            <?php
            $arconStatus  = $cliente['arcon_status']     ?? 'pendente';
            $arconPlano   = $cliente['arcon_plano_saas'] ?? 'free';
            $arconEmpId   = $cliente['arcon_empresa_id'] ?? null;
            $arconSyncEm  = $cliente['arcon_sync_em']    ?? null;

            $statusCores = [
                'ativo'     => ['#10b981','rgba(16,185,129,.1)','Ativo'],
                'pendente'  => ['#f59e0b','rgba(245,158,11,.1)','Pendente'],
                'suspenso'  => ['#ef4444','rgba(239,68,68,.1)','Suspenso'],
                'cancelado' => ['#64748b','rgba(100,116,139,.1)','Cancelado'],
            ];
            [$sc, $sb, $sl] = $statusCores[$arconStatus] ?? ['#64748b','rgba(100,116,139,.1)',$arconStatus];
            ?>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
                <div style="background:#f8faff;border-radius:12px;padding:14px;">
                    <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Status Arcon</div>
                    <span style="font-weight:700;color:<?= $sc ?>;background:<?= $sb ?>;padding:4px 12px;border-radius:20px;font-size:.85rem;"><?= $sl ?></span>
                </div>
                <div style="background:#f8faff;border-radius:12px;padding:14px;">
                    <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Plano SaaS</div>
                    <div style="font-weight:700;color:#4361ee;text-transform:uppercase;font-size:.9rem;"><?= htmlspecialchars($arconPlano) ?></div>
                </div>
                <div style="background:#f8faff;border-radius:12px;padding:14px;">
                    <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Empresa ID Supabase</div>
                    <div style="font-weight:700;color:var(--text-primary);font-size:.9rem;"><?= $arconEmpId ? '#'.$arconEmpId : '—' ?></div>
                </div>
            </div>
            <?php if ($arconSyncEm): ?>
            <p style="font-size:.78rem;color:var(--text-muted);margin:0 0 16px;">Última sincronização: <?= date('d/m/Y H:i', strtotime($arconSyncEm)) ?></p>
            <?php endif; ?>

            <!-- Seletor de plano SaaS -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                <label style="font-weight:600;font-size:.88rem;white-space:nowrap;">Plano SaaS:</label>
                <select id="arcon-plano-select" style="padding:8px 14px;border:1px solid var(--border);border-radius:8px;background:#f8faff;font-size:.9rem;">
                    <?php foreach(['free','basico','profissional','empresarial','enterprise'] as $p): ?>
                    <option value="<?= $p ?>" <?= $arconPlano===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="arconAction('atualizar_plano')" style="padding:8px 16px;background:#e0e7ff;color:#4361ee;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;">
                    <i class="fas fa-save"></i> Salvar plano
                </button>
            </div>

            <!-- Ações rápidas -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <?php if (!$arconEmpId): ?>
                <button onclick="arconAction('vincular')" style="padding:10px 18px;background:linear-gradient(135deg,#4361ee,#667eea);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:.88rem;">
                    <i class="fas fa-link"></i> Vincular ao Arcon
                </button>
                <?php endif; ?>
                <button onclick="arconAction('ativar')" style="padding:10px 18px;background:#d1fae5;color:#059669;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:.88rem;">
                    <i class="fas fa-check-circle"></i> Ativar Assinatura
                </button>
                <button onclick="arconAction('suspender')" style="padding:10px 18px;background:#fef3c7;color:#d97706;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:.88rem;">
                    <i class="fas fa-pause-circle"></i> Suspender
                </button>
                <button onclick="arconAction('cancelar')" style="padding:10px 18px;background:#fee2e2;color:#ef4444;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:.88rem;"
                    onclick="return confirm('Confirmar cancelamento no Arcon?')">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
                <button onclick="arconAction('sync')" style="padding:10px 18px;background:#f8faff;color:#64748b;border:1px solid var(--border);border-radius:10px;font-weight:600;cursor:pointer;font-size:.88rem;">
                    <i class="fas fa-sync-alt"></i> Sincronizar
                </button>
            </div>

            <div id="arcon-msg" style="margin-top:14px;display:none;padding:12px 16px;border-radius:10px;font-size:.88rem;font-weight:600;"></div>
        </div>

        <script>
        function arconAction(acao) {
            const btn = event.currentTarget;
            if (acao === 'cancelar' && !confirm('Confirmar cancelamento no Arcon?')) return;
            const plano = document.getElementById('arcon-plano-select')?.value || 'free';
            const msg   = document.getElementById('arcon-msg');
            msg.style.display = 'none';
            btn.disabled = true;
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aguarde...';

            fetch('/admin/api/arcon-action.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `acao=${acao}&cliente_id=<?= $id ?>&plano_saas=${plano}&status=${acao==='vincular'?'ativo':'pendente'}`,
            })
            .then(r => r.json())
            .then(d => {
                msg.style.display = 'block';
                msg.style.background = d.ok ? 'rgba(16,185,129,.1)' : 'rgba(239,68,68,.1)';
                msg.style.color      = d.ok ? '#059669' : '#ef4444';
                msg.style.border     = '1px solid ' + (d.ok ? '#10b981' : '#ef4444');
                msg.innerHTML        = (d.ok ? '✅ ' : '❌ ') + d.msg;
                if (d.ok) setTimeout(() => location.reload(), 1500);
            })
            .catch(e => {
                msg.style.display = 'block';
                msg.style.background = 'rgba(239,68,68,.1)';
                msg.style.color      = '#ef4444';
                msg.style.border     = '1px solid #ef4444';
                msg.innerHTML        = '❌ Erro: ' + e.message;
            })
            .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
        }
        </script>

        <!-- Logs de Atividade -->
        <div class="info-card">
            <h3><i class="fas fa-history"></i> Últimas Atividades</h3>
            <?php if($logs->num_rows == 0): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">Nenhuma atividade registrada</p>
            <?php else: ?>
                <?php while($log = $logs->fetch_assoc()): ?>
                <div class="log-item">
                    <div class="log-icon">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="log-content">
                        <div class="log-acao"><?php echo $log['acao']; ?></div>
                        <div class="log-data"><?php echo date('d/m/Y H:i:s', strtotime($log['data_hora'])); ?></div>
                    </div>
                    <div class="log-ip">IP: <?php echo $log['ip']; ?></div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
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