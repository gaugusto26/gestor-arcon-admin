-- Base multi-produto para o Gestor SaaS.
-- O Arcon passa a ser apenas o primeiro produto plugado.

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO produtos_saas (nome, slug, descricao, tipo_integracao, config_json)
VALUES ('Arcon', 'arcon', 'Primeiro produto SaaS integrado ao Gestor.', 'supabase', '{"remote_table":"empresas","remote_id_field":"gestor_cliente_id","notice_table":"avisos_sistema"}')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), tipo_integracao = VALUES(tipo_integracao), config_json = VALUES(config_json), ativo = 1;

INSERT INTO planos_saas (produto_id, nome, slug)
SELECT p.id, x.nome, x.slug
FROM produtos_saas p
JOIN (
  SELECT 'Free' nome, 'free' slug UNION ALL
  SELECT 'Basico', 'basico' UNION ALL
  SELECT 'Profissional', 'profissional' UNION ALL
  SELECT 'Empresarial', 'empresarial' UNION ALL
  SELECT 'Enterprise', 'enterprise'
) x
WHERE p.slug = 'arcon'
ON DUPLICATE KEY UPDATE nome = VALUES(nome), ativo = 1;

INSERT INTO integracoes_produto (produto_id, nome, tipo, api_key_env, service_key_env)
SELECT p.id, 'Supabase Arcon', 'supabase', 'SUPABASE_URL', 'SUPABASE_SERVICE_KEY'
FROM produtos_saas p
WHERE p.slug = 'arcon'
  AND NOT EXISTS (
    SELECT 1
    FROM integracoes_produto i
    WHERE i.produto_id = p.id
      AND i.nome = 'Supabase Arcon'
  );
