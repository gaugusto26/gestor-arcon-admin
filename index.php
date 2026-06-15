<?php
// Caminho do arquivo onde as visitas serão armazenadas
$file = 'contador_visitas.txt';

// Verificar se o arquivo existe
if (file_exists($file)) {
    // Ler o número atual de visitas
    $visitas = (int) file_get_contents($file);
} else {
    // Se o arquivo não existir, começar com 0 visitas
    $visitas = 0;
}

// Incrementar o contador
$visitas++;

// Salvar o novo número de visitas no arquivo
file_put_contents($file, $visitas);

// Passar o número de visitas para o JavaScript
echo "<script>var visitas = $visitas;</script>";
?>

<?php
// Conectar ao banco de dados
$servername = getenv('MYSQL_HOST') ?: 'localhost';
$username   = getenv('MYSQL_USER') ?: 'root';
$password   = getenv('MYSQL_PASSWORD') ?: '';
$dbname     = getenv('MSG_DB_NAME') ?: 'msg';

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário
    $nome = $_POST['name'];
    $email = $_POST['email'];
    $telefone = $_POST['phone'];
    $empresa = $_POST['text'];
    $mensagem = $_POST['textarea'];

    // Prepara a consulta SQL para inserir os dados no banco de dados
    $sql = "INSERT INTO mensagens (nome, email, telefone, empresa, mensagem) 
            VALUES ('$nome', '$email', '$telefone', '$empresa', '$mensagem')";

    if ($conn->query($sql) === TRUE) {
        // Mensagem enviada com sucesso, mostrar o pop-up
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        document.getElementById('popup-sucesso').style.display = 'flex';
                    }, 500);
                });
              </script>";
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="shortcut icon" href="assets/image/logo2.png" type="image/x-icon">
    <meta name="description" content="Gestor Arcon Admin | Administração de assinaturas, contratos, clientes e pagamentos do SaaS Arcon.">
    <title>Gestor Arcon Admin | Assinaturas do Arcon</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- GSAP for animations -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    
    <style>
        /* ===== RESET COMPLETO ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body {
            position: relative;
            min-height: 100vh;
        }

        /* ===== VARIÁVEIS DE TEMA ===== */
        :root {
            /* Tema Claro (padrão) - Branco com azul */
            --bg-primary: #ffffff;
            --bg-secondary: #f0f7ff;
            --bg-gradient: linear-gradient(135deg, #ffffff 0%, #f0f7ff 50%, #e3f2fd 100%);
            --text-primary: #0d47a1;
            --text-secondary: #1565c0;
            --text-tertiary: #1976d2;
            --accent-primary: #0d47a1;
            --accent-secondary: #1976d2;
            --accent-gradient: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
            --card-border: rgba(13, 71, 161, 0.1);
            --card-hover-border: rgba(13, 71, 161, 0.3);
            --navbar-bg: rgba(255, 255, 255, 0.95);
            --navbar-border: rgba(13, 71, 161, 0.2);
            --input-bg: #ffffff;
            --input-border: rgba(13, 71, 161, 0.2);
            --input-focus-border: #0d47a1;
            --footer-bg: rgba(255, 255, 255, 0.95);
            --footer-border: rgba(13, 71, 161, 0.2);
            --shadow-color: rgba(13, 71, 161, 0.1);
            --binary-color: rgba(13, 71, 161, 0.08);
            --btn-text: #ffffff;
        }

        /* Tema Escuro */
        [data-theme="dark"] {
            --bg-primary: #0a0f1c;
            --bg-secondary: #0f1a2b;
            --bg-gradient: linear-gradient(135deg, #0a0f1c 0%, #0f1a2b 50%, #1a2639 100%);
            --text-primary: #ffffff;
            --text-secondary: #90caf9;
            --text-tertiary: #64b5f6;
            --accent-primary: #42a5f5;
            --accent-secondary: #64b5f6;
            --accent-gradient: linear-gradient(135deg, #42a5f5 0%, #64b5f6 100%);
            --card-bg: rgba(15, 26, 43, 0.9);
            --card-border: rgba(66, 165, 245, 0.2);
            --card-hover-border: rgba(66, 165, 245, 0.5);
            --navbar-bg: rgba(10, 15, 28, 0.95);
            --navbar-border: rgba(66, 165, 245, 0.3);
            --input-bg: rgba(15, 26, 43, 0.9);
            --input-border: rgba(66, 165, 245, 0.2);
            --input-focus-border: #42a5f5;
            --footer-bg: rgba(10, 15, 28, 0.95);
            --footer-border: rgba(66, 165, 245, 0.3);
            --shadow-color: rgba(0, 0, 0, 0.3);
            --binary-color: rgba(66, 165, 245, 0.06);
            --btn-text: #ffffff;
        }

        body {
            background: var(--bg-gradient);
            color: var(--text-primary);
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ===== FUNDO COM 0 E 1 ANIMADOS (BEM SUTIL) ===== */
        #binary-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        /* ===== SPLASH SCREEN COM ANIMAÇÃO GELATINHA ===== */
        #splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-gradient);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: opacity 0.8s cubic-bezier(0.87, 0, 0.13, 1), visibility 0.8s cubic-bezier(0.87, 0, 0.13, 1);
        }

        .splash-content {
            text-align: center;
            z-index: 10001;
            padding: 20px;
        }

        .splash-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--accent-primary);
            margin-bottom: 40px;
            animation: logoJelly 2s infinite;
            box-shadow: 0 0 30px var(--accent-primary);
        }

        @keyframes logoJelly {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 0 30px var(--accent-primary); 
            }
            25% { 
                transform: scale(1.1) rotate(-2deg); 
                box-shadow: 0 0 40px var(--accent-primary); 
            }
            50% { 
                transform: scale(0.95) rotate(2deg); 
                box-shadow: 0 0 20px var(--accent-primary); 
            }
            75% { 
                transform: scale(1.05) rotate(-1deg); 
                box-shadow: 0 0 35px var(--accent-primary); 
            }
        }

        .typing-container {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 30px;
            font-family: 'Fira Code', monospace;
            color: var(--text-primary);
            perspective: 500px;
        }

        .typing-text {
            display: inline-block;
            border-right: 4px solid var(--accent-primary);
            white-space: nowrap;
            overflow: hidden;
            animation: blinkCursor 0.8s infinite;
            transform-origin: center;
        }

        .jelly-letter {
            display: inline-block;
            animation: jellyPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes jellyPop {
            0% { 
                transform: scale(0.8) translateY(10px); 
                opacity: 0; 
            }
            50% { 
                transform: scale(1.2) translateY(-5px); 
            }
            100% { 
                transform: scale(1) translateY(0); 
                opacity: 1; 
            }
        }

        @keyframes blinkCursor {
            0%, 100% { border-color: var(--accent-primary); }
            50% { border-color: transparent; }
        }

        .splash-subtitle {
            font-size: 1.3rem;
            color: var(--text-secondary);
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(30px) scale(0.9);
        }

        .splash-progress-container {
            width: 300px;
            height: 4px;
            background: var(--card-border);
            border-radius: 2px;
            margin: 40px auto;
            opacity: 0;
            transform: translateY(30px) scale(0.9);
            overflow: hidden;
        }

        .splash-progress-bar {
            width: 0%;
            height: 100%;
            background: var(--accent-gradient);
            border-radius: 2px;
            transition: width 0.1s linear;
            position: relative;
        }

        .splash-progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 20px;
            height: 100%;
            background: var(--accent-gradient);
            filter: blur(5px);
            animation: progressGlow 1s infinite;
        }

        @keyframes progressGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* ===== TOGGLE DE TEMA ===== */
        .theme-toggle {
            position: fixed;
            top: 100px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(180deg);
            border-color: var(--accent-primary);
        }

        .theme-toggle i {
            font-size: 1.5rem;
            color: var(--accent-primary);
        }

        /* ===== NAVBAR MODERNA ===== */
        .navbar-modern {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 48px);
            max-width: 1280px;
            background: var(--navbar-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 100px;
            padding: 12px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            border: 1px solid var(--navbar-border);
            box-shadow: 0 10px 30px var(--shadow-color);
            transition: all 0.3s ease;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-logo img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-primary);
        }

        .navbar-logo span {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-primary);
        }

        .navbar-menu {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .navbar-menu a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-gradient);
            transition: width 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .navbar-menu a:hover {
            color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .navbar-menu a:hover::after {
            width: 100%;
        }

        .navbar-toggle {
            display: none;
            flex-direction: column;
            gap: 6px;
            cursor: pointer;
        }

        .navbar-toggle span {
            width: 30px;
            height: 3px;
            background: var(--text-primary);
            border-radius: 3px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* ===== CONTAINER E LAYOUT ===== */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 2;
        }

        .container-fluid {
            width: 100%;
            padding: 0 24px;
            position: relative;
            z-index: 2;
        }

        section {
            padding: 100px 0;
            position: relative;
            z-index: 2;
        }

        /* ===== TIPOGRAFIA ===== */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        h1 { font-size: 4rem; letter-spacing: -0.02em; }
        h2 { font-size: 3rem; letter-spacing: -0.01em; }
        h3 { font-size: 2rem; }
        h4 { font-size: 1.5rem; }

        p {
            line-height: 1.6;
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .gradient-text {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== BOTÕES ===== */
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
            color: var(--btn-text);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
            z-index: -1;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--accent-gradient);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--accent-primary);
            color: var(--accent-primary);
        }

        .btn-outline:hover {
            background: var(--accent-gradient);
            color: white;
            transform: translateY(-5px) scale(1.05);
            border-color: transparent;
        }

        /* ===== CARDS ===== */
        .card-modern {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 30px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }

        .card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--accent-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .card-modern:hover {
            transform: translateY(-15px) scale(1.02);
            border-color: var(--card-hover-border);
        }

        .card-modern:hover::before {
            opacity: 0.05;
        }

        .card-modern > * {
            position: relative;
            z-index: 1;
        }

        /* ===== GRIDS ===== */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        /* ===== FEATURES ===== */
        .feature-item {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .feature-item:hover {
            background: var(--card-bg);
            border-color: var(--accent-primary);
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 30px var(--shadow-color);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--accent-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
            color: white;
            opacity: 0.9;
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .feature-item:hover .feature-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .feature-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* ===== FORMULÁRIO ===== */
        .form-modern {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 50px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--input-focus-border);
            background: var(--input-bg);
            box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
            transform: scale(1.02);
        }

        .form-control::placeholder {
            color: var(--text-tertiary);
        }

        textarea.form-control {
            border-radius: 20px;
            resize: vertical;
            min-height: 120px;
        }

        /* ===== POP-UP DE SUCESSO ===== */
        .popup-modern {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .popup-content-modern {
            background: var(--bg-primary);
            border: 1px solid var(--accent-primary);
            border-radius: 30px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            animation: popupJelly 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes popupJelly {
            0% { transform: scale(0.5) rotate(-5deg); opacity: 0; }
            50% { transform: scale(1.1) rotate(2deg); }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }

        .popup-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: iconJelly 1s infinite;
        }

        @keyframes iconJelly {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.2) rotate(5deg); }
            50% { transform: scale(0.9) rotate(-5deg); }
            75% { transform: scale(1.1) rotate(3deg); }
        }

        .popup-title {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .popup-text {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        .popup-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            color: var(--text-tertiary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .popup-close:hover {
            color: var(--accent-primary);
            transform: scale(1.2) rotate(90deg);
        }

        /* ===== CONTADOR DE VISITAS ===== */
        .visit-counter {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--navbar-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--navbar-border);
            border-radius: 50px;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 100;
            box-shadow: 0 5px 20px var(--shadow-color);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .visit-counter:hover {
            transform: scale(1.05) translateY(-5px);
            border-color: var(--accent-primary);
        }

        .visit-counter span {
            color: var(--text-secondary);
        }

        .visit-counter span:first-child {
            color: var(--accent-primary);
            font-size: 1.2rem;
        }

        .visit-counter span:nth-child(2) {
            color: var(--accent-primary);
            font-weight: 700;
            font-size: 1.2rem;
        }

        /* ===== FOOTER ===== */
        .footer-modern {
            background: var(--footer-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid var(--footer-border);
            padding: 60px 0 30px;
            margin-top: 100px;
            position: relative;
            z-index: 2;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
        }

        .footer-logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 2px solid var(--accent-primary);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .footer-logo img:hover {
            transform: rotate(360deg) scale(1.1);
        }

        .footer-logo h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .footer-links h4 {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--accent-primary);
            transform: translateX(10px) scale(1.05);
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-primary);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .social-link:hover {
            background: var(--accent-gradient);
            color: var(--btn-text);
            border-color: transparent;
            transform: translateY(-5px) scale(1.1) rotate(360deg);
        }

        .footer-bottom {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--card-border);
            text-align: center;
            color: var(--text-tertiary);
            font-size: 0.9rem;
        }

        /* ===== ACCORDION ===== */
        .accordion-item {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .accordion-item:hover {
            border-color: var(--accent-primary);
            transform: scale(1.02);
        }

        .accordion-header {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .accordion-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }

        .accordion-header span {
            color: var(--accent-primary);
            font-size: 1.5rem;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .accordion-item:hover .accordion-header span {
            transform: rotate(90deg) scale(1.2);
        }

        .accordion-content {
            padding: 0 20px 20px;
            color: var(--text-secondary);
            display: none;
        }

        .accordion-content.active {
            display: block;
        }

        /* ===== GALERIA ===== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .gallery-item {
            border-radius: 15px;
            overflow: hidden;
            aspect-ratio: 1;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .gallery-item:hover {
            transform: scale(1.05) rotate(2deg);
            box-shadow: 0 20px 30px var(--shadow-color);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .gallery-item:hover img {
            transform: scale(1.2);
        }

        /* ===== ANIMAÇÕES ===== */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .fade-up.active {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes scrollText {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .scroll-text {
            display: inline-block;
            animation: scrollText 30s linear infinite;
            font-size: 2rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* ===== VIDEO WRAPPER ===== */
        .video-wrapper {
            position: relative;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 40px var(--shadow-color);
        }

        /* ===== SERVICES ===== */
        .services {
            padding: 80px 0;
        }

        .services-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 60px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .services-header h2 {
            font-size: 3rem;
        }

        .services-list {
            display: grid;
            gap: 40px;
        }

        .service-card {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            background: var(--card-bg);
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid var(--card-border);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--accent-primary);
            box-shadow: 0 20px 30px var(--shadow-color);
        }

        .service-image {
            height: 300px;
            overflow: hidden;
        }

        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .service-card:hover .service-image img {
            transform: scale(1.1);
        }

        .service-content {
            padding: 40px 40px 40px 0;
        }

        .service-tag {
            color: var(--accent-primary);
            margin-bottom: 15px;
        }

        .service-content h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .service-content p {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* ===== RESPONSIVIDADE MOBILE ===== */
        @media (max-width: 1200px) {
            h1 { font-size: 3.5rem; }
            h2 { font-size: 2.5rem; }
            .grid-4 { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 992px) {
            h1 { font-size: 3rem; }
            h2 { font-size: 2.2rem; }
            .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .footer-content { grid-template-columns: repeat(2, 1fr); }
            
            .typing-container { font-size: 3rem; }
            
            .services-header h2 { font-size: 2.5rem; }
            .service-card { grid-template-columns: 1fr; gap: 0; }
            .service-image { height: 250px; }
            .service-content { padding: 30px; }
        }

        @media (max-width: 768px) {
            section { padding: 60px 0; }
            
            h1 { font-size: 2.5rem; }
            h2 { font-size: 2rem; }
            h3 { font-size: 1.5rem; }
            
            .navbar-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 350px;
                height: 100vh;
                background: var(--navbar-bg);
                backdrop-filter: blur(20px);
                flex-direction: column;
                justify-content: center;
                padding: 80px 40px;
                transition: right 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                border-radius: 30px 0 0 30px;
                box-shadow: -10px 0 30px var(--shadow-color);
            }
            
            .navbar-menu.active { right: 0; }
            
            .navbar-toggle { display: flex; }
            
            .navbar-toggle.active span:nth-child(1) {
                transform: rotate(45deg) translate(8px, 8px);
            }
            
            .navbar-toggle.active span:nth-child(2) { opacity: 0; }
            
            .navbar-toggle.active span:nth-child(3) {
                transform: rotate(-45deg) translate(7px, -7px);
            }
            
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; gap: 20px; }
            .footer-content { grid-template-columns: 1fr; gap: 30px; }
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
            
            .theme-toggle {
                top: 90px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
            
            .visit-counter {
                bottom: 20px;
                right: 20px;
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .typing-container { font-size: 2.2rem; }
            .splash-logo { width: 100px; height: 100px; }
            .splash-progress-container { width: 250px; }
        }

        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            
            h1 { font-size: 2rem; }
            h2 { font-size: 1.8rem; }
            
            .navbar-modern {
                padding: 10px 20px;
                width: calc(100% - 32px);
            }
            
            .navbar-logo span { font-size: 1rem; }
            .navbar-logo img { width: 35px; height: 35px; }
            
            .btn { padding: 12px 24px; font-size: 0.9rem; }
            .gallery-grid { grid-template-columns: 1fr; }
            .feature-item { padding: 30px 20px; }
            .form-modern { padding: 30px 20px; }
            
            .typing-container { font-size: 1.8rem; }
            .splash-logo { width: 80px; height: 80px; }
            .splash-progress-container { width: 200px; }
        }
    </style>
</head>
<body data-theme="light">
    <!-- Canvas para 0 e 1 animados (BEM SUTIL) -->
    <canvas id="binary-canvas"></canvas>

    <!-- Splash Screen com Animação Gelatinha -->
    <div id="splash-screen">
        <div class="splash-content">
            <img src="assets/image/logo.png" widht="400" alt="Arcon Software" class="splash-logo">
            <div class="typing-container">
                <span class="typing-text" id="typing-text"></span>
            </div>
            <div class="splash-subtitle" id="splash-subtitle">Tecnologia que transforma</div>
            <div class="splash-progress-container" id="progress-container">
                <div class="splash-progress-bar" id="splash-progress"></div>
            </div>
        </div>
    </div>

    <!-- Toggle de Tema -->
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-sun" id="themeIcon"></i>
    </div>

    <!-- Navbar Moderna -->
    <nav class="navbar-modern">
        <div class="navbar-logo">
            <img src="assets/image/logo2.png" alt="Gestor Arcon Admin">
            <span>ARCON ADMIN</span>
        </div>
        
        <div class="navbar-menu" id="navbarMenu">
            <a href="#home">Home</a>
            <a href="#sobre-nos">Sobre Nós</a>
            <a href="#servicos">Serviços</a>
            <a href="#planos">Planos</a>
            <a href="#contato">Contato</a>
            <a href="#faqs">FAQ</a>
            <a href="blog/blog.php">Blog</a>
            <a href="#contato" class="btn btn-primary" style="padding: 10px 24px; color: #ffff;">Começar</a>
        </div>
        
        <div class="navbar-toggle" id="navbarToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Contador de Visitas -->
    <div class="visit-counter">
        <span>👥</span>
        <span id="visitorCount"><?php echo $visitas; ?></span>
        <span>visitas</span>
    </div>

    <!-- Pop-up de Sucesso -->
    <div id="popup-sucesso" class="popup-modern">
        <div class="popup-content-modern">
            <span class="popup-close" onclick="document.getElementById('popup-sucesso').style.display='none'">&times;</span>
            <div class="popup-icon">✅</div>
            <h3 class="popup-title">Mensagem Enviada!</h3>
            <p class="popup-text">Obrigado pelo contato! Nossa equipe responderá em até 24 horas.</p>
            <button class="btn btn-primary" onclick="document.getElementById('popup-sucesso').style.display='none'">Fechar</button>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="main-content" style="display: none;">
        <!-- Hero Section -->
        <section id="home" style="padding-top: 150px;">
            <div class="container">
                <div class="grid-2" style="align-items: center;">
                    <div>
                        <h1 class="gradient-text" style="font-size: 4.5rem; margin-bottom: 20px;">Gestor Arcon Admin</h1>
                        <p style="font-size: 1.3rem; margin-bottom: 30px;">Controle assinaturas, contratos, faturas e clientes do SaaS Arcon em um painel administrativo dedicado.</p>
                        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <a href="#planos" class="btn btn-primary">Ver Planos</a>
                            <a href="admin/" class="btn btn-outline">Acessar Admin</a>
                        </div>
                    </div>
                    <div>
                        <div class="card-modern" style="border-radius: 30px; padding: 40px;">
                            <div style="font-size: 3rem; color: var(--accent-primary); margin-bottom: 20px;">✓</div>
                            <h4 style="margin-bottom: 15px;">Administração do SaaS</h4>
                            <p style="color: var(--text-secondary);">O Gestor Arcon Admin concentra contratos, planos, pagamentos e portal do cliente, separado do aplicativo operacional Arcon.</p>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 60px;">
                    <img src="assets/image/codigos.jpg" alt="Tecnologia" style="width: 100%; border-radius: 30px; object-fit: cover; height: 500px; box-shadow: 0 20px 40px var(--shadow-color);">
                </div>
            </div>
        </section>

        <!-- Por Que Escolher-nos -->
        <section id="servicos">
            <div class="container">
                <div class="text-center" style="margin-bottom: 60px;">
                    <h2 class="gradient-text">Por Que Escolher-nos?</h2>
                    <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto;">Soluções que impressionam e transformam negócios</p>
                </div>
                
                <div class="grid-3">
                    <div class="feature-item fade-up">
                        <div class="feature-icon">💡</div>
                        <h3 class="feature-title">Inovação</h3>
                        <p class="feature-description">Tecnologia de ponta para resultados surpreendentes. Estamos sempre à frente.</p>
                    </div>
                    <div class="feature-item fade-up">
                        <div class="feature-icon">🛠️</div>
                        <h3 class="feature-title">Suporte 24/7</h3>
                        <p class="feature-description">Estamos sempre aqui para você, dia e noite, com atendimento personalizado.</p>
                    </div>
                    <div class="feature-item fade-up">
                        <div class="feature-icon">🎯</div>
                        <h3 class="feature-title">Personalização</h3>
                        <p class="feature-description">Soluções sob medida para suas necessidades específicas de negócio.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- O Que Fazemos -->
        <section id="oque-fazemos">
            <div class="container">
                <div class="grid-2" style="align-items: center; gap: 50px;">
                    <div>
                        <h2 class="gradient-text" style="font-size: 3rem; margin-bottom: 30px;" id="sobre-nos">O Que Fazemos</h2>
                        <p style="font-size: 1.2rem; margin-bottom: 30px;">Centralize o ciclo comercial do Arcon: planos, clientes, contratos, cobranças, faturas e acesso ao portal do cliente em um ambiente separado do app operacional.</p>
                    </div>
                    <div>
                        <img src="assets/image/computadores.jpg" alt="Tecnologia" style="width: 100%; border-radius: 20px; box-shadow: 0 20px 40px var(--shadow-color);">
                    </div>
                </div>
            </div>
        </section>

        <!-- Planos -->
        <section id="planos">
            <div class="container">
                <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 50px; padding: 80px 40px; text-align: center; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 70% 30%, var(--accent-primary) 0%, transparent 70%); opacity: 0.1;"></div>
                    <h2 class="gradient-text" style="font-size: 3rem; margin-bottom: 20px; position: relative;">Conheça Nossos Planos</h2>
                    <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto 30px; position: relative;">Soluções digitais sob medida para acelerar o crescimento da sua empresa com tecnologia de ponta.</p>
                    <p style="max-width: 800px; margin: 0 auto 40px; color: var(--text-secondary); position: relative;">O Gestor Arcon Admin organiza a gestão comercial do SaaS: cadastro de clientes, planos contratados, contratos digitais, pagamentos e acompanhamento de inadimplência.</p>
                    <a href="planos.php" class="btn btn-primary" style="position: relative; padding: 16px 48px; font-size: 1.2rem;">Saiba mais</a>
                </div>
            </div>
        </section>

        <!-- Sobre Nós -->
        <section id="sobre-nos-full">
            <div class="container">
                <div class="grid-3" style="gap: 50px;">
                    <div>
                        <h2 class="gradient-text" style="font-size: 3rem; margin-bottom: 20px;">Sobre Nós</h2>
                        <div style="width: 100px; height: 4px; background: var(--accent-gradient); margin-bottom: 30px;"></div>
                    </div>
                    <div>
                        <img src="assets/image/robo.jpg" alt="Sobre Nós" style="width: 100%; border-radius: 20px; box-shadow: 0 20px 40px var(--shadow-color);">
                    </div>
                    <div>
                        <p style="font-size: 1.1rem; margin-bottom: 30px;">No Gestor Arcon Admin, a operação comercial fica separada do Arcon de campo. Assim o SaaS mantém cobrança, contratos e assinaturas em um painel próprio.</p>
                        <a href="#" class="btn btn-primary">Saiba Mais</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Por Que Escolher Nossa Empresa -->
        <section>
            <div class="container">
                <div style="text-align: center; max-width: 900px; margin: 0 auto;">
                    <h2 class="gradient-text" style="font-size: 3rem; margin-bottom: 30px;">Por Que Escolher Nossa Empresa?</h2>
                    <p style="font-size: 1.2rem; margin-bottom: 40px;">O painel foi preparado para administrar assinaturas do Arcon com clareza: clientes, contratos, planos, faturas, pagamentos e suporte ao assinante.</p>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <img src="assets/image/logo.png" alt="Equipe" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid var(--accent-primary);">
                        <span style="font-weight: 600;">GESTOR ARCON ADMIN</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Linha de texto animada -->
        <section>
            <div class="container-fluid">
                <div style="padding: 40px 0; overflow: hidden; white-space: nowrap;">
                    <div class="scroll-text">
                        <span class="gradient-text">Sites que impressionam e convertem! • Bots que facilitam sua vida! • Suporte 24/7 • Sistema que ajudam sua empresa em até 90% • </span>
                        <span class="gradient-text">Sites que impressionam e convertem! • Bots que facilitam sua vida! • Suporte 24/7 • Sistema que ajudam sua empresa em até 90% • </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Video -->
        <section>
            <div class="container">
                <div class="video-wrapper">
                    <iframe src="https://www.youtube.com/embed/BadB1z-V_qU?rel=0&amp;mute=1&amp;showinfo=0&amp;autoplay=1&amp;loop=1&amp;playlist=BadB1z-V_qU" width="100%" height="600" frameborder="0" allowfullscreen style="border-radius: 30px;"></iframe>
                </div>
            </div>
        </section>

        <!-- Novidades Recentes -->
        <section>
            <div class="container">
                <div class="grid-2" style="align-items: center; gap: 50px;">
                    <div>
                        <h2 class="gradient-text" style="font-size: 3rem;">Novidades Recentes</h2>
                    </div>
                    <div>
                        <p style="font-size: 1.2rem;">Acompanhe novidades, comunicados e melhorias da plataforma Arcon.</p>
                    </div>
                </div>
                
                <div style="margin-top: 50px; background: var(--card-bg); border-radius: 30px; overflow: hidden; border: 1px solid var(--card-border); display: grid; grid-template-columns: 1fr 1fr; gap: 0;">
                    <div style="height: 400px; overflow: hidden; position: relative;">
                        <img src="assets/image/ponto.png" alt="Novidade" style="position: absolute; width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    </div>
                    <div style="padding: 50px;">
                        <p style="color: var(--accent-primary); margin-bottom: 20px;">Março 2025</p>
                        <h3 style="font-size: 2rem; margin-bottom: 15px;">Lançamento</h3>
                        <p style="font-size: 1.1rem; margin-bottom: 20px; color: var(--text-primary);">SISTEMA DE BATER PONTO</p>
                        <p style="margin-bottom: 30px;">Apresentamos nosso mais novo SISTEMA que automatiza tarefas e aumenta sua produtividade em até 90%! Não fique de fora!</p>
                        <a href="#contato" class="btn btn-primary">Saiba Mais</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Galeria -->
        

        <!-- FAQ -->
        <section id="faqs">
            <div class="container">
                <div class="text-center" style="margin-bottom: 60px;">
                    <h2 class="gradient-text">Perguntas Frequentes</h2>
                    <p style="font-size: 1.2rem;">Tire suas dúvidas sobre nossos serviços</p>
                </div>
                
                <div style="max-width: 900px; margin: 0 auto;">
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h4>Como funciona o processo?</h4>
                            <span>+</span>
                        </div>
                        <div class="accordion-content">
                            <p>Nosso processo é simples e transparente, dividido em etapas claras:</p>
                            <ul style="margin-top: 15px; list-style: none;">
                                <li style="margin-bottom: 10px;">✅ <strong>Planejamento:</strong> Entendemos suas necessidades e objetivos</li>
                                <li style="margin-bottom: 10px;">✅ <strong>Desenvolvimento:</strong> Criamos sua solução personalizada</li>
                                <li style="margin-bottom: 10px;">✅ <strong>Testes:</strong> Garantimos qualidade e performance</li>
                                <li style="margin-bottom: 10px;">✅ <strong>Entrega:</strong> Implementamos seu projeto</li>
                                <li style="margin-bottom: 10px;">✅ <strong>Suporte:</strong> Acompanhamento contínuo</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h4>Quais serviços vocês oferecem?</h4>
                            <span>+</span>
                        </div>
                        <div class="accordion-content">
                            <p>Gerencie planos, contratos, pagamentos e clientes em um painel dedicado ao controle comercial do SaaS Arcon.</p>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h4>Qual o prazo de entrega?</h4>
                            <span>+</span>
                        </div>
                        <div class="accordion-content">
                            <p>O prazo de entrega varia conforme a complexidade do projeto, normalmente entre 7 a 20 dias. Mantemos uma comunicação clara durante todo o processo para atender suas expectativas.</p>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <h4>Vocês oferecem suporte?</h4>
                            <span>+</span>
                        </div>
                        <div class="accordion-content">
                            <p>Sim! Oferecemos suporte contínuo 24/7 para garantir que seus projetos funcionem perfeitamente. Nossa equipe está sempre disponível para resolver dúvidas e problemas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Video Background -->
        <section style="display: flex; justify-content: center; margin: 60px 0; padding: 0 15px;">

    <div style="
        position: relative;
        width: 100%;
        max-width: 900px;
        height: 320px;
        border: 3px solid rgba(13,71,161,0.6);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        background: #000;
    ">
        
        <img 
            src="assets/image/code.gif" 
            alt="Background"
            style="
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            "
        >

    </div>

</section>


        <!-- Nossos Serviços -->
        <section class="services">
            <div class="container">
                <div class="services-header">
                    <h2 class="gradient-text">Nossos Serviços</h2>
                    <a href="#" class="btn btn-primary">Saiba Mais</a>
                </div>

                <div class="services-list">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://www.locaweb.com.br/blog/wp-content/uploads/2023/01/principais-tendencias-em-desenvolvimento.png" alt="Sites">
                        </div>
                        <div class="service-content">
                            <p class="service-tag">Sites</p>
                            <h3>Desenvolvimento Web Premium</h3>
                            <p>Desenvolvemos sites inovadores e de alta performance, projetados para destacar sua empresa no mercado e superar a concorrência.</p>
                        </div>
                    </div>

                    <div class="service-card">
                        <div class="service-image">
                            <img src="assets/image/bot.jpg" alt="Bots">
                        </div>
                        <div class="service-content">
                            <p class="service-tag">Bots</p>
                            <h3>Inteligência Artificial</h3>
                            <p>Automatize acompanhamento de assinaturas, vencimentos, contratos e status comercial dos clientes Arcon.</p>
                        </div>
                    </div>

                    <div class="service-card">
                        <div class="service-image">
                            <img src="assets/image/sistemas.jpg" alt="Sistemas">
                        </div>
                        <div class="service-content">
                            <p class="service-tag">Sistemas</p>
                            <h3>Soluções Personalizadas</h3>
                            <p>Desenvolvemos sistemas sob medida, escaláveis e de alta performance para impulsionar o crescimento do seu negócio.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Final -->
        <section style="position: relative; overflow: hidden; border-radius: 30px; margin: 100px 0;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--accent-gradient); opacity: 0.1; z-index: 1;"></div>
            <div style="position: relative; z-index: 2; padding: 100px 0; text-align: center;">
                <div class="container">
                    <h2 style="font-size: 4rem; margin-bottom: 20px;" class="gradient-text">Transforme Seu Mundo Digital</h2>
                    <p style="font-size: 1.3rem; max-width: 700px; margin: 0 auto 40px;">Use o Gestor Arcon Admin para cuidar da assinatura; use o Arcon para a operação em campo.</p>
                    <a href="#contato" class="btn btn-primary" style="padding: 16px 48px; font-size: 1.2rem;">Comece Agora</a>
                </div>
            </div>
        </section>

        <!-- Formulário de Contato -->
        <section id="contato">
            <div class="container">
                <div class="grid-2" style="gap: 50px;">
                    <div>
                        <div style="text-align: left; margin-bottom: 40px;">
                            <h2 class="gradient-text" style="font-size: 3rem;" id="mensagem">Fale Conosco</h2>
                            <p style="font-size: 1.2rem;">Fale com o suporte para dúvidas sobre contratação, assinatura, pagamento ou acesso ao Arcon.</p>
                        </div>
                        
                        <div class="form-modern">
                            <form action="index.php" method="POST">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="Seu nome completo" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Seu e-mail" required>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="phone" class="form-control" placeholder="Seu telefone" required>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="text" class="form-control" placeholder="Nome da empresa" required>
                                </div>
                                <div class="form-group">
                                    <textarea name="textarea" class="form-control" placeholder="Sua mensagem" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Mensagem</button>
                            </form>
                        </div>
                    </div>
                    
                    <div>
    <div style="
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 30px;
        padding: 40px;
        backdrop-filter: blur(10px);
    ">

        <h3 style="font-size: 2rem; margin-bottom: 30px;">
            <i class="fas fa-envelope-open-text" style="margin-right:10px;"></i>
            Contato
        </h3>

        <div style="margin-bottom: 30px;">
            <p style="color: var(--accent-primary); margin-bottom: 5px;">
                <i class="fas fa-envelope" style="margin-right:8px;"></i>
                E-mail
            </p>
            <p style="font-size: 1.1rem;">sistemasntw@gmail.com</p>
        </div>

        <div style="margin-bottom: 30px;">
            <p style="color: var(--accent-primary); margin-bottom: 5px;">
                <i class="fas fa-phone" style="margin-right:8px;"></i>
                Telefone
            </p>
            <p style="font-size: 1.1rem;">(19) 98711-1656</p>
        </div>

        <div style="margin-bottom: 30px;">
            <p style="color: var(--accent-primary); margin-bottom: 15px;">
                <i class="fas fa-share-alt" style="margin-right:8px;"></i>
                Siga-nos
            </p>

            <div class="social-links" style="display:flex; justify-content:center;">
                <a href="https://instagram.com/arcon.digitalfive" target="_blank"
                   style="
                        width:50px;
                        height:50px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        border-radius:50%;
                        background:linear-gradient(45deg,#f9ce34,#ee2a7b,#6228d7);
                        text-decoration:none;
                        transition:0.3s;
                   "
                   onmouseover="this.style.transform='scale(1.1)'"
                   onmouseout="this.style.transform='scale(1)'"
                >
                    <i class="fa-brands fa-instagram" style="color:white; font-size:22px;"></i>
                </a>
            </div>
        </div>

    </div>
</div>

                </div>
            </div>
        </section>
    </div>

    <!-- Footer Moderno -->
    <footer class="footer-modern" id="main-footer" style="display: none;">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="assets/image/logo.png" alt="Arcon">
                    <h3>Gestor Arcon Admin</h3>
                    <p style="color: var(--text-tertiary);">A tecnologia é a nossa paixão. Junte-se a nós e vamos revolucionar o mundo digital!</p>
                </div>
                
                <div class="footer-links">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#sobre-nos">Sobre Nós</a></li>
                        <li><a href="#servicos">Serviços</a></li>
                        <li><a href="#planos">Planos</a></li>
                        <li><a href="#contato">Contato</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Serviços</h4>
                    <ul>
                        <li><a href="planos.php">Sites Profissionais</a></li>
                        <li><a href="planos.php">Sistemas Personalizados</a></li>
                        <li><a href="planos.php">Bots com IA</a></li>
                        <li><a href="planos.php">Suporte Técnico</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="politica de privacidade/index.php">Política de Privacidade</a></li>
                        
                    </ul>
                </div>
                
                <div class="footer-links">
    <h4>Redes Sociais</h4>

    <div class="social-links" style="margin-top: 20px; display: flex; justify-content: center;">
        
        <a href="https://instagram.com/arcon.digitalfive" target="_blank"
           style="
                width: 55px;
                height: 55px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: linear-gradient(45deg,#f9ce34,#ee2a7b,#6228d7);
                text-decoration: none;
                transition: 0.3s ease;
           "
           onmouseover="this.style.transform='scale(1.1)'"
           onmouseout="this.style.transform='scale(1)'"
        >
            <i class="fa-brands fa-instagram" style="color: white; font-size: 24px;"></i>
        </a>

    </div>
</div>
            </div>
            
            <div class="footer-bottom">
                <p>Copyright © 2025 Gestor Arcon Admin. Todos os direitos reservados.</p>
                <p style="margin-top: 10px;">Founded By Renan.</p>
            </div>
        </div>
    </footer>

    <script>
        // ===== FUNDO COM 0 E 1 ANIMADOS (BEM SUTIL) =====
        const canvas = document.getElementById('binary-canvas');
        const ctx = canvas.getContext('2d');
        let width, height;
        let binaryChars = [];
        
        function initCanvas() {
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width;
            canvas.height = height;
            
            // REDUZI DRASTICAMENTE A QUANTIDADE DE CARACTERES
            const charCount = Math.floor(width * height / 15000); // MUITO MENOS CARACTERES
            
            binaryChars = [];
            
            for (let i = 0; i < charCount; i++) {
                binaryChars.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    value: Math.random() > 0.5 ? '1' : '0',
                    speed: 0.1 + Math.random() * 0.3, // MAIS LENTO
                    size: 10 + Math.floor(Math.random() * 12),
                    opacity: 0.03 + Math.random() * 0.05 // BEM BAIXA OPACIDADE
                });
            }
        }

        function drawBinary() {
            ctx.clearRect(0, 0, width, height);
            
            const rootStyles = getComputedStyle(document.body);
            const binaryColor = rootStyles.getPropertyValue('--binary-color').trim();
            
            binaryChars.forEach(char => {
                ctx.fillStyle = binaryColor;
                ctx.font = `${char.size}px "Fira Code", "Courier New", monospace`;
                ctx.fillText(char.value, char.x, char.y);
                
                char.y += char.speed;
                
                if (char.y > height + 50) {
                    char.y = -50;
                    char.x = Math.random() * width;
                    char.value = Math.random() > 0.5 ? '1' : '0';
                }
            });
            
            requestAnimationFrame(drawBinary);
        }

        window.addEventListener('resize', () => {
            initCanvas();
        });

        initCanvas();
        drawBinary();

        // ===== SPLASH SCREEN COM ANIMAÇÃO GELATINHA =====
        const splashScreen = document.getElementById('splash-screen');
        const typingText = document.getElementById('typing-text');
        const splashSubtitle = document.getElementById('splash-subtitle');
        const progressContainer = document.getElementById('progress-container');
        const splashProgress = document.getElementById('splash-progress');
        const mainContent = document.getElementById('main-content');
        const mainFooter = document.getElementById('main-footer');
        
        const fullText = "NEW SOFTWARE";
        let i = 0;
        let progress = 0;
        
        // Função para criar letras com efeito gelatinha
        function addLetterWithJelly(letter) {
            const span = document.createElement('span');
            span.className = 'jelly-letter';
            span.textContent = letter;
            typingText.appendChild(span);
            
            // Animar cada letra com GSAP
            gsap.fromTo(span, 
                { 
                    scale: 0, 
                    rotation: -10,
                    y: 20,
                    opacity: 0 
                },
                { 
                    scale: 1, 
                    rotation: 0,
                    y: 0,
                    opacity: 1,
                    duration: 0.5,
                    ease: "elastic.out(1, 0.3)"
                }
            );
        }
        
        // Animação de digitação com efeito gelatinha
        const typingInterval = setInterval(() => {
            if (i < fullText.length) {
                addLetterWithJelly(fullText.charAt(i));
                i++;
            } else {
                clearInterval(typingInterval);
                
                // Mostrar subtítulo com efeito gelatinha
                setTimeout(() => {
                    gsap.to(splashSubtitle, {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.8,
                        ease: "elastic.out(1, 0.3)"
                    });
                    
                    gsap.to(progressContainer, {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.8,
                        ease: "elastic.out(1, 0.3)"
                    });
                    
                    // Iniciar barra de progresso
                    const progressInterval = setInterval(() => {
                        progress += 1;
                        splashProgress.style.width = progress + '%';
                        
                        if (progress === 100) {
                            clearInterval(progressInterval);
                            
                            setTimeout(() => {
                                gsap.to(splashScreen, {
                                    opacity: 0,
                                    duration: 1,
                                    ease: "power3.inOut",
                                    onComplete: () => {
    splashScreen.style.visibility = 'hidden';
    mainContent.style.display = 'block';
    mainFooter.style.display = 'block';

    // 👇 se tiver QUALQUER hash na URL
    if (window.location.hash) {
        setTimeout(() => {
            const target = document.querySelector(window.location.hash);
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        }, 100);
    }

    gsap.from('.feature-item', {
        opacity: 0,
        scale: 0.8,
        y: 50,
        duration: 0.8,
        stagger: 0.2,
        ease: "elastic.out(1, 0.3)"
    });

    ScrollTrigger.refresh();
}

                                });
                            }, 500);
                        }
                    }, 30);
                }, 500);
            }
        }, 120); // Velocidade da digitação

        // ===== SISTEMA DE TEMA =====
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.setAttribute('data-theme', savedTheme);
        themeIcon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

        themeToggle.addEventListener('click', () => {
            const current = document.body.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            themeIcon.className = next === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            gsap.fromTo(themeToggle, { scale: 1, rotation: 0 }, { scale: 1.2, rotation: 180, duration: 0.3, yoyo: true, repeat: 1 });
        });

        // ===== MENU MOBILE =====
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu = document.getElementById('navbarMenu');

        navbarToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navbarMenu.classList.toggle('active');
        });

        document.querySelectorAll('.navbar-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navbarToggle.classList.remove('active');
                navbarMenu.classList.remove('active');
            });
        });

        // ===== SCROLL ANIMATIONS =====
        gsap.registerPlugin(ScrollTrigger);

        gsap.utils.toArray('.fade-up').forEach(element => {
            gsap.fromTo(element, 
                { opacity: 0, y: 50, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 1,
                    ease: "elastic.out(1, 0.3)",
                    scrollTrigger: {
                        trigger: element,
                        start: 'top 80%',
                        toggleActions: 'play none none reverse'
                    }
                }
            );
        });

        // ===== ACCORDION FAQ =====
        function toggleAccordion(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('span');
            
            content.classList.toggle('active');
            icon.textContent = content.classList.contains('active') ? '−' : '+';
            
            // Animação do conteúdo
            if (content.classList.contains('active')) {
                gsap.fromTo(content, 
                    { opacity: 0, y: -20, height: 0 },
                    { opacity: 1, y: 0, height: 'auto', duration: 0.5, ease: "elastic.out(1, 0.3)" }
                );
            }
        }

        // ===== SCROLL SUAVE =====
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // ===== NAVBAR SCROLL EFFECT =====
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-modern');
            if (window.scrollY > 100) {
                navbar.style.background = getComputedStyle(document.body).getPropertyValue('--navbar-bg');
                navbar.style.boxShadow = '0 10px 30px var(--shadow-color)';
            } else {
                navbar.style.background = 'var(--navbar-bg)';
                navbar.style.boxShadow = '0 10px 30px var(--shadow-color)';
            }
        });

        // ===== ATUALIZAR CONTADOR DE VISITAS =====
        if (typeof visitas !== 'undefined') {
            document.getElementById('visitorCount').textContent = visitas;
        }

        // ===== FECHAR POP-UP =====
        window.addEventListener('click', function(e) {
            const popup = document.getElementById('popup-sucesso');
            if (e.target === popup) {
                popup.style.display = 'none';
            }
        });
    </script>
    <script>
window.addEventListener("load", function () {
    setTimeout(() => {

        const loader = document.getElementById("loader");
        if (loader) {
            loader.style.display = "none";
        }

        const target = document.getElementById("sobre-nos");
        if (target) {
            target.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }

    }, 1500); // tempo do seu loading
});
if (window.location.hash === "#sobre-nos") {
    const target = document.getElementById("sobre-nos");
    if (target) {
        target.scrollIntoView({ behavior: "smooth" });
    }
}

</script>

</body>
</html>
