<?php
require_once '../config.php';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 6;
$offset = ($pagina - 1) * $por_pagina;

// Filtro por categoria
$categoria_filtro = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

// Busca posts publicados
$sql_posts = "SELECT p.*, c.nome as categoria_nome, c.cor as categoria_cor, c.slug as categoria_slug,
              (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id AND aprovado = 1) as total_comentarios,
              (SELECT COUNT(*) FROM blog_curtidas WHERE post_id = p.id) as total_curtidas
              FROM blog_posts p
              LEFT JOIN blog_categorias c ON p.categoria_id = c.id
              WHERE p.status = 'publicado' AND p.ativo = 1";

if($categoria_filtro > 0) {
    $sql_posts .= " AND p.categoria_id = " . (int)$categoria_filtro;
}

$sql_posts .= " ORDER BY p.destaque DESC, p.data_publicacao DESC LIMIT $offset, $por_pagina";
$posts = $conn->query($sql_posts);

// Conta total de posts para paginação
$sql_total = "SELECT COUNT(*) as total FROM blog_posts WHERE status = 'publicado' AND ativo = 1";
if($categoria_filtro > 0) {
    $sql_total .= " AND categoria_id = " . (int)$categoria_filtro;
}
$total_result = $conn->query($sql_total);
$total_posts = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_posts / $por_pagina);

