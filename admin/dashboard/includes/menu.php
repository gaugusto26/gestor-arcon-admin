<?php
// Menu do painel admin
require_once $_SERVER['DOCUMENT_ROOT'] . '/newsoftware/config.php';
$current_url = $_SERVER['REQUEST_URI'];

$result_planos = $conn->query("SELECT COUNT(*) as total FROM planos WHERE ativo = 1");
$total_planos = $result_planos->fetch_assoc()['total'];

$admin_base = '/newsoftware/admin/dashboard/';

$menu_items = [
    'site' => [
        'titulo' => 'Site',
        'itens' => [
            ['nome' => 'Dashboard',              'icone' => 'fa-home',               'link' => $admin_base . 'index.php',                   'badge' => ''],
            ['nome' => 'Planos',                 'icone' => 'fa-crown',              'link' => $admin_base . 'modules/planos/index.php',     'badge' => $total_planos],
            ['nome' => 'Blog',                   'icone' => 'fa-blog',               'link' => $admin_base . 'modules/blog/index.php',       'badge' => ''],
            ['nome' => 'Newsletter',             'icone' => 'fa-envelope-open-text', 'link' => $admin_base . 'modules/newsletter/index.php', 'badge' => ''],
            ['nome' => 'Política de privacidade','icone' => 'fa-shield-alt',         'link' => $admin_base . 'modules/politica/index.php',   'badge' => '']
        ]
    ],
    'sistema' => [
        'titulo' => 'Sistema',
        'itens' => [
            ['nome' => 'Contratos',               'icone' => 'fa-file-signature',    'link' => $admin_base . 'modules/contratos/index.php', 'badge' => ''],
            ['nome' => 'Gerador de contrato',     'icone' => 'fa-file-contract',     'link' => $admin_base . 'modules/gerador/index.php', 'badge' => ''],
            ['nome' => 'Usuários',                'icone' => 'fa-users',             'link' => $admin_base . 'modules/clientes/index.php', 'badge' => ''],
            ['nome' => 'Gerenciar Usuários',      'icone' => 'fa-user-cog',          'link' => $admin_base . 'modules/clientes/gerenciar.php', 'badge' => ''],
            ['nome' => 'Visão geral de indicação','icone' => 'fa-chart-line',        'link' => $admin_base . 'modules/indicacoes/index.php', 'badge' => ''],
            ['nome' => 'Empresas',                'icone' => 'fa-building',          'link' => $admin_base . 'modules/empresas/index.php', 'badge' => ''],
            ['nome' => 'Pagamento',               'icone' => 'fa-credit-card',       'link' => $admin_base . 'modules/pagamentos/index.php', 'badge' => ''],
            ['nome' => 'Planos (boletos)',         'icone' => 'fa-file-invoice',      'link' => '#', 'badge' => ''],
            ['nome' => 'Relatório (boletos)',      'icone' => 'fa-chart-bar',         'link' => '#', 'badge' => ''],
            ['nome' => 'Lucro (empresa)',          'icone' => 'fa-dollar-sign',       'link' => '#', 'badge' => ''],
            ['nome' => 'API',                     'icone' => 'fa-code',              'link' => '#', 'badge' => 'dev'],
            ['nome' => 'Atualizar status',        'icone' => 'fa-sync-alt',          'link' => $admin_base . 'modules/status/index.php', 'badge' => ''],
            ['nome' => 'Avisos',                  'icone' => 'fa-exclamation-triangle','link' => '#','badge' => '']
        ]
    ],
    'others' => [
        'titulo' => 'Others',
        'itens' => [
            ['nome' => 'Total de usuarios newsletter','icone' => 'fa-users',         'link' => '#', 'badge' => '1.2k'],
            ['nome' => 'Relatório dos usuarios',      'icone' => 'fa-chart-pie',     'link' => '#', 'badge' => ''],
            ['nome' => 'Apoiadores',                  'icone' => 'fa-hand-holding-heart','link' => '#','badge' => '']
        ]
    ]
];
?>

