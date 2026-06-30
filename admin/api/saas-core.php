<?php
/**
 * Camada base para gestão SaaS multi-produto.
 * O Arcon usa esta base como primeiro produto integrado, mas as tabelas
 * aceitam outros sistemas com REST, webhook, Supabase ou modo manual.
 */

function saasEnsureSchema(mysqli $conn): void {
    $conn->query("
        CREATE TABLE IF NOT EXISTS produtos_saas (
            id int(11) NOT NULL AUTO_INCREMENT,
            nome varchar(120) NOT NULL,
            slug varchar(80) NOT NULL,
            descricao text DEFAULT NULL,
            tipo_integracao enum('manual','supabase','rest','webhook') NOT NULL DEFAULT 'manual',
            ativo tinyint(1) NOT NULL DEFAULT 1,
            config_json longtext DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY produtos_saas_slug_idx (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS planos_saas (
            id int(11) NOT NULL AUTO_INCREMENT,
            produto_id int(11) NOT NULL,
            nome varchar(120) NOT NULL,
            slug varchar(80) NOT NULL,
            descricao text DEFAULT NULL,
            valor_mensal decimal(10,2) DEFAULT 0.00,
            ativo tinyint(1) NOT NULL DEFAULT 1,
            config_json longtext DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY planos_saas_produto_slug_idx (produto_id, slug),
            KEY planos_saas_produto_idx (produto_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS assinaturas_saas (
            id int(11) NOT NULL AUTO_INCREMENT,
            cliente_id int(11) NOT NULL,
            produto_id int(11) NOT NULL,
            plano_id int(11) DEFAULT NULL,
            plano_slug varchar(80) NOT NULL DEFAULT 'free',
            status enum('pendente','trial','ativo','suspenso','cancelado','expirado') NOT NULL DEFAULT 'pendente',
            external_empresa_id varchar(120) DEFAULT NULL,
            external_cliente_id varchar(120) DEFAULT NULL,
            origem varchar(40) NOT NULL DEFAULT 'manual',
            data_inicio date DEFAULT NULL,
            trial_ate date DEFAULT NULL,
            proxima_cobranca date DEFAULT NULL,
            data_cancelamento datetime DEFAULT NULL,
            sincronizado_em datetime DEFAULT NULL,
            metadata_json longtext DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY assinaturas_saas_cliente_produto_idx (cliente_id, produto_id),
            KEY assinaturas_saas_produto_status_idx (produto_id, status),
            KEY assinaturas_saas_cliente_idx (cliente_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS integracoes_produto (
            id int(11) NOT NULL AUTO_INCREMENT,
            produto_id int(11) NOT NULL,
            nome varchar(120) NOT NULL,
            tipo enum('supabase','rest','webhook','manual') NOT NULL DEFAULT 'manual',
            base_url varchar(255) DEFAULT NULL,
            api_key_env varchar(120) DEFAULT NULL,
            service_key_env varchar(120) DEFAULT NULL,
            ativo tinyint(1) NOT NULL DEFAULT 1,
            config_json longtext DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            KEY integracoes_produto_idx (produto_id, ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS eventos_assinatura (
            id int(11) NOT NULL AUTO_INCREMENT,
            assinatura_id int(11) DEFAULT NULL,
            cliente_id int(11) NOT NULL,
            produto_id int(11) NOT NULL,
            tipo varchar(80) NOT NULL,
            status_anterior varchar(40) DEFAULT NULL,
            status_novo varchar(40) DEFAULT NULL,
            payload_json longtext DEFAULT NULL,
            resultado_json longtext DEFAULT NULL,
            ok tinyint(1) NOT NULL DEFAULT 1,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            KEY eventos_assinatura_assinatura_idx (assinatura_id),
            KEY eventos_assinatura_cliente_produto_idx (cliente_id, produto_id),
            KEY eventos_assinatura_tipo_idx (tipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

function saasSeedArcon(mysqli $conn): int {
    $config = json_encode([
        'remote_table' => 'empresas',
        'remote_id_field' => 'gestor_cliente_id',
        'notice_table' => 'avisos_sistema',
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("
        INSERT INTO produtos_saas (nome, slug, descricao, tipo_integracao, config_json)
        VALUES ('Arcon', 'arcon', 'Primeiro produto SaaS integrado ao Gestor.', 'supabase', ?)
        ON DUPLICATE KEY UPDATE
            nome = VALUES(nome),
            tipo_integracao = VALUES(tipo_integracao),
            config_json = VALUES(config_json),
            ativo = 1
    ");
    $stmt->bind_param("s", $config);
    $stmt->execute();
    $stmt->close();

    $produto = saasGetProdutoBySlug($conn, 'arcon');
    $produtoId = (int)$produto['id'];

    foreach (['free', 'basico', 'profissional', 'empresarial', 'enterprise'] as $slug) {
        $nome = ucfirst($slug);
        $stmt = $conn->prepare("
            INSERT INTO planos_saas (produto_id, nome, slug)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE nome = VALUES(nome), ativo = 1
        ");
        $stmt->bind_param("iss", $produtoId, $nome, $slug);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT id FROM integracoes_produto WHERE produto_id = ? AND nome = 'Supabase Arcon' LIMIT 1");
    $stmt->bind_param("i", $produtoId);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existe) {
        $stmt = $conn->prepare("
            INSERT INTO integracoes_produto (produto_id, nome, tipo, api_key_env, service_key_env)
            VALUES (?, 'Supabase Arcon', 'supabase', 'SUPABASE_URL', 'SUPABASE_SERVICE_KEY')
        ");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        $stmt->close();
    }

    return $produtoId;
}

function saasBoot(mysqli $conn): void {
    saasEnsureSchema($conn);
    saasSeedArcon($conn);
}

function saasGetProdutoBySlug(mysqli $conn, string $slug): ?array {
    $stmt = $conn->prepare("SELECT * FROM produtos_saas WHERE slug = ? LIMIT 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function saasGetPlanoId(mysqli $conn, int $produtoId, string $planoSlug): ?int {
    $stmt = $conn->prepare("SELECT id FROM planos_saas WHERE produto_id = ? AND slug = ? LIMIT 1");
    $stmt->bind_param("is", $produtoId, $planoSlug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['id'] : null;
}

function saasNormalizeStatus(string $status): string {
    $map = [
        'ativo' => 'ativo',
        'ativa' => 'ativo',
        'pago' => 'ativo',
        'trial' => 'trial',
        'teste' => 'trial',
        'pendente' => 'pendente',
        'atrasado' => 'pendente',
        'suspenso' => 'suspenso',
        'bloqueado' => 'suspenso',
        'cancelado' => 'cancelado',
        'inativo' => 'cancelado',
        'expirado' => 'expirado',
    ];
    return $map[strtolower($status)] ?? 'pendente';
}

function saasGetAssinaturaClienteProduto(mysqli $conn, int $clienteId, string $produtoSlug): ?array {
    $produto = saasGetProdutoBySlug($conn, $produtoSlug);
    if (!$produto) return null;

    $stmt = $conn->prepare("
        SELECT a.*, p.nome as produto_nome, p.slug as produto_slug
        FROM assinaturas_saas a
        JOIN produtos_saas p ON p.id = a.produto_id
        WHERE a.cliente_id = ? AND a.produto_id = ?
        LIMIT 1
    ");
    $produtoId = (int)$produto['id'];
    $stmt->bind_param("ii", $clienteId, $produtoId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function saasUpsertAssinatura(mysqli $conn, int $clienteId, string $produtoSlug, array $dados): array {
    $produto = saasGetProdutoBySlug($conn, $produtoSlug);
    if (!$produto) {
        throw new RuntimeException("Produto SaaS não encontrado: {$produtoSlug}");
    }

    $produtoId = (int)$produto['id'];
    $planoSlug = $dados['plano_slug'] ?? $dados['plano'] ?? 'free';
    $status = saasNormalizeStatus($dados['status'] ?? 'pendente');
    $planoId = saasGetPlanoId($conn, $produtoId, $planoSlug);
    $externalEmpresaId = isset($dados['external_empresa_id']) ? (string)$dados['external_empresa_id'] : null;
    $externalClienteId = isset($dados['external_cliente_id']) ? (string)$dados['external_cliente_id'] : null;
    $origem = $dados['origem'] ?? 'manual';
    $metadata = isset($dados['metadata']) ? json_encode($dados['metadata'], JSON_UNESCAPED_UNICODE) : null;

    $anterior = saasGetAssinaturaClienteProduto($conn, $clienteId, $produtoSlug);

    $stmt = $conn->prepare("
        INSERT INTO assinaturas_saas
            (cliente_id, produto_id, plano_id, plano_slug, status, external_empresa_id, external_cliente_id, origem, sincronizado_em, metadata_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ON DUPLICATE KEY UPDATE
            plano_id = VALUES(plano_id),
            plano_slug = VALUES(plano_slug),
            status = VALUES(status),
            external_empresa_id = COALESCE(VALUES(external_empresa_id), external_empresa_id),
            external_cliente_id = COALESCE(VALUES(external_cliente_id), external_cliente_id),
            origem = VALUES(origem),
            sincronizado_em = NOW(),
            metadata_json = COALESCE(VALUES(metadata_json), metadata_json)
    ");
    $stmt->bind_param(
        "iiissssss",
        $clienteId,
        $produtoId,
        $planoId,
        $planoSlug,
        $status,
        $externalEmpresaId,
        $externalClienteId,
        $origem,
        $metadata
    );
    $stmt->execute();
    $stmt->close();

    $assinatura = saasGetAssinaturaClienteProduto($conn, $clienteId, $produtoSlug);
    saasRegistrarEvento($conn, [
        'assinatura_id' => $assinatura['id'] ?? null,
        'cliente_id' => $clienteId,
        'produto_id' => $produtoId,
        'tipo' => $dados['evento'] ?? 'assinatura_atualizada',
        'status_anterior' => $anterior['status'] ?? null,
        'status_novo' => $status,
        'payload' => $dados,
        'resultado' => $dados['resultado'] ?? null,
        'ok' => $dados['ok'] ?? true,
    ]);

    return $assinatura ?: [];
}

function saasRegistrarEvento(mysqli $conn, array $evento): void {
    $payload = isset($evento['payload']) ? json_encode($evento['payload'], JSON_UNESCAPED_UNICODE) : null;
    $resultado = isset($evento['resultado']) ? json_encode($evento['resultado'], JSON_UNESCAPED_UNICODE) : null;
    $ok = !empty($evento['ok']) ? 1 : 0;
    $assinaturaId = isset($evento['assinatura_id']) ? (int)$evento['assinatura_id'] : null;

    $stmt = $conn->prepare("
        INSERT INTO eventos_assinatura
            (assinatura_id, cliente_id, produto_id, tipo, status_anterior, status_novo, payload_json, resultado_json, ok)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiisssssi",
        $assinaturaId,
        $evento['cliente_id'],
        $evento['produto_id'],
        $evento['tipo'],
        $evento['status_anterior'],
        $evento['status_novo'],
        $payload,
        $resultado,
        $ok
    );
    $stmt->execute();
    $stmt->close();
}
