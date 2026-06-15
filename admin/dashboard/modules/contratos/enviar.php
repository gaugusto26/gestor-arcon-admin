<?php
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'config.php';
require_once '../../../../config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Busca contrato com dados do cliente
$stmt = $conn->prepare("
    SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
    FROM contratos c
    JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$contrato = $stmt->get_result()->fetch_assoc();

if(!$contrato) {
    header('Location: index.php');
    exit;
}

$erro = null;

// Envia e-mail ao submeter o formulário
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    

    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sistemasntw@gmail.com';
        $mail->Password   = 'dcvj lezc qwoc yvim';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        $mail->setFrom('sistemasntw@gmail.com', 'NTW - New Software');
        $mail->addAddress($contrato['cliente_email'], $contrato['cliente_nome']);
        
        $link = SITE_URL . '/cliente/contrato.php?token=' . base64_encode($contrato['id']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Contrato para assinatura - NTW';
        $mail->Body    = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; background: #f8faff; }
                .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 26px; }
                .header p  { margin-top: 10px; color: rgba(255,255,255,0.9); }
                .content { padding: 40px 30px; }
                .content p { color: #475569; font-size: 16px; line-height: 1.8; }
                .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; text-decoration: none; padding: 16px 40px; border-radius: 50px; font-weight: 600; font-size: 16px; margin: 20px 0; }
                .link-box { background: #f8faff; border: 1px dashed #667eea; border-radius: 12px; padding: 15px; word-break: break-all; font-size: 13px; color: #667eea; margin: 15px 0; }
                .footer { background: #f8faff; padding: 25px 30px; text-align: center; border-top: 1px solid #e2e8f0; }
                .footer p { color: #94a3b8; font-size: 13px; margin: 4px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📄 Contrato para Assinatura</h1>
                    <p>NTW - New Software</p>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$contrato['cliente_nome']}</strong>!</p>
                    <p>Preparamos seu contrato para assinatura digital. Clique no botão abaixo para visualizar e assinar:</p>
                    <p style='text-align: center;'>
                        <a href='{$link}' class='button'>VER E ASSINAR CONTRATO</a>
                    </p>
                    <p>Ou copie o link abaixo:</p>
                    <div class='link-box'>{$link}</div>
                    <p>Qualquer dúvida, nossa equipe está à disposição para ajudar.</p>
                    <p>Atenciosamente,<br><strong>Equipe NTW - New Software</strong></p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " NTW - New Software. Todos os direitos reservados.</p>
                    <p>Este é um e-mail automático, por favor não responda.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        
        // Atualiza status do contrato
        $conn->query("UPDATE contratos SET status = 'enviado' WHERE id = $id");
        
        // Registra histórico
        $stmt_hist = $conn->prepare("
            INSERT INTO contrato_historico (contrato_id, acao, created_by, created_at)
            VALUES (?, 'enviado para cliente', ?, NOW())
        ");
        $stmt_hist->bind_param("ii", $id, $_SESSION['admin_id']);
        $stmt_hist->execute();
        
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Contrato enviado com sucesso para ' . $contrato['cliente_email'] . '!'];
        header('Location: visualizar.php?id=' . $id);
        exit;
        
    } catch (Exception $e) {
        $erro = "Erro ao enviar e-mail: " . $mail->ErrorInfo;
    }
}

// =============================================
// SÓ AGORA INCLUI O HTML
// =============================================
$page_title = 'Enviar Contrato';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
?>

<style>
.enviar-container {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.enviar-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 50px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.enviar-icon {
    font-size: 5rem;
    color: #4361ee;
    margin-bottom: 20px;
}

.enviar-card h2 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: var(--text-primary);
}

.enviar-card p {
    color: var(--text-muted);
    margin-bottom: 30px;
}

.contrato-info {
    background: #f8faff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    margin: 30px 0;
    text-align: left;
}

.contrato-info p {
    margin: 10px 0;
    color: var(--text-primary);
}

.btn {
    padding: 15px 40px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(102,126,234,0.3);
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

.alert-erro {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
    text-align: left;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-paper-plane" style="color: #4361ee; margin-right: 10px;"></i>
            Enviar Contrato
        </h1>
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="enviar-container">

            <?php if($erro): ?>
            <div class="alert-erro">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
            <?php endif; ?>

            <div class="enviar-card">
                <div class="enviar-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                
                <h2>Enviar Contrato</h2>
                <p>O contrato será enviado para o e-mail do cliente para assinatura digital.</p>
                
                <div class="contrato-info">
                    <p><strong>Contrato:</strong> <?php echo htmlspecialchars($contrato['numero_contrato']); ?></p>
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($contrato['cliente_nome']); ?></p>
                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($contrato['cliente_email']); ?></p>
                </div>
                
                <form method="POST">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Confirmar Envio
                    </button>
                    <a href="visualizar.php?id=<?php echo $id; ?>" class="btn btn-secondary" style="margin-left: 10px;">
                        Cancelar
                    </a>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = document.getElementById('themeIcon');
const body        = document.body;

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