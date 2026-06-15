<?php
/**
 * FUNÇÕES DE PAGAMENTO - Arcon SYSTEM
 * Suporte: Mercado Pago e PagBank
 */
/**
 * GESTOR ARCON ADMIN
 * Criar pagamento PIX baseado no gateway configurado
 */
function criarPix($fatura_id, $conn) {
    $config = getGatewayConfig($conn);
    
    if ($config['gateway'] == 'mercadopago') {
        return criarPixMercadoPago($fatura_id, $conn, $config);
    } elseif ($config['gateway'] == 'pagbank') {
        return criarPixPagBank($fatura_id, $conn, $config);
    } else {
        return criarPixSimulado($fatura_id, $conn);
    }
}

/**
 * Processar pagamento com cartão baseado no gateway
 */
function processarCartao($fatura_id, $dados, $conn) {
    $config = getGatewayConfig($conn);
    
    if ($config['gateway'] == 'mercadopago') {
        return processarCartaoMercadoPago($fatura_id, $dados, $conn, $config);
    } elseif ($config['gateway'] == 'pagbank') {
        return processarCartaoPagBank($fatura_id, $dados, $conn, $config);
    } else {
        return processarCartaoSimulado($fatura_id, $dados, $conn);
    }
}

// =============================================
// MERCADO PAGO
// =============================================

/**
 * Criar PIX no Mercado Pago
 */
