<?php
require_once '../../../../config.php';
require_once '../contratos/config.php';

// Recebe os dados JSON
$dados = json_decode(file_get_contents('php://input'), true);

if(!$dados) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos']);
    exit;
}

// Validações
if(empty($dados['cliente_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Cliente não selecionado']);
    exit;
}

// Calcula valores
$cliente_id           = intval($dados['cliente_id']);
$valor_total          = floatval($dados['valor_plano'] ?? 0);
$valor_mensal         = floatval($dados['valor_mensal'] ?? 0);
$percentual_multa     = floatval($dados['percentual_multa'] ?? 20);
$multa                = ($valor_total * $percentual_multa) / 100;
$fidelidade           = intval($dados['fidelidade'] ?? 12);
$numero_parcelas      = intval($dados['numero_parcelas'] ?? 1);
$dia_vencimento       = intval($dados['dia_vencimento'] ?? 10);
$prazo_primeira_parc  = intval($dados['prazo_primeira_parcela'] ?? 30);
$prazo_desenvolvimento= intval($dados['prazo_desenvolvimento'] ?? 30);
$forma_pagamento      = $dados['forma_pagamento'] ?? 'recorrente';
$plano_id             = !empty($dados['plano_id']) ? intval($dados['plano_id']) : null;
$nome_plano           = $dados['nome_plano'] ?? 'Plano Personalizado';
$tipo_plano           = $dados['tipo_plano'] === 'padrao' ? 'sistema' : 'outros';

// Calcula as datas
$data_inicio = new DateTime($dados['data_inicio'] ?? date('Y-m-d'));

$data_primeira_parcela = clone $data_inicio;
$data_primeira_parcela->modify("+{$prazo_primeira_parc} days");

$data_primeira_mensalidade = clone $data_inicio;
$data_primeira_mensalidade->modify("+{$prazo_desenvolvimento} days");

$data_parc = $data_primeira_parcela->format('Y-m-d');
$data_mens = $data_primeira_mensalidade->format('Y-m-d');
$data_ini  = $data_inicio->format('Y-m-d');

// ── 1. Cria registro em planos_contratados ──────────────────────────────────
$stmt_plano = $conn->prepare("
    INSERT INTO planos_contratados 
        (cliente_id, plano_id, tipo_plano, nome_plano, valor_plano, valor_mensal,
         forma_pagamento, numero_parcelas, data_inicio, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')
");
$stmt_plano->bind_param(
    "iissddsss",
    $cliente_id,
    $plano_id,
    $tipo_plano,
    $nome_plano,
    $valor_total,
    $valor_mensal,
    $forma_pagamento,
    $numero_parcelas,
    $data_ini
);

if(!$stmt_plano->execute()) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao criar plano: ' . $conn->error]);
    exit;
}

$plano_contratado_id = $conn->insert_id;
$stmt_plano->close();

// ── 2. Gera número do contrato único ───────────────────────────────────────
$ano       = date('Y');
$mes       = date('m');
$sequencia = mt_rand(1000, 9999);
$numero_contrato = "NTW-{$ano}{$mes}-{$sequencia}";

// ── 3. Salva o contrato vinculado ao plano_contratado_id ───────────────────
$sql = "INSERT INTO contratos (
    cliente_id,
    plano_contratado_id,
    tipo_contrato,
    numero_contrato,
    versao,
    titulo,
    conteudo,
    valor_total,
    valor_mensal,
    numero_parcelas,
    dia_vencimento,
    data_primeira_parcela,
    data_primeira_mensalidade,
    multa_cancelamento,
    percentual_multa,
    prazo_fidelidade,
    status,
    observacoes,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$versao = '1.0';
$status = 'rascunho';

$stmt->bind_param(
    "iisssssddiissddiss",  // 18 tipos
    $cliente_id,
    $plano_contratado_id,
    $dados['tipo_contrato'],
    $numero_contrato,
    $versao,
    $dados['titulo'],
    $dados['conteudo'],
    $valor_total,
    $valor_mensal,
    $numero_parcelas,
    $dia_vencimento,
    $data_parc,
    $data_mens,
    $multa,
    $percentual_multa,
    $fidelidade,
    $status,
    $dados['observacoes']
);

if($stmt->execute()) {
    $contrato_id = $conn->insert_id;
    $stmt->close();

    echo json_encode([
        'sucesso' => true,
        'id'      => $contrato_id,
        'numero'  => $numero_contrato
    ]);
} else {
    // Se falhou o contrato, desfaz o plano criado
    $conn->query("DELETE FROM planos_contratados WHERE id = {$plano_contratado_id}");
    
    echo json_encode([
        'sucesso' => false,
        'erro'    => 'Erro no banco: ' . $conn->error
    ]);
}
?>