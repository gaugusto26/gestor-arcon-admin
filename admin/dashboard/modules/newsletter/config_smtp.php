<?php

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$page_title = 'Configurações SMTP';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';
require_once '../../../../config.php';

$config = getNewsletterConfig();
$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Testar conexão SMTP
    if(isset($_POST['testar'])) {
        
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = $_POST['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_POST['smtp_user'];
            $mail->Password   = $_POST['smtp_pass'];
            $mail->SMTPSecure = $_POST['smtp_secure'];
            $mail->Port       = $_POST['smtp_port'];
            
            $mail->setFrom($_POST['remetente_email'], $_POST['remetente_nome']);
            $mail->addAddress($_SESSION['admin_email'] ?? 'universepett@gmail.com');
            
            $mail->isHTML(true);
            $mail->Subject = 'Teste de Configuração SMTP - Arcon Newsletter';
            $mail->Body    = '<h1>Teste de Conexão SMTP</h1><p>Se você está vendo este e-mail, suas configurações SMTP estão funcionando corretamente!</p>';
            
            $mail->send();
            $sucesso = 'Teste enviado com sucesso! Verifique sua caixa de entrada.';
        } catch (Exception $e) {
            $erros[] = 'Erro no teste: ' . $mail->ErrorInfo;
        }
    }
    
    // Salvar configurações
    if(isset($_POST['salvar'])) {
        if(salvarConfigSMTP($_POST)) {
            $sucesso = 'Configurações salvas com sucesso!';
            $config = getNewsletterConfig(); // Recarrega
        } else {
            $erros[] = 'Erro ao salvar configurações';
        }
    }
}
?>

<style>
.config-container {
    max-width: 800px;
    margin: 0 auto;
}

.config-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
}

.card-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--accent);
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title i {
    color: var(--accent);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
}

