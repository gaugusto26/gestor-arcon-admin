<?php
require_once __DIR__ . '/../config.php';
precisaLoginCliente();
$cliente  = getCliente();
$pagina_atual  = basename($_SERVER['PHP_SELF']);
$modulo_atual  = isset($_GET['mod']) ? $_GET['mod'] : 'dashboard';

// ── AJAX: salvar tema no banco ────────────────────────────────────────────────
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'toggle_tema'
) {
    header('Content-Type: application/json');
    $novo_tema = ($_POST['tema'] ?? '') === 'dark' ? 'dark' : 'light';
    $cid = (int)$_SESSION['cliente_id'];
    $conn->query("UPDATE clientes SET tema_preferencia = '$novo_tema' WHERE id = $cid");
    echo json_encode(['ok' => true, 'tema' => $novo_tema]);
    exit;
}

// ── Carregar tema salvo ───────────────────────────────────────────────────────
$tema_atual = isset($cliente['tema_preferencia']) && $cliente['tema_preferencia'] === 'dark'
    ? 'dark'
    : 'light';

// Faturas pendentes
global $conn;
$cid = (int)$_SESSION['cliente_id'];
$faturas_pendentes = $conn->query("
    SELECT COUNT(*) AS total, COALESCE(SUM(valor_total),0) AS total_valor
    FROM cliente_faturas
    WHERE cliente_id = $cid AND status = 'pendente'
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $tema_atual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?> | Área do Cliente</title>
    <link rel="icon" type="image/png" href="/assets/image/logo_quadrada.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
    /* ══════════════════════════════════════════════════════════════════
       DESIGN TOKENS - TODO O SEU CSS AQUI
    ══════════════════════════════════════════════════════════════════ */
    :root {
        color-scheme: light;
        /* surfaces */
        --bg:        #eef1fb;
        --bg2:       #ffffff;
        --bg3:       #f5f7ff;
        --surf:      #ffffff;
        --surf2:     #f0f3ff;
        --surf3:     #e5eaff;
        /* borders */
        --bdr:       rgba(79,110,247,.13);
        --bdr2:      rgba(79,110,247,.22);
        /* text */
        --tx:        #0f1730;
        --tx2:       #2e3a5c;
        --tx3:       #6b7a9f;
        --tx4:       #a0aac0;
        /* accent */
        --ac:        #4f6ef7;
        --ac2:       #7c3aed;
        --ac3:       #059669;
        --warn:      #b45309;
        --danger:    #dc2626;
        --info:      #0284c7;
        /* gradients */
        --grad:      linear-gradient(135deg,#4f6ef7 0%,#7c3aed 100%);
        --grad-soft: linear-gradient(135deg,rgba(79,110,247,.10) 0%,rgba(124,58,237,.07) 100%);
        /* shadows */
        --sh:        0 2px 12px rgba(79,110,247,.09);
        --sh2:       0 8px 36px rgba(79,110,247,.14);
        /* radii */
        --r:  14px;  --r2: 10px;  --r3: 8px;
        /* layout */
        --sb-w:   254px;
        --sb-c:   66px;
        --top-h:  66px;
        /* type */
        --font: 'Inter', 'Plus Jakarta Sans', sans-serif;
        --mono: 'DM Mono', monospace;
        --ease: cubic-bezier(.4,0,.2,1);
        --tr:   all .24s var(--ease);
    }

    [data-theme="dark"] {
        color-scheme: dark;
        --bg:        #080c18;
        --bg2:       #0e1220;
        --bg3:       #131828;
        --surf:      #171d30;
        --surf2:     #1c2338;
        --surf3:     #212840;
        --bdr:       rgba(255,255,255,.07);
        --bdr2:      rgba(255,255,255,.13);
        --tx:        #dde3f5;
        --tx2:       #9aa5c4;
        --tx3:       #545f7e;
        --tx4:       #353e5c;
        --ac:        #6b85ff;
        --ac2:       #9d6fff;
        --ac3:       #34d399;
        --warn:      #fbbf24;
        --danger:    #f87171;
        --info:      #38bdf8;
        --grad:      linear-gradient(135deg,#6b85ff 0%,#9d6fff 100%);
        --grad-soft: linear-gradient(135deg,rgba(107,133,255,.13) 0%,rgba(157,111,255,.09) 100%);
        --sh:        0 2px 16px rgba(0,0,0,.4);
        --sh2:       0 8px 44px rgba(0,0,0,.55);
    }

    /* ── Base ── */
    *,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior:smooth; }
    body {
        font-family:var(--font); background:var(--bg); color:var(--tx);
        overflow-x:hidden; -webkit-font-smoothing:antialiased;
        transition:background .3s var(--ease),color .3s var(--ease);
    }
    a { text-decoration:none; color:inherit; }
    ::-webkit-scrollbar { width:5px; height:5px; }
    ::-webkit-scrollbar-track { background:var(--bg2); }
    ::-webkit-scrollbar-thumb { background:var(--surf3); border-radius:4px; }

    /* ── Layout ── */
    .dash { display:flex; min-height:100vh; }

    /* ══════════════════════════════════════════════════════════════════
       SIDEBAR - as classes são as mesmas do seu design
    ══════════════════════════════════════════════════════════════════ */
    .sidebar {
        width:var(--sb-w);
        background:var(--bg2);
        border-right:1px solid var(--bdr);
        position:fixed; inset:0 auto 0 0;
        display:flex; flex-direction:column;
        overflow:hidden;
        transition:width .26s var(--ease), transform .26s var(--ease);
        z-index:300;
    }
    .sidebar.collapsed { width:var(--sb-c); }

    /* Header */
    .sb-head {
        height:var(--top-h); display:flex; align-items:center; gap:11px;
        padding:0 15px; border-bottom:1px solid var(--bdr); flex-shrink:0;
    }
    .sb-mark {
        width:37px; height:37px; border-radius:11px;
        background:var(--grad); display:grid; place-items:center;
        color:#fff; font-weight:800; font-size:.9rem; flex-shrink:0;
        box-shadow:0 4px 16px rgba(79,110,247,.3);
    }
    .sb-brand {
        font-size:.93rem; font-weight:800; color:var(--tx);
        white-space:nowrap; overflow:hidden;
        transition:opacity .2s, width .26s var(--ease);
    }
    .sidebar.collapsed .sb-brand { opacity:0; width:0; }

    /* Profile */
    .sb-prof { padding:11px; border-bottom:1px solid var(--bdr); flex-shrink:0; }
    .sb-prof-pill {
        display:flex; align-items:center; gap:9px; padding:9px;
        border-radius:var(--r2); background:var(--grad-soft);
        border:1px solid var(--bdr); overflow:hidden;
    }
    .sb-av {
        width:34px; height:34px; border-radius:9px;
        background:var(--grad); display:grid; place-items:center;
        font-weight:700; font-size:.9rem; color:#fff; flex-shrink:0;
    }
    .sb-prof-info { flex:1; min-width:0; overflow:hidden; }
    .sb-prof-name { font-size:.8rem; font-weight:700; color:var(--tx); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sb-prof-role { font-size:.65rem; color:var(--tx3); margin-top:1px; }
    .sidebar.collapsed .sb-prof-info { display:none; }

    /* Nav */
    .sb-nav { padding:9px 7px; flex:1; overflow-y:auto; overflow-x:hidden; }
    .sb-section-lbl {
        font-size:.58rem; font-weight:700; letter-spacing:.08em;
        text-transform:uppercase; color:var(--tx4);
        padding:8px 11px 5px; white-space:nowrap;
        transition:opacity .2s;
    }
    .sidebar.collapsed .sb-section-lbl { opacity:0; }

    .sb-link {
        display:flex; align-items:center; gap:10px;
        padding:9px 11px; border-radius:var(--r2);
        color:var(--tx3); font-size:.82rem; font-weight:500;
        transition:var(--tr); margin-bottom:1px;
        position:relative; white-space:nowrap; cursor:pointer;
    }
    .sb-link:hover { background:var(--surf2); color:var(--tx); }
    .sb-link.active {
        background:var(--grad-soft); color:var(--ac); font-weight:600;
    }
    .sb-link.active::before {
        content:''; position:absolute; left:0; top:22%; bottom:22%;
        width:3px; border-radius:0 3px 3px 0; background:var(--ac);
    }
    .sb-ico { width:17px; text-align:center; font-size:.9rem; flex-shrink:0; }
    .sb-txt { flex:1; transition:opacity .2s, width .26s var(--ease); overflow:hidden; }
    .sidebar.collapsed .sb-txt { opacity:0; width:0; }

    .sb-badge {
        font-size:.58rem; font-weight:700; font-family:var(--mono);
        background:var(--danger); color:#fff;
        padding:2px 5px; border-radius:20px; flex-shrink:0;
    }
    .sidebar.collapsed .sb-badge {
        position:absolute; right:5px; top:5px;
        padding:1px 3px; font-size:.52rem;
    }

    /* Bottom */
    .sb-foot { padding:9px 7px; border-top:1px solid var(--bdr); flex-shrink:0; }

    /* ── Toggle pill ── */
    .sb-tog {
        position:fixed; left:var(--sb-w); top:50%; transform:translateY(-50%);
        z-index:301; transition:left .26s var(--ease);
    }
    .sb-tog.collapsed { left:var(--sb-c); }
    .sb-tog-btn {
        width:20px; height:46px; background:var(--bg2);
        border:1px solid var(--bdr2); border-left:none;
        border-radius:0 22px 22px 0;
        display:grid; place-items:center; cursor:pointer;
        color:var(--tx3); transition:var(--tr);
    }
    .sb-tog-btn:hover { background:var(--surf2); color:var(--ac); }
    .sb-tog-btn i { font-size:.6rem; transition:transform .26s var(--ease); }
    .sb-tog.collapsed .sb-tog-btn i { transform:rotate(180deg); }

    /* ══════════════════════════════════════════════════════════════════
       MAIN
    ══════════════════════════════════════════════════════════════════ */
    .main {
        flex:1; margin-left:var(--sb-w);
        display:flex; flex-direction:column; min-width:0;
        transition:margin-left .26s var(--ease);
    }
    .main.expanded { margin-left:var(--sb-c); }

    /* ── Topbar ── */
    .topbar {
        height:var(--top-h); background:var(--bg2);
        border-bottom:1px solid var(--bdr);
        padding:0 22px; display:flex; align-items:center;
        justify-content:space-between; position:sticky; top:0; z-index:200;
        transition:background .3s var(--ease);
    }
    .top-left { display:flex; align-items:center; gap:10px; }
    .top-pg-ico {
        width:32px; height:32px; border-radius:var(--r3);
        background:var(--grad-soft); display:grid; place-items:center;
        color:var(--ac); font-size:.8rem;
    }
    .top-pg-title { font-size:1.05rem; font-weight:700; color:var(--tx); }
    .top-right { display:flex; align-items:center; gap:9px; }

    /* Icon btn */
    .ico-btn {
        width:36px; height:36px; border-radius:var(--r3);
        background:var(--surf2); border:1px solid var(--bdr);
        display:grid; place-items:center; cursor:pointer;
        color:var(--tx3); transition:var(--tr); position:relative;
        flex-shrink:0;
    }
    .ico-btn:hover { background:var(--surf3); color:var(--tx); border-color:var(--bdr2); }
    .ico-btn i { font-size:.88rem; }
    .ico-dot {
        position:absolute; top:5px; right:5px;
        width:7px; height:7px; border-radius:50%;
        background:var(--danger); border:2px solid var(--bg2);
    }

    /* Theme toggle */
    .theme-tog {
        width:58px; height:30px; border-radius:20px;
        background:var(--surf2); border:1px solid var(--bdr2);
        position:relative; cursor:pointer; display:flex;
        align-items:center; padding:3px; transition:var(--tr);
        flex-shrink:0;
    }
    .theme-tog:hover { border-color:var(--ac); }
    .theme-thumb {
        width:24px; height:24px; border-radius:50%;
        background:var(--grad); display:grid; place-items:center;
        color:#fff; font-size:.65rem;
        box-shadow:0 2px 8px rgba(79,110,247,.35);
        transition:transform .28s var(--ease);
    }
    [data-theme="dark"] .theme-thumb { transform:translateX(28px); }

    /* User btn */
    .user-btn {
        display:flex; align-items:center; gap:8px;
        padding:4px 10px 4px 4px;
        background:var(--surf2); border:1px solid var(--bdr);
        border-radius:40px; cursor:pointer; transition:var(--tr);
        position:relative;
    }
    .user-btn:hover { background:var(--surf3); border-color:var(--bdr2); }
    .user-btn .sb-av { width:27px; height:27px; border-radius:50%; font-size:.72rem; }
    .user-btn-n { font-size:.8rem; font-weight:600; color:var(--tx); }
    .user-chevron { font-size:.65rem; color:var(--tx3); transition:transform .2s; }
    .user-btn.open .user-chevron { transform:rotate(180deg); }

    /* Dropdown */
    .ddrop {
        position:absolute; top:calc(100% + 7px); right:0;
        min-width:170px; background:var(--bg2);
        border:1px solid var(--bdr2); border-radius:var(--r);
        padding:5px; box-shadow:var(--sh2);
        display:none; z-index:500;
        animation:dIn .17s var(--ease);
    }
    .ddrop.open { display:block; }
    @keyframes dIn {
        from { opacity:0; transform:translateY(-7px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .ddrop a {
        display:flex; align-items:center; gap:9px;
        padding:8px 10px; border-radius:var(--r3);
        font-size:.8rem; color:var(--tx2); transition:var(--tr);
    }
    .ddrop a:hover { background:var(--surf2); color:var(--tx); }
    .ddrop a i { width:15px; color:var(--tx3); font-size:.82rem; }
    .ddrop hr { border:none; border-top:1px solid var(--bdr); margin:3px 0; }
    .ddrop a.dd-danger { color:var(--danger); }
    .ddrop a.dd-danger i { color:var(--danger); }

    /* Overlay */
    .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:299; backdrop-filter:blur(3px); }
    .overlay.show { display:block; }

    .admin-bridge-banner {
        position: fixed;
        top: 0;
        left: var(--sb-w);
        right: 0;
        z-index: 700;
        min-height: 48px;
        padding: 10px 22px;
        background: #081b3a;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid rgba(255,255,255,.14);
        transition: left .26s var(--ease);
    }

    .admin-bridge-active .main {
        padding-top: 48px;
    }

    .admin-bridge-banner strong {
        font-weight: 800;
    }

    .admin-bridge-copy {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: .86rem;
        line-height: 1.4;
    }

    .admin-bridge-copy i {
        color: #8fb3ff;
    }

    .admin-bridge-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 13px;
        border-radius: 999px;
        background: #ffffff;
        color: #0b5cff;
        font-size: .8rem;
        font-weight: 800;
        white-space: nowrap;
        transition: var(--tr);
    }

    .admin-bridge-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(0,0,0,.18);
    }

    /* ══════════════════════════════════════════════════════════════════
       CONTENT AREA (shared classes reused by all pages)
    ══════════════════════════════════════════════════════════════════ */
    .content { padding:22px; flex:1; }

    /* Welcome banner */
    .welcome {
        background:var(--grad); border-radius:var(--r);
        padding:24px 28px; margin-bottom:18px;
        display:flex; align-items:center; justify-content:space-between;
        position:relative; overflow:hidden; box-shadow:var(--sh2);
    }
    .welcome::before {
        content:''; position:absolute; inset:0; pointer-events:none;
        background:url("data:image/svg+xml,%3Csvg width='52' height='52' viewBox='0 0 52 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.04'%3E%3Ccircle cx='26' cy='26' r='10'/%3E%3Ccircle cx='0' cy='0' r='8'/%3E%3Ccircle cx='52' cy='52' r='8'/%3E%3C/g%3E%3C/svg%3E");
    }
    .welcome-copy h1 { font-size:1.3rem; font-weight:800; color:#fff; margin-bottom:4px; position:relative; }
    .welcome-copy p  { color:rgba(255,255,255,.72); font-size:.85rem; position:relative; }
    .welcome-emoji   { font-size:2.8rem; line-height:1; flex-shrink:0; filter:drop-shadow(0 4px 10px rgba(0,0,0,.25)); }

    /* Stat cards */
    .stats-grid {
        display:grid; grid-template-columns:repeat(4,1fr);
        gap:13px; margin-bottom:18px;
    }
    .stat-card {
        background:var(--surf); border:1px solid var(--bdr); border-radius:var(--r);
        padding:18px; transition:var(--tr); position:relative; overflow:hidden;
    }
    .stat-card:hover { border-color:var(--bdr2); box-shadow:var(--sh2); transform:translateY(-2px); }
    .stat-card::after {
        content:''; position:absolute; bottom:0; left:0; right:0; height:2px;
        background:var(--grad); transform:scaleX(0); transform-origin:left;
        transition:transform .3s var(--ease);
    }
    .stat-card:hover::after { transform:scaleX(1); }
    .stat-ico { width:40px; height:40px; border-radius:10px; display:grid; place-items:center; font-size:1rem; margin-bottom:12px; }
    .stat-val { font-size:1.5rem; font-weight:800; color:var(--tx); font-family:var(--mono); letter-spacing:-.03em; line-height:1; margin-bottom:3px; }
    .stat-val.sm { font-size:1.05rem; }
    .stat-lbl { font-size:.75rem; color:var(--tx3); margin-bottom:7px; }
    .stat-sub { font-size:.7rem; display:flex; align-items:center; gap:4px; }

    /* Chart cards — FIXED */
    .charts-grid {
        display:grid; grid-template-columns:repeat(2,1fr);
        gap:13px; margin-bottom:18px;
    }
    .chart-card {
        background:var(--surf); border:1px solid var(--bdr); border-radius:var(--r);
        padding:18px;
        overflow:hidden; min-width:0;
    }
    .chart-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
    .chart-ttl { font-size:.82rem; font-weight:700; color:var(--tx); display:flex; align-items:center; gap:6px; }
    .chart-ttl i { color:var(--ac); font-size:.77rem; }
    .chart-box {
        position:relative; width:100%; height:205px; overflow:hidden;
    }
    .chart-box canvas {
        position:absolute !important; inset:0 !important;
        width:100% !important; height:100% !important;
    }

    /* Quick actions */
    .actions-grid {
        display:grid; grid-template-columns:repeat(4,1fr);
        gap:11px; margin-bottom:18px;
    }
    .act-card {
        background:var(--surf); border:1px solid var(--bdr); border-radius:var(--r);
        padding:16px 12px; display:flex; flex-direction:column;
        align-items:center; gap:8px; cursor:pointer; transition:var(--tr); text-align:center;
    }
    .act-card:hover { border-color:var(--ac); background:var(--grad-soft); transform:translateY(-2px); box-shadow:var(--sh); }
    .act-ico { width:44px; height:44px; border-radius:12px; display:grid; place-items:center; font-size:1.1rem; transition:var(--tr); }
    .act-card:hover .act-ico { background:var(--grad) !important; color:#fff !important; box-shadow:0 4px 14px rgba(79,110,247,.3); }
    .act-lbl { font-size:.74rem; font-weight:600; color:var(--tx2); }

    /* Sec title */
    .sec-ttl { font-size:.9rem; font-weight:700; color:var(--tx); display:flex; align-items:center; gap:7px; margin:22px 0 13px; }
    .sec-ttl i { color:var(--ac); }

    /* Systems */
    .systems-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:13px; margin-bottom:18px; }
    .sys-card { background:var(--surf); border:1px solid var(--bdr); border-radius:var(--r); padding:18px; transition:var(--tr); }
    .sys-card:hover { border-color:var(--bdr2); box-shadow:var(--sh); transform:translateY(-2px); }
    .sys-hd { display:flex; align-items:center; gap:11px; margin-bottom:14px; }
    .sys-ico { width:42px; height:42px; border-radius:10px; display:grid; place-items:center; font-size:1rem; flex-shrink:0; }
    .sys-name { font-size:.87rem; font-weight:700; color:var(--tx); margin-bottom:3px; }

    .chip { display:inline-flex; align-items:center; gap:4px; font-size:.62rem; font-weight:700; padding:2px 7px; border-radius:20px; }
    .chip::before { content:''; width:4px; height:4px; border-radius:50%; background:currentColor; flex-shrink:0; }
    .chip-warn  { background:rgba(180,83,9,.1);   color:var(--warn); }
    .chip-green { background:rgba(5,150,105,.1);  color:var(--ac3); }
    .chip-blue  { background:rgba(79,110,247,.1); color:var(--ac); }
    .chip-red   { background:rgba(220,38,38,.09); color:var(--danger); }
    [data-theme="dark"] .chip-warn  { background:rgba(251,191,36,.12);  color:var(--warn); }
    [data-theme="dark"] .chip-green { background:rgba(52,211,153,.12);  color:var(--ac3); }
    [data-theme="dark"] .chip-blue  { background:rgba(107,133,255,.12); color:var(--ac); }
    [data-theme="dark"] .chip-red   { background:rgba(248,113,113,.1);  color:var(--danger); }

    .prog-track { width:100%; height:4px; background:var(--surf3); border-radius:3px; overflow:hidden; margin-bottom:4px; }
    .prog-fill  { height:100%; background:var(--grad); border-radius:3px; transition:width .6s var(--ease); }
    .prog-lbls  { display:flex; justify-content:space-between; font-size:.67rem; color:var(--tx4); }

    .sys-foot { display:flex; align-items:center; justify-content:space-between; margin-top:13px; padding-top:13px; border-top:1px solid var(--bdr); }
    .sys-price { font-family:var(--mono); font-size:.87rem; font-weight:500; color:var(--ac); }
    .sys-price small { font-family:var(--font); font-size:.65rem; color:var(--tx4); font-weight:400; }
    .btn-sm { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; border-radius:var(--r3); background:var(--surf2); border:1px solid var(--bdr2); color:var(--tx3); font-size:.72rem; font-family:var(--font); cursor:pointer; transition:var(--tr); }
    .btn-sm:hover { background:var(--ac); border-color:var(--ac); color:#fff; }

    /* Alert card */
    .alert-wrap { background:var(--surf); border:1px solid var(--bdr); border-radius:var(--r); overflow:hidden; margin-bottom:22px; }
    .alert-row { display:flex; align-items:center; gap:11px; padding:13px 17px; border-bottom:1px solid var(--bdr); transition:var(--tr); }
    .alert-row:last-child { border-bottom:none; }
    .alert-row:hover { background:var(--surf2); }
    .alert-ico { width:34px; height:34px; border-radius:9px; display:grid; place-items:center; font-size:.78rem; flex-shrink:0; }
    .alert-info { flex:1; min-width:0; }
    .alert-ttl { font-size:.82rem; font-weight:600; color:var(--tx); margin-bottom:2px; }
    .alert-dt  { font-size:.7rem; color:var(--tx3); }
    .alert-val { font-family:var(--mono); font-size:.85rem; font-weight:500; flex-shrink:0; }

    /* ══════════════════════════════════════════════════════════════════
       RESPONSIVE
    ══════════════════════════════════════════════════════════════════ */
    @media (max-width: 1100px) {
        .stats-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width: 900px) {
        .charts-grid { grid-template-columns:1fr; }
        .chart-box   { height:190px; }
    }
    @media (max-width: 768px) {
        .sidebar { transform:translateX(-100%); width:var(--sb-w) !important; }
        .sidebar.mob-open { transform:translateX(0); box-shadow:var(--sh2); }
        .sb-tog  { left:0 !important; }
        .sidebar.mob-open ~ .sb-tog { left:var(--sb-w) !important; }
        .admin-bridge-banner { left:0; flex-direction:column; align-items:flex-start; }
        .main    { margin-left:0 !important; }
        .content { padding:14px; }
        .topbar  { padding:0 14px; }
        .stats-grid   { grid-template-columns:repeat(2,1fr); gap:10px; }
        .actions-grid { grid-template-columns:repeat(2,1fr); gap:10px; }
        .welcome      { padding:18px; }
        .welcome-copy h1 { font-size:1.05rem; }
        .welcome-emoji   { font-size:2rem; }
        .user-btn-n  { display:none; }
    }
    @media (max-width: 480px) {
        .stats-grid   { grid-template-columns:1fr; gap:9px; }
        .actions-grid { grid-template-columns:repeat(2,1fr); }
        .systems-grid { grid-template-columns:1fr; }
        .chart-box    { height:170px; }
        .stat-val     { font-size:1.3rem; }
    }
    </style>
    <link rel="stylesheet" href="/assets/css/arcon-identity.css">
</head>
<body class="<?php echo !empty($_SESSION['admin_impersonando_cliente']) ? 'admin-bridge-active' : ''; ?>">
<div class="dash" id="app">
<div class="overlay" id="overlay"></div>
<?php if (!empty($_SESSION['admin_impersonando_cliente'])): ?>
<div class="admin-bridge-banner">
    <div class="admin-bridge-copy">
        <i class="fas fa-user-shield"></i>
        <span>
            <strong>Visualização integrada Digital Five + ARCON:</strong>
            você está acessando como <?php echo htmlspecialchars($cliente['nome'] ?? 'cliente'); ?>.
        </span>
    </div>
    <a href="/cliente/logout.php" class="admin-bridge-action">
        <i class="fas fa-arrow-left"></i>
        Voltar ao admin
    </a>
</div>
<?php endif; ?>
