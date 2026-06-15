<?php
$page_title = 'Meus Contratos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php'; // ← ADICIONA O MENU AQUI!

global $conn;
$cid = (int)$_SESSION['cliente_id'];

// Filtros
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';

// Busca contratos - APENAS os que não são rascunho
$sql = "SELECT c.*, 
               pc.nome_plano,
               pc.valor_mensal,
               (SELECT COUNT(*) FROM cliente_assinaturas_contratos WHERE contrato_id = c.id) as assinado
        FROM contratos c
        LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
        WHERE c.cliente_id = ? 
        AND c.status IN ('enviado', 'assinado', 'cancelado')"; // ← FILTRO IMPORTANTE!

$params = [$cid];
$types = "i";

if ($status_filter) {
    $sql .= " AND c.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($busca) {
    $sql .= " AND (c.numero_contrato LIKE ? OR c.titulo LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "ss";
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$contratos = $stmt->get_result();

// Estatísticas - considera apenas os status visíveis
$stats = [
    'total' => $contratos->num_rows,
    'enviado' => 0,
    'assinado' => 0,
    'cancelado' => 0
];

$contratos->data_seek(0);
while ($c = $contratos->fetch_assoc()) {
    if (isset($stats[$c['status']])) $stats[$c['status']]++;
}
$contratos->data_seek(0);
?>

<!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-file-contract"></i></div>
            <span class="top-pg-title">Meus Contratos</span>
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
                    <i class="fas <?php echo $tema_atual==='dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
                </div>
            </div>

            <div class="user-btn" id="userBtn">
                <div class="sb-av"><?php echo strtoupper(substr($cliente['nome'],0,1)); ?></div>
                <span class="user-btn-n"><?php echo htmlspecialchars(explode(' ',$cliente['nome'])[0]); ?></span>
                <i class="fas fa-chevron-down user-chevron"></i>
                <div class="ddrop" id="userDrop">
                    <a href="/newsoftware/cliente/modules/perfil/index.php"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                    <a href="/newsoftware/cliente/modules/assinatura/index.php"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                    <hr>
                    <a href="/newsoftware/cliente/logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </div><!-- /topbar -->

    <!-- Content -->
    <div class="content">

        <!-- Stats cards -->
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);"> <!-- Só 4 cards agora -->
            <div class="stat-card">
                <div class="stat-ico" style="background:rgba(79,110,247,.1);color:var(--ac)">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-val"><?php echo $stats['total']; ?></div>
                <div class="stat-lbl">Total</div>
            </div>

            <div class="stat-card">
                <div class="stat-ico" style="background:rgba(79,110,247,.1);color:var(--ac)">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-val"><?php echo $stats['enviado']; ?></div>
                <div class="stat-lbl">Aguardando</div>
            </div>

            <div class="stat-card">
                <div class="stat-ico" style="background:rgba(5,150,105,.1);color:var(--ac3)">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-val"><?php echo $stats['assinado']; ?></div>
                <div class="stat-lbl">Assinados</div>
            </div>

            <div class="stat-card">
                <div class="stat-ico" style="background:rgba(220,38,38,.1);color:var(--danger)">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-val"><?php echo $stats['cancelado']; ?></div>
                <div class="stat-lbl">Cancelados</div>
            </div>
        </div>

        <!-- Filtros -->
        <div style="background:var(--surf); border:1px solid var(--bdr); border-radius:var(--r); padding:18px; margin-bottom:18px;">
            <form method="GET" style="display:grid; grid-template-columns:1fr 200px auto; gap:10px;">
                <input type="hidden" name="mod" value="contratos">
                <input type="text" name="busca" placeholder="Buscar por nº do contrato ou título..." 
                       value="<?php echo htmlspecialchars($busca); ?>"
                       style="padding:8px 14px; border-radius:var(--r3); border:1px solid var(--bdr); background:var(--surf2); color:var(--tx); font-size:.85rem;">
                
                <select name="status" style="padding:8px 14px; border-radius:var(--r3); border:1px solid var(--bdr); background:var(--surf2); color:var(--tx); font-size:.85rem;">
                    <option value="">Todos status</option>
                    <option value="enviado" <?php echo $status_filter=='enviado'?'selected':''; ?>>Aguardando assinatura</option>
                    <option value="assinado" <?php echo $status_filter=='assinado'?'selected':''; ?>>Assinado</option>
                    <option value="cancelado" <?php echo $status_filter=='cancelado'?'selected':''; ?>>Cancelado</option>
                </select>

                <div style="display:flex; gap:5px;">
                    <button type="submit" class="btn-sm" style="background:var(--ac); color:#fff; border:none;">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="?mod=contratos" class="btn-sm">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Lista de Contratos -->
        <?php if ($contratos->num_rows == 0): ?>
        <div style="text-align:center; padding:60px 20px; background:var(--surf); border-radius:var(--r);">
            <i class="fas fa-file-contract" style="font-size:3rem; color:var(--tx4); margin-bottom:15px;"></i>
            <h3 style="margin-bottom:10px;">Nenhum contrato encontrado</h3>
            <p style="color:var(--tx3);">Você ainda não possui contratos disponíveis.</p>
        </div>
        <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:13px;">
            <?php while ($c = $contratos->fetch_assoc()): 
                $status_class = match($c['status']) {
                    'enviado'  => 'chip-blue',
                    'assinado' => 'chip-green',
                    'cancelado'=> 'chip-red',
                    default    => 'chip-warn'
                };
                
                $status_label = match($c['status']) {
                    'enviado'  => 'Aguardando assinatura',
                    'assinado' => 'Assinado',
                    'cancelado'=> 'Cancelado',
                    default    => ucfirst($c['status'])
                };
                
                $assinado = $c['assinado'] > 0;
            ?>
            <div class="act-card" style="flex-direction:row; justify-content:space-between; padding:18px; margin:0;" onclick="window.location.href='?mod=contratos&action=view&id=<?php echo $c['id']; ?>'">
                <div style="display:flex; align-items:center; gap:16px; flex:1;">
                    <div class="act-ico" style="background:rgba(79,110,247,.1);color:var(--ac); width:48px; height:48px;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:5px; flex-wrap:wrap;">
                            <span style="font-weight:700; color:var(--tx);"><?php echo htmlspecialchars($c['numero_contrato']); ?></span>
                            <span class="chip <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                            <?php if ($assinado): ?>
                            <span class="chip chip-green" style="background:rgba(5,150,105,.1);color:var(--ac3);">
                                <i class="fas fa-check-circle" style="font-size:.6rem;"></i> Assinado
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                            <span style="font-size:.8rem; color:var(--tx3);">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($c['nome_plano'] ?? 'Plano Personalizado'); ?>
                            </span>
                            <span style="font-size:.8rem; color:var(--tx3);">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d/m/Y', strtotime($c['created_at'])); ?>
                            </span>
                            <?php if ($c['valor_total']): ?>
                            <span style="font-weight:600; color:var(--ac);">
                                R$ <?php echo number_format($c['valor_total'], 2, ',', '.'); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex; gap:5px;">
                    <a href="visualizar.php?mod=contratos&action=view&id=<?php echo $c['id']; ?>" class="btn-sm">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <?php if ($c['status'] == 'enviado' && !$assinado): ?>
                    <a href="?mod=contratos&action=assinar&id=<?php echo $c['id']; ?>" class="btn-sm" style="background:var(--ac3); color:#fff; border:none;">
                        <i class="fas fa-pen-fancy"></i> Assinar
                    </a>
                    <?php endif; ?>
                    <?php if ($assinado): ?>
                    <a href="?mod=contratos&action=pdf&id=<?php echo $c['id']; ?>" class="btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

    </div><!-- /content -->
</main><!-- /main -->

<script>
(function() {
    // User dropdown
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

    // Notificação
    const notifBtn = document.getElementById('notifBtn');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            window.location.href = '/newsoftware/cliente/modules/faturas/index.php';
        });
    }

    // Theme toggle
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