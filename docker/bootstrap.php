<?php

$host = getenv('MYSQL_HOST') ?: 'gestor-arcon-db';
$db = getenv('MYSQL_DATABASE') ?: 'newsoftware';
$user = getenv('MYSQL_USER') ?: 'arcon_admin';
$pass = getenv('MYSQL_PASSWORD') ?: '';
$rootUser = getenv('MYSQL_ROOT_USER') ?: 'root';
$rootPass = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$msgDb = getenv('MSG_DB_NAME') ?: 'msg';
$adminUser = getenv('ADMIN_USER') ?: 'admin';
$adminPass = getenv('ADMIN_PASSWORD') ?: '';
$adminName = getenv('ADMIN_NAME') ?: 'Administrador Arcon';
$adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@arcon.local';

if (!$pass || !$rootPass || !$adminPass) {
    fwrite(STDERR, "MYSQL_PASSWORD, MYSQL_ROOT_PASSWORD e ADMIN_PASSWORD sao obrigatorios.\n");
    exit(1);
}

$conn = new mysqli($host, $rootUser, $rootPass);
if ($conn->connect_error) {
    fwrite(STDERR, "Falha ao conectar no MariaDB: {$conn->connect_error}\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->query("GRANT ALL PRIVILEGES ON `$db`.* TO '$user'@'%'");
$conn->select_db($db);

$hasAdminTable = $conn->query("SHOW TABLES LIKE 'admin_users'");
if (!$hasAdminTable || $hasAdminTable->num_rows === 0) {
    $schema = '/var/www/html/db.sql';
    if (!is_file($schema)) {
        fwrite(STDERR, "Schema nao encontrado em {$schema}\n");
        exit(1);
    }
    $cmd = sprintf(
        'mysql --skip-ssl -h%s -u%s -p%s %s < %s',
        escapeshellarg($host),
        escapeshellarg($rootUser),
        escapeshellarg($rootPass),
        escapeshellarg($db),
        escapeshellarg($schema)
    );
    passthru($cmd, $code);
    if ($code !== 0) {
        fwrite(STDERR, "Falha ao importar schema do Gestor Arcon Admin.\n");
        exit($code);
    }
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$msgDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->query("GRANT ALL PRIVILEGES ON `$msgDb`.* TO '$user'@'%'");
$conn->query("CREATE TABLE IF NOT EXISTS `$msgDb`.`mensagens` (
    id int not null auto_increment primary key,
    nome varchar(255),
    email varchar(255),
    telefone varchar(80),
    empresa varchar(255),
    mensagem text,
    created_at timestamp default current_timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$conn->select_db($db);
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $adminUser);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, nome_completo, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $adminUser, $hash, $adminName, $adminEmail);
    $stmt->execute();
    $stmt->close();
    echo "Admin inicial criado: {$adminUser}\n";
}

echo "Bootstrap concluido.\n";
