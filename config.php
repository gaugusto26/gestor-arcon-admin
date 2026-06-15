<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "newsoftware";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}


define('SITE_URL', 'http://localhost/newsoftware'); // ADICIONE ESTA LINHA
define('SITE_NAME', 'NTW - New Software'); // Opcional

// Definir charset
$conn->set_charset("utf8mb4");

// ===== FUNÇÕES GLOBAIS =====

// Formatar preço
function formatarPreco($preco) {
    return 'R$ ' . number_format($preco, 2, ',', '.');
}

// Formatar data
function formatarData($data, $formato = 'd/m/Y H:i') {
    return date($formato, strtotime($data));
}

// Limpar input
function limparInput($dado) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($dado));
}

// Verificar se usuário está logado
function isLogado() {
    return isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true;
}

// Redirecionar se não estiver logado
function precisaLogin() {
    if (!isLogado()) {
        header('Location: ../admin_login.php');
        exit();
    }
}

// Buscar configurações do WhatsApp
function getWhatsAppConfig($conn) {
    $result = $conn->query("SELECT * FROM config_whatsapp WHERE ativo = 1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return ['numero' => '5519987111656', 'mensagem_padrao' => 'Olá! Tenho interesse no plano: {plano_nome}'];
}

// Registrar log de admin
function registrarLog($conn, $admin_id, $acao) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, acao, ip, user_agent, data_hora) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $admin_id, $acao, $ip, $user_agent);
    $stmt->execute();
}

// Upload de imagem
function uploadImagem($arquivo, $pasta_destino, $nome_arquivo = null) {
    $target_dir = "../uploads/" . $pasta_destino . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $extensao = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
    $extensoes_permitidas = array("jpg", "jpeg", "png", "webp");
    
    if (!in_array($extensao, $extensoes_permitidas)) {
        return false;
    }
    
    if ($nome_arquivo) {
        $nome_final = $nome_arquivo . "." . $extensao;
    } else {
        $nome_final = uniqid() . "." . $extensao;
    }
    
    $target_file = $target_dir . $nome_final;
    
    if (move_uploaded_file($arquivo["tmp_name"], $target_file)) {
        return "uploads/" . $pasta_destino . "/" . $nome_final;
    }
    
    return false;
}
?>