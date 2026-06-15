<?php
require_once '../../../../config.php';
precisaLogin(); // Verifica se tá logado

// Pega o tema salvo
$tema = $_COOKIE['admin_theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> | Arcon Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --sidebar-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-light: #dbeafe;
            --border: #e2e8f0;
            --hover: #f1f5f9;
            --card-bg: #ffffff;
            --shadow: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --sidebar-bg: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-light: #1e293b;
            --border: #334155;
            --hover: #2d3a4f;
            --card-bg: #1e293b;
            --shadow: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.3);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease;
            overflow-x: hidden;
        }

        /* Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .toggle-sidebar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--hover);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .toggle-sidebar:hover {
            background: var(--accent);
            color: white;
        }

        .sidebar.collapsed .toggle-sidebar {
            transform: rotate(180deg);
        }

        /* Profile */
        .profile-section {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .profile-details {
            flex: 1;
        }

        .profile-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .profile-role {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .sidebar.collapsed .profile-details {
            display: none;
        }

        /* Menu */
        .sidebar-menu {
            padding: 20px 16px;
        }

        .menu-section {
            margin-bottom: 24px;
        }

        .menu-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            padding: 0 12px;
            margin-bottom: 12px;
        }

        .sidebar.collapsed .menu-title {
            text-align: center;
            font-size: 0.6rem;
            padding: 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 4px;
            position: relative;
            white-space: nowrap;
        }

        .menu-item:hover {
            background: var(--hover);
            color: var(--accent);
        }

        .menu-item.active {
            background: var(--accent-light);
            color: var(--accent);
        }

        .menu-item i {
            width: 20px;
            font-size: 1.1rem;
        }

        .menu-item span {
            flex: 1;
        }

        .menu-badge {
            background: var(--accent);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 12px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .menu-item span {
            display: none;
        }

        .sidebar.collapsed .menu-badge {
            position: absolute;
            right: 8px;
            top: 8px;
            font-size: 0.6rem;
            padding: 2px 4px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Top Bar */
        .top-bar {
            height: 80px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .theme-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--hover);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: var(--accent);
            color: white;
        }

        .notification-badge {
            position: relative;
            cursor: pointer;
        }

        .notification-badge i {
            font-size: 1.3rem;
            color: var(--text-secondary);
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-visible {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .top-bar {
                padding: 0 20px;
            }
            
            .content-area {
                padding: 20px;
            }
        }
    </style>
</head>
<body data-theme="<?php echo $tema; ?>">
    <div class="dashboard">