-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www..phpmyadminnet/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01/03/2026 às 18:18
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `digitalfive`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `acao` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_hora` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nome_completo` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ip_permitido` varchar(45) DEFAULT NULL,
  `ultimo_acesso` datetime DEFAULT NULL,
  `tentativas_falhas` int(11) DEFAULT 0,
  `bloqueado_ate` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `blog_categorias`
--

CREATE TABLE `blog_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `icone` varchar(50) DEFAULT 'fa-folder',
  `cor` varchar(20) DEFAULT '#3b82f6',
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `blog_comentarios`
--

CREATE TABLE `blog_comentarios` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `comentario` text NOT NULL,
  `aprovado` tinyint(1) DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `data_comentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `blog_curtidas`
--

CREATE TABLE `blog_curtidas` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `data_curtida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `blog_newsletter`
--

CREATE TABLE `blog_newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `confirmado` tinyint(1) DEFAULT 0,
  `data_confirmacao` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `conteudo` longtext NOT NULL,
  `resumo` text DEFAULT NULL,
  `imagem_destaque` varchar(255) DEFAULT NULL,
  `imagem_og` varchar(255) DEFAULT NULL COMMENT 'Imagem para compartilhamento',
  `autor` varchar(100) DEFAULT 'Renan',
  `autor_avatar` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `tempo_leitura` int(11) DEFAULT NULL COMMENT 'Em minutos',
  `destaque` tinyint(1) DEFAULT 0,
  `status` enum('rascunho','publicado','arquivado') DEFAULT 'rascunho',
  `data_publicacao` datetime DEFAULT NULL,
  `data_agendamento` datetime DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `codigo_indicacao` varchar(100) DEFAULT NULL,
  `indicado_por` int(11) DEFAULT NULL,
  `total_indicacoes` int(11) DEFAULT 0,
  `desconto_indicacao` decimal(10,2) DEFAULT 0.00,
  `validade_desconto` date DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `rg_ie` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `empresa` varchar(200) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo','bloqueado') DEFAULT 'ativo',
  `tipo` enum('cliente','admin','parceiro') DEFAULT 'cliente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL,
  `tentativas_falhas` int(11) DEFAULT 0,
  `bloqueado_ate` datetime DEFAULT NULL,
  `token_recuperacao` varchar(100) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_assinaturas`
--

CREATE TABLE `cliente_assinaturas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nome_assinatura` varchar(255) NOT NULL,
  `assinatura_base64` longtext NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_assinaturas_contratos`
--

CREATE TABLE `cliente_assinaturas_contratos` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `assinatura_id` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_assinatura` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_documentos`
--

CREATE TABLE `cliente_documentos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `tipo` enum('contrato','fatura','nota_fiscal','recibo','proposta','outros') NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `arquivo_nome` varchar(255) NOT NULL,
  `arquivo_path` varchar(500) NOT NULL,
  `arquivo_tamanho` int(11) DEFAULT NULL COMMENT 'Tamanho em bytes',
  `arquivo_tipo` varchar(100) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL COMMENT 'ID do contrato/fatura relacionado',
  `referencia_numero` varchar(100) DEFAULT NULL COMMENT 'Número do contrato/fatura',
  `data_documento` date DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `status` enum('ativo','cancelado','excluido') DEFAULT 'ativo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_documentos_categorias`
--

CREATE TABLE `cliente_documentos_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'fa-folder',
  `cor` varchar(20) DEFAULT '#3b82f6',
  `ordem` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_faturas`
--

CREATE TABLE `cliente_faturas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_contratado_id` int(11) NOT NULL,
  `numero_fatura` varchar(50) NOT NULL,
  `mes_referencia` date NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `juros` decimal(10,2) DEFAULT 0.00,
  `multa` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `status` enum('pendente','paga','atrasada','cancelada') DEFAULT 'pendente',
  `transacao_id` int(11) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_logs`
--

CREATE TABLE `cliente_logs` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_hora` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_sistemas`
--

CREATE TABLE `cliente_sistemas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_contratado_id` int(11) NOT NULL,
  `nome_sistema` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `url_acesso` varchar(255) DEFAULT NULL,
  `login` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `status` enum('aguardando_inicio','reuniao_inicial','levantamento_requisitos','design_aprovacao','design_aprovado','desenvolvimento','desenvolvimento_frontend','desenvolvimento_backend','integracao_apis','testes_internos','homologacao_cliente','ajustes_finais','ambiente_teste','treinamento_cliente','aguardando_aprovacao','aprovado_cliente','implantacao','concluido','manutencao','cancelado','pausado') DEFAULT 'aguardando_inicio',
  `etapa_atual` varchar(255) DEFAULT NULL,
  `proxima_etapa` varchar(255) DEFAULT NULL,
  `feedback_cliente` text DEFAULT NULL,
  `responsavel` varchar(100) DEFAULT NULL,
  `equipe` text DEFAULT NULL,
  `repositorio_url` varchar(255) DEFAULT NULL,
  `ambiente_teste_url` varchar(255) DEFAULT NULL,
  `ambiente_producao_url` varchar(255) DEFAULT NULL,
  `ultima_atualizacao` datetime DEFAULT NULL,
  `proximo_passo` text DEFAULT NULL,
  `percentual_concluido` int(11) DEFAULT 0,
  `data_inicio` date DEFAULT NULL,
  `data_previsao_entrega` date DEFAULT NULL,
  `data_entrega` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_sistemas_historico`
