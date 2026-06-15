<?php
require_once '../../includes/header.php';

function getNewsletterStats() {
    global $conn;
    $stats = [];
    
    // Total de inscritos
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE status = 'ativo'");
    $stats['total_inscritos'] = $result->fetch_assoc()['total'];
    
    // Confirmados vs não confirmados
    $result = $conn->query("SELECT confirmado, COUNT(*) as total FROM newsletter_inscritos GROUP BY confirmado");
    while($row = $result->fetch_assoc()) {
        $stats[$row['confirmado'] ? 'confirmados' : 'nao_confirmados'] = $row['total'];
    }
    
    // Inscrições hoje
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE DATE(created_at) = CURDATE()");
    $stats['hoje'] = $result->fetch_assoc()['total'];
    
    // Inscrições esta semana
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_inscritos WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
    $stats['semana'] = $result->fetch_assoc()['total'];
    
    // Total de campanhas
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_campanhas");
    $stats['total_campanhas'] = $result->fetch_assoc()['total'];
    
    // Campanhas este mês
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_campanhas WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    $stats['campanhas_mes'] = $result->fetch_assoc()['total'];
    
    // Total de envios
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_envios");
    $stats['total_envios'] = $result->fetch_assoc()['total'];
    
    // Taxa de abertura
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter_envios WHERE status = 'aberto'");
    $abertos = $result->fetch_assoc()['total'];
    $stats['taxa_abertura'] = $stats['total_envios'] > 0 ? round(($abertos / $stats['total_envios']) * 100, 2) : 0;
    
    return $stats;
}

function getNewsletterConfig() {
    global $conn;
    $result = $conn->query("SELECT * FROM newsletter_config WHERE id = 1");
    return $result->fetch_assoc();
}

function salvarConfigSMTP($dados) {
    global $conn;
    
    $remetente_nome    = $conn->real_escape_string($dados['remetente_nome']);
    $remetente_email   = $conn->real_escape_string($dados['remetente_email']);
    $limite_por_minuto = (int)$dados['limite_por_minuto'];
    $limite_por_hora   = (int)$dados['limite_por_hora'];
    $smtp_host         = $conn->real_escape_string($dados['smtp_host']);
    $smtp_port         = (int)$dados['smtp_port'];
    $smtp_user         = $conn->real_escape_string($dados['smtp_user']);
    $smtp_pass         = $conn->real_escape_string($dados['smtp_pass']);
    $smtp_secure       = $conn->real_escape_string($dados['smtp_secure']);
    $logo_url          = $conn->real_escape_string($dados['logo_url']);
    $assinatura_html   = $conn->real_escape_string($dados['assinatura_html']);
    $rodape_html       = $conn->real_escape_string($dados['rodape_html']);
    $facebook_url      = $conn->real_escape_string($dados['facebook_url']);
    $instagram_url     = $conn->real_escape_string($dados['instagram_url']);
    $whatsapp_numero   = $conn->real_escape_string($dados['whatsapp_numero']);
    
    $sql = "UPDATE newsletter_config SET 
        remetente_nome    = '$remetente_nome',
        remetente_email   = '$remetente_email',
        limite_por_minuto = $limite_por_minuto,
        limite_por_hora   = $limite_por_hora,
        smtp_host         = '$smtp_host',
        smtp_port         = $smtp_port,
        smtp_user         = '$smtp_user',
        smtp_pass         = '$smtp_pass',
        smtp_secure       = '$smtp_secure',
        logo_url          = '$logo_url',
        assinatura_html   = '$assinatura_html',
        rodape_html       = '$rodape_html',
        facebook_url      = '$facebook_url',
        instagram_url     = '$instagram_url',
        whatsapp_numero   = '$whatsapp_numero'
    WHERE id = 1";
    
    return $conn->query($sql);
}

function gerarTokenUnico() {
    return bin2hex(random_bytes(32));
}
?>