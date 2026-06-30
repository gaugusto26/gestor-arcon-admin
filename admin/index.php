<?php
require_once '../config.php';

// Verificar se já está logado
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header('Location: dashboard/index.php');
    exit();
}

$erro = '';
$ip_usuario = $_SERVER['REMOTE_ADDR'];

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM admin_users WHERE username = ? AND (ip_permitido = ? OR ip_permitido IS NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $ip_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logado'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nome'] = $user['nome_completo'];
            
            header('Location: dashboard/index.php');
            exit();
        } else {
            $erro = "Usuário ou senha incorretos";
        }
    } else {
        $erro = "Usuário ou senha incorretos";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Digital Five</title>
    <link rel="icon" type="image/png" href="/assets/image/logo_quadrada.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --accent: #0b5cff;
            --accent-light: #e8f0ff;
            --border: #e2e8f0;
            --card-bg: #ffffff;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            --input-bg: #f8fafc;
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --accent: #0b5cff;
            --accent-light: #1e293b;
            --border: #334155;
            --card-bg: #1e293b;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            --input-bg: #0f172a;
        }

        body {
            font-family: 'Inter', 'Manrope', 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
            padding: 20px;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .theme-toggle:hover {
            transform: rotate(180deg);
        }

        .theme-toggle i {
            font-size: 1.3rem;
            color: var(--accent);
        }

        /* Container */
        .login-container {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Card */
        .login-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            margin-bottom: 16px;
            object-fit: cover;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* IP Info */
        .ip-info {
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .ip-info i {
            color: var(--accent);
            font-size: 1rem;
        }

        .ip-info strong {
            color: var(--text-primary);
            font-weight: 500;
        }

        .ip-info .status {
            margin-left: auto;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
        }

        /* Error Message */
        .error-message {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #dc2626;
            font-size: 0.9rem;
        }

        [data-theme="dark"] .error-message {
            background: rgba(220, 38, 38, 0.1);
            border-color: rgba(220, 38, 38, 0.2);
        }

        .error-message i {
            font-size: 1rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 14px 14px 14px 42px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            font-family: 'Manrope', 'Plus Jakarta Sans', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(11, 92, 255, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.5;
        }

        /* Button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #0a4fe0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(11, 92, 255, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer */
        .login-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .security-badge {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .security-badge i {
            color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .theme-toggle {
                top: 20px;
                right: 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="/assets/css/arcon-identity.css">
</head>
<body data-theme="light">
    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-sun" id="themeIcon"></i>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <img src="/assets/image/logo_quadrada.png" alt="Digital Five" class="logo">
                <h1>Acesso Administrativo</h1>
                <p>Digital Five</p>
            </div>

            <!-- IP Info -->
            <div class="ip-info">
                <i class="fas fa-shield-alt"></i>
                <span>IP: <strong><?php echo $ip_usuario; ?></strong></span>
                <div class="status"></div>
            </div>

            <!-- Error Message -->
            <?php if($erro): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $erro; ?>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label>Usuário</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Digite seu usuário" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Digite sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-arrow-right"></i>
                    Entrar
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <a href="<?php echo htmlspecialchars(PUBLIC_SITE_URL); ?>">
                    <i class="fas fa-arrow-left"></i>
                    Voltar para o site
                </a>
            </div>

            <!-- Security Badge -->
            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <span>IP Restrito</span>
                <i class="fas fa-shield-alt"></i>
                <span>Criptografado</span>
                <i class="fas fa-clock"></i>
                <span>Session</span>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle System
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        // Check for saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);

            // Animate toggle
            themeToggle.style.transform = 'rotate(180deg)';
            setTimeout(() => {
                themeToggle.style.transform = 'rotate(0)';
            }, 300);
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon';
            } else {
                themeIcon.className = 'fas fa-sun';
            }
        }

        // Input animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('i').style.color = '#0b5cff';
            });

            input.addEventListener('blur', () => {
                input.parentElement.querySelector('i').style.color = 'var(--text-secondary)';
            });
        });

        // Smooth appearance for error message
        const errorMsg = document.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.style.animation = 'fadeIn 0.3s ease';
        }
    </script>
</body>
</html>
