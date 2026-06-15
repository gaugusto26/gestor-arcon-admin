<?php
require_once '../config.php';

// Verifica se tem slug
if(!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: blog.php');
    exit;
}

$slug = limparInput($_GET['slug']);

// Busca o post
$stmt = $conn->prepare("
    SELECT p.*, c.nome as categoria_nome, c.cor as categoria_cor, c.slug as categoria_slug,
           c.icone as categoria_icone
    FROM blog_posts p
    LEFT JOIN blog_categorias c ON p.categoria_id = c.id
    WHERE p.slug = ? AND p.status = 'publicado' AND p.ativo = 1
");
$stmt->bind_param("s", $slug);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post) {
    header('Location: blog.php');
    exit;
}

// Incrementa views
$conn->query("UPDATE blog_posts SET views = views + 1 WHERE id = " . $post['id']);

// Busca comentários aprovados
$stmt_coment = $conn->prepare("
    SELECT * FROM blog_comentarios 
    WHERE post_id = ? AND aprovado = 1 
    ORDER BY data_comentario DESC
");
$stmt_coment->bind_param("i", $post['id']);
$stmt_coment->execute();
$comentarios = $stmt_coment->get_result();

// Busca posts relacionados (mesma categoria)
$stmt_rel = $conn->prepare("
    SELECT id, titulo, slug, imagem_destaque, resumo, data_publicacao
    FROM blog_posts 
    WHERE categoria_id = ? AND id != ? AND status = 'publicado' AND ativo = 1
    ORDER BY data_publicacao DESC LIMIT 3
");
$stmt_rel->bind_param("ii", $post['categoria_id'], $post['id']);
$stmt_rel->execute();
$relacionados = $stmt_rel->get_result();

// Busca posts recentes para sidebar
$posts_recentes = $conn->query("
    SELECT id, titulo, slug, data_publicacao 
    FROM blog_posts 
    WHERE status = 'publicado' AND ativo = 1 AND id != " . $post['id'] . "
    ORDER BY data_publicacao DESC LIMIT 5
");

// Processa tags
$tags = $post['tags'] ? explode(',', $post['tags']) : [];

// Verifica se usuário já curtiu (por IP/sessão)
$ip = $_SERVER['REMOTE_ADDR'];
$session_id = session_id();
$stmt_curtida = $conn->prepare("
    SELECT id FROM blog_curtidas 
    WHERE post_id = ? AND (ip = ? OR session_id = ?)
");
$stmt_curtida->bind_param("iss", $post['id'], $ip, $session_id);
$stmt_curtida->execute();
$ja_curtiu = $stmt_curtida->get_result()->num_rows > 0;

// Processa formulário de comentário
$erro_comentario = '';
$sucesso_comentario = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comentario'])) {
    $nome = limparInput($_POST['nome']);
    $email = limparInput($_POST['email']);
    $website = limparInput($_POST['website']);
    $comentario = limparInput($_POST['comentario']);
    
    if(empty($nome) || empty($email) || empty($comentario)) {
        $erro_comentario = 'Preencha todos os campos obrigatórios.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_comentario = 'E-mail inválido.';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO blog_comentarios (post_id, nome, email, website, comentario, ip) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssss", $post['id'], $nome, $email, $website, $comentario, $ip);
        
        if($stmt->execute()) {
            $sucesso_comentario = 'Comentário enviado para aprovação!';
        } else {
            $erro_comentario = 'Erro ao enviar comentário.';
        }
    }
}

// Configuração do WhatsApp
$whatsapp = getWhatsAppConfig($conn);

// Meta tags para SEO
$meta_title = $post['meta_title'] ?: $post['titulo'] . ' | NTW Blog';
$meta_description = $post['meta_description'] ?: $post['resumo'] ?: substr(strip_tags($post['conteudo']), 0, 160);
$meta_image = $post['imagem_og'] ?: $post['imagem_destaque'] ?: '../assets/image/logo2.png';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    
    <!-- Primary Meta Tags -->
    <title><?php echo $meta_title; ?></title>
    <meta name="title" content="<?php echo $meta_title; ?>">
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="keywords" content="<?php echo $post['meta_keywords'] ?: $post['tags']; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/post.php?slug=<?php echo $post['slug']; ?>">
    <meta property="og:title" content="<?php echo $meta_title; ?>">
    <meta property="og:description" content="<?php echo $meta_description; ?>">
    <meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo $meta_image; ?>">
    <meta property="article:published_time" content="<?php echo $post['data_publicacao']; ?>">
    <meta property="article:author" content="<?php echo $post['autor']; ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/post.php?slug=<?php echo $post['slug']; ?>">
    <meta property="twitter:title" content="<?php echo $meta_title; ?>">
    <meta property="twitter:description" content="<?php echo $meta_description; ?>">
    <meta property="twitter:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo $meta_image; ?>">
    
    <link rel="shortcut icon" href="../assets/image/logo2.png" type="image/x-icon">
    
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
            --code-bg: #1e1e1e;
            --code-color: #d4d4d4;
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
            --code-bg: #2d2d2d;
            --code-color: #e6e6e6;
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

        .navbar-menu a.active {
            color: var(--accent-primary);
            font-weight: 600;
        }

        .navbar-menu a.active::after {
            width: 100%;
        }

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

        /* Post Header */
        .post-header {
            padding-top: 140px;
            padding-bottom: 40px;
            text-align: center;
        }

        .post-categoria {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .post-categoria:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .post-titulo {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .post-subtitulo {
            font-size: 1.3rem;
            color: var(--text-secondary);
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
        }

        .post-meta {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 30px;
            color: var(--text-muted);
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-meta i {
            color: var(--accent-primary);
        }

        .post-autor {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .autor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-primary);
        }

        .autor-info {
            text-align: left;
        }

        .autor-nome {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .autor-data {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Post Imagem */
        .post-imagem-container {
            margin: 40px 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px var(--shadow-color);
        }

        .post-imagem {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
        }

        /* Post Conteúdo */
        .post-conteudo {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 50px;
            margin: 40px 0;
            backdrop-filter: blur(10px);
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .post-conteudo h2 {
            font-size: 2rem;
            margin: 40px 0 20px;
            color: var(--text-primary);
        }

        .post-conteudo h3 {
            font-size: 1.5rem;
            margin: 30px 0 15px;
            color: var(--text-primary);
        }

        .post-conteudo h4 {
            font-size: 1.2rem;
            margin: 25px 0 15px;
            color: var(--text-primary);
        }

        .post-conteudo p {
            margin-bottom: 20px;
            color: var(--text-secondary);
        }

        .post-conteudo a {
            color: var(--accent-primary);
            text-decoration: none;
            border-bottom: 1px dashed var(--accent-primary);
        }

        .post-conteudo a:hover {
            border-bottom: 1px solid var(--accent-primary);
        }

        .post-conteudo ul, .post-conteudo ol {
            margin-bottom: 20px;
            padding-left: 30px;
            color: var(--text-secondary);
        }

        .post-conteudo li {
            margin-bottom: 10px;
        }

        .post-conteudo blockquote {
            border-left: 4px solid var(--accent-primary);
            padding: 20px 30px;
            background: var(--bg-secondary);
            border-radius: 12px;
            margin: 30px 0;
            font-style: italic;
            color: var(--text-tertiary);
        }

        .post-conteudo blockquote p:last-child {
            margin-bottom: 0;
        }

        .post-conteudo img {
            max-width: 100%;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 10px 30px var(--shadow-color);
        }

        .post-conteudo pre {
            background: var(--code-bg);
            color: var(--code-color);
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
            margin: 30px 0;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            border: 1px solid var(--card-border);
        }

        .post-conteudo code {
            background: var(--code-bg);
            color: var(--code-color);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
        }

        .post-conteudo pre code {
            padding: 0;
            background: transparent;
        }

        .post-conteudo table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .post-conteudo th {
            background: var(--accent-primary);
            color: white;
            padding: 12px;
            text-align: left;
        }

        .post-conteudo td {
            padding: 12px;
            border: 1px solid var(--card-border);
        }

        .post-conteudo tr:nth-child(even) {
            background: var(--bg-secondary);
        }

        /* Post Tags */
        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 40px 0;
            justify-content: center;
        }

        .post-tag {
            background: var(--bg-secondary);
            color: var(--text-tertiary);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 0.9rem;
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }

        .post-tag:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        /* Post Actions */
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 40px 0;
            padding: 30px 0;
            border-top: 1px solid var(--card-border);
            border-bottom: 1px solid var(--card-border);
        }

        .post-share {
            display: flex;
            gap: 15px;
        }

        .share-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--bg-secondary);
            border: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .share-btn:hover {
            background: var(--accent-gradient);
            color: white;
            transform: translateY(-3px);
        }

        .post-like {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .like-btn {
            padding: 12px 25px;
            border: 2px solid var(--accent-primary);
            border-radius: 30px;
            background: transparent;
            color: var(--accent-primary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .like-btn:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        .like-btn.liked {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
        }

        .like-count {
            font-size: 1.1rem;
            color: var(--text-secondary);
        }

        /* Post Navigation */
        .post-navigation {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
        }

        .nav-prev, .nav-next {
            flex: 1;
            max-width: 300px;
        }

        .nav-next {
            text-align: right;
            margin-left: auto;
        }

        .nav-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .nav-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-title {
            font-weight: 600;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .nav-link:hover .nav-title {
            color: var(--accent-primary);
        }

        /* Posts Relacionados */
        .relacionados-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 30px;
        }

        .relacionado-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .relacionado-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow-color);
            border-color: var(--accent-primary);
        }

        .relacionado-imagem {
            width: 100%;
            height: 150px;
            background-size: cover;
            background-position: center;
        }

        .relacionado-info {
            padding: 20px;
        }

        .relacionado-titulo {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .relacionado-data {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Comentários */
        .comentarios-section {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px;
            margin: 40px 0;
        }

        .comentarios-titulo {
            font-size: 1.8rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comentarios-lista {
            margin-bottom: 40px;
        }

        .comentario {
            padding: 25px;
            border-bottom: 1px solid var(--card-border);
        }

        .comentario:last-child {
            border-bottom: none;
        }

        .comentario-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .comentario-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .comentario-info h4 {
            color: var(--text-primary);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .comentario-info .comentario-data {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .comentario-texto {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-left: 65px;
        }

        .comentario-website {
            margin-left: 65px;
            margin-top: 10px;
        }

        .comentario-website a {
            color: var(--accent-primary);
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .comentario-website a:hover {
            text-decoration: underline;
        }

        .sem-comentarios {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .sem-comentarios i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        /* Formulário de Comentário */
        .comentario-form {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            margin-top: 30px;
        }

        .form-titulo {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-label i {
            color: var(--accent-primary);
            margin-right: 5px;
        }

        .form-control {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
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

        /* Sidebar */
        .blog-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
        }

        .blog-sidebar {
            position: sticky;
            top: 120px;
            align-self: start;
        }

        .sidebar-widget {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .widget-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-primary);
            display: inline-block;
        }

        .recentes-list {
            list-style: none;
        }

        .recente-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--card-border);
        }

        .recente-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .recente-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .recente-link h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .recente-link:hover h4 {
            color: var(--accent-primary);
        }

        .recente-data {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            background: var(--bg-secondary);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .btn-voltar:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateX(-5px);
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

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg,#f9ce34,#ee2a7b,#6228d7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: scale(1.1) rotate(360deg);
        }

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
            .blog-layout {
                grid-template-columns: 1fr;
            }
            
            .blog-sidebar {
                position: static;
            }
            
            .relacionados-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            section { padding: 60px 0; }
            .post-header { padding-top: 120px; }
            
            .post-titulo {
                font-size: 2rem;
            }
            
            .post-subtitulo {
                font-size: 1.1rem;
            }
            
            .post-conteudo {
                padding: 30px;
                font-size: 1rem;
            }
            
            .post-conteudo h2 {
                font-size: 1.5rem;
            }
            
            .post-conteudo h3 {
                font-size: 1.2rem;
            }
            
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

            .post-meta {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
            
            .post-actions {
                flex-direction: column;
                gap: 20px;
            }
            
            .relacionados-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .theme-toggle {
                top: 90px;
                right: 15px;
                width: 42px;
                height: 42px;
            }
        }

        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            .navbar-modern { padding: 10px 18px; }
            .post-conteudo { padding: 20px; }
            .comentarios-section { padding: 25px; }
            .comentario-texto { margin-left: 0; }
        }
    </style>
</head>
<body data-theme="light">

    <canvas id="binary-canvas"></canvas>

    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-sun" id="themeIcon"></i>
    </div>

    <!-- Navbar -->
    <nav class="navbar-modern">
        <div class="navbar-logo">
            <img src="../assets/image/logo2.png" alt="NTW - New Software">
            <span>NTW SOFTWARE</span>
        </div>
        <div class="navbar-menu" id="navbarMenu">
            <a href="../index.php">Home</a>
            <a href="../index.php#sobre-nos">Sobre Nós</a>
            <a href="../index.php#servicos">Serviços</a>
            <a href="../planos.php">Planos</a>
            <a href="../index.php#contato">Contato</a>
            <a href="../index.php#faqs">FAQ</a>
            <a href="blog.php">Blog</a>
            <a href="../index.php#contato" class="btn btn-primary" style="padding: 10px 24px; color: #fff;">Começar</a>
        </div>
        <div class="navbar-toggle" id="navbarToggle">
            <span></span><span></span><span></span>
        </div>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="main-content">
        <div class="container">
            <!-- Botão Voltar -->
            <a href="blog.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para o Blog
            </a>

            <!-- Post Header -->
            <header class="post-header">
                <?php if($post['categoria_nome']): ?>
                <a href="blog.php?categoria=<?php echo $post['categoria_id']; ?>" class="post-categoria" style="background: <?php echo $post['categoria_cor']; ?>">
                    <i class="fas <?php echo $post['categoria_icone'] ?? 'fa-folder'; ?>"></i>
                    <?php echo $post['categoria_nome']; ?>
                </a>
                <?php endif; ?>
                
                <h1 class="post-titulo gradient-text"><?php echo $post['titulo']; ?></h1>
                
                <?php if($post['subtitulo']): ?>
                <h2 class="post-subtitulo"><?php echo $post['subtitulo']; ?></h2>
                <?php endif; ?>
                
                <div class="post-meta">
                    <span>
                        <i class="fas fa-user"></i> <?php echo $post['autor']; ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar"></i> 
                        <?php echo date('d \d\e F \d\e Y', strtotime($post['data_publicacao'])); ?>
                    </span>
                    <?php if($post['tempo_leitura']): ?>
                    <span>
                        <i class="fas fa-clock"></i> <?php echo $post['tempo_leitura']; ?> min de leitura
                    </span>
                    <?php endif; ?>
                    <span>
                        <i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?> visualizações
                    </span>
                </div>
                
                <?php if($post['autor_avatar'] || $post['autor']): ?>
                <div class="post-autor">
                    <?php if($post['autor_avatar']): ?>
                    <img src="<?php echo $post['autor_avatar']; ?>" class="autor-avatar">
                    <?php else: ?>
                    <div class="autor-avatar" style="background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <?php echo strtoupper(substr($post['autor'], 0, 1)); ?>
                    </div>
                    <?php endif; ?>
                    <div class="autor-info">
                        <div class="autor-nome"><?php echo $post['autor']; ?></div>
                        <div class="autor-data">Autor do artigo</div>
                    </div>
                </div>
                <?php endif; ?>
            </header>

            <!-- Post Imagem -->
            <?php if($post['imagem_destaque']): ?>
            <div class="post-imagem-container">
                <img src="<?php echo $post['imagem_destaque']; ?>" class="post-imagem" alt="<?php echo $post['titulo']; ?>">
            </div>
            <?php endif; ?>

            <!-- Post Conteúdo -->
            <div class="post-conteudo">
                <?php echo $post['conteudo']; ?>
            </div>

            <!-- Post Tags -->
            <?php if(!empty($tags)): ?>
            <div class="post-tags">
                <?php foreach($tags as $tag): ?>
                <a href="blog.php?tag=<?php echo urlencode(trim($tag)); ?>" class="post-tag">
                    <i class="fas fa-tag"></i> <?php echo trim($tag); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Post Actions -->
            <div class="post-actions">
                <div class="post-share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/post.php?slug=' . $post['slug']); ?>" target="_blank" class="share-btn">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/post.php?slug=' . $post['slug']); ?>&text=<?php echo urlencode($post['titulo']); ?>" target="_blank" class="share-btn">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/post.php?slug=' . $post['slug']); ?>&title=<?php echo urlencode($post['titulo']); ?>" target="_blank" class="share-btn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($post['titulo'] . ' - https://' . $_SERVER['HTTP_HOST'] . '/post.php?slug=' . $post['slug']); ?>" target="_blank" class="share-btn">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
                
                <div class="post-like">
                    <button class="like-btn <?php echo $ja_curtiu ? 'liked' : ''; ?>" onclick="curtirPost(<?php echo $post['id']; ?>)">
                        <i class="fas fa-heart"></i> Curtir
                    </button>
                    <span class="like-count" id="like-count"><?php echo $post['total_curtidas']; ?></span>
                </div>
            </div>

            <!-- Posts Relacionados -->
            <?php if($relacionados->num_rows > 0): ?>
            <section style="margin: 60px 0;">
                <h2 style="font-size: 2rem; margin-bottom: 30px; text-align: center;">
                    <span class="gradient-text">Posts Relacionados</span>
                </h2>
                
                <div class="relacionados-grid">
                    <?php while($rel = $relacionados->fetch_assoc()): ?>
                    <a href="post.php?slug=<?php echo $rel['slug']; ?>" class="relacionado-card">
                        <?php if($rel['imagem_destaque']): ?>
                        <div class="relacionado-imagem" style="background-image: url('<?php echo $rel['imagem_destaque']; ?>');"></div>
                        <?php else: ?>
                        <div class="relacionado-imagem" style="background: var(--accent-gradient);"></div>
                        <?php endif; ?>
                        <div class="relacionado-info">
                            <h3 class="relacionado-titulo"><?php echo $rel['titulo']; ?></h3>
                            <span class="relacionado-data">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d/m/Y', strtotime($rel['data_publicacao'])); ?>
                            </span>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Seção de Comentários -->
            <section class="comentarios-section">
                <h2 class="comentarios-titulo">
                    <i class="fas fa-comments"></i> 
                    Comentários (<?php echo $comentarios->num_rows; ?>)
                </h2>
                
                <?php if($comentarios->num_rows > 0): ?>
                <div class="comentarios-lista">
                    <?php while($coment = $comentarios->fetch_assoc()): ?>
                    <div class="comentario">
                        <div class="comentario-header">
                            <div class="comentario-avatar">
                                <?php echo strtoupper(substr($coment['nome'], 0, 1)); ?>
                            </div>
                            <div class="comentario-info">
                                <h4><?php echo $coment['nome']; ?></h4>
                                <div class="comentario-data">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($coment['data_comentario'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="comentario-texto">
                            <?php echo nl2br($coment['comentario']); ?>
                        </div>
                        <?php if($coment['website']): ?>
                        <div class="comentario-website">
                            <a href="<?php echo $coment['website']; ?>" target="_blank" rel="nofollow">
                                <i class="fas fa-globe"></i> <?php echo $coment['website']; ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="sem-comentarios">
                    <i class="fas fa-comment-dots"></i>
                    <h3>Nenhum comentário ainda</h3>
                    <p>Seja o primeiro a comentar!</p>
                </div>
                <?php endif; ?>

                <!-- Formulário de Comentário -->
                <div class="comentario-form">
                    <h3 class="form-titulo">Deixe seu comentário</h3>
                    
                    <?php if($erro_comentario): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erro_comentario; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($sucesso_comentario): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $sucesso_comentario; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="formComentario">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Nome *
                                </label>
                                <input type="text" name="nome" class="form-control" required value="<?php echo $_POST['nome'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> E-mail *
                                </label>
                                <input type="email" name="email" class="form-control" required value="<?php echo $_POST['email'] ?? ''; ?>">
                                <small style="color: var(--text-muted);">Não será publicado</small>
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">
                                    <i class="fas fa-globe"></i> Website
                                </label>
                                <input type="url" name="website" class="form-control" value="<?php echo $_POST['website'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">
                                    <i class="fas fa-comment"></i> Comentário *
                                </label>
                                <textarea name="comentario" class="form-control" required><?php echo $_POST['comentario'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" name="comentario" value="1">
                            <i class="fas fa-paper-plane"></i> Enviar Comentário
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="../assets/image/logo.png" alt="NTW">
                    <h3>New Software</h3>
                    <p style="color:var(--text-tertiary);">A tecnologia é a nossa paixão. Junte-se a nós e revolucione seu negócio!</p>
                </div>
                <div class="footer-links">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#sobre-nos">Sobre Nós</a></li>
                        <li><a href="index.php#servicos">Serviços</a></li>
                        <li><a href="planos.php">Planos</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="index.php#contato">Contato</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Serviços</h4>
                    <ul>
                        <li><a href="planos.php#sites">Sites Profissionais</a></li>
                        <li><a href="planos.php#sistemas">Sistemas Personalizados</a></li>
                        <li><a href="planos.php#bots">Bots com IA</a></li>
                        <li><a href="#">Suporte Técnico</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Redes Sociais</h4>
                    <div class="social-links">
                        <a href="https://instagram.com/newsoftwarebr" target="_blank" class="social-link">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>Copyright &copy; 2025 NTW - New Software. Todos os direitos reservados.</p>
                <p style="margin-top:10px;">Founded By Renan.</p>
            </div>
        </div>
    </footer>

    <script>
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

        // ===== CURTIR POST =====
        function curtirPost(postId) {
            fetch('curtir_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({post_id: postId})
            })
            .then(response => response.json())
            .then(data => {
                if(data.sucesso) {
                    const likeBtn = document.querySelector('.like-btn');
                    const likeCount = document.getElementById('like-count');
                    
                    if(data.curtiu) {
                        likeBtn.classList.add('liked');
                    } else {
                        likeBtn.classList.remove('liked');
                    }
                    
                    likeCount.textContent = data.total;
                }
            });
        }

        // Smooth scroll para âncoras
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>