// Busca posts em destaque (para sidebar)
$posts_destaque = $conn->query("
    SELECT id, titulo, slug, imagem_destaque, data_publicacao 
    FROM blog_posts 
    WHERE status = 'publicado' AND ativo = 1 AND destaque = 1 
    ORDER BY data_publicacao DESC LIMIT 5
");

// Busca posts recentes (para sidebar)
$posts_recentes = $conn->query("
    SELECT id, titulo, slug, data_publicacao 
    FROM blog_posts 
    WHERE status = 'publicado' AND ativo = 1 
    ORDER BY data_publicacao DESC LIMIT 5
");

// Busca categorias com contagem de posts
$categorias = $conn->query("
    SELECT c.*, COUNT(p.id) as total_posts 
    FROM blog_categorias c
    LEFT JOIN blog_posts p ON c.id = p.categoria_id AND p.status = 'publicado' AND p.ativo = 1
    WHERE c.ativo = 1
    GROUP BY c.id
    ORDER BY c.ordem, c.nome
");

// Busca tags populares
$tags_populares = $conn->query("
    SELECT tags, COUNT(*) as total 
    FROM blog_posts 
    WHERE status = 'publicado' AND ativo = 1 AND tags IS NOT NULL AND tags != ''
    GROUP BY tags 
    ORDER BY total DESC LIMIT 10
");

// Processa tags em array
$tags_array = [];
while($tag_row = $tags_populares->fetch_assoc()) {
    $tags = explode(',', $tag_row['tags']);
    foreach($tags as $tag) {
        $tag = trim($tag);
        if(!empty($tag)) {
            $tags_array[$tag] = ($tags_array[$tag] ?? 0) + 1;
        }
    }
}
arsort($tags_array);
$tags_populares = array_slice($tags_array, 0, 15, true);

// Configuração do WhatsApp
$whatsapp = getWhatsAppConfig($conn);

// Nome da categoria ativa para exibir no filtro
$cat_nome_filtro = '';
if($categoria_filtro > 0) {
    $cat_res = $conn->query("SELECT nome FROM blog_categorias WHERE id = " . (int)$categoria_filtro);
    if($cat_res && $cat_res->num_rows > 0) {
        $cat_nome_filtro = $cat_res->fetch_assoc()['nome'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link rel="shortcut icon" href="../assets/image/logo2.png" type="image/x-icon">
    <meta name="description" content="Blog da NTW - New Software | Artigos sobre tecnologia, programação, marketing digital e negócios">
    <title>Blog | NTW - New Software</title>

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
            --text-muted: #5c85c7;
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
            --text-muted: #7ab3e0;
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

        /* ===== Theme Toggle ===== */
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
            font-size: 1.3rem;
            color: var(--accent-primary);
        }

        /* ===== Navbar ===== */
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
            white-space: nowrap;
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

        .navbar-menu a.active {
            color: var(--accent-primary);
            font-weight: 600;
        }

        .navbar-menu a.active::after {
            width: 100%;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white !important;
            padding: 10px 24px !important;
            border-radius: 50px !important;
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .btn-primary:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .btn-primary::after {
            display: none !important;
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

        /* ===== Container ===== */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 2;
            width: 100%;
        }

        section {
            padding: 80px 0;
            position: relative;
            z-index: 2;
        }

        .gradient-text {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== Hero do Blog ===== */
        .blog-hero {
            padding-top: 140px;
            padding-bottom: 40px;
            text-align: center;
        }

        .blog-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .blog-hero p {
            font-size: 1.2rem;
            max-width: 650px;
            margin: 0 auto 30px;
            color: var(--text-secondary);
        }

        /* ===== Layout do Blog ===== */
        .blog-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-top: 40px;
            align-items: start;
            width: 100%;
        }

        /* ===== Posts Grid ===== */
        .posts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            min-width: 0; /* FIX: impede que o grid estoure o container */
            width: 100%;
        }

        /* ===== Post Card ===== */
        .post-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            width: 100%;
            min-width: 0; /* FIX: impede overflow no grid */
        }

        .post-card:hover {
            transform: translateY(-8px);
            border-color: var(--card-hover-border);
            box-shadow: 0 20px 40px var(--shadow-color);
        }

        .post-card.destaque {
            border: 2px solid var(--accent-primary);
            position: relative;
        }

        .post-card.destaque::before {
            content: '⭐ DESTAQUE';
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--accent-gradient);
            color: white;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .post-imagem {
            width: 100%;
            height: 250px;
            background-size: cover;
            background-position: center;
            position: relative;
            flex-shrink: 0;
        }

        .post-imagem .sem-imagem {
            width: 100%;
            height: 100%;
            background: var(--accent-gradient);
            opacity: 0.3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        .post-categoria-tag {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 16px;
            border-radius: 30px;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            z-index: 2;
            max-width: 70%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .post-conteudo {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; /* FIX: importante para overflow no flexbox */
        }

        .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .post-meta i {
            color: var(--accent-primary);
        }

        .post-titulo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-primary);
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .post-titulo a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .post-titulo a:hover {
            color: var(--accent-primary);
        }

        .post-resumo {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 20px;
            line-height: 1.6;
            flex: 1;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .post-tag {
            background: var(--bg-secondary);
            color: var(--text-tertiary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            border: 1px solid var(--card-border);
            white-space: nowrap;
        }

        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid var(--card-border);
            flex-wrap: wrap;
            gap: 15px;
        }

        .post-stats {
            display: flex;
            gap: 15px;
            color: var(--text-muted);
            font-size: 0.85rem;
            flex-wrap: wrap;
        }

        .post-stats span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .post-stats i {
            color: var(--accent-primary);
        }

        .btn-ler-mais {
            padding: 10px 20px;
            background: transparent;
            border: 2px solid var(--accent-primary);
            color: var(--accent-primary);
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-ler-mais:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateX(5px);
        }

        /* ===== Sidebar ===== */
        .blog-sidebar {
            position: sticky;
            top: 120px;
            align-self: start;
            min-width: 0; /* FIX: impede overflow */
            width: 100%;
        }

        .sidebar-widget {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            width: 100%;
            min-width: 0; /* FIX */
        }

        .widget-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-primary);
            display: inline-block;
        }

        /* ===== Categorias ===== */
        .categorias-list {
            list-style: none;
        }

        .categoria-item {
            margin-bottom: 12px;
        }

        .categoria-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: var(--bg-secondary);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 10px;
        }

        .categoria-link:hover,
        .categoria-link.active {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateX(5px);
        }

        .categoria-link:hover .categoria-count,
        .categoria-link.active .categoria-count {
            background: white;
            color: var(--accent-primary);
        }

        .categoria-nome {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 0;
        }

        .categoria-nome i {
            width: 20px;
            flex-shrink: 0;
        }

        .categoria-nome span {
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .categoria-count {
            background: var(--accent-primary);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        /* ===== Posts Recentes / Destaque ===== */
        .recentes-list {
            list-style: none;
        }

        .recente-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--card-border);
        }

        .recente-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .recente-link {
            display: flex;
            gap: 15px;
            text-decoration: none;
            color: inherit;
        }

        .recente-imagem {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
            background-color: var(--bg-secondary);
        }

        .recente-imagem .sem-imagem {
            width: 100%;
            height: 100%;
            background: var(--accent-gradient);
            opacity: 0.3;
            border-radius: 12px;
        }

        .recente-info {
            flex: 1;
            min-width: 0;
        }

        .recente-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
            transition: color 0.3s ease;
            word-wrap: break-word;
            overflow-wrap: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

        /* ===== Nuvem de Tags ===== */
        .tags-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag-cloud-item {
            padding: 6px 14px;
            background: var(--bg-secondary);
            border: 1px solid var(--card-border);
            border-radius: 30px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .tag-cloud-item:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: scale(1.05);
        }

        /* ===== Newsletter ===== */
        .newsletter-widget p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .newsletter-input {
            padding: 12px 18px;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }

        .newsletter-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
        }

        .btn-newsletter {
            padding: 12px;
            background: var(--accent-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }

        .btn-newsletter:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        /* ===== WhatsApp ===== */
        .btn-wa {
            background: #25D366;
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .btn-wa:hover {
            background: #1ebe5b;
            transform: translateY(-3px);
        }

        /* ===== Filtro Ativo ===== */
        .filtro-ativo {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .filtro-badge {
            background: var(--accent-gradient);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .filtro-badge a {
            color: white;
            text-decoration: none;
            margin-left: 5px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .filtro-badge a:hover {
            opacity: 1;
        }

        /* ===== Paginação ===== */
        .paginacao {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 12px 18px;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            background: var(--card-bg);
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            min-width: 45px;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .page-link:hover {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        .page-link.active {
            background: var(--accent-gradient);
            color: white;
            border-color: transparent;
        }

        .page-link.disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        /* ===== Sem posts ===== */
        .sem-posts {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--card-border);
        }

        .sem-posts i {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: block;
        }

        .sem-posts h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .sem-posts p {
            color: var(--text-secondary);
        }

        /* ===== Footer ===== */
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

        .footer-logo p {
            color: var(--text-tertiary);
            line-height: 1.6;
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
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--accent-primary);
            transform: translateX(10px);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg, #f9ce34, #ee2a7b, #6228d7);
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

        /* ===== Responsive ===== */
        @media (max-width: 1200px) {
            .blog-layout {
                gap: 30px;
            }

            .post-titulo {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 1024px) {
            .blog-layout {
                grid-template-columns: 1fr;
            }

            .blog-sidebar {
                position: static;
                margin-top: 40px;
            }
        }

        @media (max-width: 768px) {
            section {
                padding: 60px 0;
            }

            .blog-hero {
                padding-top: 120px;
            }

            .blog-hero h1 {
                font-size: 2.5rem;
            }

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
                gap: 20px;
                overflow-y: auto;
            }

            .navbar-menu.active {
                right: 0;
            }

            .navbar-menu a {
                width: 100%;
                text-align: center;
                white-space: normal;
            }

            .navbar-toggle {
                display: flex;
            }

            .navbar-toggle.active span:nth-child(1) {
                transform: rotate(45deg) translate(8px, 8px);
            }

            .navbar-toggle.active span:nth-child(2) {
                opacity: 0;
            }

            .navbar-toggle.active span:nth-child(3) {
                transform: rotate(-45deg) translate(7px, -7px);
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

            .post-imagem {
                height: 200px;
            }

            .post-titulo {
                font-size: 1.4rem;
            }

            .post-conteudo {
                padding: 20px;
            }

            .post-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-ler-mais {
                width: 100%;
                justify-content: center;
            }

            .post-meta {
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 16px;
            }

            .blog-hero h1 {
                font-size: 2rem;
            }

            .blog-hero p {
                font-size: 1rem;
            }

            .navbar-modern {
                padding: 10px 18px;
                width: calc(100% - 24px);
            }

            .navbar-logo span {
                font-size: 1rem;
            }

            .post-meta span {
                font-size: 0.75rem;
            }

            .post-stats {
                gap: 10px;
            }

            .categoria-link {
                padding: 8px 12px;
            }

            .recente-link {
                gap: 10px;
            }

            .recente-imagem {
                width: 55px;
                height: 55px;
            }

            .paginacao {
                gap: 5px;
            }

            .page-link {
                padding: 8px 12px;
                min-width: 38px;
            }
        }
    </style>
</head>
<body data-theme="light">

    <canvas id="binary-canvas"></canvas>

    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle" title="Alternar tema">
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
            <a href="../index.php#contato" class="btn btn-primary">Começar</a>
        </div>

        <div class="navbar-toggle" id="navbarToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="main-content">

        <!-- Hero do Blog -->
        <section class="blog-hero">
            <div class="container">
                <h1 class="gradient-text">Blog da NTW</h1>
                <p>Fique por dentro das últimas tendências em tecnologia, programação, marketing digital e negócios. Artigos escritos por quem entende do assunto.</p>

                <?php if($categoria_filtro > 0 && $cat_nome_filtro): ?>
                <div class="filtro-ativo">
                    <span class="filtro-badge">
                        <i class="fas fa-filter"></i>
                        Filtrando por: <?php echo htmlspecialchars($cat_nome_filtro); ?>
                        <a href="blog.php" title="Remover filtro"><i class="fas fa-times"></i></a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Conteúdo do Blog -->
        <section style="padding-top: 0;">
            <div class="container">
                <div class="blog-layout">

                    <!-- Posts -->
                    <div class="posts-grid">
                        <?php if($posts->num_rows == 0): ?>
                        <div class="sem-posts">
                            <i class="fas fa-newspaper"></i>
                            <h3>Nenhum post encontrado</h3>
                            <p>
                                <?php if($categoria_filtro > 0): ?>
                                    Não há posts publicados nesta categoria ainda. <a href="blog.php" style="color: var(--accent-primary);">Ver todos os posts</a>
                                <?php else: ?>
                                    Volte em breve para novos artigos!
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php else: ?>
                            <?php while($post = $posts->fetch_assoc()):
                                $tags = !empty($post['tags']) ? explode(',', $post['tags']) : [];
                            ?>
                            <div class="post-card <?php echo $post['destaque'] ? 'destaque' : ''; ?>">

                                <div class="post-imagem" style="<?php echo $post['imagem_destaque'] ? 'background-image: url(\'' . htmlspecialchars($post['imagem_destaque']) . '\');' : ''; ?>">
                                    <?php if($post['categoria_nome']): ?>
                                    <span class="post-categoria-tag" style="background: <?php echo htmlspecialchars($post['categoria_cor'] ?? '#3b82f6'); ?>">
                                        <?php echo htmlspecialchars($post['categoria_nome']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if(!$post['imagem_destaque']): ?>
                                    <div class="sem-imagem">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="post-conteudo">
                                    <div class="post-meta">
                                        <span>
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($post['autor']); ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?>
                                        </span>
                                        <?php if(!empty($post['tempo_leitura'])): ?>
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            <?php echo (int)$post['tempo_leitura']; ?> min
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <h2 class="post-titulo">
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>">
                                            <?php echo htmlspecialchars($post['titulo']); ?>
                                        </a>
                                    </h2>

                                    <?php if(!empty($post['resumo'])): ?>
                                    <div class="post-resumo">
                                        <?php echo htmlspecialchars($post['resumo']); ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(!empty($tags)): ?>
                                    <div class="post-tags">
                                        <?php foreach(array_slice($tags, 0, 3) as $tag): ?>
                                        <span class="post-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="post-footer">
                                        <div class="post-stats">
                                            <span>
                                                <i class="fas fa-eye"></i>
                                                <?php echo number_format((int)$post['views']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-comment"></i>
                                                <?php echo (int)$post['total_comentarios']; ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-heart"></i>
                                                <?php echo (int)$post['total_curtidas']; ?>
                                            </span>
                                        </div>
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" class="btn-ler-mais">
                                            Ler mais <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>

                            <!-- Paginação -->
                            <?php if($total_paginas > 1): ?>
                            <div class="paginacao">
                                <?php $cat_param = $categoria_filtro > 0 ? '&categoria=' . $categoria_filtro : ''; ?>

                                <a href="?pagina=<?php echo max(1, $pagina - 1) . $cat_param; ?>" class="page-link <?php echo $pagina == 1 ? 'disabled' : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>

                                <?php
                                $inicio = max(1, $pagina - 2);
                                $fim    = min($total_paginas, $pagina + 2);

                                if($inicio > 1) {
                                    echo '<a href="?pagina=1' . $cat_param . '" class="page-link">1</a>';
                                    if($inicio > 2) echo '<span class="page-link disabled">…</span>';
                                }

                                for($i = $inicio; $i <= $fim; $i++):
                                ?>
                                <a href="?pagina=<?php echo $i . $cat_param; ?>" class="page-link <?php echo $i == $pagina ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor;

                                if($fim < $total_paginas) {
                                    if($fim < $total_paginas - 1) echo '<span class="page-link disabled">…</span>';
                                    echo '<a href="?pagina=' . $total_paginas . $cat_param . '" class="page-link">' . $total_paginas . '</a>';
                                }
                                ?>

                                <a href="?pagina=<?php echo min($total_paginas, $pagina + 1) . $cat_param; ?>" class="page-link <?php echo $pagina == $total_paginas ? 'disabled' : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div><!-- /posts-grid -->

                    <!-- Sidebar -->
                    <div class="blog-sidebar">

                        <!-- Widget Categorias -->
                        <div class="sidebar-widget">
                            <h3 class="widget-title">Categorias</h3>
                            <ul class="categorias-list">
                                <?php
                                $categorias->data_seek(0);
                                while($cat = $categorias->fetch_assoc()):
                                ?>
                                <li class="categoria-item">
                                    <a href="blog.php?categoria=<?php echo (int)$cat['id']; ?>" class="categoria-link <?php echo $categoria_filtro == $cat['id'] ? 'active' : ''; ?>">
                                        <span class="categoria-nome">
                                            <i class="fas <?php echo htmlspecialchars($cat['icone'] ?? 'fa-folder'); ?>" style="color: <?php echo htmlspecialchars($cat['cor'] ?? '#3b82f6'); ?>"></i>
                                            <span><?php echo htmlspecialchars($cat['nome']); ?></span>
                                        </span>
                                        <span class="categoria-count"><?php echo (int)$cat['total_posts']; ?></span>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <!-- Widget Posts em Destaque -->
                        <?php if($posts_destaque->num_rows > 0): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">Em Destaque</h3>
                            <ul class="recentes-list">
                                <?php while($destaque = $posts_destaque->fetch_assoc()): ?>
                                <li class="recente-item">
                                    <a href="post.php?slug=<?php echo urlencode($destaque['slug']); ?>" class="recente-link">
                                        <div class="recente-imagem" style="<?php echo $destaque['imagem_destaque'] ? 'background-image: url(\'' . htmlspecialchars($destaque['imagem_destaque']) . '\');' : ''; ?>">
                                            <?php if(!$destaque['imagem_destaque']): ?>
                                            <div class="sem-imagem"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="recente-info">
                                            <h4><?php echo htmlspecialchars($destaque['titulo']); ?></h4>
                                            <span class="recente-data">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('d/m/Y', strtotime($destaque['data_publicacao'])); ?>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Widget Posts Recentes -->
                        <div class="sidebar-widget">
                            <h3 class="widget-title">Posts Recentes</h3>
                            <ul class="recentes-list">
                                <?php while($recente = $posts_recentes->fetch_assoc()): ?>
                                <li class="recente-item">
                                    <a href="post.php?slug=<?php echo urlencode($recente['slug']); ?>" class="recente-link">
                                        <div class="recente-info" style="width: 100%;">
                                            <h4><?php echo htmlspecialchars($recente['titulo']); ?></h4>
                                            <span class="recente-data">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('d/m/Y', strtotime($recente['data_publicacao'])); ?>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <!-- Widget Nuvem de Tags -->
                        <?php if(!empty($tags_populares)): ?>
                        <div class="sidebar-widget">
                            <h3 class="widget-title">Tags Populares</h3>
                            <div class="tags-cloud">
                                <?php foreach($tags_populares as $tag => $count): ?>
                                <a href="blog.php?tag=<?php echo urlencode($tag); ?>" class="tag-cloud-item">
                                    <?php echo htmlspecialchars($tag); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Widget Newsletter -->
                        <div class="sidebar-widget newsletter-widget">
                            <h3 class="widget-title">Newsletter</h3>
                            <p>Receba os melhores conteúdos diretamente no seu e-mail.</p>
                            <form class="newsletter-form" id="newsletterForm" novalidate>
                                <input type="text" class="newsletter-input" placeholder="Seu nome" id="newsletter_nome" required>
                                <input type="email" class="newsletter-input" placeholder="Seu melhor e-mail" id="newsletter_email" required>
                                <button type="submit" class="btn-newsletter">
                                    <i class="fas fa-paper-plane"></i> Inscrever-se
                                </button>
                            </form>
                        </div>

                        <!-- Widget WhatsApp -->
                        <div class="sidebar-widget" style="background: rgba(37, 211, 102, 0.06); border-color: #25D366;">
                            <h3 class="widget-title" style="color: #25D366; border-color: #25D366;">
                                <i class="fab fa-whatsapp"></i> Fale Conosco
                            </h3>
                            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                                Tem dúvidas sobre nossos serviços? Fale diretamente com nossa equipe.
                            </p>
                            <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp['numero']); ?>" target="_blank" rel="noopener noreferrer" class="btn-wa">
                                <i class="fab fa-whatsapp"></i> Iniciar Conversa
                            </a>
                        </div>

                    </div><!-- /blog-sidebar -->

                </div><!-- /blog-layout -->
            </div><!-- /container -->
        </section>

    </div><!-- /main-content -->

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="../assets/image/logo.png" alt="NTW">
                    <h3>New Software</h3>
                    <p>A tecnologia é a nossa paixão. Junte-se a nós e revolucione seu negócio!</p>
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
                    <div class="social-links" style="display:flex; justify-content:center;">
                <a href="https://instagram.com/newsoftwarebr" target="_blank"
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
            <div class="footer-bottom">
                <p>Copyright &copy; <?php echo date('Y'); ?> NTW - New Software. Todos os direitos reservados.</p>
                <p style="margin-top: 10px;">Founded By Renan.</p>
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

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initCanvas, 200);
        });
        initCanvas();
        drawBinary();

        // ===== THEME TOGGLE =====
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon   = document.getElementById('themeIcon');
        const savedTheme  = localStorage.getItem('theme') || 'light';

        document.body.setAttribute('data-theme', savedTheme);
        themeIcon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

        themeToggle.addEventListener('click', () => {
            const current = document.body.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            themeIcon.className = next === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(themeToggle,
                    { scale: 1, rotation: 0 },
                    { scale: 1.2, rotation: 180, duration: 0.3, yoyo: true, repeat: 1 }
                );
            }
        });

        // ===== MENU MOBILE =====
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu   = document.getElementById('navbarMenu');

        if (navbarToggle) {
            navbarToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                navbarMenu.classList.toggle('active');
                document.body.style.overflow = navbarMenu.classList.contains('active') ? 'hidden' : '';
            });
        }

        document.querySelectorAll('.navbar-menu a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    navbarToggle.classList.remove('active');
                    navbarMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                if (navbarMenu)   navbarMenu.classList.remove('active');
                if (navbarToggle) navbarToggle.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // ===== NEWSLETTER =====
        const newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const nome  = document.getElementById('newsletter_nome').value.trim();
                const email = document.getElementById('newsletter_email').value.trim();

                if (!nome || !email) {
                    alert('Por favor, preencha todos os campos.');
                    return;
                }

                const btn = this.querySelector('.btn-newsletter');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aguarde...';

                fetch('newsletter_inscrever.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome, email })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.sucesso) {
                        alert('Inscrição realizada com sucesso! Verifique seu e-mail para confirmar.');
                        newsletterForm.reset();
                    } else {
                        alert('Erro ao se inscrever: ' + (data.erro || 'Tente novamente.'));
                    }
                })
                .catch(() => {
                    alert('Erro ao processar sua inscrição. Tente novamente.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Inscrever-se';
                });
            });
        }
    </script>
</body>
</html>