.form-group label i {
    color: var(--accent);
    margin-right: 5px;
    width: 20px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.95rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.btn {
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: #0a4fe0;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

.btn-test {
    background: #10b981;
    color: white;
}

.btn-test:hover {
    background: #059669;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #22c55e20;
    color: #22c55e;
    border: 1px solid #22c55e;
}

.alert-error {
    background: #ef444420;
    color: #ef4444;
    border: 1px solid #ef4444;
}

.info-box {
    background: var(--accent-light);
    border: 1px solid var(--accent);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    color: var(--accent);
}

.info-box i {
    margin-right: 8px;
}

.smtp-examples {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.smtp-example {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.smtp-example:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
}

.smtp-example h4 {
    color: var(--text-primary);
    margin-bottom: 5px;
}

.smtp-example p {
    color: var(--text-muted);
    font-size: 0.85rem;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-cog"></i>
            Configurações SMTP
        </h1>
    </div>

    <div class="content-area">
        <div class="config-container">
            <?php if(!empty($erros)): ?>
                <?php foreach($erros as $erro): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Importante:</strong> Configure corretamente os dados do seu servidor SMTP para garantir o envio dos e-mails.
            </div>

            <!-- Exemplos de SMTP -->
            <div class="smtp-examples">
                <div class="smtp-example" onclick="preencherExemplo('gmail')">
                    <i class="fab fa-google" style="font-size: 2rem; color: #ea4335; margin-bottom: 10px;"></i>
                    <h4>Gmail</h4>
                    <p>smtp.gmail.com:587 (TLS)</p>
                </div>
                <div class="smtp-example" onclick="preencherExemplo('outlook')">
                    <i class="fab fa-microsoft" style="font-size: 2rem; color: #0078d4; margin-bottom: 10px;"></i>
                    <h4>Outlook</h4>
                    <p>smtp-mail.outlook.com:587 (TLS)</p>
                </div>
                <div class="smtp-example" onclick="preencherExemplo('yahoo')">
                    <i class="fab fa-yahoo" style="font-size: 2rem; color: #6001d2; margin-bottom: 10px;"></i>
                    <h4>Yahoo</h4>
                    <p>smtp.mail.yahoo.com:465 (SSL)</p>
                </div>
            </div>

            <form method="POST">
                <!-- Configurações Gerais -->
                <div class="config-card">
                    <h3 class="card-title">
                        <i class="fas fa-envelope"></i> Configurações Gerais
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Remetente (Nome)</label>
                            <input type="text" name="remetente_nome" class="form-control" value="<?php echo $config['remetente_nome']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-at"></i> Remetente (E-mail)</label>
                            <input type="email" name="remetente_email" class="form-control" value="<?php echo $config['remetente_email']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tachometer-alt"></i> Limite por minuto</label>
                            <input type="number" name="limite_por_minuto" class="form-control" value="<?php echo $config['limite_por_minuto']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-clock"></i> Limite por hora</label>
                            <input type="number" name="limite_por_hora" class="form-control" value="<?php echo $config['limite_por_hora']; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Configurações SMTP -->
                <div class="config-card">
                    <h3 class="card-title">
                        <i class="fas fa-server"></i> Servidor SMTP
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-globe"></i> Host</label>
                            <input type="text" name="smtp_host" id="smtp_host" class="form-control" value="<?php echo $config['smtp_host']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-plug"></i> Porta</label>
                            <input type="number" name="smtp_port" id="smtp_port" class="form-control" value="<?php echo $config['smtp_port']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user-circle"></i> Usuário</label>
                            <input type="text" name="smtp_user" class="form-control" value="<?php echo $config['smtp_user']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Senha</label>
                            <input type="password" name="smtp_pass" class="form-control" value="<?php echo $config['smtp_pass']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-shield-alt"></i> Criptografia</label>
                        <select name="smtp_secure" id="smtp_secure" class="form-control">
                            <option value="tls" <?php echo $config['smtp_secure'] == 'tls' ? 'selected' : ''; ?>>TLS (recomendado)</option>
                            <option value="ssl" <?php echo $config['smtp_secure'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                </div>

                <!-- Personalização -->
                <div class="config-card">
                    <h3 class="card-title">
                        <i class="fas fa-paint-brush"></i> Personalização
                    </h3>
                    
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> URL do Logo</label>
                        <input type="text" name="logo_url" class="form-control" value="<?php echo $config['logo_url']; ?>" placeholder="https://seusite.com/logo.png">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-signature"></i> Assinatura HTML</label>
                        <textarea name="assinatura_html" class="form-control" rows="3"><?php echo $config['assinatura_html']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-shoe-prints"></i> Rodapé HTML</label>
                        <textarea name="rodape_html" class="form-control" rows="3"><?php echo $config['rodape_html']; ?></textarea>
                    </div>
                </div>

                <!-- Redes Sociais -->
                <div class="config-card">
                    <h3 class="card-title">
                        <i class="fas fa-share-alt"></i> Redes Sociais
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fab fa-facebook"></i> Facebook</label>
                            <input type="text" name="facebook_url" class="form-control" value="<?php echo $config['facebook_url']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-instagram"></i> Instagram</label>
                            <input type="text" name="instagram_url" class="form-control" value="<?php echo $config['instagram_url']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp"></i> WhatsApp</label>
                        <input type="text" name="whatsapp_numero" class="form-control" value="<?php echo $config['whatsapp_numero']; ?>" placeholder="5519999999999">
                    </div>
                </div>

                <!-- Botões -->
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                    <button type="submit" name="testar" class="btn btn-test">
                        <i class="fas fa-vial"></i> Testar Configuração
                    </button>
                    <button type="submit" name="salvar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function preencherExemplo(tipo) {
    if(tipo == 'gmail') {
        document.getElementById('smtp_host').value = 'smtp.gmail.com';
        document.getElementById('smtp_port').value = '587';
        document.getElementById('smtp_secure').value = 'tls';
    } else if(tipo == 'outlook') {
        document.getElementById('smtp_host').value = 'smtp-mail.outlook.com';
        document.getElementById('smtp_port').value = '587';
        document.getElementById('smtp_secure').value = 'tls';
    } else if(tipo == 'yahoo') {
        document.getElementById('smtp_host').value = 'smtp.mail.yahoo.com';
        document.getElementById('smtp_port').value = '465';
        document.getElementById('smtp_secure').value = 'ssl';
    }
}

// Theme Toggle
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