<style>
    #toggleSidebar {
        position: fixed;          /* nunca some com scroll */
        top: 50%;
        transform: translateY(-50%);
        z-index: 9999;            /* acima de tudo */
        left: 0;                  /* JS vai ajustar conforme largura da sidebar */

        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.18);
        background: #1e1e2e;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.45);

        /* transição suave ao mover junto com a sidebar */
        transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    background 0.2s ease,
                    border-color 0.2s ease,
                    box-shadow 0.2s ease;
    }
    #toggleSidebar:hover {
        background: #2e2e45;
        border-color: rgba(255,255,255,0.4);
        box-shadow: 0 3px 14px rgba(0,0,0,0.6);
    }
    #toggleSidebar i {
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        display: inline-block;
    }
    /* seta gira quando colapsado */
    #toggleSidebar.rotated i {
        transform: rotate(180deg);
    }

/* Scrollbar estilizada - azul */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #0a0f1e00;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #1e90ff, #0057c2);
  border-radius: 10px;
  transition: background 0.3s ease;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #42aaff, #1e90ff);
}

::-webkit-scrollbar-corner {
  background: transparent;
}

/* Firefox */
* {
  scrollbar-width: thin;
  scrollbar-color: #1e90ff #0a0f1e00;
}
    #sidebar {
     
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                    min-width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ─────────────────────────────────────────────────────────────
       TEXTOS — fade + slide ao colapsar
       ───────────────────────────────────────────────────────────── */
    #sidebar .logo-text,
    #sidebar .profile-details,
    #sidebar .menu-item span,
    #sidebar .menu-badge,
    #sidebar .menu-title {
        white-space: nowrap;
        transition: opacity 0.2s ease 0.05s,
                    transform 0.2s ease 0.05s,
                    visibility 0.2s;
    }

    #sidebar.collapsed .logo-text,
    #sidebar.collapsed .profile-details,
    #sidebar.collapsed .menu-item span,
    #sidebar.collapsed .menu-badge,
    #sidebar.collapsed .menu-title {
        opacity: 0;
        transform: translateX(-10px);
        pointer-events: none;
        visibility: hidden;
    }

    #sidebar:not(.collapsed) .logo-text,
    #sidebar:not(.collapsed) .profile-details,
    #sidebar:not(.collapsed) .menu-item span,
    #sidebar:not(.collapsed) .menu-badge,
    #sidebar:not(.collapsed) .menu-title {
        opacity: 1;
        transform: translateX(0);
        visibility: visible;
        /* atraso: espera a sidebar abrir antes de mostrar o texto */
        transition: opacity 0.25s ease 0.18s,
                    transform 0.25s ease 0.18s,
                    visibility 0.25s;
    }

    /* ─────────────────────────────────────────────────────────────
       ÍCONES — micro-pulso no momento do clique
       ───────────────────────────────────────────────────────────── */
    #sidebar .menu-item i,
    #sidebar .profile-avatar {
        flex-shrink: 0;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #sidebar.collapsing .menu-item i {
        transform: scale(0.78);
    }

    /* ─────────────────────────────────────────────────────────────
       CONTEÚDO PRINCIPAL acompanha a sidebar
       ───────────────────────────────────────────────────────────── */
    #mainContent {
        transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- Botão toggle FORA da sidebar — fixo na tela, nunca desaparece -->
<button id="toggleSidebar" title="Recolher menu">
    <i class="fas fa-chevron-left"></i>
