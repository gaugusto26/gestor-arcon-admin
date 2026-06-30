<?php
$page_title = 'Relatório dos Usuários';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
?>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-chart-pie" style="color: #4361ee; margin-right: 10px;"></i>
            Relatório dos Usuários
        </h1>
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo ($tema ?? 'light') == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area" style="display:flex; align-items:center; justify-content:center; min-height:60vh;">
        <div style="text-align:center; max-width:480px;">
            <div style="width:100px;height:100px;background:linear-gradient(135deg,#4361ee,#667eea);border-radius:28px;display:flex;align-items:center;justify-content:center;margin:0 auto 28px;box-shadow:0 20px 40px rgba(67,97,238,0.25);">
                <i class="fas fa-code" style="font-size:2.8rem;color:#fff;"></i>
            </div>
            <h2 style="font-size:1.8rem;font-weight:700;margin-bottom:12px;color:var(--text-primary);">Em desenvolvimento</h2>
            <p style="color:var(--text-secondary);font-size:1.05rem;line-height:1.6;margin-bottom:32px;">
                Esta funcionalidade está sendo desenvolvida e estará disponível em breve.
            </p>
            <a href="/admin/dashboard/index.php" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg,#4361ee,#667eea);color:#fff;border-radius:12px;text-decoration:none;font-weight:600;box-shadow:0 10px 20px rgba(67,97,238,0.2);">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<script>
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = document.getElementById('themeIcon');
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const t = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.body.setAttribute('data-theme', t);
        document.cookie = `admin_theme=${t}; path=/`;
        themeIcon.className = t === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>
