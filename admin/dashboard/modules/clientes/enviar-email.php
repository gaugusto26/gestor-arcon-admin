<?php
require_once 'vendor/autoload.php';
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

$page_title = 'Enviar E-mail';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if(!$cliente) {
    header('Location: index.php');
    exit;
}

$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assunto = limparInput($_POST['assunto']);
    $mensagem = $_POST['mensagem'];
    
    if(empty($assunto)) {
        $erros[] = 'Assunto é obrigatório';
    }
    if(empty($mensagem)) {
        $erros[] = 'Mensagem é obrigatória';
    }
    
    if(empty($erros)) {
        // Configurações de e-mail
        $smtp_host = 'smtp.gmail.com';
        $smtp_port = 587;
        $smtp_user = 'sistemasntw@gmail.com';
        $smtp_pass = 'dcvj lezc qwoc yvim';
        $smtp_secure = 'tls';
        $remetente_email = 'sistemasntw@gmail.com';
        $remetente_nome = 'Digital Five';
        
        
        
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = $smtp_secure;
            $mail->Port       = $smtp_port;
            
            $mail->setFrom($remetente_email, $remetente_nome);
            $mail->addAddress($cliente['email'], $cliente['nome']);
            
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            $mail->AltBody = strip_tags($mensagem);
            
            $mail->send();
            
            registrarLogCliente($id, "E-mail enviado: $assunto");
            $sucesso = 'E-mail enviado com sucesso!';
            
        } catch (Exception $e) {
            $erros[] = 'Erro ao enviar e-mail: ' . $mail->ErrorInfo;
        }
    }
}
?>

<style>
.form-container {
    max-width: 800px;
    margin: 0 auto;
}

.form-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.cliente-info {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.cliente-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.cliente-details h3 {
    font-size: 1.1rem;
    margin-bottom: 3px;
}

.cliente-details p {
    color: var(--text-muted);
    font-size: 0.9rem;
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
    color: #4361ee;
    margin-right: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 18px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #f8faff;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    background: #ffffff;
}

textarea.form-control {
    min-height: 300px;
    resize: vertical;
    font-family: 'Inter', monospace;
}

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
}

.btn {
    padding: 14px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
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

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}

.template-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.template-btn {
    padding: 8px 16px;
    border-radius: 30px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.template-btn:hover {
    background: #f8faff;
    border-color: #4361ee;
    color: #4361ee;
}

.template-btn i {
    margin-right: 5px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-envelope" style="color: #4361ee; margin-right: 10px;"></i>
            Enviar E-mail
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="form-container">
            <a href="visualizar.php?id=<?php echo $id; ?>" class="btn btn-secondary" style="margin-bottom: 20px;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            <div class="cliente-info">
                <div class="cliente-avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                </div>
                <div class="cliente-details">
                    <h3><?php echo $cliente['nome']; ?></h3>
                    <p><i class="fas fa-envelope"></i> <?php echo $cliente['email']; ?></p>
                </div>
            </div>

            <?php if(!empty($erros)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php foreach($erros as $erro): ?>
                        <div><?php echo $erro; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="template-buttons">
                    <button type="button" class="template-btn" onclick="carregarTemplate('boasvindas')">
                        <i class="fas fa-hand-peace"></i> Boas-vindas
                    </button>
                    <button type="button" class="template-btn" onclick="carregarTemplate('cobranca')">
                        <i class="fas fa-credit-card"></i> Cobrança
                    </button>
                    <button type="button" class="template-btn" onclick="carregarTemplate('lembrete')">
                        <i class="fas fa-clock"></i> Lembrete
                    </button>
                    <button type="button" class="template-btn" onclick="carregarTemplate('promocao')">
                        <i class="fas fa-tags"></i> Promoção
                    </button>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Assunto *</label>
                        <input type="text" name="assunto" class="form-control" required id="assunto" value="<?php echo $_POST['assunto'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-newspaper"></i> Mensagem *</label>
                        <textarea name="mensagem" class="form-control" required id="mensagem"><?php echo $_POST['mensagem'] ?? ''; ?></textarea>
                        <small style="color: var(--text-muted);">Você pode usar HTML para formatar a mensagem</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar E-mail
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function carregarTemplate(tipo) {
    let assunto = '';
    let mensagem = '';
    
    if(tipo == 'boasvindas') {
        assunto = 'Bem-vindo à Digital Five!';
        mensagem = `<h1 style="color: #4361ee;">Olá {{nome}}!</h1>
<p>É com grande satisfação que damos as boas-vindas à Digital Five.</p>
<p>Estamos muito felizes em ter você conosco. Em breve você receberá mais informações sobre nossos serviços e como acessar sua área do cliente.</p>
<p>Qualquer dúvida, estamos à disposição!</p>
<p>Atenciosamente,<br>Equipe Arcon</p>`;
    }
    else if(tipo == 'cobranca') {
        assunto = 'Lembrete de Pagamento - Arcon';
        mensagem = `<h1 style="color: #4361ee;">Olá {{nome}}!</h1>
<p>Este é um lembrete amigável sobre o pagamento da sua mensalidade.</p>
<p><strong>Data de vencimento:</strong> 10/03/2026</p>
<p><strong>Valor:</strong> R$ 297,00</p>
<p>Caso já tenha efetuado o pagamento, desconsidere esta mensagem.</p>
<p>Qualquer dúvida, estamos à disposição!</p>`;
    }
    else if(tipo == 'lembrete') {
        assunto = 'Lembrete Importante - Arcon';
        mensagem = `<h1 style="color: #4361ee;">Olá {{nome}}!</h1>
<p>Gostaríamos de lembrar que sua reunião de acompanhamento está agendada para:</p>
<p><strong>Data:</strong> 15/03/2026</p>
<p><strong>Horário:</strong> 14:00</p>
<p><strong>Link:</strong> <a href="#">Clique aqui para acessar</a></p>
<p>Confirme sua presença respondendo a este e-mail.</p>`;
    }
    else if(tipo == 'promocao') {
        assunto = 'Oferta Especial para você! - Arcon';
        mensagem = `<h1 style="color: #4361ee;">Olá {{nome}}!</h1>
<p>Preparamos uma oferta especial para você, cliente Arcon:</p>
<div style="background: #f8faff; padding: 20px; border-radius: 10px;">
    <h2 style="color: #f97316;">20% OFF</h2>
    <p>na próxima atualização do seu sistema</p>
    <p><strong>Cupom:</strong> NTW20</p>
</div>
<p>Aproveite esta oportunidade!</p>`;
    }
    
    document.getElementById('assunto').value = assunto;
    document.getElementById('mensagem').value = mensagem.replace('{{nome}}', '<?php echo addslashes($cliente['nome']); ?>');
}

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