</button>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-area">
            <div class="logo">N</div>
            <span class="logo-text">NTW Admin</span>
        </div>
    </div>

    <div class="profile-section">
        <div class="profile-info">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['admin_nome'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="profile-details">
                <div class="profile-name"><?php echo $_SESSION['admin_nome'] ?? 'Administrador'; ?></div>
                <div class="profile-role"><?php echo $_SESSION['admin_role'] ?? 'Super Admin'; ?></div>
            </div>
        </div>
    </div>

    <nav class="sidebar-menu">
        <?php foreach($menu_items as $section => $data): ?>
        <div class="menu-section">
            <div class="menu-title"><?php echo $data['titulo']; ?></div>

            <?php foreach($data['itens'] as $item):
                $is_active = '';
                if($item['link'] != '#') {
                    $current_path = parse_url($current_url, PHP_URL_PATH);

                    if(strpos($current_path, '/admin/index.php') !== false || $current_path == '/newsoftware/admin/') {
                        if($item['nome'] == 'Dashboard') $is_active = 'active';
                    }
                    elseif(strpos($current_path, '/admin/modules/planos/') !== false) {
                        if($item['nome'] == 'Planos') $is_active = 'active';
                    }
                    elseif(strpos($current_path, '/admin/modules/blog/') !== false) {
                        if($item['nome'] == 'Blog') $is_active = 'active';
                    }
                    elseif(strpos($current_path, '/admin/modules/newsletter/') !== false) {
                        if($item['nome'] == 'Newsletter') $is_active = 'active';
                    }
                    elseif(strpos($current_path, '/admin/modules/politica/') !== false) {
                        if($item['nome'] == 'Política de privacidade') $is_active = 'active';
                    }
                    else {
                        $item_path = parse_url($item['link'], PHP_URL_PATH);
                        if($item_path && strpos($current_path, $item_path) !== false) {
                            $is_active = 'active';
                        }
                    }
                }
            ?>
            <a href="<?php echo $item['link']; ?>" class="menu-item <?php echo $is_active; ?>">
                <i class="fas <?php echo $item['icone']; ?>"></i>
                <span><?php echo $item['nome']; ?></span>
                <?php if(!empty($item['badge'])): ?>
                <span class="menu-badge"><?php echo $item['badge']; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
(function () {
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn   = document.getElementById('toggleSidebar');

    // Posiciona o botão na borda direita da sidebar
    function syncButtonPosition(animate) {
        if (!animate) toggleBtn.style.transition = 'none';
        const sidebarRect = sidebar.getBoundingClientRect();
        // centraliza o botão na borda direita (-13px = metade da largura do botão)
        toggleBtn.style.left = (sidebarRect.right - 13) + 'px';
        if (!animate) {
            // força reflow e reativa a transição
            toggleBtn.offsetHeight;
            toggleBtn.style.transition = '';
        }
    }

    function updateIcon() {
        const collapsed = sidebar.classList.contains('collapsed');
        toggleBtn.classList.toggle('rotated', collapsed);
        toggleBtn.title = collapsed ? 'Expandir menu' : 'Recolher menu';
    }

    function toggleSidebar() {
        // micro-pulso nos ícones
        sidebar.classList.add('collapsing');
        setTimeout(() => sidebar.classList.remove('collapsing'), 300);

        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');

        updateIcon();

        // Sincroniza posição do botão APÓS a transição da sidebar terminar
        // (dispara imediatamente também para acompanhar em tempo real)
        requestAnimationFrame(function step() {
            syncButtonPosition(true);
            if (sidebar.classList.contains('collapsing') ||
                getComputedStyle(sidebar).transitionDuration !== '0s') {
                requestAnimationFrame(step);
            }
        });

        localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
    }

    toggleBtn.addEventListener('click', toggleSidebar);

    // Também atualiza se a janela for redimensionada
    window.addEventListener('resize', () => syncButtonPosition(true));

    // ── Restaura estado salvo SEM animação (sem flash no carregamento) ──
    function init() {
        const saved = localStorage.getItem('sidebar_collapsed') === 'true';

        if (saved) {
            // Desliga transições temporariamente
            sidebar.style.transition  = 'none';
            if (mainContent) mainContent.style.transition = 'none';
            toggleBtn.style.transition = 'none';

            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
        }

        // Posiciona o botão já no lugar certo antes de qualquer render
        syncButtonPosition(false);
        updateIcon();

        // Reativa as transições após o primeiro frame pintado
        requestAnimationFrame(() => requestAnimationFrame(() => {
            sidebar.style.transition  = '';
            if (mainContent) mainContent.style.transition = '';
            toggleBtn.style.transition = '';
        }));
    }

    // Executa assim que o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Garante posição correta depois que tudo carregar (fontes, imagens etc.)
    window.addEventListener('load', () => syncButtonPosition(false));
})();
</script>