<?php
require_once '../config.php';

// Busca política ativa
$politica = $conn->query("
    SELECT * FROM politica_privacidade 
    WHERE status = 'publicado' 
    ORDER BY versao DESC 
    LIMIT 1
")->fetch_assoc();

if(!$politica) {
    // Se não tiver política publicada, usa a última versão
    $politica = $conn->query("
        SELECT * FROM politica_privacidade 
        ORDER BY created_at DESC 
        LIMIT 1
    ")->fetch_assoc();
}

// Meta tags
$meta_title = $politica['meta_title'] ?: 'Política de Privacidade | NTW - New Software';
$meta_description = $politica['meta_description'] ?: 'Conheça nossa política de privacidade e saiba como protegemos seus dados.';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="shortcut icon" href="assets/image/logo2.webp" type="image/x-icon">
    
    <title><?php echo $meta_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
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

        .btn-primary {
            background: var(--accent-gradient);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px var(--shadow-color);
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

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Navbar */
        .navbar-modern {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 48px);
            max-width: 1280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 100px;
            padding: 12px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
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
            border: 2px solid var(--accent);
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

        .navbar-menu a:hover {
            color: var(--accent);
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
            transition: all 0.3s ease;
        }

        /* Container */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Hero */
        .politica-hero {
            padding-top: 140px;
            padding-bottom: 40px;
            text-align: center;
            background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
        }

        .politica-hero h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .politica-hero .versao {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .politica-hero .subtitulo {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
        }

        .politica-hero .data {
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .politica-hero .data i {
            color: var(--accent);
            margin-right: 5px;
        }

        /* Conteúdo */
        .politica-conteudo {
            background: #ffffff;
            border-radius: 40px;
            padding: 60px;
            margin: 40px 0;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
        }

        .politica-conteudo h2 {
            font-size: 2rem;
            color: var(--text-primary);
            margin: 40px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .politica-conteudo h2:first-of-type {
            margin-top: 0;
        }

        .politica-conteudo h3 {
            font-size: 1.3rem;
            color: var(--text-primary);
            margin: 30px 0 15px;
        }

        .politica-conteudo p {
            margin-bottom: 20px;
            line-height: 1.8;
            color: var(--text-secondary);
        }

        .politica-conteudo ul, .politica-conteudo ol {
            margin-bottom: 20px;
            padding-left: 30px;
        }

        .politica-conteudo li {
            margin-bottom: 10px;
            color: var(--text-secondary);
        }

        .politica-conteudo a {
            color: var(--accent);
            text-decoration: none;
        }

        .politica-conteudo a:hover {
            text-decoration: underline;
        }

        .politica-conteudo blockquote {
            border-left: 4px solid var(--accent);
            padding: 15px 25px;
            background: #f8faff;
            border-radius: 12px;
            margin: 20px 0;
            color: var(--text-secondary);
        }

        .politica-conteudo table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .politica-conteudo th {
            background: #f8faff;
            padding: 12px;
            text-align: left;
            color: var(--text-primary);
        }

        .politica-conteudo td {
            padding: 12px;
            border: 1px solid var(--border);
        }

        /* Footer */
        .footer-modern {
            background: #ffffff;
            border-top: 1px solid var(--border);
            padding: 60px 0 30px;
            margin-top: 80px;
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
            border: 2px solid var(--accent);
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
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent);
            transform: translateX(5px);
        }

        .footer-bottom {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-muted);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-menu {
                position: fixed;
                top: 0; right: -100%;
                width: 80%;
                height: 100vh;
                background: white;
                flex-direction: column;
                justify-content: center;
                padding: 80px 40px;
                transition: right 0.3s ease;
                border-radius: 30px 0 0 30px;
                box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            }

            .navbar-menu.active { right: 0; }
            .navbar-toggle { display: flex; }
            .navbar-toggle.active span:nth-child(1) { transform: rotate(45deg) translate(8px, 8px); }
            .navbar-toggle.active span:nth-child(2) { opacity: 0; }
            .navbar-toggle.active span:nth-child(3) { transform: rotate(-45deg) translate(7px, -7px); }

            .politica-conteudo {
                padding: 30px;
            }

            .politica-conteudo h2 {
                font-size: 1.5rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar-modern">
        <div class="navbar-logo">
            <img src="../assets/image/logo2.png" alt="NTW - New Software">
            <span>NTW SOFTWARE</span>
        </div>
        
        <div class="navbar-menu" id="navbarMenu">
            <a href="../index.php#home">Home</a>
            <a href="../index.php#sobre-nos">Sobre Nós</a>
            <a href="../index.php#servicos">Serviços</a>
            <a href="../planos.php">Planos</a>
            <a href="../index.php#contato">Contato</a>
            <a href="../index.php#faqs">FAQ</a>
            <a href="../blog/blog.php">Blog</a>
            <a href="../index.php#contato" class="btn btn-primary" style="padding: 10px 24px; color: #ffff;">Começar</a>
        </div>
        
        <div class="navbar-toggle" id="navbarToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Hero -->
    <section class="politica-hero">
        <div class="container">
            <span class="versao">Versão <?php echo $politica['versao']; ?></span>
            <h1><?php echo $politica['titulo']; ?></h1>
            <?php if($politica['subtitulo']): ?>
            <div class="subtitulo"><?php echo $politica['subtitulo']; ?></div>
            <?php endif; ?>
            <div class="data">
                <i class="fas fa-calendar"></i> 
                Última atualização: <?php echo date('d/m/Y', strtotime($politica['updated_at'] ?? $politica['created_at'])); ?>
            </div>
        </div>
    </section>

    <!-- Conteúdo -->
    <section>
        <div class="container">
            <div class="politica-conteudo">
                <?php echo $politica['conteudo']; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="../assets/image/logo.png" alt="NTW">
                    <h3>New Software</h3>
                    <p style="color: var(--text-tertiary);">A tecnologia é a nossa paixão. Junte-se a nós e vamos revolucionar o mundo digital!</p>
                </div>
                
                <div class="footer-links">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="../index.php#home">Home</a></li>
                        <li><a href="../index.php#sobre-nos">Sobre Nós</a></li>
                        <li><a href="../index.php#servicos">Serviços</a></li>
                        <li><a href="../index.php#planos">Planos</a></li>
                        <li><a href="../index.php#contato">Contato</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Serviços</h4>
                    <ul>
                        <li><a href="../planos.php">Sites Profissionais</a></li>
                        <li><a href="../planos.php">Sistemas Personalizados</a></li>
                        <li><a href="../planos.php">Bots com IA</a></li>
                        <li><a href="../planos.php">Suporte Técnico</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="index.php">Política de Privacidade</a></li>
                        
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
        // Menu Mobile
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu = document.getElementById('navbarMenu');
        
        navbarToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navbarMenu.classList.toggle('active');
        });

        // Fecha menu ao clicar em link
        document.querySelectorAll('.navbar-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navbarToggle.classList.remove('active');
                navbarMenu.classList.remove('active');
            });
        });
    </script>

</body>
</html>