function criarPixMercadoPago($fatura_id, $conn, $config) {
    // Buscar dados da fatura
    $fatura = buscarFatura($fatura_id, $conn);
    if (!$fatura) {
        return ['success' => false, 'error' => 'Fatura não encontrada'];
    }
    
    // Verificar se já tem transação pendente
    $transacao_existente = verificarTransacaoPendente($fatura_id, $conn);
    if ($transacao_existente) {
        return $transacao_existente;
    }
    
    require_once 'vendor/autoload.php';
    
    MercadoPagoConfig::setAccessToken($config['access_token']);
    
    // Definir modo de teste/produção
    if ($config['modo'] == 'teste') {
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }
    
    $client = new MercadoPago\Client\Payment\PaymentClient();
    
    try {
        // Criar requisição PIX
        $payment = $client->create([
            "transaction_amount" => (float)$fatura['valor_total'],
            "description" => "Fatura " . $fatura['numero_fatura'],
            "payment_method_id" => "pix",
            "payer" => [
                "email" => $fatura['email'],
                "first_name" => explode(' ', trim($fatura['nome']))[0],
                "last_name" => count(explode(' ', trim($fatura['nome']))) > 1 ? 
                              implode(' ', array_slice(explode(' ', trim($fatura['nome'])), 1)) : '',
                "identification" => [
                    "type" => strlen(preg_replace('/[^0-9]/', '', $fatura['cpf_cnpj'])) <= 11 ? "CPF" : "CNPJ",
                    "number" => preg_replace('/[^0-9]/', '', $fatura['cpf_cnpj'])
                ]
            ],
            "date_of_expiration" => date('c', strtotime('+24 hours'))
        ]);
        
        // Extrair dados do PIX
        $qr_code = $payment->point_of_interaction->transaction_data->qr_code;
        $qr_code_base64 = $payment->point_of_interaction->transaction_data->qr_code_base64;
        $ticket_url = $payment->point_of_interaction->transaction_data->ticket_url;
        
        // Gerar ID único para transação
        $transacao_id = 'MP_' . uniqid() . '_' . time();
        
        // Inserir transação no banco
        $transacao_db_id = inserirTransacao([
            'cliente_id' => $fatura['cliente_id'],
            'fatura_id' => $fatura_id,
            'transacao_id' => $transacao_id,
            'gateway' => 'mercadopago',
            'forma_pagamento' => 'pix',
            'valor' => $fatura['valor_total'],
            'status' => 'aguardando',
            'mp_payment_id' => $payment->id,
            'pix_qrcode' => $qr_code_base64,
            'pix_copiaecola' => $qr_code,
            'link_pagamento' => $ticket_url,
            'expiracao' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ], $conn);
        
        if (!$transacao_db_id) {
            return ['success' => false, 'error' => 'Erro ao registrar transação'];
        }
        
        // Atualizar fatura
        atualizarFaturaComPix($fatura_id, $transacao_db_id, $payment->id, 
                              $qr_code_base64, $qr_code, $conn);
        
        // Registrar log
        registrarLogPagamento($fatura['cliente_id'], $fatura_id, $transacao_id, 
                             'pix_gerado', null, 'aguardando', 'PIX gerado via Mercado Pago', $conn);
        
        return [
            'success' => true,
            'transacao_id' => $transacao_id,
            'pix_qrcode' => $qr_code_base64,
            'pix_copiaecola' => $qr_code,
            'link_pagamento' => $ticket_url,
            'valor' => $fatura['valor_total'],
            'expiracao' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
        
    } catch (MercadoPago\Exceptions\MPApiException $e) {
        $error = $e->getApiResponse()->getContent();
        error_log("Erro Mercado Pago API: " . print_r($error, true));
        
        $mensagem = isset($error['message']) ? $error['message'] : 'Erro na API do Mercado Pago';
        if (isset($error['cause'][0]['description'])) {
            $mensagem = $error['cause'][0]['description'];
        }
        
        return ['success' => false, 'error' => $mensagem];
        
    } catch (Exception $e) {
        error_log("Erro geral Mercado Pago: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro ao gerar PIX: ' . $e->getMessage()];
    }
}

/**
 * Processar cartão no Mercado Pago
 */
function processarCartaoMercadoPago($fatura_id, $dados, $conn, $config) {
    $fatura = buscarFatura($fatura_id, $conn);
    if (!$fatura) {
        return ['success' => false, 'error' => 'Fatura não encontrada'];
    }
    
    require_once 'vendor/autoload.php';
    
    MercadoPagoConfig::setAccessToken($config['access_token']);
    $client = new MercadoPago\Client\Payment\PaymentClient();
    
    try {
        // Processar token do cartão (você precisa gerar no frontend)
        $payment = $client->create([
            "transaction_amount" => (float)$fatura['valor_total'],
            "token" => $dados['token'],
            "description" => "Fatura " . $fatura['numero_fatura'],
            "installments" => (int)$dados['parcelas'],
            "payment_method_id" => $dados['payment_method_id'],
            "issuer_id" => $dados['issuer_id'] ?? null,
            "payer" => [
                "email" => $fatura['email'],
                "identification" => [
                    "type" => "CPF",
                    "number" => preg_replace('/[^0-9]/', '', $fatura['cpf_cnpj'])
                ]
            ]
        ]);
        
        $transacao_id = 'MP_' . uniqid() . '_' . time();
        $status = $payment->status == 'approved' ? 'aprovado' : 
                 ($payment->status == 'pending' ? 'pendente' : 'recusado');
        
        $transacao_db_id = inserirTransacao([
            'cliente_id' => $fatura['cliente_id'],
            'fatura_id' => $fatura_id,
            'transacao_id' => $transacao_id,
            'gateway' => 'mercadopago',
            'forma_pagamento' => 'cartao_credito',
            'valor' => $fatura['valor_total'],
            'parcelas' => $dados['parcelas'],
            'status' => $status,
            'mp_payment_id' => $payment->id,
            'data_pagamento' => $status == 'aprovado' ? date('Y-m-d H:i:s') : null
        ], $conn);
        
        if ($status == 'aprovado') {
            atualizarFaturaPaga($fatura_id, $transacao_db_id, $conn);
            
            registrarLogPagamento($fatura['cliente_id'], $fatura_id, $transacao_id,
                                 'cartao_aprovado', 'pendente', 'aprovado', 
                                 'Cartão aprovado via Mercado Pago', $conn);
        }
        
        return [
            'success' => $status == 'aprovado',
            'status' => $status,
            'transacao_id' => $transacao_id,
            'mensagem' => $payment->status_detail ?? ($status == 'aprovado' ? 'Pagamento aprovado' : 'Pagamento recusado')
        ];
        
    } catch (Exception $e) {
        error_log("Erro cartão Mercado Pago: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro no processamento: ' . $e->getMessage()];
    }
}

// =============================================
// PAGBANK
// =============================================

/**
 * Criar PIX no PagBank
 */
function criarPixPagBank($fatura_id, $conn, $config) {
    $fatura = buscarFatura($fatura_id, $conn);
    if (!$fatura) {
        return ['success' => false, 'error' => 'Fatura não encontrada'];
    }
    
    // Verificar se já tem transação pendente
    $transacao_existente = verificarTransacaoPendente($fatura_id, $conn);
    if ($transacao_existente) {
        return $transacao_existente;
    }
    
    require_once 'vendor/autoload.php';
    
    // Configurar cliente PagBank
    $client = new \GuzzleHttp\Client([
        'base_uri' => $config['modo'] == 'teste' ? 
                     'https://sandbox.api.pagseguro.com/' : 
                     'https://api.pagseguro.com/',
        'headers' => [
            'Authorization' => 'Bearer ' . $config['access_token'],
            'Content-Type' => 'application/json'
        ]
    ]);
    
    try {
        // Preparar dados do PIX
        $payload = [
            'reference_id' => 'fatura_' . $fatura_id,
            'description' => 'Fatura ' . $fatura['numero_fatura'],
            'amount' => [
                'value' => (int)($fatura['valor_total'] * 100), // PagBank usa centavos
                'currency' => 'BRL'
            ],
            'payment_method' => [
                'type' => 'PIX',
                'pix' => [
                    'expiration_date' => date('c', strtotime('+24 hours'))
                ]
            ],
            'payer' => [
                'name' => $fatura['nome'],
                'email' => $fatura['email'],
                'tax_id' => preg_replace('/[^0-9]/', '', $fatura['cpf_cnpj'])
            ]
        ];
        
        $response = $client->post('orders', ['json' => $payload]);
        $result = json_decode($response->getBody(), true);
        
        // Extrair dados do PIX
        $pix_data = $result['charges'][0]['payment_method']['pix'];
        $qr_code = $pix_data['qr_codes'][0]['text'];
        $qr_code_base64 = $pix_data['qr_codes'][0]['image'];
        $payment_id = $result['charges'][0]['id'];
        
        $transacao_id = 'PB_' . uniqid() . '_' . time();
        
        $transacao_db_id = inserirTransacao([
            'cliente_id' => $fatura['cliente_id'],
            'fatura_id' => $fatura_id,
            'transacao_id' => $transacao_id,
            'gateway' => 'pagbank',
            'forma_pagamento' => 'pix',
            'valor' => $fatura['valor_total'],
            'status' => 'aguardando',
            'mp_payment_id' => $payment_id,
            'pix_qrcode' => $qr_code_base64,
            'pix_copiaecola' => $qr_code,
            'expiracao' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ], $conn);
        
        if (!$transacao_db_id) {
            return ['success' => false, 'error' => 'Erro ao registrar transação'];
        }
        
        atualizarFaturaComPix($fatura_id, $transacao_db_id, $payment_id, 
                              $qr_code_base64, $qr_code, $conn);
        
        registrarLogPagamento($fatura['cliente_id'], $fatura_id, $transacao_id,
                             'pix_gerado', null, 'aguardando', 'PIX gerado via PagBank', $conn);
        
        return [
            'success' => true,
            'transacao_id' => $transacao_id,
            'pix_qrcode' => $qr_code_base64,
            'pix_copiaecola' => $qr_code,
            'valor' => $fatura['valor_total'],
            'expiracao' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
        
    } catch (Exception $e) {
        error_log("Erro PagBank: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro ao gerar PIX: ' . $e->getMessage()];
    }
}

/**
 * Processar cartão no PagBank
 */
function processarCartaoPagBank($fatura_id, $dados, $conn, $config) {
    $fatura = buscarFatura($fatura_id, $conn);
    if (!$fatura) {
        return ['success' => false, 'error' => 'Fatura não encontrada'];
    }
    
    require_once 'vendor/autoload.php';
    
    $client = new \GuzzleHttp\Client([
        'base_uri' => $config['modo'] == 'teste' ? 
                     'https://sandbox.api.pagseguro.com/' : 
                     'https://api.pagseguro.com/',
        'headers' => [
            'Authorization' => 'Bearer ' . $config['access_token'],
            'Content-Type' => 'application/json'
        ]
    ]);
    
    try {
        $payload = [
            'reference_id' => 'fatura_' . $fatura_id,
            'description' => 'Fatura ' . $fatura['numero_fatura'],
            'amount' => [
                'value' => (int)($fatura['valor_total'] * 100),
                'currency' => 'BRL'
            ],
            'payment_method' => [
                'type' => 'CREDIT_CARD',
                'installments' => (int)$dados['parcelas'],
                'card' => [
                    'encrypted' => $dados['encrypted_card'], // Token gerado no frontend
                    'holder' => [
                        'name' => $dados['card_name']
                    ]
                ]
            ],
            'payer' => [
                'name' => $fatura['nome'],
                'email' => $fatura['email'],
                'tax_id' => preg_replace('/[^0-9]/', '', $fatura['cpf_cnpj'])
            ]
        ];
        
        $response = $client->post('orders', ['json' => $payload]);
        $result = json_decode($response->getBody(), true);
        
        $status = $result['charges'][0]['status'] == 'PAID' ? 'aprovado' : 
                 ($result['charges'][0]['status'] == 'WAITING' ? 'pendente' : 'recusado');
        
        $transacao_id = 'PB_' . uniqid() . '_' . time();
        
        $transacao_db_id = inserirTransacao([
            'cliente_id' => $fatura['cliente_id'],
            'fatura_id' => $fatura_id,
            'transacao_id' => $transacao_id,
            'gateway' => 'pagbank',
            'forma_pagamento' => 'cartao_credito',
            'valor' => $fatura['valor_total'],
            'parcelas' => $dados['parcelas'],
            'status' => $status,
            'mp_payment_id' => $result['charges'][0]['id'],
            'data_pagamento' => $status == 'aprovado' ? date('Y-m-d H:i:s') : null
        ], $conn);
        
        if ($status == 'aprovado') {
            atualizarFaturaPaga($fatura_id, $transacao_db_id, $conn);
        }
        
        return [
            'success' => $status == 'aprovado',
            'status' => $status,
            'transacao_id' => $transacao_id,
            'mensagem' => $status == 'aprovado' ? 'Pagamento aprovado' : 'Pagamento recusado'
        ];
        
    } catch (Exception $e) {
        error_log("Erro cartão PagBank: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro no processamento: ' . $e->getMessage()];
    }
}

// =============================================
// FUNÇÕES SIMULADAS (PARA TESTE)
// =============================================

function criarPixSimulado($fatura_id, $conn) {
    $fatura = buscarFatura($fatura_id, $conn);
    if (!$fatura) {
        return ['success' => false, 'error' => 'Fatura não encontrada'];
    }
    
    $transacao_id = 'SIM_' . uniqid() . '_' . time();
    $qr_code = '00020126580014br.gov.bcb.pix0136' . $fatura['email'] . '5204000053039865401' . 
               str_pad((string)($fatura['valor_total'] * 100), 10, '0', STR_PAD_LEFT) . 
               '5802BR5913' . preg_replace('/[^a-zA-Z0-9]/', '', substr($fatura['nome'], 0, 25)) . 
               '6009SAO PAULO62070503***6304E4E5';
    
    $qr_code_base64 = 'data:image/png;base64,' . base64_encode('QR Code Simulado para ' . $fatura['numero_fatura']);
    
    $transacao_db_id = inserirTransacao([
        'cliente_id' => $fatura['cliente_id'],
        'fatura_id' => $fatura_id,
        'transacao_id' => $transacao_id,
        'gateway' => 'nenhum',
        'forma_pagamento' => 'pix',
        'valor' => $fatura['valor_total'],
        'status' => 'pendente',
        'pix_qrcode' => $qr_code_base64,
        'pix_copiaecola' => $qr_code,
        'expiracao' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
    ], $conn);
    
    atualizarFaturaComPix($fatura_id, $transacao_db_id, null, $qr_code_base64, $qr_code, $conn);
    
    return [
        'success' => true,
        'transacao_id' => $transacao_id,
        'pix_qrcode' => $qr_code_base64,
        'pix_copiaecola' => $qr_code,
        'valor' => $fatura['valor_total'],
        'expiracao' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
    ];
}

function processarCartaoSimulado($fatura_id, $dados, $conn) {
    $fatura = buscarFatura($fatura_id, $conn);
    if (!$fatura) {
        return ['success' => false, 'error' => 'Fatura não encontrada'];
    }
    
    // Simular aprovação (sempre aprova para teste)
    $transacao_id = 'SIM_' . uniqid() . '_' . time();
    
    $transacao_db_id = inserirTransacao([
        'cliente_id' => $fatura['cliente_id'],
        'fatura_id' => $fatura_id,
        'transacao_id' => $transacao_id,
        'gateway' => 'nenhum',
        'forma_pagamento' => 'cartao_credito',
        'valor' => $fatura['valor_total'],
        'parcelas' => $dados['parcelas'],
        'status' => 'aprovado',
        'data_pagamento' => date('Y-m-d H:i:s')
    ], $conn);
    
    atualizarFaturaPaga($fatura_id, $transacao_db_id, $conn);
    
    return [
        'success' => true,
        'status' => 'aprovado',
        'transacao_id' => $transacao_id,
        'mensagem' => 'Pagamento aprovado (modo simulação)'
    ];
}

// =============================================
// FUNÇÕES AUXILIARES
// =============================================

function getGatewayConfig($conn) {
    $sql = "SELECT * FROM pagamento_config WHERE id = 1";
    $result = $conn->query($sql);
    $config = $result->fetch_assoc();
    
    if (!$config) {
        return [
            'gateway' => 'nenhum',
            'modo' => 'teste',
            'access_token' => '',
            'public_key' => ''
        ];
    }
    
    return $config;
}

function buscarFatura($fatura_id, $conn) {
    $sql = "SELECT f.*, c.nome, c.email, c.cpf_cnpj 
            FROM pagamento_faturas f
            JOIN clientes c ON c.id = f.cliente_id
            WHERE f.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fatura_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function verificarTransacaoPendente($fatura_id, $conn) {
    $sql = "SELECT t.transacao_id, t.pix_qrcode, t.pix_copiaecola, t.pix_expiracao,
                   t.valor
            FROM pagamento_transacoes t
            WHERE t.fatura_id = ? AND t.status IN ('pendente', 'aguardando')
            AND t.pix_expiracao > NOW()
            ORDER BY t.created_at DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fatura_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return [
            'success' => true,
            'transacao_id' => $row['transacao_id'],
            'pix_qrcode' => $row['pix_qrcode'],
            'pix_copiaecola' => $row['pix_copiaecola'],
            'valor' => $row['valor'],
            'expiracao' => $row['pix_expiracao'],
            'existente' => true
        ];
    }
    
    return null;
}

function inserirTransacao($dados, $conn) {
    $sql = "INSERT INTO pagamento_transacoes 
            (cliente_id, fatura_id, transacao_id, gateway, forma_pagamento, 
             valor, parcelas, status, mp_payment_id, pix_qrcode, pix_copiaecola,
             pix_expiracao, link_pagamento, data_pagamento, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssdisssssss", 
        $dados['cliente_id'],
        $dados['fatura_id'],
        $dados['transacao_id'],
        $dados['gateway'],
        $dados['forma_pagamento'],
        $dados['valor'],
        $dados['parcelas'] ?? 1,
        $dados['status'],
        $dados['mp_payment_id'] ?? null,
        $dados['pix_qrcode'] ?? null,
        $dados['pix_copiaecola'] ?? null,
        $dados['expiracao'] ?? null,
        $dados['link_pagamento'] ?? null,
        $dados['data_pagamento'] ?? null
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    error_log("Erro ao inserir transação: " . $stmt->error);
    return false;
}

function atualizarFaturaComPix($fatura_id, $transacao_db_id, $mp_payment_id, $qr_code, $copiaecola, $conn) {
    $sql = "UPDATE pagamento_faturas 
            SET transacao_id = ?, mp_payment_id = ?, 
                pix_qrcode = ?, pix_copiaecola = ?,
                pix_expiracao = DATE_ADD(NOW(), INTERVAL 24 HOUR)
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $transacao_db_id, $mp_payment_id, $qr_code, $copiaecola, $fatura_id);
    return $stmt->execute();
}

function atualizarFaturaPaga($fatura_id, $transacao_db_id, $conn) {
    $sql = "UPDATE pagamento_faturas 
            SET status = 'paga', data_pagamento = NOW(), transacao_id = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $transacao_db_id, $fatura_id);
    return $stmt->execute();
}

function registrarLogPagamento($cliente_id, $fatura_id, $transacao_id, $acao, 
                                $status_anterior, $status_novo, $detalhes, $conn) {
    $sql = "INSERT INTO pagamento_logs 
            (cliente_id, fatura_id, transacao_id, acao, status_anterior, 
             status_novo, detalhes, ip, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssss", 
        $cliente_id, $fatura_id, $transacao_id, $acao,
        $status_anterior, $status_novo, $detalhes, $ip, $user_agent
    );
    
    return $stmt->execute();
}

function buscarFaturasCliente($cliente_id, $filtros = [], $conn) {
    $sql = "SELECT f.*, 
                   pc.nome_plano,
                   DATEDIFF(f.data_vencimento, CURDATE()) as dias_para_vencer,
                   CASE 
                       WHEN f.status = 'pendente' AND f.data_vencimento < CURDATE() THEN 'atrasada'
                       ELSE f.status
                   END as status_real
            FROM pagamento_faturas f
            LEFT JOIN planos_contratados pc ON pc.id = f.plano_contratado_id
            WHERE f.cliente_id = ?";
    
    $params = [$cliente_id];
    $types = "i";
    
    if (!empty($filtros['status'])) {
        if ($filtros['status'] == 'atrasada') {
            $sql .= " AND f.status = 'pendente' AND f.data_vencimento < CURDATE()";
        } else {
            $sql .= " AND f.status = ?";
            $params[] = $filtros['status'];
            $types .= "s";
        }
    }
    
    if (!empty($filtros['periodo'])) {
        switch ($filtros['periodo']) {
            case 'mes':
                $sql .= " AND MONTH(f.data_vencimento) = MONTH(CURDATE()) AND YEAR(f.data_vencimento) = YEAR(CURDATE())";
                break;
            case 'trimestre':
                $sql .= " AND f.data_vencimento >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                break;
            case 'ano':
                $sql .= " AND YEAR(f.data_vencimento) = YEAR(CURDATE())";
                break;
        }
    }
    
    $sql .= " ORDER BY f.data_vencimento DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $faturas = [];
    while ($row = $result->fetch_assoc()) {
        $faturas[] = $row;
    }
    
    return $faturas;
}

function estatisticasFaturasCliente($cliente_id, $conn) {
    $sql = "SELECT 
                COUNT(*) as total_faturas,
                SUM(CASE WHEN status = 'pendente' AND data_vencimento >= CURDATE() THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 'paga' THEN 1 ELSE 0 END) as pagas,
                SUM(CASE WHEN status = 'pendente' AND data_vencimento < CURDATE() THEN 1 ELSE 0 END) as atrasadas,
                SUM(CASE WHEN status = 'pendente' AND data_vencimento >= CURDATE() THEN valor_total ELSE 0 END) as total_pendente,
                SUM(CASE WHEN status = 'paga' THEN valor_total ELSE 0 END) as total_pago,
                MIN(CASE WHEN status = 'pendente' AND data_vencimento >= CURDATE() THEN data_vencimento END) as proxima_fatura
            FROM pagamento_faturas
            WHERE cliente_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    
    $default_stats = [
        'total_faturas' => 0,
        'pendentes' => 0,
        'pagas' => 0,
        'atrasadas' => 0,
        'total_pendente' => 0,
        'total_pago' => 0,
        'proxima_fatura' => null
    ];
    
    return array_merge($default_stats, $stats ?: []);
}