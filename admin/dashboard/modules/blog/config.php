<?php
// Configurações do módulo Blog

function getCategoriasBlog($apenas_ativas = true) {
    global $conn;
    $sql = "SELECT * FROM blog_categorias";
    if($apenas_ativas) {
        $sql .= " WHERE ativo = 1";
    }
    $sql .= " ORDER BY ordem, nome";
    $result = $conn->query($sql);
    $categorias = [];
    while($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    return $categorias;
}

function getStatusBlog() {
    return [
        'rascunho' => 'Rascunho',
        'publicado' => 'Publicado',
        'arquivado' => 'Arquivado'
    ];
}

function formatarTempoLeitura($minutos) {
    if($minutos < 60) {
        return $minutos . ' min';
    } else {
        $horas = floor($minutos / 60);
        $min_rest = $minutos % 60;
        return $horas . 'h' . ($min_rest > 0 ? ' ' . $min_rest . 'min' : '');
    }
}

function gerarSlug($texto) {
    $texto = preg_replace('/[^a-zA-Z0-9áéíóúâêîôûãõçÁÉÍÓÚÂÊÎÔÛÃÕÇ\s-]/', '', $texto);
    $texto = strtolower($texto);
    $texto = preg_replace('/[áàâã]/', 'a', $texto);
    $texto = preg_replace('/[éèê]/', 'e', $texto);
    $texto = preg_replace('/[íìî]/', 'i', $texto);
    $texto = preg_replace('/[óòôõ]/', 'o', $texto);
    $texto = preg_replace('/[úùû]/', 'u', $texto);
    $texto = preg_replace('/ç/', 'c', $texto);
    $texto = preg_replace('/[^a-z0-9]/', '-', $texto);
    $texto = preg_replace('/-+/', '-', $texto);
    return trim($texto, '-');
}
?>