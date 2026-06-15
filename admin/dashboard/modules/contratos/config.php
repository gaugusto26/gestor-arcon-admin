<?php
function gerarNumeroContrato() {
    $ano = date('Y');
    $mes = date('m');
    $sequencia = mt_rand(1000, 9999);
    return "NTW-{$ano}{$mes}-{$sequencia}";
}

function formatarValorExtenso($valor) {
    // Função simplificada - em produção usar biblioteca específica
    return number_format($valor, 2, ',', '.') . ' reais';
}

function getClausulasPorTipo($tipo) {
    global $conn;
    $sql = "SELECT * FROM contrato_clausulas WHERE (tipo = ? OR tipo = 'todos') AND ativa = 1 ORDER BY ordem";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    return $stmt->get_result();
}

function calcularMultaCancelamento($valor_total, $percentual, $meses_fidelidade, $meses_cumpridos) {
    if($meses_cumpridos >= $meses_fidelidade) {
        return 0;
    }
    $multa = ($valor_total * $percentual) / 100;
    $multa_proporcional = $multa * (($meses_fidelidade - $meses_cumpridos) / $meses_fidelidade);
    return round($multa_proporcional, 2);
}

function registrarHistoricoContrato($contrato_id, $acao, $dados_anteriores, $dados_novos) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $admin_id = $_SESSION['admin_id'] ?? null;
    
    $stmt = $conn->prepare("
        INSERT INTO contrato_historico (contrato_id, acao, dados_anteriores, dados_novos, ip, user_agent, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssssi", $contrato_id, $acao, $dados_anteriores, $dados_novos, $ip, $user_agent, $admin_id);
    $stmt->execute();
}
?>