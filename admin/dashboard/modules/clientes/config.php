<?php

require_once 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
function gerarUsername($nome) {
    // Remove acentos e caracteres especiais
    $nome = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($nome));
    // Pega o primeiro nome + número aleatório
    $partes = explode(' ', $nome);
    $username = $partes[0] . rand(100, 999);
    return $username;
}

function gerarCodigoIndicacao($referencia, $cliente_id) {
    // Remove acentos e caracteres especiais
    $base = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($referencia));
    // Pega até 5 caracteres da referência
    $base = strtoupper(substr($base, 0, 5));
    // Adiciona o ID com zero à esquerda para garantir unicidade
    $codigo = $base . str_pad($cliente_id, 4, '0', STR_PAD_LEFT);
    return $codigo;
}

function enviarEmailBoasVindas($cliente_id, $senha_original) {
    global $conn;
    
    // Busca dados do cliente
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $cliente = $stmt->get_result()->fetch_assoc();
    
    if(!$cliente) return false;
    
    // Configurações fixas de e-mail (você pode mudar depois)
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 587;
    $smtp_user = 'sistemasntw@gmail.com';
    $smtp_pass = 'dcvj lezc qwoc yvim'; 
    $smtp_secure = 'tls';
    $remetente_email = 'sistemasntw@gmail.com';
    $remetente_nome = 'Gestor Arcon Admin';
    
    $site_url = 'http://localhost';
    $logo_url = $site_url . '/assets/image/logo.gif';
    $ano = date('Y');
    
    // Template de e-mail moderno
    $assunto = "Bem-vindo à Gestor Arcon Admin!";
    
    $mensagem = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <title>Bem-vindo à Arcon</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background: #f8faff;
                font-family: "Inter", Arial, sans-serif;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 24px;
                overflow: hidden;
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            }
            .header {
                background: linear-gradient(135deg, #66aaea 0%, #4b7fa2 100%);
                padding: 40px 30px;
                text-align: center;
            }
            .header img {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                border: 4px solid rgba(255,255,255,0.3);
                margin-bottom: 20px;
            }
            .header h1 {
                color: #ffffff;
                font-size: 28px;
                margin: 0;
                font-weight: 700;
                letter-spacing: -0.5px;
            }
            .header p {
                color: rgba(255,255,255,0.9);
                font-size: 16px;
                margin-top: 10px;
            }
            .content {
                padding: 40px 30px;
            }
            .content h2 {
                color: #1e293b;
                font-size: 22px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .content h2 i {
                color: #667eea;
            }
            .content p {
                color: #475569;
                line-height: 1.8;
                margin-bottom: 20px;
                font-size: 16px;
            }
            .info-box {
                background: #f8faff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 25px;
                margin: 30px 0;
            }
            .info-box h3 {
                color: #1e293b;
                font-size: 18px;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .info-box h3 i {
                color: #667eea;
            }
            .info-row {
                display: flex;
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e2e8f0;
            }
            .info-label {
                width: 120px;
                color: #64748b;
                font-weight: 500;
            }
            .info-value {
                flex: 1;
                color: #1e293b;
                font-weight: 600;
            }
            .info-value.highlight {
                background: #ffffff;
                padding: 8px 15px;
                border-radius: 8px;
                border: 1px solid #667eea;
                color: #667eea;
                font-family: monospace;
                font-size: 14px;
            }
            .button {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #ffffff;
                text-decoration: none;
                padding: 16px 40px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 16px;
                margin: 20px 0;
                box-shadow: 0 10px 20px rgba(102,126,234,0.2);
                transition: all 0.3s ease;
            }
            .button:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 30px rgba(102,126,234,0.3);
            }
            .features {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin: 30px 0;
            }
            .feature-item {
                text-align: center;
                padding: 20px;
                background: #f8faff;
                border-radius: 16px;
            }
            .feature-icon {
                width: 50px;
                height: 50px;
                background: rgba(102,126,234,0.1);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
                color: #667eea;
                font-size: 1.5rem;
            }
            .feature-item h4 {
                color: #1e293b;
                font-size: 16px;
                margin-bottom: 5px;
            }
            .feature-item p {
                color: #64748b;
                font-size: 13px;
                margin: 0;
            }
            .footer {
                background: #f8faff;
                padding: 30px;
                text-align: center;
                border-top: 1px solid #e2e8f0;
            }
            .footer p {
                color: #94a3b8;
                font-size: 14px;
                margin: 5px 0;
            }
            .footer a {
                color: #667eea;
                text-decoration: none;
            }
            .footer a:hover {
                text-decoration: underline;
            }
            .social-links {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin: 20px 0;
            }
            .social-link {
                width: 40px;
                height: 40px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #667eea;
                text-decoration: none;
                transition: all 0.3s ease;
            }
            .social-link:hover {
                background: #667eea;
                color: white;
                transform: translateY(-3px);
            }
            .warning {
                background: #fff7ed;
                border-left: 4px solid #f97316;
                padding: 15px 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .warning p {
                color: #9a3412;
                margin: 0;
                font-size: 14px;
            }
            @media (max-width: 600px) {
                .features {
                    grid-template-columns: 1fr;
                }
                .info-row {
                    flex-direction: column;
                    gap: 5px;
                }
                .info-label {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <img src="/assets/image/logo.gif" alt="Arcon">
                <h1>Bem-vindo à Arcon!</h1>
                <p>Olá, ' . $cliente['nome'] . '! Sua conta foi criada com sucesso.</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <h2>
                    <i class="fas fa-rocket"></i> 
                    Sua jornada começa agora
                </h2>
                
                <p>Estamos muito felizes em ter você conosco! A partir de agora, você terá acesso à nossa plataforma para acompanhar seus projetos, contratos e muito mais.</p>
                
                <!-- Dados de Acesso -->
                <div class="info-box">
                    <h3>
                        <i class="fas fa-key"></i>
                        Dados de Acesso
                    </h3>
                    
                    <div class="info-row">
                        <span class="info-label">Usuário:</span>
                        <span class="info-value highlight">' . $cliente['username'] . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Senha:</span>
                        <span class="info-value highlight">' . $senha_original . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">E-mail:</span>
                        <span class="info-value">' . $cliente['email'] . '</span>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <a href="' . $site_url . '/cliente/login.php" class="button">
                            <i class="fas fa-sign-in-alt"></i> 
                            ACESSAR MINHA CONTA
                        </a>
                    </div>
                    
                    <div class="warning">
                        <p>
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Recomendação:</strong> Ao acessar pela primeira vez, altere sua senha para uma de sua preferência.
                        </p>
                    </div>
                </div>
                
                <!-- Features -->
                <h3 style="color: #1e293b; margin: 30px 0 20px;">O que você pode fazer no seu painel:</h3>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h4>Contratos</h4>
                        <p>Visualize todos os seus contratos e termos</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4>Pagamentos</h4>
                        <p>Acompanhe suas mensalidades</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Projetos</h4>
                        <p>Acompanhe o andamento dos projetos</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Suporte</h4>
                        <p>Abra chamados e receba ajuda</p>
                    </div>
                </div>
                
                <p style="text-align: center; color: #64748b; font-style: italic;">
                    Qualquer dúvida, nossa equipe está à disposição para ajudar!
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="social-links">
                    <a href="https://instagram.com/arcon.digitalfive" class="social-link" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://wa.me/5519987111656" class="social-link" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="mailto:sistemasntw@gmail.com" class="social-link">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
                
                <p>© ' . $ano . ' Gestor Arcon Admin. Todos os direitos reservados.</p>
                <p>
                    <a href="' . $site_url . '/politica.php">Política de Privacidade</a> | 
                    <a href="' . $site_url . '/termos.php">Termos de Uso</a>
                </p>
                <p style="font-size: 12px;">Este é um e-mail automático, por favor não responda.</p>
            </div>
        </div>
        
        <!-- Ícones Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </body>
    </html>
    ';
    
    // Enviar e-mail usando PHPMailer
    
    
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor
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
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $mensagem));
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de boas-vindas: " . $mail->ErrorInfo);
        return false;
    }
}

function enviarEmailNovaSenha($email, $nome, $nova_senha) {
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 587;
    $smtp_user = 'sistemasntw@gmail.com';
    $smtp_pass = 'dcvj lezc qwoc yvim'; // COLOQUE A SENHA DO SEU E-MAIL AQUI
    $smtp_secure = 'tls';
    $remetente_email = 'sistemasntw@gmail.com';
    $remetente_nome = 'Gestor Arcon Admin';
    
    $site_url = 'http://localhost';
    
    $assunto = "Sua senha foi resetada - Arcon";
    
    $mensagem = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Nova Senha - Arcon</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background: #f8faff;
                font-family: "Inter", Arial, sans-serif;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 24px;
                overflow: hidden;
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            }
            .header {
                background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
                padding: 40px 30px;
                text-align: center;
            }
            .header h1 {
                color: #ffffff;
                font-size: 28px;
                margin: 0;
                font-weight: 700;
            }
            .content {
                padding: 40px 30px;
            }
            .senha-box {
                background: #f8faff;
                border: 2px dashed #f97316;
                border-radius: 16px;
                padding: 30px;
                text-align: center;
                margin: 30px 0;
            }
            .senha-texto {
                font-size: 32px;
                font-weight: 700;
                color: #f97316;
                font-family: monospace;
                letter-spacing: 2px;
                margin: 10px 0;
            }
            .button {
                display: inline-block;
                background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
                color: #ffffff;
                text-decoration: none;
                padding: 16px 40px;
                border-radius: 50px;
                font-weight: 600;
                margin: 20px 0;
            }
            .warning {
                background: #fff7ed;
                border-left: 4px solid #f97316;
                padding: 15px 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .footer {
                background: #f8faff;
                padding: 30px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 Nova Senha de Acesso</h1>
            </div>
            <div class="content">
                <p>Olá <strong>' . $nome . '</strong>,</p>
                <p>Sua senha foi resetada pelo administrador. Utilize a nova senha abaixo para acessar sua conta:</p>
                
                <div class="senha-box">
                    <p style="color: #666; margin-bottom: 10px;">Sua nova senha é:</p>
                    <div class="senha-texto">' . $nova_senha . '</div>
                </div>
                
                <div style="text-align: center;">
                    <a href="' . $site_url . '/cliente/login.php" class="button">
                        ACESSAR MINHA CONTA
                    </a>
                </div>
                
                <div class="warning">
                    <p><strong>⚠️ Recomendação:</strong> Altere esta senha após o primeiro acesso para maior segurança.</p>
                </div>
                
                <p style="color: #666; font-size: 14px;">Se você não solicitou esta alteração, entre em contato com nosso suporte imediatamente.</p>
            </div>
            <div class="footer">
                <p>© ' . date('Y') . ' Gestor Arcon Admin</p>
            </div>
        </div>
    </body>
    </html>
    ';
    

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
        $mail->addAddress($email, $nome);
        
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de nova senha: " . $mail->ErrorInfo);
        return false;
    }
}

function registrarLogCliente($cliente_id, $acao) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO cliente_logs (cliente_id, acao, ip, user_agent, data_hora) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $cliente_id, $acao, $ip, $user_agent);
    $stmt->execute();
}
?>