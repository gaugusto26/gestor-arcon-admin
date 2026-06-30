<?php
$page_title = 'Configurações do Sistema';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

// Garante tabela de config global
$conn->query("CREATE TABLE IF NOT EXISTS config_sistema (
    id         int NOT NULL DEFAULT 1,
    site_nome  varchar(200) DEFAULT 'Gestor Arcon Admin',
    site_url   varchar(255) DEFAULT '',
    logo_url   varchar(255) DEFAULT '/assets/image/logoarcon_quadrada.png.png',
    smtp_host  varchar(150) DEFAULT '',
    smtp_port  int          DEFAULT 587,
    smtp_user  varchar(150) DEFAULT '',
    smtp_pass  varchar(255) DEFAULT '',
    smtp_secure enum('tls','ssl') DEFAULT 'tls',
    smtp_from_nome  varchar(150) DEFAULT '',
    smtp_from_email varchar(150) DEFAULT '',
    updated_at timestamp    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Garante row padrão
$conn->query("INSERT IGNORE INTO config_sistema (id, site_nome, site_url)
    VALUES (1, '" . $conn->real_escape_string(SITE_NAME) . "', '" . $conn->real_escape_string(SITE_URL) . "')");

$msg_ok  = '';
$msg_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $fields = [
            'site_nome'       => limparInput($_POST['site_nome'] ?? ''),
            'site_url'        => limparInput($_POST['site_url'] ?? ''),
            'logo_url'        => limparInput($_POST['logo_url'] ?? ''),
            'smtp_host'       => limparInput($_POST['smtp_host'] ?? ''),
            'smtp_port'       => (int)($_POST['smtp_port'] ?? 587),
            'smtp_user'       => limparInput($_POST['smtp_user'] ?? ''),
            'smtp_secure'     => limparInput($_POST['smtp_secure'] ?? 'tls'),
            'smtp_from_nome'  => limparInput($_POST['smtp_from_nome'] ?? ''),
            'smtp_from_email' => limparInput($_POST['smtp_from_email'] ?? ''),
        ];
        // Senha só atualiza se preenchida
        if (!empty($_POST['smtp_pass'])) {
            $fields['smtp_pass'] = limparInput($_POST['smtp_pass']);
        }

        $sets = implode(', ', array_map(fn($k, $v) => "`$k` = '$v'", array_keys($fields), $fields));
        if ($conn->query("UPDATE config_sistema SET $sets WHERE id = 1")) {
            $msg_ok = 'Configurações salvas com sucesso!';
        } else {
            $msg_err = 'Erro ao salvar: ' . $conn->error;
        }
    }

    if ($acao === 'testar_smtp') {
        require_once '../../../../vendor/autoload.php';
        use PHPMailer\PHPMailer\PHPMailer;

        $cfg = $conn->query("SELECT * FROM config_sistema WHERE id=1")->fetch_assoc();
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['smtp_user'];
            $mail->Password   = $cfg['smtp_pass'];
            $mail->SMTPSecure = $cfg['smtp_secure'];
            $mail->Port       = (int)$cfg['smtp_port'];
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($cfg['smtp_from_email'] ?: $cfg['smtp_user'], $cfg['smtp_from_nome'] ?: $cfg['site_nome']);
            $mail->addAddress($_SESSION['admin_email'] ?? $cfg['smtp_from_email']);
            $mail->isHTML(true);
            $mail->Subject = 'Teste SMTP — ' . $cfg['site_nome'];
            $mail->Body    = '<h2>✅ SMTP funcionando!</h2><p>Configuração do <strong>' . htmlspecialchars($cfg['site_nome']) . '</strong> está correta.</p>';
            $mail->send();
            $msg_ok = 'E-mail de teste enviado! Verifique sua caixa de entrada.';
        } catch (\Exception $e) {
            $msg_err = 'Erro SMTP: ' . $mail->ErrorInfo;
        }
    }
}

