<?php
// Configurações específicas do módulo de planos
define('PLANOS_PATH', dirname(__FILE__));
define('PLANOS_URL', '/newsoftware/admin/modules/planos/');

// Funções auxiliares (usam a variável $conn do config principal)
function getCategorias() {
    global $conn;
    $sql = "SELECT * FROM planos_categorias WHERE ativo = 1 ORDER BY ordem, nome";
    $result = $conn->query($sql);
    $categorias = [];
    while($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    return $categorias;
}

function getPerfis() {
    return [
        'mei' => 'Microempreendedor',
        'empresa' => 'Profissional/Empresa',
        'ambos' => 'Ambos'
    ];
}

function getPeriodos() {
    return [
        'mensal' => 'Mensal',
        'anual' => 'Anual',
        'permanente' => 'Permanente',
        'unico' => 'Único'
    ];
}

// A função formatarPreco já existe no config.php principal
?>