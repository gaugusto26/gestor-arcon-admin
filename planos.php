<?php
require_once 'config.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function moneyPlan($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function periodPlan($period) {
    $labels = [
        'mensal' => '/mês',
        'anual' => '/ano',
        'permanente' => '',
        'unico' => '',
    ];
    return $labels[$period] ?? '';
}

function getPlanFeatures($conn) {
    $features = [];
    $result = $conn->query("SELECT plano_id, caracteristica, icone FROM planos_caracteristicas ORDER BY plano_id, ordem");
    while ($row = $result->fetch_assoc()) {
        $features[(int)$row['plano_id']][] = $row;
    }
    return $features;
}

function getPlansByProfile($conn, $profile) {
    $stmt = $conn->prepare("
        SELECT p.*, c.nome AS categoria_nome, c.icone AS categoria_icone
        FROM planos p
        LEFT JOIN planos_categorias c ON p.categoria_id = c.id
        WHERE p.ativo = 1 AND (p.perfil = ? OR p.perfil = 'ambos')
        ORDER BY p.destaque DESC, COALESCE(c.ordem, 999), p.ordem, p.id
    ");
    $stmt->bind_param('s', $profile);
    $stmt->execute();
    return $stmt->get_result();
}

function planWhatsappUrl($plan, $whatsapp) {
    if (!empty($plan['link_whatsapp'])) {
        return $plan['link_whatsapp'];
    }

    $number = preg_replace('/\D+/', '', $whatsapp['numero'] ?? '5517992347622');
    $message = $plan['mensagem_whatsapp'] ?: ($whatsapp['mensagem_padrao'] ?? 'Olá, quero saber mais sobre o plano {plano_nome}.');
    $message = str_replace('{plano_nome}', $plan['nome'], $message);

    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}

$features = getPlanFeatures($conn);
$plansMei = getPlansByProfile($conn, 'mei');
$plansEmpresa = getPlansByProfile($conn, 'empresa');
$whatsapp = getWhatsAppConfig($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/assets/image/logo_quadrada.png" type="image/x-icon">
    <meta name="description" content="Planos ARCON administrados pela Digital Five.">
    <title>Planos | Digital Five</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8fafd;
            color: #081b3a;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        .navbar {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 50;
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(8,27,58,.08);
        }
        .navbar-inner {
            max-width: 1280px;
            height: 64px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: -.02em;
        }
        .brand img {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            object-fit: cover;
        }
        .brand strong { color: #0b5cff; }
        .nav {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: .875rem;
            color: #64748b;
            font-weight: 500;
        }
        .nav a:hover { color: #0b5cff; }
        .nav .active { color: #0b5cff; font-weight: 700; }
        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .login-link {
            color: #64748b;
            font-size: .875rem;
            font-weight: 500;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            cursor: pointer;
            border-radius: 999px;
            font-weight: 700;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: linear-gradient(135deg, #0b5cff, #6c5ce7);
            color: #fff;
            padding: 11px 20px;
            box-shadow: 0 12px 24px rgba(11,92,255,.18);
        }
        .hero {
            padding: 132px 24px 56px;
            background: linear-gradient(180deg, #fff 0%, #f8fafd 100%);
            text-align: center;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(11,92,255,.10);
            color: #0b5cff;
            border-radius: 999px;
            padding: 8px 16px;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }
        h1 {
            font-size: clamp(2.5rem, 5vw, 4.75rem);
            line-height: .98;
            letter-spacing: -.03em;
            margin-bottom: 18px;
            font-weight: 800;
        }
        .gradient-text {
            background: linear-gradient(135deg, #0b5cff, #6c5ce7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .hero p {
            color: #64748b;
            max-width: 680px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.7;
        }
        .tabs {
            display: inline-flex;
            gap: 8px;
            margin-top: 32px;
            padding: 6px;
            border: 1px solid rgba(8,27,58,.08);
            background: #fff;
            border-radius: 999px;
            box-shadow: 0 12px 36px rgba(8,27,58,.08);
        }
        .tab {
            border: 0;
            background: transparent;
            color: #64748b;
            padding: 11px 18px;
            border-radius: 999px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }
        .tab.active {
            background: linear-gradient(135deg, #0b5cff, #6c5ce7);
            color: #fff;
        }
        .plans-section {
            padding: 48px 0 88px;
            display: none;
        }
        .plans-section.active { display: block; }
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }
        .plan-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 430px;
            background: #fff;
            border: 1px solid rgba(8,27,58,.08);
            border-radius: 24px;
            padding: 26px;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .plan-card:hover {
            transform: translateY(-4px);
            border-color: rgba(11,92,255,.22);
            box-shadow: 0 24px 52px rgba(8,27,58,.10);
        }
        .plan-card.featured {
            background: linear-gradient(135deg, #0b5cff, #6c5ce7);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 28px 60px rgba(11,92,255,.22);
        }
        .badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            border-radius: 999px;
            background: #fff;
            color: #0b5cff;
            border: 1px solid rgba(11,92,255,.12);
            box-shadow: 0 10px 24px rgba(8,27,58,.10);
            padding: 7px 14px;
            font-size: .75rem;
            font-weight: 800;
        }
        .category {
            color: inherit;
            opacity: .7;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .plan-card:not(.featured) .category { color: #64748b; opacity: 1; }
        .plan-name {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 12px;
        }
        .price {
            display: flex;
            align-items: baseline;
            gap: 4px;
            margin-bottom: 12px;
        }
        .price strong {
            font-size: 2rem;
            letter-spacing: -.03em;
        }
        .price span {
            color: inherit;
            opacity: .65;
            font-size: .9rem;
        }
        .description {
            color: inherit;
            opacity: .72;
            font-size: .9rem;
            line-height: 1.55;
            margin-bottom: 20px;
        }
        .plan-card:not(.featured) .description { color: #64748b; opacity: 1; }
        .features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 24px;
            flex: 1;
        }
        .features li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: inherit;
            opacity: .9;
            font-size: .9rem;
        }
        .plan-card:not(.featured) .features li { color: #64748b; opacity: 1; }
        .features i {
            color: inherit;
            margin-top: 3px;
        }
        .plan-card:not(.featured) .features i { color: #0b5cff; }
        .meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
            color: inherit;
            opacity: .72;
            font-size: .8rem;
        }
        .plan-card:not(.featured) .meta { color: #64748b; opacity: 1; }
        .plan-card.featured .btn-primary {
            background: #fff;
            color: #0b5cff;
            box-shadow: none;
        }
        .empty {
            background: #fff;
            border: 1px solid rgba(8,27,58,.08);
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            color: #64748b;
        }
        .footer {
            background: #081b3a;
            color: #fff;
            padding: 56px 24px 28px;
        }
        .footer-brand {
            max-width: 420px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }
        .footer-brand img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff;
            object-fit: cover;
            margin-bottom: 14px;
        }
        .footer-brand h3 { margin-bottom: 10px; }
        .footer-brand p { color: rgba(255,255,255,.58); line-height: 1.6; font-size: .92rem; }
        .footer-bottom {
            color: rgba(255,255,255,.45);
            font-size: .9rem;
            padding-top: 24px;
        }
        @media (max-width: 900px) {
            .nav, .actions { display: none; }
            .hero { padding-top: 112px; }
            .tabs { width: 100%; display: grid; grid-template-columns: 1fr; border-radius: 20px; }
            .tab { border-radius: 16px; }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="navbar-inner">
            <a href="https://digitalfive.com.br/" class="brand">
                <img src="/assets/image/logo_quadrada.png" alt="Digital Five">
                <span>DIGITAL <strong>FIVE</strong></span>
            </a>
            <nav class="nav" aria-label="Menu principal">
                <a href="https://digitalfive.com.br/#produtos">Produtos</a>
                <a href="https://digitalfive.com.br/#solucoes">Soluções</a>
                <a href="https://digitalfive.com.br/#integracoes">Integrações</a>
                <a href="#" class="active">Preços</a>
                <a href="https://digitalfive.com.br/#blog">Blog</a>
                <a href="https://digitalfive.com.br/#contato">Contato</a>
            </nav>
            <div class="actions">
                <a href="/cliente/login.php" class="login-link">Entrar</a>
                <a href="https://wa.me/5517992347622?text=Ol%C3%A1%2C%20quero%20come%C3%A7ar%20a%20usar%20o%20ARCON!" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Começar gratuitamente</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="eyebrow"><i class="fas fa-crown"></i> Planos</div>
                <h1>Planos que crescem <span class="gradient-text">com você</span></h1>
                <p>Os planos abaixo vêm diretamente do painel administrativo e acompanham as alterações feitas pela equipe.</p>
                <div class="tabs" role="tablist" aria-label="Perfis de planos">
                    <button class="tab active" type="button" data-target="plans-mei">Microempreendedor</button>
                    <button class="tab" type="button" data-target="plans-empresa">Profissional / Empresa</button>
                </div>
            </div>
        </section>

        <?php
        $sections = [
            'plans-mei' => $plansMei,
            'plans-empresa' => $plansEmpresa,
        ];
        foreach ($sections as $sectionId => $plans):
        ?>
        <section id="<?php echo e($sectionId); ?>" class="plans-section <?php echo $sectionId === 'plans-mei' ? 'active' : ''; ?>">
            <div class="container">
                <?php if ($plans->num_rows === 0): ?>
                    <div class="empty">Nenhum plano ativo encontrado para este perfil.</div>
                <?php else: ?>
                    <div class="plans-grid">
                        <?php while ($plan = $plans->fetch_assoc()): ?>
                            <?php $featured = (int)$plan['destaque'] === 1; ?>
                            <article class="plan-card <?php echo $featured ? 'featured' : ''; ?>">
                                <?php if (!empty($plan['badge_text']) || $featured): ?>
                                    <div class="badge"><?php echo e($plan['badge_text'] ?: 'Mais popular'); ?></div>
                                <?php endif; ?>
                                <div class="category"><?php echo e($plan['categoria_nome'] ?: 'Plano'); ?></div>
                                <h2 class="plan-name"><?php echo e($plan['nome']); ?></h2>
                                <div class="price">
                                    <strong><?php echo e(moneyPlan($plan['preco'])); ?></strong>
                                    <?php if (periodPlan($plan['periodo'])): ?>
                                        <span><?php echo e(periodPlan($plan['periodo'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($plan['descricao_curta']) || !empty($plan['descricao_completa'])): ?>
                                    <p class="description"><?php echo e($plan['descricao_curta'] ?: $plan['descricao_completa']); ?></p>
                                <?php endif; ?>
                                <ul class="features">
                                    <?php foreach (array_slice($features[(int)$plan['id']] ?? [], 0, 8) as $feature): ?>
                                        <li>
                                            <i class="fas <?php echo e($feature['icone'] ?: 'fa-check-circle'); ?>"></i>
                                            <span><?php echo e($feature['caracteristica']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if (!empty($plan['prazo_entrega']) || !empty($plan['observacao'])): ?>
                                    <div class="meta">
                                        <?php if (!empty($plan['prazo_entrega'])): ?>
                                            <span><i class="fas fa-clock"></i> Entrega: <?php echo e($plan['prazo_entrega']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($plan['observacao'])): ?>
                                            <span><i class="fas fa-info-circle"></i> <?php echo e($plan['observacao']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php echo e(planWhatsappUrl($plan, $whatsapp)); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                    Começar agora <i class="fas fa-arrow-right"></i>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-brand">
                <img src="/assets/image/logo_quadrada.png" alt="Digital Five">
                <h3>DIGITAL FIVE</h3>
                <p>O ecossistema SaaS completo para a sua empresa crescer sem bagunça.</p>
            </div>
            <div class="footer-bottom">Copyright © <?php echo date('Y'); ?> Digital Five. Todos os direitos reservados.</div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.tab').forEach((tab) => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach((item) => item.classList.remove('active'));
                document.querySelectorAll('.plans-section').forEach((section) => section.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tab.dataset.target)?.classList.add('active');
            });
        });

        document.querySelectorAll('a[href="#"]').forEach((link) => {
            link.addEventListener('click', (event) => event.preventDefault());
        });
    </script>
</body>
</html>