$config = $conn->query("SELECT * FROM config_sistema WHERE id=1")->fetch_assoc();
?>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-cog" style="color:#4361ee;margin-right:10px;"></i>
            Configurações do Sistema
        </h1>
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?= $tema=='dark'?'fa-moon':'fa-sun' ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area" style="max-width:860px;">

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

        <form method="POST">
            <input type="hidden" name="acao" value="salvar">

            <!-- Identidade -->
            <div style="background:#fff;border:1px solid var(--border);border-radius:20px;padding:28px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                <h3 style="font-size:1.1rem;margin:0 0 22px;padding-bottom:14px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-id-card" style="color:#4361ee;"></i> Identidade do Sistema
                </h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Nome do Sistema</label>
                        <input type="text" name="site_nome" value="<?= htmlspecialchars($config['site_nome']) ?>"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">URL do Site</label>
                        <input type="text" name="site_url" value="<?= htmlspecialchars($config['site_url']) ?>"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>

                <div>
                    <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">URL do Logo (exibido nos e-mails)</label>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <input type="text" name="logo_url" value="<?= htmlspecialchars($config['logo_url']) ?>"
                            style="flex:1;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;">
                        <?php if ($config['logo_url']): ?>
                        <img src="<?= htmlspecialchars($config['logo_url']) ?>" alt="Logo" style="height:44px;width:44px;object-fit:contain;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff;">
                        <?php endif; ?>
                    </div>
                    <p style="font-size:.78rem;color:var(--text-muted);margin-top:6px;">Logos disponíveis: <code>/assets/image/logoarcon_quadrada.png.png</code> · <code>/assets/image/logo_horizontal.png</code></p>
                </div>
            </div>

            <!-- SMTP -->
            <div style="background:#fff;border:1px solid var(--border);border-radius:20px;padding:28px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
                <h3 style="font-size:1.1rem;margin:0 0 22px;padding-bottom:14px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-server" style="color:#4361ee;"></i> Servidor SMTP (e-mails do sistema)
                </h3>

                <!-- Atalhos -->
                <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
                    <?php foreach([
                        ['Gmail',   'smtp.gmail.com',            587, 'tls', '#ea4335','fab fa-google'],
                        ['Outlook', 'smtp-mail.outlook.com',     587, 'tls', '#0078d4','fab fa-microsoft'],
                        ['Hostinger','smtp.hostinger.com',       465, 'ssl', '#673de6','fas fa-server'],
                        ['Locaweb', 'email-ssl.com.br',          465, 'ssl', '#f97316','fas fa-server'],
                    ] as [$nome,$host,$port,$sec,$cor,$icon]): ?>
                    <button type="button" onclick="preencherSmtp('<?=$host?>',<?=$port?>,'<?=$sec?>')"
                        style="padding:8px 14px;background:#f8faff;border:1px solid var(--border);border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;color:<?=$cor?>;">
                        <i class="<?=$icon?>"></i> <?=$nome?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <div style="display:grid;grid-template-columns:1fr 120px 120px;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Host SMTP</label>
                        <input type="text" name="smtp_host" id="smtp_host" value="<?= htmlspecialchars($config['smtp_host']) ?>"
                            placeholder="smtp.gmail.com"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Porta</label>
                        <input type="number" name="smtp_port" id="smtp_port" value="<?= $config['smtp_port'] ?>"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Criptografia</label>
                        <select name="smtp_secure" id="smtp_secure"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;">
                            <option value="tls" <?= $config['smtp_secure']==='tls'?'selected':'' ?>>TLS</option>
                            <option value="ssl" <?= $config['smtp_secure']==='ssl'?'selected':'' ?>>SSL</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Usuário (e-mail de login)</label>
                        <input type="text" name="smtp_user" value="<?= htmlspecialchars($config['smtp_user']) ?>"
                            autocomplete="username"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Senha <span style="font-weight:400;font-size:.78rem;color:var(--text-muted);">(deixe em branco para manter)</span></label>
                        <input type="password" name="smtp_pass" placeholder="••••••••"
                            autocomplete="new-password"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">Nome do Remetente</label>
                        <input type="text" name="smtp_from_nome" value="<?= htmlspecialchars($config['smtp_from_nome'] ?? SITE_NAME) ?>"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-weight:600;font-size:.88rem;">E-mail do Remetente</label>
                        <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($config['smtp_from_email'] ?? '') ?>"
                            style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:#f8faff;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="submit" formaction="?testar=1" name="acao" value="testar_smtp"
                    style="padding:13px 24px;background:#d1fae5;color:#059669;border:none;border-radius:12px;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-vial"></i> Testar SMTP
                </button>
                <button type="submit"
                    style="padding:13px 28px;background:linear-gradient(135deg,#4361ee,#667eea);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-save"></i> Salvar Configurações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function preencherSmtp(host, port, secure) {
    document.getElementById('smtp_host').value   = host;
    document.getElementById('smtp_port').value   = port;
    document.getElementById('smtp_secure').value = secure;
}
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
