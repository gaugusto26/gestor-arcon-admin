<?php
require_once 'config.php';

// Processar formulário de contato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = limparInput($_POST['name']);
    $email = limparInput($_POST['email']);
    $telefone = limparInput($_POST['phone']);
    $empresa = limparInput($_POST['text']);
    $mensagem = limparInput($_POST['textarea']);

    $sql = "INSERT INTO mensagens (nome, email, telefone, empresa, mensagem) 
            VALUES ('$nome', '$email', '$telefone', '$empresa', '$mensagem')";

    if ($conn->query($sql) === TRUE) {
        $sucesso = true;
    }
}

// Buscar categorias ativas
$categorias = $conn->query("SELECT * FROM planos_categorias WHERE ativo = 1 ORDER BY ordem");

// Buscar planos MEI
$planos_mei = $conn->query("
    SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone 
    FROM planos p 
    LEFT JOIN planos_categorias c ON p.categoria_id = c.id 
    WHERE p.ativo = 1 AND (p.perfil = 'mei' OR p.perfil = 'ambos') 
    ORDER BY c.ordem, p.ordem
");

// Buscar planos EMPRESA
$planos_empresa = $conn->query("
    SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone 
    FROM planos p 
    LEFT JOIN planos_categorias c ON p.categoria_id = c.id 
    WHERE p.ativo = 1 AND (p.perfil = 'empresa' OR p.perfil = 'ambos') 
    ORDER BY c.ordem, p.ordem
");

// Organizar planos por categoria
$planos_mei_por_categoria = [];
while ($plano = $planos_mei->fetch_assoc()) {
    $cat_id = $plano['categoria_id'];
    if (!isset($planos_mei_por_categoria[$cat_id])) {
        $planos_mei_por_categoria[$cat_id] = [
            'categoria' => $plano['categoria_nome'],
            'icone' => $plano['categoria_icone'],
            'planos' => []
        ];
    }
    $planos_mei_por_categoria[$cat_id]['planos'][] = $plano;
}

$planos_empresa_por_categoria = [];
while ($plano = $planos_empresa->fetch_assoc()) {
    $cat_id = $plano['categoria_id'];
    if (!isset($planos_empresa_por_categoria[$cat_id])) {
        $planos_empresa_por_categoria[$cat_id] = [
            'categoria' => $plano['categoria_nome'],
            'icone' => $plano['categoria_icone'],
            'planos' => []
        ];
    }
    $planos_empresa_por_categoria[$cat_id]['planos'][] = $plano;
}

// Buscar características dos planos
$caracteristicas = [];
$result_carac = $conn->query("SELECT * FROM planos_caracteristicas ORDER BY plano_id, ordem");
while ($carac = $result_carac->fetch_assoc()) {
    $caracteristicas[$carac['plano_id']][] = $carac;
}

// Configuração do WhatsApp
$whatsapp = getWhatsAppConfig($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="shortcut icon" href="assets/image/logo2.webp" type="image/x-icon">
    <meta name="description" content="NTW - New Software | Planos e soluções digitais para microempreendedores e empresas.">
    <title>Planos | NTW - New Software</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 100%;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        :root {
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
            min-height: 100vh;
        }

        #binary-canvas {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        /* Splash Screen */
        #splash-screen {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: var(--bg-gradient);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .splash-content { text-align: center; z-index: 10001; padding: 20px; }

        .splash-logo {
            width: 120px; height: 120px;
            border-radius: 50%;
            border: 4px solid var(--accent-primary);
            margin-bottom: 40px;
            animation: logoJelly 2s infinite;
            box-shadow: 0 0 30px var(--accent-primary);
        }

        @keyframes logoJelly {
            0%, 100% { transform: scale(1); box-shadow: 0 0 30px var(--accent-primary); }
            25% { transform: scale(1.1) rotate(-2deg); box-shadow: 0 0 40px var(--accent-primary); }
            50% { transform: scale(0.95) rotate(2deg); box-shadow: 0 0 20px var(--accent-primary); }
            75% { transform: scale(1.05) rotate(-1deg); box-shadow: 0 0 35px var(--accent-primary); }
        }

        .typing-container {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 30px;
            font-family: 'Fira Code', monospace;
            color: var(--text-primary);
        }

        .typing-text {
            display: inline-block;
            border-right: 4px solid var(--accent-primary);
            white-space: nowrap;
            overflow: hidden;
            animation: blinkCursor 0.8s infinite;
        }

        .jelly-letter {
            display: inline-block;
            animation: jellyPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes jellyPop {
            0% { transform: scale(0.8) translateY(10px); opacity: 0; }
            50% { transform: scale(1.2) translateY(-5px); }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }

        @keyframes blinkCursor {
            0%, 100% { border-color: var(--accent-primary); }
            50% { border-color: transparent; }
        }

        .splash-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(30px) scale(0.9);
        }

        .splash-progress-container {
            width: 300px; height: 4px;
            background: var(--card-border);
            border-radius: 2px;
            margin: 20px auto;
            opacity: 0;
            overflow: hidden;
        }

        .splash-progress-bar {
            width: 0%; height: 100%;
            background: var(--accent-gradient);
            border-radius: 2px;
            transition: width 0.1s linear;
        }

        /* Profile Selector */
        #profile-selector {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: var(--bg-gradient);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .profile-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }

        .profile-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .profile-cards {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: stretch;
        }

        .profile-card {
            background: var(--card-bg);
            border: 2px solid var(--card-border);
            border-radius: 30px;
            padding: 50px 40px;
            text-align: center;
            cursor: pointer;
            width: 300px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
        }

        .profile-card:hover {
            border-color: var(--accent-primary);
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 25px 50px var(--shadow-color);
        }

        .profile-card .profile-icon {
            width: 90px; height: 90px;
            background: var(--accent-gradient);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            color: white;
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .profile-card:hover .profile-icon {
            transform: rotate(10deg) scale(1.1);
        }

        .profile-card h3 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .profile-card p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 100px;
            right: 30px;
            width: 50px; height: 50px;
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

        .theme-toggle i { font-size: 1.3rem; color: var(--accent-primary); }

        /* Navbar */
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
            width: 45px; height: 45px;
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
            bottom: -5px; left: 0;
            width: 0; height: 2px;
            background: var(--accent-gradient);
            transition: width 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .navbar-menu a:hover { color: var(--accent-primary); transform: translateY(-2px); }
        .navbar-menu a:hover::after { width: 100%; }

        .navbar-toggle {
            display: none;
            flex-direction: column;
            gap: 6px;
            cursor: pointer;
        }

        .navbar-toggle span {
            width: 30px; height: 3px;
            background: var(--text-primary);
            border-radius: 3px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* Main Content */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 2;
        }

        section { padding: 80px 0; position: relative; z-index: 2; }

        .gradient-text {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Profile Switcher */
        .profile-switcher {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 60px;
            flex-wrap: wrap;
        }

        .profile-tab {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 30px;
            border-radius: 50px;
            border: 2px solid var(--card-border);
            background: var(--card-bg);
            color: var(--text-secondary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
        }

        .profile-tab:hover {
            border-color: var(--accent-primary);
            transform: translateY(-3px);
        }

        .profile-tab.active {
            background: var(--accent-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 10px 25px var(--shadow-color);
        }

        .profile-tab i { font-size: 1.1rem; }

        /* Category Label */
        .category-label {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 35px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--card-border);
        }

        .category-label-icon {
            width: 50px; height: 50px;
            background: var(--accent-gradient);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .category-label h3 {
            font-size: 1.8rem;
            color: var(--text-primary);
        }

        /* Plan Cards */
        .plans-section { display: none; }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .plan-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 35px 28px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .plan-card:hover {
            transform: translateY(-15px) scale(1.02);
            border-color: var(--card-hover-border);
            box-shadow: 0 25px 50px var(--shadow-color);
        }

        .plan-card.featured {
            border-color: var(--accent-primary);
            box-shadow: 0 15px 40px var(--shadow-color);
        }

        .plan-badge {
            position: absolute;
            top: 22px; right: -35px;
            background: var(--accent-gradient);
            color: white;
            padding: 6px 55px;
            transform: rotate(45deg);
            font-size: 0.72rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            letter-spacing: 0.5px;
        }

        .plan-icon {
            width: 65px; height: 65px;
            background: var(--accent-gradient);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: white;
            margin-bottom: 20px;
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .plan-card:hover .plan-icon { transform: rotate(10deg) scale(1.1); }

        .plan-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .plan-price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent-primary);
            margin-bottom: 25px;
        }

        .plan-price span { 
            font-size: 1rem; 
            font-weight: 400; 
            color: var(--text-secondary);
        }

        .plan-features {
            list-style: none;
            margin-bottom: 30px;
        }

        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .plan-features li i {
            color: #28a745;
            font-size: 0.85rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .plan-deadline {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--text-tertiary);
            margin-bottom: 25px;
            padding: 8px 12px;
            background: var(--bg-secondary);
            border-radius: 10px;
        }

        .plan-deadline i { color: var(--accent-primary); }

        .plan-obs {
            font-size: 0.8rem;
            color: var(--text-tertiary);
            margin-top: -15px;
            margin-bottom: 20px;
            font-style: italic;
            background: var(--bg-secondary);
            padding: 8px 12px;
            border-radius: 8px;
        }

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
            color: var(--btn-text);
        }

        .btn-primary {
            background: var(--accent-gradient);
            box-shadow: 0 10px 20px var(--shadow-color);
            width: 100%;
            text-align: center;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .btn-wa {
            background: #25D366;
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-wa:hover {
            background: #1ebe5b;
            transform: translateY(-3px);
        }

        /* Hero */
        .plans-hero {
            padding-top: 140px;
            padding-bottom: 20px;
            text-align: center;
        }

        .plans-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .plans-hero p { font-size: 1.2rem; max-width: 650px; margin: 0 auto 40px; }

        /* CTA Banner */
        .cta-banner {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 60px 40px;
            text-align: center;
            margin: 60px 0;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .cta-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at 70% 30%, var(--accent-primary) 0%, transparent 70%);
            opacity: 0.07;
        }

        .cta-banner h3 { font-size: 2rem; margin-bottom: 15px; position: relative; }
        .cta-banner p { font-size: 1.1rem; margin-bottom: 30px; position: relative; }
        .cta-banner-buttons { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; position: relative; }

        /* Form */
        .form-modern {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            padding: 40px;
        }

        .form-group { margin-bottom: 20px; }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 50px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
        }

        .form-control::placeholder { color: var(--text-tertiary); }
        textarea.form-control { border-radius: 20px; resize: vertical; min-height: 120px; }

        /* Popup */
        .popup-modern {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
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

        .popup-close {
            position: absolute;
            top: 20px; right: 20px;
            font-size: 1.5rem;
            color: var(--text-tertiary);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .popup-close:hover { color: var(--accent-primary); transform: scale(1.2) rotate(90deg); }

        /* WhatsApp Popup */
        .wa-popup-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9998;
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .wa-popup-content {
            background: var(--card-bg);
            border: 1px solid var(--accent-primary);
            border-radius: 25px;
            max-width: 420px;
            padding: 40px 30px;
            margin: 20px;
            text-align: center;
            animation: popupJelly 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .wa-popup-content h2 { 
            font-size: 1.6rem; 
            margin-bottom: 20px; 
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .wa-popup-content h2 i { color: #25D366; }

        .input-group-wa {
            display: flex;
            align-items: center;
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 12px;
            margin: 10px 0;
            background: var(--input-bg);
            gap: 10px;
        }

        .input-group-wa i { color: var(--accent-primary); font-size: 1.2rem; flex-shrink: 0; }
        .input-group-wa input, .input-group-wa textarea { 
            border: none; 
            background: transparent; 
            outline: none; 
            width: 100%; 
            font-size: 15px; 
            color: var(--text-primary); 
            font-family: 'Inter', sans-serif; 
        }
        .input-group-wa textarea { resize: none; min-height: 80px; }

        #plano-selecionado-info {
            background: var(--accent-light);
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        /* Footer */
        .footer-modern {
            background: var(--footer-bg);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--footer-border);
            padding: 60px 0 30px;
            margin-top: 80px;
            position: relative;
            z-index: 2;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
        }

        .footer-logo img {
            width: 60px; height: 60px;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 2px solid var(--accent-primary);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .footer-logo img:hover { transform: rotate(360deg) scale(1.1); }
        .footer-logo h3 { font-size: 1.3rem; margin-bottom: 15px; color: var(--text-primary); }

        .footer-links h4 { font-size: 1.1rem; margin-bottom: 20px; color: var(--text-primary); }
        .footer-links ul { list-style: none; }
        .footer-links li { margin-bottom: 12px; }
        .footer-links a { color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; display: inline-block; }
        .footer-links a:hover { color: var(--accent-primary); transform: translateX(10px); }

        .social-links { display: flex; gap: 15px; margin-top: 15px; }

        .footer-bottom {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--card-border);
            text-align: center;
            color: var(--text-tertiary);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .plans-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            section { padding: 60px 0; }
            .plans-hero { padding-top: 120px; }
            .plans-hero h1 { font-size: 2.5rem; }

            .navbar-menu {
                position: fixed;
                top: 0; right: -100%;
                width: 80%; max-width: 350px;
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
            .navbar-toggle.active span:nth-child(1) { transform: rotate(45deg) translate(8px, 8px); }
            .navbar-toggle.active span:nth-child(2) { opacity: 0; }
            .navbar-toggle.active span:nth-child(3) { transform: rotate(-45deg) translate(7px, -7px); }

            .plans-grid { grid-template-columns: 1fr; }
            .footer-content { grid-template-columns: 1fr; gap: 30px; }
            .profile-cards { flex-direction: column; align-items: center; }
            .profile-card { width: 100%; max-width: 340px; }
            .theme-toggle { top: 90px; right: 15px; width: 42px; height: 42px; }
            .typing-container { font-size: 2.5rem; }
            .cta-banner { padding: 40px 20px; }
            #contato-form > div > div { grid-template-columns: 1fr !important; }
        }

        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            .plans-hero h1 { font-size: 2rem; }
            .navbar-modern { padding: 10px 18px; }
            .plan-price { font-size: 1.7rem; }
            .typing-container { font-size: 1.8rem; }
        }
    </style>
</head>
<body data-theme="light">

    <canvas id="binary-canvas"></canvas>

    <!-- Splash Screen -->
    <div id="splash-screen">
        <div class="splash-content">
            <img src="assets/image/logo.png" alt="NTW" class="splash-logo">
            <div class="typing-container">
                <span class="typing-text" id="typing-text"></span>
            </div>
            <div class="splash-subtitle" id="splash-subtitle">Escolha o seu plano ideal</div>
            <div class="splash-progress-container" id="progress-container">
                <div class="splash-progress-bar" id="splash-progress"></div>
            </div>
        </div>
    </div>

    <!-- Profile Selector -->
    <div id="profile-selector">
        <div class="profile-header">
            <h2>Para quem são os planos?</h2>
            <p>Selecione o seu perfil para ver as soluções ideais para você</p>
        </div>
        <div class="profile-cards">
            <div class="profile-card" onclick="selectProfile('mei')">
                <div class="profile-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h3>Microempreendedor</h3>
                <p>MEI, autônomo, freelancer ou pequeno negócio. Soluções acessíveis para começar ou crescer.</p>
            </div>
            <div class="profile-card" onclick="selectProfile('empresa')">
                <div class="profile-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>Profissional / Empresa</h3>
                <p>Empresa de médio ou grande porte que precisa de sistemas robustos e soluções completas.</p>
            </div>
        </div>
    </div>

    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-sun" id="themeIcon"></i>
    </div>

    <!-- Navbar -->
    <nav class="navbar-modern">
        <div class="navbar-logo">
            <img src="assets/image/logo2.png" alt="NTW - New Software">
            <span>NTW SOFTWARE</span>
        </div>
        <div class="navbar-menu" id="navbarMenu">
            <a href="index.php">Home</a>
            <a href="index.php#sobre-nos">Sobre Nós</a>
            <a href="index.php#servicos">Serviços</a>
            <a href="planos.php">Planos</a>
            <a href="index.php#contato">Contato</a>
            <a href="index.php#faqs">FAQ</a>
            <a href="blog/blog.php">Blog</a>
            <a href="index.php#contato" class="btn btn-primary" style="padding: 10px 24px; color: #fff;">Começar</a>
        </div>
        <div class="navbar-toggle" id="navbarToggle">
            <span></span><span></span><span></span>
        </div>
    </nav>

    <!-- Pop-up Sucesso -->
    <div id="popup-sucesso" class="popup-modern" <?php if(isset($sucesso)) echo 'style="display:flex;"'; ?>>
        <div class="popup-content-modern">
            <span class="popup-close" onclick="document.getElementById('popup-sucesso').style.display='none'">&times;</span>
            <div style="font-size:4rem; margin-bottom:20px;"><i class="fas fa-circle-check" style="color:var(--accent-primary);"></i></div>
            <h3 style="font-size:1.8rem; margin-bottom:15px; color:var(--text-primary);">Mensagem Enviada!</h3>
            <p style="color:var(--text-secondary); margin-bottom:30px;">Obrigado pelo contato! Nossa equipe responderá em até 24 horas.</p>
            <button class="btn btn-primary" onclick="document.getElementById('popup-sucesso').style.display='none'" style="max-width:200px; margin:0 auto; display:block;">Fechar</button>
        </div>
    </div>

    <!-- WhatsApp Popup -->
    <div class="wa-popup-overlay" id="waPopup">
        <div class="wa-popup-content">
            <h2><i class="fab fa-whatsapp"></i> Fale via WhatsApp</h2>
            <div id="plano-selecionado-info"></div>
            <div class="input-group-wa">
                <i class="fas fa-user"></i>
                <input type="text" id="wa-nome" placeholder="Seu nome">
            </div>
            <div class="input-group-wa">
                <i class="fas fa-comment-dots"></i>
                <textarea id="wa-descricao" placeholder="Descreva o que você precisa"></textarea>
            </div>
            <button class="btn-wa" onclick="enviarWA()" style="width:100%; justify-content:center; margin-top:15px;">
                <i class="fas fa-paper-plane"></i> Enviar mensagem
            </button>
            <button class="btn btn-outline" onclick="fecharWaPopup()" style="width:100%; margin-top:10px;">Fechar</button>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="main-content" style="display:none;">

        <!-- Hero -->
        <section class="plans-hero">
            <div class="container">
                <h1 class="gradient-text">Planos & Soluções</h1>
                <p>Escolha o plano ideal para o seu negócio. Soluções digitais que entregam resultados reais.</p>

                <!-- Profile switcher -->
                <div class="profile-switcher">
                    <button class="profile-tab active" id="tab-mei" onclick="switchProfile('mei')">
                        <i class="fas fa-store"></i> Microempreendedor
                    </button>
                    <button class="profile-tab" id="tab-empresa" onclick="switchProfile('empresa')">
                        <i class="fas fa-building"></i> Profissional / Empresa
                    </button>
                </div>
            </div>
        </section>

        <!-- ===== PLANOS MEI ===== -->
        <div id="plans-mei" class="plans-section">
            <div class="container">
                <?php foreach($planos_mei_por_categoria as $cat): ?>
                <div class="category-block">
                    <div class="category-label">
                        <div class="category-label-icon"><i class="fas <?php echo $cat['icone']; ?>"></i></div>
                        <h3><?php echo $cat['categoria']; ?></h3>
                    </div>
                    <div class="plans-grid">
                        <?php foreach($cat['planos'] as $plano): ?>
                        <div class="plan-card <?php echo $plano['destaque'] ? 'featured' : ''; ?>">
                            <?php if($plano['badge_text']): ?>
                            <div class="plan-badge"><?php echo $plano['badge_text']; ?></div>
                            <?php endif; ?>
                            
                            <div class="plan-icon"><i class="fas <?php echo $cat['icone']; ?>"></i></div>
                            <div class="plan-name"><?php echo $plano['nome']; ?></div>
                            <div class="plan-price">
                                <?php echo formatarPreco($plano['preco']); ?>
                                <span>/<?php echo $plano['periodo']; ?></span>
                            </div>
                            
                            <ul class="plan-features">
                                <?php if(isset($caracteristicas[$plano['id']])): ?>
                                    <?php foreach($caracteristicas[$plano['id']] as $carac): ?>
                                    <li><i class="fas <?php echo $carac['icone']; ?>"></i> <?php echo $carac['caracteristica']; ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            
                            <?php if($plano['prazo_entrega']): ?>
                            <div class="plan-deadline">
                                <i class="fas fa-clock"></i> Entrega: <?php echo $plano['prazo_entrega']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($plano['observacao']): ?>
                            <div class="plan-obs">
                                <i class="fas fa-info-circle"></i> <?php echo $plano['observacao']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <button class="btn btn-primary" onclick="abrirWaPopup('<?php echo $plano['nome']; ?>')">
                                <i class="fab fa-whatsapp"></i> Adquirir Agora
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===== PLANOS EMPRESA ===== -->
        <div id="plans-empresa" class="plans-section">
            <div class="container">
                <?php foreach($planos_empresa_por_categoria as $cat): ?>
                <div class="category-block">
                    <div class="category-label">
                        <div class="category-label-icon"><i class="fas <?php echo $cat['icone']; ?>"></i></div>
                        <h3><?php echo $cat['categoria']; ?></h3>
                    </div>
                    <div class="plans-grid">
                        <?php foreach($cat['planos'] as $plano): ?>
                        <div class="plan-card <?php echo $plano['destaque'] ? 'featured' : ''; ?>">
                            <?php if($plano['badge_text']): ?>
                            <div class="plan-badge"><?php echo $plano['badge_text']; ?></div>
                            <?php endif; ?>
                            
                            <div class="plan-icon"><i class="fas <?php echo $cat['icone']; ?>"></i></div>
                            <div class="plan-name"><?php echo $plano['nome']; ?></div>
                            <div class="plan-price">
                                <?php echo formatarPreco($plano['preco']); ?>
                                <span>/<?php echo $plano['periodo']; ?></span>
                            </div>
                            
                            <ul class="plan-features">
                                <?php if(isset($caracteristicas[$plano['id']])): ?>
                                    <?php foreach($caracteristicas[$plano['id']] as $carac): ?>
                                    <li><i class="fas <?php echo $carac['icone']; ?>"></i> <?php echo $carac['caracteristica']; ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            
                            <?php if($plano['prazo_entrega']): ?>
                            <div class="plan-deadline">
                                <i class="fas fa-clock"></i> Entrega: <?php echo $plano['prazo_entrega']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($plano['observacao']): ?>
                            <div class="plan-obs">
                                <i class="fas fa-info-circle"></i> <?php echo $plano['observacao']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <button class="btn btn-primary" onclick="abrirWaPopup('<?php echo $plano['nome']; ?>')">
                                <i class="fab fa-whatsapp"></i> Adquirir Agora
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA Banner -->
        <section>
            <div class="container">
                <div class="cta-banner">
                    <h3 class="gradient-text">Não encontrou o plano ideal?</h3>
                    <p>Fale conosco. Criamos soluções totalmente personalizadas para o seu negócio.</p>
                    <div class="cta-banner-buttons">
                        <a href="#contato-form" class="btn btn-primary" style="display:flex; align-items:center; gap:10px;">
                            <i class="fas fa-envelope"></i> Formulário de Contato
                        </a>
                        <button class="btn-wa" onclick="abrirWaPopup('Plano Personalizado')">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Formulário de Contato -->
        <section id="contato-form">
            <div class="container">
                <div class="section-header">
                    <h2 class="gradient-text">Fale Conosco</h2>
                    <p>Estamos prontos para transformar suas ideias em soluções digitais reais.</p>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: start;">
                    <div class="form-modern">
                        <form action="planos.php" method="POST">
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane" style="margin-right:8px;"></i> Enviar Mensagem
                            </button>
                        </form>
                    </div>
                    <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 30px; padding: 40px; backdrop-filter: blur(10px);">
                        <h3 style="font-size: 1.8rem; margin-bottom: 30px; color: var(--text-primary);">
                            <i class="fas fa-headset" style="color: var(--accent-primary); margin-right:10px;"></i> Contato Direto
                        </h3>
                        <div style="margin-bottom: 25px;">
                            <p style="color: var(--accent-primary); margin-bottom: 5px; font-weight:600;">
                                <i class="fas fa-envelope" style="margin-right:8px;"></i> E-mail
                            </p>
                            <p style="font-size: 1.05rem; color: var(--text-primary);">sistemasntw@gmail.com</p>
                        </div>
                        <div style="margin-bottom: 25px;">
                            <p style="color: var(--accent-primary); margin-bottom: 5px; font-weight:600;">
                                <i class="fas fa-phone" style="margin-right:8px;"></i> Telefone
                            </p>
                            <p style="font-size: 1.05rem; color: var(--text-primary);">(19) 98711-1656</p>
                        </div>
                        <div>
                            <p style="color: var(--accent-primary); margin-bottom: 15px; font-weight:600;">
                                <i class="fas fa-share-alt" style="margin-right:8px;"></i> Redes Sociais
                            </p>
                            <a href="https://instagram.com/newsoftwarebr" target="_blank" style="width:50px; height:50px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:linear-gradient(45deg,#f9ce34,#ee2a7b,#6228d7); text-decoration:none; transition:0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="fa-brands fa-instagram" style="color:white; font-size:22px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div><!-- /main-content -->

    <!-- Footer -->
    <footer class="footer-modern" id="main-footer" style="display: none;">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="assets/image/logo.png" alt="NTW">
                    <h3>New Software</h3>
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
        
        <a href="https://instagram.com/newsoftwarebr" target="_blank"
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
                <p>Copyright © 2025 NTW - New Software. Todos os direitos reservados.</p>
                <p style="margin-top: 10px;">Founded By Renan.</p>
            </div>
        </div>
    </footer>

    <script>
        // ===== VARIÁVEIS GLOBAIS =====
        let planoAtual = '';
        const numeroWhatsApp = '<?php echo $whatsapp['numero']; ?>';
        const msgPadrao = '<?php echo $whatsapp['mensagem_padrao']; ?>';

        // ===== BINARY CANVAS =====
        const canvas = document.getElementById('binary-canvas');
        const ctx = canvas.getContext('2d');
        let binaryChars = [];

        function initCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            const count = Math.floor(canvas.width * canvas.height / 15000);
            binaryChars = [];
            for (let i = 0; i < count; i++) {
                binaryChars.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    value: Math.random() > 0.5 ? '1' : '0',
                    speed: 0.1 + Math.random() * 0.3,
                    size: 10 + Math.floor(Math.random() * 12),
                });
            }
        }

        function drawBinary() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            const color = getComputedStyle(document.body).getPropertyValue('--binary-color').trim();
            binaryChars.forEach(c => {
                ctx.fillStyle = color;
                ctx.font = `${c.size}px "Fira Code", monospace`;
                ctx.fillText(c.value, c.x, c.y);
                c.y += c.speed;
                if (c.y > canvas.height + 50) {
                    c.y = -50;
                    c.x = Math.random() * canvas.width;
                    c.value = Math.random() > 0.5 ? '1' : '0';
                }
            });
            requestAnimationFrame(drawBinary);
        }

        window.addEventListener('resize', initCanvas);
        initCanvas();
        drawBinary();

        // ===== SPLASH SCREEN =====
        const splashScreen = document.getElementById('splash-screen');
        const typingText = document.getElementById('typing-text');
        const splashSubtitle = document.getElementById('splash-subtitle');
        const progressContainer = document.getElementById('progress-container');
        const splashProgress = document.getElementById('splash-progress');

        const fullText = "PLANOS NTW";
        let i = 0;
        let progress = 0;

        function addLetter(letter) {
            const span = document.createElement('span');
            span.className = 'jelly-letter';
            span.textContent = letter;
            typingText.appendChild(span);
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(span,
                    { scale: 0, rotation: -10, y: 20, opacity: 0 },
                    { scale: 1, rotation: 0, y: 0, opacity: 1, duration: 0.5, ease: "elastic.out(1, 0.3)" }
                );
            }
        }

        const typingInterval = setInterval(() => {
            if (i < fullText.length) {
                addLetter(fullText.charAt(i));
                i++;
            } else {
                clearInterval(typingInterval);
                setTimeout(() => {
                    if (typeof gsap !== 'undefined') {
                        gsap.to(splashSubtitle, { opacity: 1, y: 0, scale: 1, duration: 0.8, ease: "elastic.out(1, 0.3)" });
                        gsap.to(progressContainer, { opacity: 1, y: 0, scale: 1, duration: 0.8, ease: "elastic.out(1, 0.3)" });
                    }
                    const pInt = setInterval(() => {
                        progress += 1;
                        splashProgress.style.width = progress + '%';
                        if (progress === 100) {
                            clearInterval(pInt);
                            setTimeout(() => {
                                if (typeof gsap !== 'undefined') {
                                    gsap.to(splashScreen, {
                                        opacity: 0, duration: 0.8, ease: "power3.inOut",
                                        onComplete: () => {
                                            splashScreen.style.visibility = 'hidden';
                                            document.getElementById('profile-selector').style.display = 'flex';
                                            if (typeof gsap !== 'undefined') {
                                                gsap.fromTo('#profile-selector', { opacity: 0 }, { opacity: 1, duration: 0.5 });
                                                gsap.from('.profile-header', {
                                                    opacity: 0, y: -30, duration: 0.6, ease: "power3.out"
                                                });
                                            }
                                        }
                                    });
                                }
                            }, 400);
                        }
                    }, 30);
                }, 400);
            }
        }, 130);

        // ===== SELECT PROFILE =====
        function selectProfile(type) {
            document.getElementById('profile-selector').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
            document.getElementById('main-footer').style.display = 'block';
            window.scrollTo(0, 0);
            document.getElementById('plans-mei').style.display = 'none';
            document.getElementById('plans-empresa').style.display = 'none';
            document.getElementById('plans-' + type).style.display = 'block';
            document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + type).classList.add('active');
        }

        // ===== SWITCH PROFILE =====
        function switchProfile(type) {
            document.getElementById('plans-mei').style.display = 'none';
            document.getElementById('plans-empresa').style.display = 'none';
            document.getElementById('plans-' + type).style.display = 'block';
            document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + type).classList.add('active');
        }

        // ===== THEME TOGGLE =====
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
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(themeToggle, { scale: 1, rotation: 0 }, { scale: 1.2, rotation: 180, duration: 0.3, yoyo: true, repeat: 1 });
            }
        });

        // ===== MENU MOBILE =====
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu = document.getElementById('navbarMenu');
        navbarToggle.addEventListener('click', function () {
            this.classList.toggle('active');
            navbarMenu.classList.toggle('active');
        });
        document.querySelectorAll('.navbar-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navbarToggle.classList.remove('active');
                navbarMenu.classList.remove('active');
            });
        });

        // ===== WHATSAPP POPUP =====
        function abrirWaPopup(plano) {
            planoAtual = plano;
            document.getElementById('plano-selecionado-info').innerHTML = 
                `<strong>Plano:</strong> ${plano}`;
            document.getElementById('waPopup').style.display = 'flex';
        }

        function fecharWaPopup() {
            document.getElementById('waPopup').style.display = 'none';
            document.getElementById('wa-nome').value = '';
            document.getElementById('wa-descricao').value = '';
        }

        function enviarWA() {
            const nome = document.getElementById('wa-nome').value.trim();
            const desc = document.getElementById('wa-descricao').value.trim();
            
            if (!nome || !desc) { 
                alert('Por favor, preencha todos os campos.'); 
                return; 
            }
            
            let msg = msgPadrao.replace('{plano_nome}', planoAtual);
            msg += `\n\nNome: ${nome}\nDescrição: ${desc}`;
            
            window.open(`https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(msg)}`, '_blank');
            fecharWaPopup();
        }

        // Fechar popup clicando fora
        document.querySelector('.wa-popup-overlay').addEventListener('click', function(e) {
            if (e.target === this) fecharWaPopup();
        });

        // ===== SCROLL SMOOTH =====
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ===== POPUP SUCESSO =====
        window.addEventListener('click', e => {
            const popup = document.getElementById('popup-sucesso');
            if (e.target === popup) popup.style.display = 'none';
        });
    </script>
</body>
</html>