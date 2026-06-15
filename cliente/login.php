<?php
require_once '../config.php';

// Se já estiver logado, redireciona para o dashboard
if(isset($_SESSION['cliente_id'])) {
    header('Location: dashboard/index.php');
    exit;
}

$erro = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = limparInput($_POST['login']); // Pode ser email ou username
    $senha = $_POST['senha'];
    
    if(empty($login) || empty($senha)) {
        $erro = 'Preencha todos os campos';
    } else {
        // Busca cliente por email ou username
        $stmt = $conn->prepare("
            SELECT id, nome, email, username, password_hash, status, tentativas_falhas, bloqueado_ate 
            FROM clientes 
            WHERE email = ? OR username = ?
        ");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 1) {
            $cliente = $result->fetch_assoc();
            
            // Verifica se está bloqueado
            if($cliente['bloqueado_ate'] && strtotime($cliente['bloqueado_ate']) > time()) {
                $erro = 'Conta temporariamente bloqueada. Tente novamente mais tarde.';
            }
            // Verifica status
            elseif($cliente['status'] != 'ativo') {
                $erro = 'Esta conta está inativa. Entre em contato com o suporte.';
            }
            // Verifica senha
            elseif(password_verify($senha, $cliente['password_hash'])) {
                // Login bem sucedido
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                $_SESSION['cliente_email'] = $cliente['email'];
                $_SESSION['cliente_username'] = $cliente['username'];
                
                // Atualiza último acesso e reseta tentativas
                $conn->query("
                    UPDATE clientes SET 
                        ultimo_acesso = NOW(),
                        tentativas_falhas = 0,
                        bloqueado_ate = NULL
                    WHERE id = {$cliente['id']}
                ");
                
                // Registrar log
                $log = $conn->prepare("INSERT INTO cliente_logs (cliente_id, acao, ip, user_agent, data_hora) VALUES (?, 'login', ?, ?, NOW())");
                $log->bind_param("iss", $cliente['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                $log->execute();
                
                header('Location: dashboard/index.php');
                exit;
            } else {
                // Senha incorreta - incrementa tentativas
                $tentativas = $cliente['tentativas_falhas'] + 1;
                $bloqueio = null;
                
                if($tentativas >= 5) {
                    $bloqueio = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                }
                
                $stmt = $conn->prepare("
                    UPDATE clientes SET 
                        tentativas_falhas = ?,
                        bloqueado_ate = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("isi", $tentativas, $bloqueio, $cliente['id']);
                $stmt->execute();
                
                $erro = 'Usuário ou senha inválidos';
                if($bloqueio) {
                    $erro = 'Muitas tentativas falhas. Conta bloqueada por 30 minutos.';
                }
            }
        } else {
            $erro = 'Usuário ou senha inválidos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Cliente | Arcon</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #66aaea 0%, #4b74a2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
        }

        .login-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.2);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 100px;
            height: 100px;
            border-radius: 30px;
            background: linear-gradient(135deg, #66baea 0%, #4b67a2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 15px 30px rgba(102,126,234,0.3);
        }

        .logo i {
            font-size: 50px;
            color: white;
        }

        .logo-area h1 {
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .logo-area p {
            color: #64748b;
            font-size: 15px;
        }

        .alert {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert i {
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group label i {
            color: #667eea;
            margin-right: 8px;
            width: 20px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            transition: all 0.2s ease;
        }

        .input-group .toggle-password {
            left: auto;
            right: 16px;
            cursor: pointer;
        }

        .input-group .toggle-password:hover {
            color: #667eea;
        }

        .form-control {
            width: 100%;
            padding: 16px 16px 16px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8faff;
            color: #1e293b;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }

        .form-control:focus + i {
            color: #667eea;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .remember-me span {
            color: #475569;
            font-size: 14px;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #669fea 0%, #4b71a2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(102,126,234,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102,126,234,0.3);
        }

        .btn-login i {
            font-size: 18px;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: all 0.2s ease;
        }

        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .features {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }

        .feature-item {
            padding: 15px 10px;
            background: #f8faff;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-3px);
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(102,126,234,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #667eea;
            font-size: 18px;
        }

        .feature-item span {
            display: block;
            font-size: 12px;
            color: #475569;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <div class="logo">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Área do Cliente</h1>
                <p>Faça login para acessar sua conta</p>
            </div>
            
            <?php if($erro): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $erro; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> E-mail ou Usuário</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="text" name="login" class="form-control" placeholder="seu@email.com" value="<?php echo $_POST['login'] ?? ''; ?>" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="senha" id="senha" class="form-control" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" onclick="toggleSenha()"></i>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="lembrar" id="lembrar">
                        <span>Lembrar-me</span>
                    </label>
                    <a href="recuperar-senha.php" class="forgot-password">Esqueceu a senha?</a>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>
            
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <span>Contratos</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <span>Pagamentos</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <span>Suporte</span>
                </div>
            </div>
            <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <center><span>Sistemas</span></center>
                </div>
            
            <div class="register-link">
                Ainda não tem conta?
                <a href="contato.php">Solicite um orçamento</a>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSenha() {
            const senha = document.getElementById('senha');
            const icon = event.currentTarget;
            
            if(senha.type === 'password') {
                senha.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                senha.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Salvar login no localStorage se "lembrar" estiver marcado
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const lembrar = document.getElementById('lembrar').checked;
            const login = document.querySelector('input[name="login"]').value;
            
            if(lembrar && login) {
                localStorage.setItem('ultimo_login', login);
            } else {
                localStorage.removeItem('ultimo_login');
            }
        });
        
        // Preencher último login se existir
        const ultimoLogin = localStorage.getItem('ultimo_login');
        if(ultimoLogin) {
            document.querySelector('input[name="login"]').value = ultimoLogin;
            document.getElementById('lembrar').checked = true;
        }
    </script>
</body>
</html>