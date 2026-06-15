<?php
require_once '../../../vendor/autoload.php'; // Se usar Composer
// OU require_once '../../../PHPMailer/PHPMailer.php'; // Se manual

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../../includes/header.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$campanha_id = $_GET['id'];

// Busca campanha
$stmt = $conn->prepare("SELECT * FROM newsletter_campanhas WHERE id = ?");
$stmt->bind_param("i", $campanha_id);
$stmt->execute();
$campanha = $stmt->get_result()->fetch_assoc();

if(!$campanha) {
    header('Location: index.php');
    exit;
}

// Busca configuração
$config = getNewsletterConfig();

// Busca inscritos ativos e confirmados
$inscritos = $conn->query("
    SELECT id, nome, email, token 
    FROM newsletter_inscritos 
    WHERE status = 'ativo' AND confirmado = 1
");

$total_inscritos = $inscritos->num_rows;

// Processa envio em lote
$enviados = 0;
$falhas = 0;
$limite_por_vez = 20; // Envia 20 por vez pra não sobrecarregar

if(isset($_POST['iniciar_envio'])) {
    // Atualiza status da campanha
    $conn->query("UPDATE newsletter_campanhas SET status = 'enviando' WHERE id = $campanha_id");
    
    $offset = (int)$_POST['offset'];
    $lote = 0;
    
    // Busca próximo lote
    $stmt_lote = $conn->prepare("
        SELECT id, nome, email, token 
        FROM newsletter_inscritos 
        WHERE status = 'ativo' AND confirmado = 1 
        LIMIT ? OFFSET ?
    ");
    $stmt_lote->bind_param("ii", $limite_por_vez, $offset);
    $stmt_lote->execute();
    $lote_inscritos = $stmt_lote->get_result();
    
    while($inscrito = $lote_inscritos->fetch_assoc()) {
        $token_envio = gerarTokenUnico();
        
        // Personaliza conteúdo
        $conteudo = $campanha['conteudo'];
        $conteudo = str_replace('{nome}', $inscrito['nome'], $conteudo);
        $conteudo = str_replace('{email}', $inscrito['email'], $conteudo);
        $conteudo = str_replace('{desinscrever_link}', SITE_URL . "/newsletter/desinscrever.php?token={$inscrito['token']}", $conteudo);
        $conteudo = str_replace('{site_url}', SITE_URL, $conteudo);
        $conteudo = str_replace('{blog_url}', SITE_URL . '/blog.php', $conteudo);
        $conteudo = str_replace('{ano}', date('Y'), $conteudo);
        
        // Monta HTML completo
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$campanha['assunto']}</title>
        </head>
        <body style='margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width:600px; margin:20px auto; background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background:linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); padding:30px; text-align:center;'>
                    <img src='{$config['logo_url']}' style='max-width:150px; margin-bottom:10px;'>
                    <h1 style='color:white; margin:0; font-size:24px;'>Gestor Arcon Admin</h1>
                </div>
                
                <!-- Content -->
                <div style='padding:30px;'>
                    {$conteudo}
                </div>
                
                <!-- Footer -->
                <div style='background:#f8f8f8; padding:20px; text-align:center; font-size:12px; color:#666; border-top:1px solid #eee;'>
                    <p style='margin:0 0 10px;'>
                        <a href='{$config['instagram_url']}' style='color:#0d47a1; text-decoration:none; margin:0 10px;'>Instagram</a> |
                        <a href='https://wa.me/{$config['whatsapp_numero']}' style='color:#0d47a1; text-decoration:none; margin:0 10px;'>WhatsApp</a>
                    </p>
                    <p style='margin:0;'>&copy; " . date('Y') . " Gestor Arcon Admin. Todos os direitos reservados.</p>
                    <p style='margin:5px 0 0;'>
                        <a href='" . SITE_URL . "/newsletter/desinscrever.php?token={$inscrito['token']}' style='color:#999; text-decoration:underline;'>Cancelar inscrição</a>
                    </p>
                </div>
            </div>
            <!-- Pixel de rastreamento -->
            <img src='" . SITE_URL . "/newsletter/rastrear.php?campanha={$campanha_id}&token={$token_envio}' width='1' height='1' style='display:none;'>
        </body>
        </html>
        ";
        
        // Configura PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_user'];
            $mail->Password   = $config['smtp_pass'];
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->Port       = $config['smtp_port'];
            
            // Remetente e destinatário
            $mail->setFrom($config['remetente_email'], $config['remetente_nome']);
            $mail->addAddress($inscrito['email'], $inscrito['nome']);
            $mail->addReplyTo($config['remetente_email'], $config['remetente_nome']);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $campanha['assunto'];
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($conteudo);
            
            $mail->send();
            
            // Registra envio com sucesso
            $stmt_envio = $conn->prepare("
                INSERT INTO newsletter_envios (campanha_id, inscrito_id, email, token_unico, status, data_envio) 
                VALUES (?, ?, ?, ?, 'enviado', NOW())
            ");
            $stmt_envio->bind_param("iiss", $campanha_id, $inscrito['id'], $inscrito['email'], $token_envio);
            $stmt_envio->execute();
            
            $enviados++;
            
            // Atualiza contador da campanha
            $conn->query("UPDATE newsletter_campanhas SET total_envios = total_envios + 1 WHERE id = $campanha_id");
            
        } catch (Exception $e) {
            // Registra falha
            $erro = $mail->ErrorInfo;
            $stmt_envio = $conn->prepare("
                INSERT INTO newsletter_envios (campanha_id, inscrito_id, email, status, erro) 
                VALUES (?, ?, ?, 'falhou', ?)
            ");
            $stmt_envio->bind_param("iiss", $campanha_id, $inscrito['id'], $inscrito['email'], $erro);
            $stmt_envio->execute();
            
            $falhas++;
        }
        
        $lote++;
        
        // Pequena pausa pra não sobrecarregar
        usleep(500000); // 0.5 segundos
    }
    
    $novo_offset = $offset + $lote;
    
    if($novo_offset >= $total_inscritos) {
        // Finalizou
        $conn->query("UPDATE newsletter_campanhas SET status = 'enviada', data_envio = NOW() WHERE id = $campanha_id");
        $finalizado = true;
    }
}
?>

<style>
.progress-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px;
    max-width: 600px;
    margin: 50px auto;
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: var(--border);
    border-radius: 10px;
    overflow: hidden;
    margin: 30px 0;
}

.progress-fill {
    height: 100%;
    background: var(--accent);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.stats {
    display: flex;
    justify-content: space-around;
    margin: 30px 0;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.btn {
    padding: 14px 30px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 1rem;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-paper-plane"></i>
            Enviar Campanha: <?php echo $campanha['titulo']; ?>
        </h1>
    </div>

    <div class="content-area">
        <div class="progress-container">
            <h2 style="margin-bottom: 20px;">Enviando Newsletter</h2>
            
            <?php if(isset($finalizado)): ?>
                <i class="fas fa-check-circle" style="font-size: 5rem; color: #22c55e; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 20px;">Campanha Enviada com Sucesso!</h3>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $enviados; ?></div>
                        <div class="stat-label">Enviados</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $falhas; ?></div>
                        <div class="stat-label">Falhas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_inscritos; ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
                <a href="relatorio_campanha.php?id=<?php echo $campanha_id; ?>" class="btn btn-primary">
                    Ver Relatório
                </a>
                
            <?php elseif(isset($_POST['iniciar_envio'])): ?>
                <?php 
                $percentual = round((($offset + $lote) / $total_inscritos) * 100);
                ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $percentual; ?>%;"></div>
                </div>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $offset + $lote; ?></div>
                        <div class="stat-label">Enviados</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_inscritos; ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $percentual; ?>%</div>
                        <div class="stat-label">Concluído</div>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="iniciar_envio" value="1">
                    <input type="hidden" name="offset" value="<?php echo $offset + $lote; ?>">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Continuar Envio
                    </button>
                </form>
                
            <?php else: ?>
                <i class="fas fa-envelope-open-text" style="font-size: 5rem; color: var(--accent); margin-bottom: 20px;"></i>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_inscritos; ?></div>
                        <div class="stat-label">Destinatários</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $config['limite_por_minuto']; ?></div>
                        <div class="stat-label">Limite/min</div>
                    </div>
                </div>
                
                <p style="margin: 20px 0; color: var(--text-secondary);">
                    Você está prestes a enviar esta campanha para <strong><?php echo $total_inscritos; ?></strong> inscritos.
                    O envio será feito em lotes para não sobrecarregar o servidor.
                </p>
                
                <form method="POST">
                    <input type="hidden" name="iniciar_envio" value="1">
                    <input type="hidden" name="offset" value="0">
                    <button type="submit" class="btn btn-primary" style="width: 100%;" onclick="return confirm('Iniciar envio da campanha?')">
                        <i class="fas fa-play"></i> Iniciar Envio
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-submit para continuar envio automaticamente (opcional)
<?php if(isset($_POST['iniciar_envio']) && !isset($finalizado)): ?>
setTimeout(() => {
    document.querySelector('form').submit();
}, 3000);
<?php endif; ?>
</script>

<?php require_once '../../includes/footer.php'; ?>