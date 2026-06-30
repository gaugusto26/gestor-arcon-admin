<?php
$page_title = 'Avisos do Sistema';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once '../../../../admin/api/arcon-push.php';

$arcon = new ArconPush();

// Garante tabela local
$conn->query("CREATE TABLE IF NOT EXISTS avisos_admin (
    id int AUTO_INCREMENT PRIMARY KEY,
    titulo varchar(255) NOT NULL,
    mensagem text NOT NULL,
    tipo enum('info','aviso','critico','sucesso') DEFAULT 'info',
    ativo tinyint(1) DEFAULT 1,
    supabase_id bigint DEFAULT NULL,
    criado_em datetime DEFAULT NOW(),
    atualizado_em datetime DEFAULT NOW() ON UPDATE NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg_ok  = '';
$msg_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar') {
        $titulo   = limparInput($_POST['titulo'] ?? '');
        $mensagem = limparInput($_POST['mensagem'] ?? '');
        $tipo     = limparInput($_POST['tipo'] ?? 'info');

        if ($titulo && $mensagem) {
            $stmt = $conn->prepare("INSERT INTO avisos_admin (titulo, mensagem, tipo) VALUES (?,?,?)");
            $stmt->bind_param('sss', $titulo, $mensagem, $tipo);
            $stmt->execute();
            $novoId = $conn->insert_id;

            $res = $arcon->criarAviso($titulo, $mensagem, $tipo);
            if ($res['ok'] && !empty($res['data'][0]['id'])) {
                $sbId = (int)$res['data'][0]['id'];
                $conn->query("UPDATE avisos_admin SET supabase_id = $sbId WHERE id = $novoId");
                $msg_ok = "Aviso criado e publicado no Arcon!";
            } elseif (!$arcon->isEnabled()) {
                $msg_ok = "Aviso criado (Supabase não configurado — não publicado no Arcon).";
            } else {
                $msg_ok  = "Aviso criado localmente.";
                $msg_err = "Erro ao publicar no Arcon: " . ($res['msg'] ?? 'desconhecido');
            }
        } else {
            $msg_err = "Título e mensagem são obrigatórios.";
        }
    }

    if ($acao === 'toggle') {
        $id  = (int)($_POST['id'] ?? 0);
        $row = $conn->query("SELECT ativo, supabase_id FROM avisos_admin WHERE id=$id")->fetch_assoc();
        if ($row) {
            $novoAtivo = $row['ativo'] ? 0 : 1;
            $conn->query("UPDATE avisos_admin SET ativo=$novoAtivo WHERE id=$id");
            if ($row['supabase_id'] && $arcon->isEnabled()) {
                if ($novoAtivo) {
                    $arcon->reativarAviso((int)$row['supabase_id']);
                } else {
                    $arcon->desativarAviso((int)$row['supabase_id']);
                }
            }
            $msg_ok = $novoAtivo ? "Aviso ativado." : "Aviso desativado.";
        }
    }

    if ($acao === 'excluir') {
        $id  = (int)($_POST['id'] ?? 0);
        $row = $conn->query("SELECT supabase_id FROM avisos_admin WHERE id=$id")->fetch_assoc();
        if ($row) {
            if ($row['supabase_id'] && $arcon->isEnabled()) {
                $arcon->excluirAviso((int)$row['supabase_id']);
            }
            $conn->query("DELETE FROM avisos_admin WHERE id=$id");
            $msg_ok = "Aviso excluído.";
        }
    }
}

$avisos = $conn->query("SELECT * FROM avisos_admin ORDER BY criado_em DESC")->fetch_all(MYSQLI_ASSOC);

$tipo_info = [
    'info'    => ['cor' => '#3b82f6', 'label' => 'Informação',  'icon' => 'fa-info-circle'],
    'aviso'   => ['cor' => '#f59e0b', 'label' => 'Aviso',       'icon' => 'fa-exclamation-triangle'],
    'critico' => ['cor' => '#ef4444', 'label' => 'Crítico',     'icon' => 'fa-times-circle'],
    'sucesso' => ['cor' => '#10b981', 'label' => 'Sucesso',     'icon' => 'fa-check-circle'],
];
?>

