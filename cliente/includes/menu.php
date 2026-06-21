<?php
$current_url = $_SERVER['REQUEST_URI'];
$modulo_atual = isset($_GET['mod']) ? $_GET['mod'] : 'dashboard';

// Define a BASE_URL do cliente
$cliente_base = '/cliente/';

// Busca faturas pendentes para o badge
global $conn;
$cid = (int)$_SESSION['cliente_id'];
$faturas_pendentes = $conn->query("
    SELECT COUNT(*) as total, COALESCE(SUM(valor_total),0) AS total_valor
    FROM cliente_faturas
    WHERE cliente_id = $cid AND status = 'pendente'
")->fetch_assoc();
$total_faturas = $faturas_pendentes['total'] ?? 0;

// Itens do menu com links absolutos
$menu_items = [
    'principal' => [
        'titulo' => 'Principal',
        'itens' => [
            ['nome' => 'Dashboard', 'icone' => 'fa-home', 'link' => $cliente_base . 'dashboard/index.php?mod=dashboard', 'modulo' => 'dashboard', 'badge' => ''],
            ['nome' => 'Meus Sistemas', 'icone' => 'fa-cube', 'link' => $cliente_base . 'modules/sistemas/index.php', 'modulo' => 'sistemas', 'badge' => ''],
        ]
    ],
    'financeiro' => [
        'titulo' => 'Financeiro',
        'itens' => [
            ['nome' => 'Contratos', 'icone' => 'fa-file-contract', 'link' => $cliente_base . 'modules/contratos/index.php', 'modulo' => 'contratos', 'badge' => ''],
            ['nome' => 'Faturas', 'icone' => 'fa-file-invoice-dollar', 'link' => $cliente_base . 'modules/faturas/index.php', 'modulo' => 'faturas', 'badge' => $total_faturas > 0 ? $total_faturas : ''],
            ['nome' => 'Pagamentos', 'icone' => 'fa-credit-card', 'link' => $cliente_base . 'modules/pagamentos/index.php', 'modulo' => 'pagamentos', 'badge' => ''],
        ]
    ],
    'documentos' => [
        'titulo' => 'Documentos',
        'itens' => [
            ['nome' => 'Assinatura', 'icone' => 'fa-pen-fancy', 'link' => $cliente_base . 'modules/assinatura/index.php', 'modulo' => 'assinatura', 'badge' => ''],
            ['nome' => 'Documentos', 'icone' => 'fa-file-pdf', 'link' => $cliente_base . 'modules/documentos/index.php', 'modulo' => 'documentos', 'badge' => ''],
        ]
    ],
    'suporte' => [
        'titulo' => 'Suporte',
        'itens' => [
            ['nome' => 'Tickets', 'icone' => 'fa-headset', 'link' => $cliente_base . 'modules/suporte/index.php', 'modulo' => 'suporte', 'badge' => ''],
            ['nome' => 'FAQ', 'icone' => 'fa-question-circle', 'link' => $cliente_base . 'modules/faq/index.php', 'modulo' => 'faq', 'badge' => ''],
        ]
    ],
    'conta' => [
        'titulo' => 'Conta',
        'itens' => [
            ['nome' => 'Meu Perfil', 'icone' => 'fa-user-cog', 'link' => $cliente_base . 'modules/perfil/index.php', 'modulo' => 'perfil', 'badge' => ''],
        ]
    ]
];
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">

    <div class="sb-head">
        <img src="/assets/image/logo_quadrada.png" alt="Digital Five" class="sb-mark" style="width:37px;height:37px;border-radius:11px;object-fit:cover;flex-shrink:0;">
        <span class="sb-brand">Digital Five</span>
    </div>

    <div class="sb-prof">
        <div class="sb-prof-pill">
            <div class="sb-av"><?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?></div>
            <div class="sb-prof-info">
                <div class="sb-prof-name"><?php echo htmlspecialchars($cliente['nome']); ?></div>
                <div class="sb-prof-role">Cliente</div>
            </div>
        </div>
    </div>

    <nav class="sb-nav">
        <?php foreach($menu_items as $section => $data): ?>
            <div class="sb-section-lbl"><?php echo $data['titulo']; ?></div>
            
            <?php foreach($data['itens'] as $item): 
                // Verifica se é o item ativo - LÓGICA CORRIGIDA PARA O CAMINHO REAL
                $is_active = '';
                
                // CASO 1: Dashboard (caminho especial)
                if ($item['modulo'] == 'dashboard' && strpos($current_url, 'dashboard/index.php') !== false) {
                    $is_active = 'active';
                }
                // CASO 2: Todos os outros módulos
                elseif (strpos($current_url, 'modules/' . $item['modulo'] . '/') !== false) {
                    $is_active = 'active';
                }
                // CASO 3: Também verifica pelo parâmetro mod (fallback)
                elseif ($item['modulo'] == $modulo_atual && $modulo_atual != 'dashboard') {
                    $is_active = 'active';
                }
            ?>
            <a href="<?php echo $item['link']; ?>" class="sb-link <?php echo $is_active; ?>">
                <i class="sb-ico fas <?php echo $item['icone']; ?>"></i>
                <span class="sb-txt"><?php echo $item['nome']; ?></span>
                <?php if(!empty($item['badge'])): ?>
                <span class="sb-badge"><?php echo $item['badge']; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sb-foot">
        <a href="<?php echo $cliente_base; ?>logout.php" class="sb-link" style="color:var(--danger);">
            <i class="sb-ico fas fa-sign-out-alt"></i>
            <span class="sb-txt">Sair</span>
        </a>
    </div>

</aside>

<!-- Sidebar toggle pill -->
<div class="sb-tog" id="sbTog">
    <div class="sb-tog-btn" id="sbTogBtn"><i class="fas fa-chevron-left"></i></div>
</div>

<script>
// Garantir que o script execute depois que tudo carregar
(function() {
    'use strict';
    
    // Função principal que será chamada várias vezes
    function initSidebar() {
        // Elementos
        const sidebar = document.getElementById('sidebar');
        const mainElement = document.querySelector('.main');
        const toggleBtn = document.getElementById('sbTogBtn');
        const toggleContainer = document.getElementById('sbTog');
        
        // Se não encontrou, tenta de novo depois
        if (!sidebar || !toggleBtn) {
            console.log('Elementos não encontrados, tentando novamente...');
            return false;
        }
        
        console.log('Sidebar inicializada com sucesso!');
        
        // Remove listeners antigos (se houver)
        const newToggleBtn = toggleBtn.cloneNode(true);
        toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);
        
        // Pega a referência do novo botão
        const finalToggleBtn = document.getElementById('sbTogBtn');
        
        // Função para recolher/expandir no desktop
        function toggleDesktop() {
            sidebar.classList.toggle('collapsed');
            if (mainElement) mainElement.classList.toggle('expanded');
            if (toggleContainer) toggleContainer.classList.toggle('collapsed');
            
            // Salva estado
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('cliente_sidebar_collapsed', isCollapsed);
            
            // Gira a setinha
            const icon = finalToggleBtn.querySelector('i');
            if (icon) {
                icon.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            
            console.log('Toggle desktop:', isCollapsed ? 'recolhido' : 'expandido');
        }
        
        // Função para abrir/fechar no mobile
        function toggleMobile() {
            sidebar.classList.toggle('mob-open');
            
            const isOpen = sidebar.classList.contains('mob-open');
            const icon = finalToggleBtn.querySelector('i');
            if (icon) {
                icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            
            console.log('Toggle mobile:', isOpen ? 'aberto' : 'fechado');
        }
        
        // Evento de clique no botão
        finalToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (window.innerWidth <= 768) {
                toggleMobile();
            } else {
                toggleDesktop();
            }
        });
        
        // Restaura estado salvo (apenas desktop)
        const savedState = localStorage.getItem('cliente_sidebar_collapsed');
        if (savedState === 'true' && window.innerWidth > 768) {
            sidebar.classList.add('collapsed');
            if (mainElement) mainElement.classList.add('expanded');
            if (toggleContainer) toggleContainer.classList.add('collapsed');
            
            const icon = finalToggleBtn.querySelector('i');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
        
        // Fecha menu ao clicar fora (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && sidebar.classList.contains('mob-open')) {
                if (!sidebar.contains(e.target) && !finalToggleBtn.contains(e.target)) {
                    sidebar.classList.remove('mob-open');
                    const icon = finalToggleBtn.querySelector('i');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            }
        });
        
        // Ajusta ao redimensionar a tela
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (window.innerWidth > 768) {
                    // Modo desktop
                    sidebar.classList.remove('mob-open');
                    
                    const savedState = localStorage.getItem('cliente_sidebar_collapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        if (mainElement) mainElement.classList.add('expanded');
                        if (toggleContainer) toggleContainer.classList.add('collapsed');
                        
                        const icon = finalToggleBtn.querySelector('i');
                        if (icon) icon.style.transform = 'rotate(180deg)';
                    } else {
                        sidebar.classList.remove('collapsed');
                        if (mainElement) mainElement.classList.remove('expanded');
                        if (toggleContainer) toggleContainer.classList.remove('collapsed');
                        
                        const icon = finalToggleBtn.querySelector('i');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    }
                } else {
                    // Modo mobile
                    sidebar.classList.remove('collapsed');
                    if (mainElement) mainElement.classList.remove('expanded');
                    if (toggleContainer) toggleContainer.classList.remove('collapsed');
                    
                    const icon = finalToggleBtn.querySelector('i');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            }, 150);
        });
        
        return true;
    }
    
    // Tentar inicializar imediatamente
    if (!initSidebar()) {
        // Se falhou, tenta novamente após um pequeno delay
        setTimeout(initSidebar, 100);
        setTimeout(initSidebar, 300);
        setTimeout(initSidebar, 500);
    }
    
    // Também tenta no DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
    });
    
    // E no load completo
    window.addEventListener('load', function() {
        initSidebar();
    });
    
})();
</script>

<!-- CSS adicional para garantir transições -->
<style>
    .sb-tog-btn i {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .sidebar, .main, .sb-tog {
        transition: all 0.26s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    /* Garantir que o menu não suma em mobile */
    @media (max-width: 768px) {
        .sidebar.mob-open {
            transform: translateX(0) !important;
        }
    }
</style>
