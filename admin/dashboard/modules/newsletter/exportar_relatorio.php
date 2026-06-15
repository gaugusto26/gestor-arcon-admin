<?php
require_once '../../includes/header.php';

// Verifica login
precisaLogin();

$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '30dias';
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'csv';

switch($periodo) {
    case '7dias':
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
        $label_periodo = 'Últimos 7 dias';
        break;
    case '30dias':
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        $label_periodo = 'Últimos 30 dias';
        break;
    case '90dias':
        $data_inicio = date('Y-m-d', strtotime('-90 days'));
        $label_periodo = 'Últimos 90 dias';
        break;
    case 'ano':
        $data_inicio = date('Y-01-01');
        $label_periodo = 'Este ano';
        break;
    default:
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        $label_periodo = 'Últimos 30 dias';
}

if($formato == 'csv') {
    // Exportar CSV com estatísticas
    $filename = 'relatorio_newsletter_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    // Estatísticas Gerais
    fputcsv($output, ['RELATÓRIO DE NEWSLETTER - ' . strtoupper($label_periodo)], ';');
    fputcsv($output, [], ';');
    
    // Totais
    $stats = [
        'Total de Inscritos' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos")->fetch_assoc()['total'],
        'Novos no Período' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE DATE(created_at) >= '$data_inicio'")->fetch_assoc()['total'],
        'Total Confirmados' => $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE confirmado = 1")->fetch_assoc()['total'],
        'Total de Campanhas' => $conn->query("SELECT COUNT(*) as total FROM newsletter_campanhas")->fetch_assoc()['total'],
        'Campanhas no Período' => $conn->query("SELECT COUNT(*) as total FROM newsletter_campanhas WHERE DATE(created_at) >= '$data_inicio'")->fetch_assoc()['total'],
        'Total de Envios' => $conn->query("SELECT COUNT(*) as total FROM newsletter_envios")->fetch_assoc()['total'],
        'Total de Aberturas' => $conn->query("SELECT COUNT(*) as total FROM newsletter_envios WHERE status = 'aberto'")->fetch_assoc()['total'],
        'Total de Cliques' => $conn->query("SELECT COUNT(*) as total FROM newsletter_envios WHERE status = 'clicou'")->fetch_assoc()['total'],
    ];
    
    foreach($stats as $label => $value) {
        fputcsv($output, [$label, $value], ';');
    }
    
    fputcsv($output, [], ';');
    fputcsv($output, ['CAMPANHAS NO PERÍODO'], ';');
    fputcsv($output, ['Título', 'Assunto', 'Status', 'Data Envio', 'Enviados', 'Abertos', 'Cliques', 'Taxa Abertura'], ';');
    
    // Dados das campanhas
    $campanhas = $conn->query("
        SELECT c.*, 
               COUNT(e.id) as total_envios,
               SUM(CASE WHEN e.status = 'aberto' THEN 1 ELSE 0 END) as total_abertos,
               SUM(CASE WHEN e.status = 'clicou' THEN 1 ELSE 0 END) as total_cliques
        FROM newsletter_campanhas c
        LEFT JOIN newsletter_envios e ON c.id = e.campanha_id
        WHERE DATE(c.created_at) >= '$data_inicio'
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    
    while($camp = $campanhas->fetch_assoc()) {
        $taxa = $camp['total_envios'] > 0 ? round(($camp['total_abertos'] / $camp['total_envios']) * 100, 1) . '%' : '0%';
        fputcsv($output, [
            $camp['titulo'],
            $camp['assunto'],
            $camp['status'],
            $camp['data_envio'] ? date('d/m/Y H:i', strtotime($camp['data_envio'])) : '-',
            $camp['total_envios'],
            $camp['total_abertos'],
            $camp['total_cliques'],
            $taxa
        ], ';');
    }
    
    fclose($output);
    
} elseif($formato == 'pdf') {
    // Para PDF, você precisaria de uma biblioteca como DOMPDF ou TCPDF
    // Por enquanto, redireciona para o relatório normal
    header('Location: relatorios.php?periodo=' . $periodo);
}

exit;
?>