--

CREATE TABLE `cliente_sistemas_historico` (
  `id` int(11) NOT NULL,
  `sistema_id` int(11) NOT NULL,
  `status_anterior` varchar(50) DEFAULT NULL,
  `status_novo` varchar(50) NOT NULL,
  `observacao` text DEFAULT NULL,
  `alterado_por` int(11) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_suporte_mensagens`
--

CREATE TABLE `cliente_suporte_mensagens` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `remetente` enum('cliente','admin') NOT NULL,
  `mensagem` text NOT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `data_envio` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_suporte_tickets`
--

CREATE TABLE `cliente_suporte_tickets` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero_ticket` varchar(50) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `categoria` enum('duvida','problema','sugestao','reclamacao','outros') DEFAULT 'duvida',
  `prioridade` enum('baixa','media','alta','urgente') DEFAULT 'media',
  `status` enum('aberto','em_andamento','respondido','resolvido','fechado') DEFAULT 'aberto',
  `ultima_mensagem` datetime DEFAULT NULL,
  `data_abertura` datetime NOT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_tokens`
--

CREATE TABLE `cliente_tokens` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `tipo` enum('acesso','api','recuperacao') DEFAULT 'acesso',
  `validade` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `config_whatsapp`
--

CREATE TABLE `config_whatsapp` (
  `id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `mensagem_padrao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_contratado_id` int(11) DEFAULT NULL,
  `tipo_contrato` enum('adesao','renovacao','cancelamento','aditivo') NOT NULL,
  `numero_contrato` varchar(50) NOT NULL,
  `versao` varchar(20) DEFAULT '1.0',
  `titulo` varchar(255) NOT NULL,
  `conteudo` longtext NOT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `valor_entrada` decimal(10,2) DEFAULT NULL,
  `valor_mensal` decimal(10,2) DEFAULT NULL,
  `numero_parcelas` int(11) DEFAULT 1,
  `dia_vencimento` int(11) DEFAULT 10,
  `data_primeira_parcela` date DEFAULT NULL,
  `data_primeira_mensalidade` date DEFAULT NULL,
  `multa_cancelamento` decimal(10,2) DEFAULT NULL,
  `percentual_multa` decimal(5,2) DEFAULT NULL COMMENT 'Percentual da multa',
  `prazo_fidelidade` int(11) DEFAULT NULL COMMENT 'Meses de fidelidade',
  `data_assinatura` datetime DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `data_cancelamento` datetime DEFAULT NULL,
  `motivo_cancelamento` text DEFAULT NULL,
  `status` enum('rascunho','enviado','assinado','cancelado','vencido') DEFAULT 'rascunho',
  `pdf_path` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Acionadores `contratos`
--
DELIMITER $$
CREATE TRIGGER `after_contrato_assinado` AFTER UPDATE ON `contratos` FOR EACH ROW BEGIN
    IF NEW.status = 'assinado' AND OLD.status != 'assinado' THEN
        INSERT INTO cliente_documentos 
            (cliente_id, tipo, titulo, descricao, arquivo_nome, arquivo_path, 
             referencia_id, referencia_numero, data_documento, valor, created_at)
        VALUES 
            (NEW.cliente_id, 'contrato', 
             CONCAT('Contrato ', NEW.numero_contrato),
             NEW.titulo,
             CONCAT('contrato_', NEW.numero_contrato, '.pdf'),
             CONCAT('/uploads/contratos/contrato_', NEW.id, '.pdf'),
             NEW.id,
             NEW.numero_contrato,
             DATE(NEW.created_at),
             NEW.valor_total,
             NEW.created_at);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_contrato_assinado_criar_faturas` AFTER UPDATE ON `contratos` FOR EACH ROW BEGIN
    -- Verificar se o contrato foi assinado AGORA
    IF NEW.status = 'assinado' AND OLD.status != 'assinado' THEN
        
        -- Criar entrada em planos_contratados (se não existir)
        IF NEW.plano_contratado_id IS NULL THEN
            INSERT INTO planos_contratados 
                (cliente_id, tipo_plano, nome_plano, valor_plano, valor_mensal, 
                 forma_pagamento, numero_parcelas, data_inicio, dia_vencimento, status, created_at)
            VALUES 
                (NEW.cliente_id, 'sistema', 
                 CONCAT('Contrato ', NEW.numero_contrato),
                 NEW.valor_total, 
                 COALESCE(NEW.valor_mensal, 0),
                 'recorrente',
                 NEW.numero_parcelas,
                 CURDATE(),
                 NEW.dia_vencimento,
                 'pendente',
                 NOW());
            
            -- Atualizar o contrato com o ID do plano criado
            UPDATE contratos 
            SET plano_contratado_id = LAST_INSERT_ID()
            WHERE id = NEW.id;
            
            -- Guardar o ID do plano para usar nas faturas
            SET @plano_id = LAST_INSERT_ID();
        ELSE
            SET @plano_id = NEW.plano_contratado_id;
        END IF;
        
        -- Criar fatura de desenvolvimento (se houver valor)
        IF NEW.valor_total > 0 THEN
            INSERT INTO pagamento_faturas 
                (cliente_id, contrato_id, plano_contratado_id, numero_fatura, tipo,
                 valor, valor_total, data_emissao, data_vencimento, status, created_at)
            VALUES 
                (NEW.cliente_id, NEW.id, @plano_id,
                 CONCAT('FAT-DEV-', DATE_FORMAT(NOW(), '%Y%m'), '-', LPAD(NEW.id, 4, '0')),
                 'desenvolvimento',
                 NEW.valor_total, NEW.valor_total,
                 CURDATE(),
                 DATE_ADD(CURDATE(), INTERVAL 7 DAY),
                 'pendente',
                 NOW());
        END IF;
        
        -- Criar primeira fatura de mensalidade (se houver)
        IF NEW.valor_mensal > 0 THEN
            INSERT INTO pagamento_faturas 
                (cliente_id, contrato_id, plano_contratado_id, numero_fatura, tipo,
                 mes_referencia, valor, valor_total, data_emissao, data_vencimento, status, created_at)
            VALUES 
                (NEW.cliente_id, NEW.id, @plano_id,
                 CONCAT('FAT-', DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y%m'), '-', LPAD(NEW.id, 4, '0')),
                 'mensalidade',
                 DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                 NEW.valor_mensal, NEW.valor_mensal,
                 CURDATE(),
                 DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), CONCAT('%Y-%m-', LPAD(COALESCE(NEW.dia_vencimento, 10), 2, '0'))),
                 'pendente',
                 NOW());
        END IF;
        
        -- Registrar no log
        INSERT INTO pagamento_logs (cliente_id, acao, detalhes)
        VALUES (NEW.cliente_id, 'faturas_geradas', 
                CONCAT('Faturas geradas para contrato ', NEW.numero_contrato));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_contrato_assinado_pagamento` AFTER UPDATE ON `contratos` FOR EACH ROW BEGIN
    IF NEW.status = 'assinado' AND OLD.status != 'assinado' THEN
        -- Registrar no log
        INSERT INTO pagamento_logs (cliente_id, acao, status_novo, detalhes)
        VALUES (NEW.cliente_id, 'contrato_assinado', 'assinado', 
                CONCAT('Contrato ', NEW.numero_contrato, ' assinado'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contrato_clausulas`
--

CREATE TABLE `contrato_clausulas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `tipo` enum('adesao','renovacao','cancelamento','todos') DEFAULT 'todos',
  `ordem` int(11) DEFAULT 0,
  `obrigatoria` tinyint(1) DEFAULT 1,
  `ativa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contrato_historico`
--

CREATE TABLE `contrato_historico` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `dados_anteriores` text DEFAULT NULL,
  `dados_novos` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `indicacoes`
--

CREATE TABLE `indicacoes` (
  `id` int(11) NOT NULL,
  `indicador_id` int(11) NOT NULL COMMENT 'Cliente que indicou',
  `indicado_id` int(11) DEFAULT NULL COMMENT 'Cliente que foi indicado',
  `codigo_indicacao` varchar(100) NOT NULL,
  `nome_indicado` varchar(200) DEFAULT NULL,
  `email_indicado` varchar(100) DEFAULT NULL,
  `telefone_indicado` varchar(20) DEFAULT NULL,
  `status` enum('pendente','convertido','expirado','cancelado') DEFAULT 'pendente',
  `data_indicacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_conversao` datetime DEFAULT NULL,
  `data_expiracao` datetime DEFAULT NULL,
  `desconto_aplicado` decimal(10,2) DEFAULT NULL,
  `valor_comissao` decimal(10,2) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `indicacoes_config`
--

CREATE TABLE `indicacoes_config` (
  `id` int(11) NOT NULL,
  `percentual_desconto` decimal(5,2) DEFAULT 10.00,
  `dias_validade` int(11) DEFAULT 90,
  `limite_indicacoes` int(11) DEFAULT 0 COMMENT '0 = ilimitado',
  `desconto_acumulativo` tinyint(1) DEFAULT 0,
  `mensagem_whatsapp` text DEFAULT NULL,
  `regras` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `indicacoes_historico`
--

CREATE TABLE `indicacoes_historico` (
  `id` int(11) NOT NULL,
  `indicacao_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `metricas_empresas`
--

CREATE TABLE `metricas_empresas` (
  `id` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `total_empresas` int(11) DEFAULT 0,
  `novas_empresas` int(11) DEFAULT 0,
  `empresas_ativas` int(11) DEFAULT 0,
  `empresas_inativas` int(11) DEFAULT 0,
  `receita_total` decimal(10,2) DEFAULT 0.00,
  `receita_mensal_total` decimal(10,2) DEFAULT 0.00,
  `ticket_medio` decimal(10,2) DEFAULT 0.00,
  `contratos_novos` int(11) DEFAULT 0,
  `contratos_renovados` int(11) DEFAULT 0,
  `contratos_cancelados` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `newsletter_campanhas`
--

CREATE TABLE `newsletter_campanhas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `conteudo` longtext NOT NULL,
  `template` enum('padrao','blog','promocao','aviso') DEFAULT 'padrao',
  `status` enum('rascunho','agendada','enviando','enviada','cancelada') DEFAULT 'rascunho',
  `data_agendamento` datetime DEFAULT NULL,
  `data_envio` datetime DEFAULT NULL,
  `total_envios` int(11) DEFAULT 0,
  `total_abertos` int(11) DEFAULT 0,
  `total_cliques` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `newsletter_config`
--

CREATE TABLE `newsletter_config` (
  `id` int(11) NOT NULL,
  `remetente_nome` varchar(100) DEFAULT 'NTW - New Software',
  `remetente_email` varchar(100) DEFAULT 'newsletter@ntw.com.br',
  `limite_por_minuto` int(11) DEFAULT 30,
  `limite_por_hora` int(11) DEFAULT 500,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_user` varchar(100) DEFAULT NULL,
  `smtp_pass` varchar(255) DEFAULT NULL,
  `smtp_secure` enum('tls','ssl') DEFAULT 'tls',
  `assinatura_html` text DEFAULT NULL,
  `rodape_html` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `whatsapp_numero` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `newsletter_envios`
--

CREATE TABLE `newsletter_envios` (
  `id` int(11) NOT NULL,
  `campanha_id` int(11) NOT NULL,
  `inscrito_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token_unico` varchar(64) DEFAULT NULL,
  `status` enum('pendente','enviado','falhou','aberto','clicou') DEFAULT 'pendente',
  `data_envio` datetime DEFAULT NULL,
  `data_abertura` datetime DEFAULT NULL,
  `data_clique` datetime DEFAULT NULL,
  `erro` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `newsletter_inscritos`
--

CREATE TABLE `newsletter_inscritos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `confirmado` tinyint(1) DEFAULT 0,
  `data_confirmacao` datetime DEFAULT NULL,
  `origem` enum('blog','site','planos','footer','popup') DEFAULT 'site',
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('ativo','inativo','bloqueado') DEFAULT 'ativo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_assinaturas`
--

CREATE TABLE `pagamento_assinaturas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_contratado_id` int(11) NOT NULL,
  `mp_preapproval_id` varchar(100) NOT NULL,
  `mp_subscription_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','authorized','paused','cancelled') DEFAULT 'pending',
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `ultima_cobranca` date DEFAULT NULL,
  `proxima_cobranca` date DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_config`
--

CREATE TABLE `pagamento_config` (
  `id` int(11) NOT NULL,
  `gateway` enum('mercadopago','pagbank','nenhum') DEFAULT 'nenhum',
  `modo` enum('teste','producao') DEFAULT 'teste',
  `public_key` text DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `client_id` varchar(100) DEFAULT NULL,
  `client_secret` text DEFAULT NULL,
  `webhook_secret` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `pix_key` varchar(100) DEFAULT NULL COMMENT 'Chave PIX (CNPJ/CPF/Telefone/Email)',
  `pix_key_type` enum('cnpj','cpf','telefone','email','aleatoria') DEFAULT 'cnpj',
  `pix_qr_code` text DEFAULT NULL,
  `juros_mensal` decimal(5,2) DEFAULT 2.00 COMMENT 'Juros ao mês para parcelamento',
  `multa_atraso` decimal(5,2) DEFAULT 2.00 COMMENT 'Multa por atraso',
  `juros_dia` decimal(5,2) DEFAULT 0.33 COMMENT 'Juros por dia de atraso',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_config_geracao`
--

CREATE TABLE `pagamento_config_geracao` (
  `id` int(11) NOT NULL,
  `dia_geracao` int(11) DEFAULT 1,
  `dias_antecedencia` int(11) DEFAULT 5,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_faturas`
--

CREATE TABLE `pagamento_faturas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `contrato_id` int(11) DEFAULT NULL,
  `plano_contratado_id` int(11) DEFAULT NULL,
  `numero_fatura` varchar(50) NOT NULL,
  `tipo` enum('desenvolvimento','mensalidade','multa','entrada') DEFAULT 'mensalidade',
  `mes_referencia` date DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `juros` decimal(10,2) DEFAULT 0.00,
  `multa` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL,
  `data_emissao` date NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `status` enum('pendente','paga','atrasada','cancelada') DEFAULT 'pendente',
  `transacao_id` int(11) DEFAULT NULL,
  `mp_payment_id` varchar(100) DEFAULT NULL,
  `mp_preference_id` varchar(100) DEFAULT NULL,
  `link_pagamento` varchar(500) DEFAULT NULL,
  `pix_qrcode` text DEFAULT NULL,
  `pix_qrcode_base64` text DEFAULT NULL,
  `pix_copiaecola` text DEFAULT NULL,
  `pix_expiracao` datetime DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_fatura_itens`
--

CREATE TABLE `pagamento_fatura_itens` (
  `id` int(11) NOT NULL,
  `fatura_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `valor_unitario` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `tipo` enum('servico','produto','taxa','desconto') DEFAULT 'servico',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_logs`
--

CREATE TABLE `pagamento_logs` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `fatura_id` int(11) DEFAULT NULL,
  `transacao_id` varchar(100) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `status_anterior` varchar(50) DEFAULT NULL,
  `status_novo` varchar(50) DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_transacoes`
--

CREATE TABLE `pagamento_transacoes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `contrato_id` int(11) DEFAULT NULL,
  `plano_contratado_id` int(11) DEFAULT NULL,
  `transacao_id` varchar(100) NOT NULL COMMENT 'ID da transação no gateway',
  `gateway` enum('mercadopago','pagbank') NOT NULL,
  `tipo` enum('adesao','mensalidade','renovacao','multa','outros') DEFAULT 'mensalidade',
  `valor` decimal(10,2) NOT NULL,
  `valor_original` decimal(10,2) DEFAULT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `juros` decimal(10,2) DEFAULT 0.00,
  `multa` decimal(10,2) DEFAULT 0.00,
  `forma_pagamento` enum('cartao_credito','cartao_debito','pix','boleto','transferencia') NOT NULL,
  `parcelas` int(11) DEFAULT 1,
  `status` enum('pendente','aprovado','recusado','cancelado','estornado','aguardando') DEFAULT 'pendente',
  `status_detalhe` varchar(255) DEFAULT NULL,
  `pix_qrcode` text DEFAULT NULL,
  `pix_copiaecola` text DEFAULT NULL,
  `pix_expiracao` datetime DEFAULT NULL,
  `link_pagamento` varchar(255) DEFAULT NULL,
  `data_aprovacao` datetime DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `webhook_recebido` tinyint(1) DEFAULT 0,
  `webhook_data` datetime DEFAULT NULL,
  `webhook_payload` longtext DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_webhooks`
--

CREATE TABLE `pagamento_webhooks` (
  `id` int(11) NOT NULL,
  `gateway` enum('mercadopago','pagbank') NOT NULL,
  `evento` varchar(100) NOT NULL,
  `transacao_id` varchar(100) DEFAULT NULL,
  `payload` longtext NOT NULL,
  `processado` tinyint(1) DEFAULT 0,
  `data_recebimento` datetime NOT NULL,
  `data_processamento` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos`
--

CREATE TABLE `planos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `nome` varchar(200) NOT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `descricao_curta` varchar(255) DEFAULT NULL,
  `descricao_completa` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `periodo` enum('mensal','anual','permanente','unico') DEFAULT 'permanente',
  `destaque` tinyint(1) DEFAULT 0,
  `badge_text` varchar(100) DEFAULT NULL,
  `prazo_entrega` varchar(100) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `perfil` enum('mei','empresa','ambos') DEFAULT 'ambos',
  `link_whatsapp` varchar(255) DEFAULT NULL,
  `mensagem_whatsapp` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_caracteristicas`
--

CREATE TABLE `planos_caracteristicas` (
  `id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `caracteristica` text NOT NULL,
  `icone` varchar(50) DEFAULT 'fa-check-circle',
  `ordem` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_categorias`
--

CREATE TABLE `planos_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'fa-globe',
  `descricao` text DEFAULT NULL,
  `perfil` enum('mei','empresa','ambos') DEFAULT 'ambos',
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_contratados`
--

CREATE TABLE `planos_contratados` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) DEFAULT NULL COMMENT 'ID do plano padrão (se aplicável)',
  `tipo_plano` enum('site','sistema','bot','outros') NOT NULL,
  `nome_plano` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor_plano` decimal(10,2) NOT NULL COMMENT 'Valor único do desenvolvimento',
  `valor_mensal` decimal(10,2) NOT NULL COMMENT 'Valor da mensalidade (manutenção)',
  `forma_pagamento` enum('vista','parcelado','recorrente') DEFAULT 'recorrente',
  `numero_parcelas` int(11) DEFAULT 1,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `periodo_teste` int(11) DEFAULT 0 COMMENT 'Dias de teste grátis',
  `dia_vencimento` int(11) DEFAULT 10,
  `data_proxima_fatura` date DEFAULT NULL,
  `ultima_fatura_gerada` date DEFAULT NULL,
  `mp_subscription_id` varchar(100) DEFAULT NULL,
  `mp_preapproval_id` varchar(100) DEFAULT NULL,
  `status` enum('ativo','pendente','cancelado','suspenso','concluido') DEFAULT 'ativo',
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Acionadores `planos_contratados`
--
DELIMITER $$
CREATE TRIGGER `after_plano_contratado_ativado` AFTER UPDATE ON `planos_contratados` FOR EACH ROW BEGIN
    IF NEW.status = 'ativo' AND OLD.status != 'ativo' THEN
        -- Inserir fatura de entrada se houver valor de desenvolvimento
        IF NEW.valor_plano > 0 THEN
            INSERT INTO pagamento_faturas 
                (cliente_id, contrato_id, plano_contratado_id, numero_fatura, tipo, 
                 mes_referencia, valor, valor_total, data_emissao, data_vencimento, status)
            SELECT 
                NEW.cliente_id,
                c.id,
                NEW.id,
                CONCAT('FAT-DEV-', DATE_FORMAT(NOW(), '%Y%m'), '-', LPAD(NEW.id, 4, '0')),
                'desenvolvimento',
                CURDATE(),
                NEW.valor_plano,
                NEW.valor_plano,
                CURDATE(),
                DATE_ADD(CURDATE(), INTERVAL 7 DAY),
                'pendente'
            FROM contratos c
            WHERE c.plano_contratado_id = NEW.id
            LIMIT 1;
        END IF;
        
        -- Atualizar data da próxima fatura
        UPDATE planos_contratados 
        SET data_proxima_fatura = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
        WHERE id = NEW.id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `politica_historico`
--

CREATE TABLE `politica_historico` (
  `id` int(11) NOT NULL,
  `politica_id` int(11) NOT NULL,
  `versao` varchar(20) NOT NULL,
  `conteudo_antigo` longtext DEFAULT NULL,
  `conteudo_novo` longtext DEFAULT NULL,
  `alteracoes` text DEFAULT NULL,
  `alterado_por` int(11) DEFAULT NULL,
  `data_alteracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `politica_privacidade`
--

CREATE TABLE `politica_privacidade` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL DEFAULT 'Política de Privacidade',
  `subtitulo` text DEFAULT NULL,
  `conteudo` longtext NOT NULL,
  `versao` varchar(20) DEFAULT '1.0',
  `data_publicacao` datetime DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `atualizado_por` int(11) DEFAULT NULL,
  `status` enum('rascunho','publicado','arquivado') DEFAULT 'rascunho',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `politica_secoes`
--

CREATE TABLE `politica_secoes` (
  `id` int(11) NOT NULL,
  `politica_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `icone` varchar(50) DEFAULT 'fa-circle',
  `ordem` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `view_empresas_analise`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `view_empresas_analise` (
`cliente_id` int(11)
,`nome` varchar(200)
,`email` varchar(100)
,`telefone` varchar(20)
,`empresa` varchar(200)
,`cpf_cnpj` varchar(20)
,`cliente_status` enum('ativo','inativo','bloqueado')
,`created_at` timestamp
,`total_contratos` bigint(21)
,`planos_ativos` bigint(21)
,`total_planos` bigint(21)
,`receita_mensal_total` decimal(32,2)
,`proximo_vencimento` date
);

-- --------------------------------------------------------

--
-- Estrutura para view `view_empresas_analise`
--
DROP TABLE IF EXISTS `view_empresas_analise`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_empresas_analise`  AS SELECT `c`.`id` AS `cliente_id`, `c`.`nome` AS `nome`, `c`.`email` AS `email`, `c`.`telefone` AS `telefone`, `c`.`empresa` AS `empresa`, `c`.`cpf_cnpj` AS `cpf_cnpj`, `c`.`status` AS `cliente_status`, `c`.`created_at` AS `created_at`, count(distinct `ct`.`id`) AS `total_contratos`, count(distinct case when `ct`.`status` = 'ativo' then `ct`.`id` end) AS `planos_ativos`, count(distinct `ct`.`id`) AS `total_planos`, coalesce(sum(case when `ct`.`status` = 'ativo' then `ct`.`valor_mensal` end),0) AS `receita_mensal_total`, min(case when `ct`.`status` = 'ativo' and `ct`.`data_vencimento` >= curdate() then `ct`.`data_vencimento` end) AS `proximo_vencimento` FROM (`clientes` `c` left join `contratos` `ct` on(`ct`.`cliente_id` = `c`.`id`)) WHERE `c`.`empresa` is not null AND `c`.`empresa` <> '' GROUP BY `c`.`id` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Índices de tabela `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Índices de tabela `blog_categorias`
--
ALTER TABLE `blog_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Índices de tabela `blog_curtidas`
--
ALTER TABLE `blog_curtidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_curtida` (`post_id`,`ip`),
  ADD KEY `post_id` (`post_id`);

--
-- Índices de tabela `blog_newsletter`
--
ALTER TABLE `blog_newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `status` (`status`),
  ADD KEY `data_publicacao` (`data_publicacao`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `codigo_indicacao` (`codigo_indicacao`),
  ADD KEY `status` (`status`),
  ADD KEY `indicado_por` (`indicado_por`);

--
-- Índices de tabela `cliente_assinaturas`
--
ALTER TABLE `cliente_assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `cliente_assinaturas_contratos`
--
ALTER TABLE `cliente_assinaturas_contratos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contrato_id` (`contrato_id`),
  ADD KEY `assinatura_id` (`assinatura_id`);

--
-- Índices de tabela `cliente_documentos`
--
ALTER TABLE `cliente_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `tipo` (`tipo`),
  ADD KEY `referencia_id` (`referencia_id`);

--
-- Índices de tabela `cliente_documentos_categorias`
--
ALTER TABLE `cliente_documentos_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cliente_faturas`
--
ALTER TABLE `cliente_faturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_fatura` (`numero_fatura`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_contratado_id` (`plano_contratado_id`),
  ADD KEY `transacao_id` (`transacao_id`);

--
-- Índices de tabela `cliente_logs`
--
ALTER TABLE `cliente_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `cliente_sistemas`
--
ALTER TABLE `cliente_sistemas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_contratado_id` (`plano_contratado_id`);

--
-- Índices de tabela `cliente_sistemas_historico`
--
ALTER TABLE `cliente_sistemas_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sistema_id` (`sistema_id`),
  ADD KEY `alterado_por` (`alterado_por`);

--
-- Índices de tabela `cliente_suporte_mensagens`
--
ALTER TABLE `cliente_suporte_mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Índices de tabela `cliente_suporte_tickets`
--
ALTER TABLE `cliente_suporte_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_ticket` (`numero_ticket`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `cliente_tokens`
--
ALTER TABLE `cliente_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `config_whatsapp`
--
ALTER TABLE `config_whatsapp`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_contrato` (`numero_contrato`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_contratado_id` (`plano_contratado_id`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `contrato_clausulas`
--
ALTER TABLE `contrato_clausulas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `contrato_historico`
--
ALTER TABLE `contrato_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrato_id` (`contrato_id`);

--
-- Índices de tabela `indicacoes`
--
ALTER TABLE `indicacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_indicacao` (`codigo_indicacao`),
  ADD KEY `indicador_id` (`indicador_id`),
  ADD KEY `indicado_id` (`indicado_id`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `indicacoes_config`
--
ALTER TABLE `indicacoes_config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `indicacoes_historico`
--
ALTER TABLE `indicacoes_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `indicacao_id` (`indicacao_id`);

--
-- Índices de tabela `metricas_empresas`
--
ALTER TABLE `metricas_empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ano_mes` (`ano`,`mes`);

--
-- Índices de tabela `newsletter_campanhas`
--
ALTER TABLE `newsletter_campanhas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `data_agendamento` (`data_agendamento`);

--
-- Índices de tabela `newsletter_config`
--
ALTER TABLE `newsletter_config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `newsletter_envios`
--
ALTER TABLE `newsletter_envios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campanha_id` (`campanha_id`),
  ADD KEY `inscrito_id` (`inscrito_id`),
  ADD KEY `token_unico` (`token_unico`);

--
-- Índices de tabela `newsletter_inscritos`
--
ALTER TABLE `newsletter_inscritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `confirmado` (`confirmado`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `pagamento_assinaturas`
--
ALTER TABLE `pagamento_assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mp_preapproval_id` (`mp_preapproval_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_contratado_id` (`plano_contratado_id`);

--
-- Índices de tabela `pagamento_config`
--
ALTER TABLE `pagamento_config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pagamento_config_geracao`
--
ALTER TABLE `pagamento_config_geracao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pagamento_faturas`
--
ALTER TABLE `pagamento_faturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_fatura` (`numero_fatura`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `contrato_id` (`contrato_id`),
  ADD KEY `plano_contratado_id` (`plano_contratado_id`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `pagamento_fatura_itens`
--
ALTER TABLE `pagamento_fatura_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fatura_id` (`fatura_id`);

--
-- Índices de tabela `pagamento_logs`
--
ALTER TABLE `pagamento_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `fatura_id` (`fatura_id`),
  ADD KEY `transacao_id` (`transacao_id`);

--
-- Índices de tabela `pagamento_transacoes`
--
ALTER TABLE `pagamento_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transacao_id` (`transacao_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `contrato_id` (`contrato_id`),
  ADD KEY `plano_contratado_id` (`plano_contratado_id`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `pagamento_webhooks`
--
ALTER TABLE `pagamento_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transacao_id` (`transacao_id`),
  ADD KEY `processado` (`processado`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `planos_caracteristicas`
--
ALTER TABLE `planos_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `planos_categorias`
--
ALTER TABLE `planos_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `planos_contratados`
--
ALTER TABLE `planos_contratados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `politica_historico`
--
ALTER TABLE `politica_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `politica_id` (`politica_id`);

--
-- Índices de tabela `politica_privacidade`
--
ALTER TABLE `politica_privacidade`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `politica_secoes`
--
ALTER TABLE `politica_secoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `politica_id` (`politica_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_categorias`
--
ALTER TABLE `blog_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_curtidas`
--
ALTER TABLE `blog_curtidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_newsletter`
--
ALTER TABLE `blog_newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_assinaturas`
--
ALTER TABLE `cliente_assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_assinaturas_contratos`
--
ALTER TABLE `cliente_assinaturas_contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_documentos`
--
ALTER TABLE `cliente_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_documentos_categorias`
--
ALTER TABLE `cliente_documentos_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_faturas`
--
ALTER TABLE `cliente_faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_logs`
--
ALTER TABLE `cliente_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_sistemas`
--
ALTER TABLE `cliente_sistemas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_sistemas_historico`
--
ALTER TABLE `cliente_sistemas_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_suporte_mensagens`
--
ALTER TABLE `cliente_suporte_mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_suporte_tickets`
--
ALTER TABLE `cliente_suporte_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_tokens`
--
ALTER TABLE `cliente_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `config_whatsapp`
--
ALTER TABLE `config_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contrato_clausulas`
--
ALTER TABLE `contrato_clausulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contrato_historico`
--
ALTER TABLE `contrato_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `indicacoes`
--
ALTER TABLE `indicacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `indicacoes_config`
--
ALTER TABLE `indicacoes_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `indicacoes_historico`
--
ALTER TABLE `indicacoes_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `metricas_empresas`
--
ALTER TABLE `metricas_empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `newsletter_campanhas`
--
ALTER TABLE `newsletter_campanhas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `newsletter_config`
--
ALTER TABLE `newsletter_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `newsletter_envios`
--
ALTER TABLE `newsletter_envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `newsletter_inscritos`
--
ALTER TABLE `newsletter_inscritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_assinaturas`
--
ALTER TABLE `pagamento_assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_config`
--
ALTER TABLE `pagamento_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_config_geracao`
--
ALTER TABLE `pagamento_config_geracao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_faturas`
--
ALTER TABLE `pagamento_faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_fatura_itens`
--
ALTER TABLE `pagamento_fatura_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_logs`
--
ALTER TABLE `pagamento_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_transacoes`
--
ALTER TABLE `pagamento_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_webhooks`
--
ALTER TABLE `pagamento_webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos_caracteristicas`
--
ALTER TABLE `planos_caracteristicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos_categorias`
--
ALTER TABLE `planos_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos_contratados`
--
ALTER TABLE `planos_contratados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `politica_historico`
--
ALTER TABLE `politica_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `politica_privacidade`
--
ALTER TABLE `politica_privacidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `politica_secoes`
--
ALTER TABLE `politica_secoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`);

--
-- Restrições para tabelas `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  ADD CONSTRAINT `blog_comentarios_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `blog_curtidas`
--
ALTER TABLE `blog_curtidas`
  ADD CONSTRAINT `blog_curtidas_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `blog_categorias` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_indicado` FOREIGN KEY (`indicado_por`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `cliente_assinaturas`
--
ALTER TABLE `cliente_assinaturas`
  ADD CONSTRAINT `cliente_assinaturas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_assinaturas_contratos`
--
ALTER TABLE `cliente_assinaturas_contratos`
  ADD CONSTRAINT `cliente_assinaturas_contratos_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_assinaturas_contratos_ibfk_2` FOREIGN KEY (`assinatura_id`) REFERENCES `cliente_assinaturas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_documentos`
--
ALTER TABLE `cliente_documentos`
  ADD CONSTRAINT `cliente_documentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_faturas`
--
ALTER TABLE `cliente_faturas`
  ADD CONSTRAINT `cliente_faturas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_faturas_ibfk_2` FOREIGN KEY (`plano_contratado_id`) REFERENCES `planos_contratados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_faturas_ibfk_3` FOREIGN KEY (`transacao_id`) REFERENCES `pagamento_transacoes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `cliente_logs`
--
ALTER TABLE `cliente_logs`
  ADD CONSTRAINT `cliente_logs_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_sistemas`
--
ALTER TABLE `cliente_sistemas`
  ADD CONSTRAINT `cliente_sistemas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_sistemas_ibfk_2` FOREIGN KEY (`plano_contratado_id`) REFERENCES `planos_contratados` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_sistemas_historico`
--
ALTER TABLE `cliente_sistemas_historico`
  ADD CONSTRAINT `cliente_sistemas_historico_ibfk_1` FOREIGN KEY (`sistema_id`) REFERENCES `cliente_sistemas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_sistemas_historico_ibfk_2` FOREIGN KEY (`alterado_por`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `cliente_suporte_mensagens`
--
ALTER TABLE `cliente_suporte_mensagens`
  ADD CONSTRAINT `cliente_suporte_mensagens_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `cliente_suporte_tickets` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_suporte_tickets`
--
ALTER TABLE `cliente_suporte_tickets`
  ADD CONSTRAINT `cliente_suporte_tickets_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_tokens`
--
ALTER TABLE `cliente_tokens`
  ADD CONSTRAINT `cliente_tokens_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`plano_contratado_id`) REFERENCES `planos_contratados` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `contrato_historico`
--
ALTER TABLE `contrato_historico`
  ADD CONSTRAINT `contrato_historico_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `indicacoes`
--
ALTER TABLE `indicacoes`
  ADD CONSTRAINT `indicacoes_ibfk_1` FOREIGN KEY (`indicador_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `indicacoes_ibfk_2` FOREIGN KEY (`indicado_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `indicacoes_historico`
--
ALTER TABLE `indicacoes_historico`
  ADD CONSTRAINT `indicacoes_historico_ibfk_1` FOREIGN KEY (`indicacao_id`) REFERENCES `indicacoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `newsletter_envios`
--
ALTER TABLE `newsletter_envios`
  ADD CONSTRAINT `newsletter_envios_ibfk_1` FOREIGN KEY (`campanha_id`) REFERENCES `newsletter_campanhas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `newsletter_envios_ibfk_2` FOREIGN KEY (`inscrito_id`) REFERENCES `newsletter_inscritos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pagamento_assinaturas`
--
ALTER TABLE `pagamento_assinaturas`
  ADD CONSTRAINT `pagamento_assinaturas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagamento_assinaturas_ibfk_2` FOREIGN KEY (`plano_contratado_id`) REFERENCES `planos_contratados` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pagamento_fatura_itens`
--
ALTER TABLE `pagamento_fatura_itens`
  ADD CONSTRAINT `pagamento_fatura_itens_ibfk_1` FOREIGN KEY (`fatura_id`) REFERENCES `pagamento_faturas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pagamento_transacoes`
--
ALTER TABLE `pagamento_transacoes`
  ADD CONSTRAINT `pagamento_transacoes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagamento_transacoes_ibfk_2` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pagamento_transacoes_ibfk_3` FOREIGN KEY (`plano_contratado_id`) REFERENCES `planos_contratados` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `planos`
--
ALTER TABLE `planos`
  ADD CONSTRAINT `planos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `planos_categorias` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `planos_caracteristicas`
--
ALTER TABLE `planos_caracteristicas`
  ADD CONSTRAINT `planos_caracteristicas_ibfk_1` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `planos_contratados`
--
ALTER TABLE `planos_contratados`
  ADD CONSTRAINT `planos_contratados_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `planos_contratados_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `politica_historico`
--
ALTER TABLE `politica_historico`
  ADD CONSTRAINT `politica_historico_ibfk_1` FOREIGN KEY (`politica_id`) REFERENCES `politica_privacidade` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `politica_secoes`
--
ALTER TABLE `politica_secoes`
  ADD CONSTRAINT `politica_secoes_ibfk_1` FOREIGN KEY (`politica_id`) REFERENCES `politica_privacidade` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