<style>
.aviso-card { background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px 24px; margin-bottom:14px; display:flex; align-items:flex-start; gap:16px; box-shadow:0 4px 12px rgba(0,0,0,0.04); }
.aviso-card.inativo { opacity:.5; }
.aviso-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.2rem; color:#fff; }
.aviso-body { flex:1; min-width:0; }
.aviso-title { font-weight:700; font-size:1rem; margin-bottom:4px; color:var(--text-primary); }
.aviso-msg { color:var(--text-secondary); font-size:.9rem; line-height:1.5; }
.aviso-meta { font-size:.75rem; color:var(--text-secondary); margin-top:8px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.aviso-actions { display:flex; gap:8px; align-items:center; flex-shrink:0; }
.btn-sm { padding:6px 12px; border-radius:8px; font-size:.8rem; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
.btn-danger  { background:#fee2e2; color:#ef4444; }
.btn-warning { background:#fef3c7; color:#d97706; }
.btn-success-sm { background:#d1fae5; color:#059669; }
.badge-sb { display:inline-flex; align-items:center; gap:4px; font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:20px; background:#ede9fe; color:#7c3aed; }
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-right:10px;"></i>
            Avisos do Sistema
        </h1>
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?= $tema=='dark'?'fa-moon':'fa-sun' ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">

        <?php if ($msg_ok): ?>
        <div style="background:rgba(16,185,129,.1);color:#059669;border:1px solid #10b981;padding:14px 20px;border-radius:12px;margin-bottom:20px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg_ok) ?>
        </div>
        <?php endif; ?>
        <?php if ($msg_err): ?>
        <div style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid #ef4444;padding:14px 20px;border-radius:12px;margin-bottom:20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg_err) ?>
        </div>
        <?php endif; ?>

        <?php if (!$arcon->isEnabled()): ?>
        <div style="background:rgba(245,158,11,.1);color:#d97706;border:1px solid #f59e0b;padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:.9rem;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Supabase não configurado.</strong> Adicione <code>SUPABASE_SERVICE_KEY</code> no <code>.env</code> para publicar avisos no Arcon em tempo real.
        </div>
        <?php else: ?>
        <div style="background:rgba(16,185,129,.1);color:#059669;border:1px solid #10b981;padding:10px 18px;border-radius:10px;margin-bottom:20px;font-size:.85rem;">
            <i class="fas fa-plug"></i> Arcon conectado — avisos publicados em tempo real no app.
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start;">

            <div>
                <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text-primary);">
                    Avisos (<?= count($avisos) ?> total · <?= count(array_filter($avisos, fn($a)=>$a['ativo'])) ?> ativos)
                </h3>

                <?php if (empty($avisos)): ?>
                <div style="text-align:center;padding:60px 20px;color:var(--text-secondary);">
                    <i class="fas fa-bell-slash" style="font-size:3rem;opacity:.3;margin-bottom:16px;display:block;"></i>
                    Nenhum aviso cadastrado.
                </div>
                <?php endif; ?>

                <?php foreach ($avisos as $av):
                    $t = $tipo_info[$av['tipo']] ?? $tipo_info['info'];
                ?>
                <div class="aviso-card <?= $av['ativo'] ? '' : 'inativo' ?>">
                    <div class="aviso-icon" style="background:<?= $t['cor'] ?>;">
                        <i class="fas <?= $t['icon'] ?>"></i>
                    </div>
                    <div class="aviso-body">
                        <div class="aviso-title"><?= htmlspecialchars($av['titulo']) ?></div>
                        <div class="aviso-msg"><?= nl2br(htmlspecialchars($av['mensagem'])) ?></div>
                        <div class="aviso-meta">
                            <span style="background:<?= $t['cor'] ?>22;color:<?= $t['cor'] ?>;padding:2px 8px;border-radius:20px;font-weight:700;font-size:.72rem;"><?= $t['label'] ?></span>
                            <span><?= date('d/m/Y H:i', strtotime($av['criado_em'])) ?></span>
                            <?php if ($av['supabase_id']): ?>
                            <span class="badge-sb"><i class="fas fa-cloud"></i> Arcon #<?= $av['supabase_id'] ?></span>
                            <?php else: ?>
                            <span style="font-size:.7rem;color:#94a3b8;">Não publicado no Arcon</span>
                            <?php endif; ?>
                            <?php if (!$av['ativo']): ?>
                            <span style="color:#94a3b8;font-size:.72rem;">● Desativado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="aviso-actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="acao" value="toggle">
                            <input type="hidden" name="id" value="<?= $av['id'] ?>">
                            <button class="btn-sm <?= $av['ativo'] ? 'btn-warning' : 'btn-success-sm' ?>" type="submit">
                                <i class="fas <?= $av['ativo'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                <?= $av['ativo'] ? 'Desativar' : 'Ativar' ?>
                            </button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir este aviso? Será removido do Arcon também.');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= $av['id'] ?>">
                            <button class="btn-sm btn-danger" type="submit">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Form novo aviso -->
            <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.04);position:sticky;top:20px;">
                <h3 style="margin:0 0 20px;font-size:1rem;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-plus-circle" style="color:#4361ee;"></i> Novo Aviso
                </h3>
                <form method="POST">
                    <input type="hidden" name="acao" value="criar">

                    <div style="margin-bottom:16px;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Tipo</label>
                        <select name="tipo" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;">
                            <option value="info">ℹ️ Informação</option>
                            <option value="aviso">⚠️ Aviso</option>
                            <option value="critico">🚨 Crítico</option>
                            <option value="sucesso">✅ Sucesso</option>
                        </select>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Título *</label>
                        <input type="text" name="titulo" required placeholder="Ex: Manutenção programada"
                            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Mensagem *</label>
                        <textarea name="mensagem" required rows="4" placeholder="Detalhe o aviso..."
                            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;resize:vertical;box-sizing:border-box;"></textarea>
                    </div>

                    <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#4361ee,#667eea);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <i class="fas fa-paper-plane"></i>
                        Publicar <?= $arcon->isEnabled() ? 'no Arcon' : 'localmente' ?>
                    </button>
                </form>
            </div>
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
