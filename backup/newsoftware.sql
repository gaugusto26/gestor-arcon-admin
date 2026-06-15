-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/02/2026 às 01:44
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
-- Banco de dados: `newsoftware`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_atualizar_metricas_mensais` ()   BEGIN
    DECLARE v_ano INT;
    DECLARE v_mes INT;
    
    SET v_ano = YEAR(CURDATE());
    SET v_mes = MONTH(CURDATE());
    
    INSERT INTO metricas_empresas (ano, mes, total_empresas, novas_empresas, empresas_ativas, empresas_inativas, 
                                   receita_total, receita_mensal_total, ticket_medio,
                                   contratos_novos, contratos_renovados, contratos_cancelados)
    SELECT 
        v_ano, v_mes,
        (SELECT COUNT(*) FROM clientes) as total_empresas,
        (SELECT COUNT(*) FROM clientes WHERE MONTH(created_at) = v_mes AND YEAR(created_at) = v_ano) as novas_empresas,
        (SELECT COUNT(*) FROM clientes WHERE status = 'ativo') as empresas_ativas,
        (SELECT COUNT(*) FROM clientes WHERE status = 'inativo') as empresas_inativas,
        (SELECT COALESCE(SUM(valor_total), 0) FROM contratos WHERE MONTH(created_at) = v_mes AND YEAR(created_at) = v_ano) as receita_total,
        (SELECT COALESCE(SUM(valor_mensal), 0) FROM planos_contratados WHERE status = 'ativo') as receita_mensal_total,
        (SELECT COALESCE(AVG(valor_total), 0) FROM contratos WHERE MONTH(created_at) = v_mes AND YEAR(created_at) = v_ano) as ticket_medio,
        (SELECT COUNT(*) FROM contratos WHERE tipo_contrato = 'adesao' AND MONTH(created_at) = v_mes AND YEAR(created_at) = v_ano) as contratos_novos,
        (SELECT COUNT(*) FROM contratos WHERE tipo_contrato = 'renovacao' AND MONTH(created_at) = v_mes AND YEAR(created_at) = v_ano) as contratos_renovados,
        (SELECT COUNT(*) FROM contratos WHERE tipo_contrato = 'cancelamento' AND MONTH(created_at) = v_mes AND YEAR(created_at) = v_ano) as contratos_cancelados
    ON DUPLICATE KEY UPDATE
        total_empresas = VALUES(total_empresas),
        novas_empresas = VALUES(novas_empresas),
        empresas_ativas = VALUES(empresas_ativas),
        empresas_inativas = VALUES(empresas_inativas),
        receita_total = VALUES(receita_total),
        receita_mensal_total = VALUES(receita_mensal_total),
        ticket_medio = VALUES(ticket_medio),
        contratos_novos = VALUES(contratos_novos),
        contratos_renovados = VALUES(contratos_renovados),
        contratos_cancelados = VALUES(contratos_cancelados);
END$$

DELIMITER ;

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

--
-- Despejando dados para a tabela `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `acao`, `ip`, `user_agent`, `data_hora`) VALUES
(1, 1, 'Editou plano: Sistema de Gestão de RHs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 21:57:35'),
(2, 1, 'Editou plano: Sistema de Gestão de RH', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 21:59:28');

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

--
-- Despejando dados para a tabela `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `nome_completo`, `email`, `ip_permitido`, `ultimo_acesso`, `tentativas_falhas`, `bloqueado_ate`, `criado_em`) VALUES
(1, 'renan', '$2a$12$T8xPzIlSbNp6b7GTfA4Hweev57iPp8N67DcMbEcs1ZFmIZXZd5Uey', 'RENAN', 'admin@ntw.com', '::1', NULL, 0, NULL, '2026-02-19 22:42:53');

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

--
-- Despejando dados para a tabela `blog_categorias`
--

INSERT INTO `blog_categorias` (`id`, `nome`, `slug`, `descricao`, `icone`, `cor`, `ordem`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Tecnologia', 'tecnologia', 'Novidades e tendências do mundo da tecnologia', 'fa-microchip', '#3b82f6', 1, 1, '2026-02-20 18:36:19', NULL),
(2, 'Negócios', 'negocios', 'Dicas e estratégias para empreendedores', 'fa-briefcase', '#10b981', 2, 1, '2026-02-20 18:36:19', NULL),
(3, 'Programação', 'programacao', 'Tutoriais e dicas de desenvolvimento', 'fa-code', '#f59e0b', 3, 1, '2026-02-20 18:36:19', NULL),
(4, 'Marketing Digital', 'marketing-digital', 'Estratégias de marketing para alavancar seu negócio', 'fa-chart-line', '#ec4899', 4, 1, '2026-02-20 18:36:19', NULL),
(5, 'Inteligência Artificial', 'inteligencia-artificial', 'O mundo da IA e suas aplicações', 'fa-brain', '#8b5cf6', 5, 1, '2026-02-20 18:36:19', NULL);

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

--
-- Despejando dados para a tabela `blog_curtidas`
--

INSERT INTO `blog_curtidas` (`id`, `post_id`, `ip`, `session_id`, `data_curtida`) VALUES
(1, 3, '::1', 'nbs468ufanrd1bln54imq6n9vk', '2026-02-20 20:25:48');

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

--
-- Despejando dados para a tabela `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `categoria_id`, `titulo`, `slug`, `subtitulo`, `conteudo`, `resumo`, `imagem_destaque`, `imagem_og`, `autor`, `autor_avatar`, `views`, `tempo_leitura`, `destaque`, `status`, `data_publicacao`, `data_agendamento`, `tags`, `meta_title`, `meta_description`, `meta_keywords`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Como a Inteligência Artificial está revolucionando o desenvolvimento de software', 'ia-revolucionando-desenvolvimento-software', 'Descubra como a IA está mudando a forma como criamos software', '<h2>A Revolução da IA no Desenvolvimento</h2><p>A inteligência artificial está transformando completamente a indústria de desenvolvimento de software. Desde assistentes de código como GitHub Copilot até ferramentas de teste automatizado, a IA está aumentando a produtividade dos desenvolvedores em até 40%.</p><h3>Principais áreas de impacto:</h3><ul><li><strong>Geração de código:</strong> Ferramentas que sugerem e completam código automaticamente</li><li><strong>Testes automatizados:</strong> IA criando casos de teste e identificando bugs</li><li><strong>Documentação:</strong> Geração automática de documentação técnica</li><li><strong>Otimização:</strong> Código mais eficiente e performático</li></ul><p>Na NTW, já estamos utilizando essas tecnologias para entregar soluções mais rápidas e eficientes para nossos clientes.</p>', 'A inteligência artificial está transformando o desenvolvimento de software, aumentando produtividade e qualidade. Veja como essa revolução pode beneficiar seu negócio.', NULL, NULL, 'Renan', NULL, 235, 5, 1, 'publicado', '2026-02-15 10:00:00', NULL, 'ia, inteligência artificial, desenvolvimento, software, programação', NULL, NULL, NULL, 1, '2026-02-20 18:36:19', '2026-02-20 20:03:51'),
(2, 2, '5 Estratégias de Marketing Digital para Pequenas Empresas', 'estrategias-marketing-digital-pequenas-empresas', 'Marketing digital acessível para microempreendedores', '<p>Pequenas empresas muitas vezes acham que marketing digital é caro e complicado. Mas com as estratégias certas, é possível obter ótimos resultados com orçamentos enxutos.</p><h3>1. Invista em SEO Local</h3><p>Otimize seu site para buscas locais. Cadastre sua empresa no Google Meu Negócio e incentive avaliações positivas.</p><h3>2. Marketing de Conteúdo</h3><p>Crie conteúdo relevante para seu público. Blog posts, vídeos e infográficos atraem visitantes qualificados.</p><h3>3. Redes Sociais Estratégicas</h3><p>Escolha as redes onde seu público está. Não precisa estar em todas, mas sim nas que importam.</p><h3>4. E-mail Marketing</h3><p>Construa uma lista de contatos e mantenha relacionamento com seus leads.</p><h3>5. Parcerias Locais</h3><p>Faça parcerias com outras empresas locais para campanhas conjuntas.</p>', 'Estratégias simples e eficientes de marketing digital para pequenas empresas que querem crescer sem gastar muito.', NULL, NULL, 'Renan', NULL, 157, 4, 1, 'publicado', '2026-02-18 14:30:00', NULL, 'marketing digital, pequenas empresas, seo, redes sociais, e-mail marketing', NULL, NULL, NULL, 1, '2026-02-20 18:36:19', '2026-02-20 19:53:52'),
(3, 3, 'PHP 8.4: O que há de novo na linguagem', 'php-8-4-novidades', 'As principais novidades da nova versão do PHP', '<p>O PHP 8.4 chegou trazendo melhorias significativas de performance e novas funcionalidades que vão facilitar a vida dos desenvolvedores.</p><h3>Principais novidades:</h3><ul><li><strong>Property Hooks:</strong> Similar a propriedades computadas em outras linguagens</li><li><strong>Nova sintaxe para arrays:</strong> Mais concisa e legível</li><li><strong>Melhorias no JIT:</strong> Aumento de performance em até 15%</li><li><strong>Tipos avançados:</strong> Suporte a tipos mais complexos</li></ul><p>Na NTW, já estamos atualizando nossos projetos para aproveitar essas melhorias.</p>', 'Conheça as principais novidades do PHP 8.4 e como elas podem melhorar seus projetos.', NULL, NULL, 'Renan', NULL, 90, 3, 0, 'publicado', '2026-02-20 09:15:00', NULL, 'php, programação, desenvolvimento web, novidades', NULL, NULL, NULL, 1, '2026-02-20 18:36:19', '2026-02-20 20:25:35'),
(4, 5, 'Chatbots com IA: Como automatizar o atendimento ao cliente', 'chatbots-ia-automatizar-atendimento-cliente', 'Guia completo para implementar chatbots inteligentes', '<p>Chatbots com inteligência artificial estão revolucionando o atendimento ao cliente. Eles podem resolver até 80% das dúvidas comuns sem intervenção humana.</p><h3>Benefícios:</h3><ul><li>Atendimento 24/7 sem pausas</li><li>Respostas instantâneas</li><li>Redução de custos operacionais</li><li>Escalabilidade ilimitada</li><li>Coleta de dados valiosos</li></ul><h3>Como implementar:</h3><p>Na NTW, desenvolvemos chatbots personalizados para cada negócio. Treinamos a IA com as informações da sua empresa e integramos com WhatsApp, site e outras plataformas.</p>', 'Aprenda como chatbots com IA podem automatizar seu atendimento e melhorar a experiência do cliente.', NULL, NULL, 'Renan', NULL, 313, 6, 1, 'publicado', '2026-02-19 11:20:00', NULL, 'chatbot, ia, inteligência artificial, atendimento, automação', NULL, NULL, NULL, 1, '2026-02-20 18:36:19', '2026-02-21 16:53:06'),
(5, 4, 'Como criar um site que converte visitantes em clientes', 'site-converte-visitantes-clientes', 'O guia definitivo para landing pages de alta conversão', '<p>Um site bonito não é suficiente. Ele precisa converter visitantes em clientes. Aqui estão as principais estratégias para aumentar suas conversões.</p><h3>Elementos essenciais:</h3><ul><li><strong>Call-to-action claro:</strong> Botões que dizem exatamente o que fazer</li><li><strong>Prova social:</strong> Depoimentos e casos de sucesso</li><li><strong>Velocidade:</strong> Sites lentos perdem até 40% dos visitantes</li><li><strong>Mobile-first:</strong> Mais de 60% dos acessos são mobile</li><li><strong>Formulários simples:</strong> Peça apenas o necessário</li></ul><p>Na NTW, criamos sites otimizados para conversão, com design responsivo e foco em resultados.</p>', 'Descubra as melhores práticas para criar um site que realmente converte visitantes em clientes.', NULL, NULL, 'Renan', NULL, 179, 4, 0, 'publicado', '2026-02-17 16:45:00', NULL, 'sites, conversão, landing page, marketing digital, web design', NULL, NULL, NULL, 1, '2026-02-20 18:36:19', '2026-02-20 20:29:43');

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

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `username`, `codigo_indicacao`, `indicado_por`, `total_indicacoes`, `desconto_indicacao`, `validade_desconto`, `password_hash`, `telefone`, `celular`, `cpf_cnpj`, `rg_ie`, `data_nascimento`, `empresa`, `cargo`, `endereco`, `cidade`, `estado`, `cep`, `observacoes`, `foto`, `status`, `tipo`, `created_at`, `updated_at`, `ultimo_acesso`, `tentativas_falhas`, `bloqueado_ate`, `token_recuperacao`, `token_expira`) VALUES
(2, 'JOSE WILSON RODRIGUES ALMEIDAsaas', 'universepett@gmail.com', 'josewilsonrodriguesalmeidasaas373', NULL, NULL, 0, 0.00, NULL, '$2y$10$QmA8NoU3ZhB9gyLKn2Zxme1uZtFBiCIT7F5hbN3k/y3qHqq.DXAmK', '19987115342', '19987111652', '432543563565', '6546546456', '1990-11-11', 'GASEO1', 'OWNER1', 'RUA PEDRO HEREMAN, Casa1', 'Engenheiro Coelho1', 'SP', '13445011', 'dsa', NULL, 'ativo', 'cliente', '2026-02-21 20:36:08', '2026-02-26 21:06:57', '2026-02-26 18:06:57', 0, NULL, NULL, NULL),
(5, 'Renan Barbosa', 'renangaseo@gmail.com', 'renanbarbosa995', 'TWOSY0005', NULL, 0, 0.00, NULL, '$2y$10$s/bng9j1/iEaKB0KZeZKSOoJRRQK9lennVnFf3aSym1s4j5cuzTm6', '19997604545', '19987111652', '55749483894', '6546546456', '1990-11-11', 'TWO SYSTEM', 'CHEFE', 'RUA PEDRO HEREMAN, Casa1', 'Engenheiro Coelho1', 'SP', '134450111', '', NULL, 'ativo', 'cliente', '2026-02-22 15:20:24', '2026-02-22 15:20:24', NULL, 0, NULL, NULL, NULL);

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

--
-- Despejando dados para a tabela `cliente_assinaturas`
--

INSERT INTO `cliente_assinaturas` (`id`, `cliente_id`, `nome_assinatura`, `assinatura_base64`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 2, 'test', 'data:image/png;base64,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', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 19:41:58', '2026-02-24 19:44:20');

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

--
-- Despejando dados para a tabela `cliente_assinaturas_contratos`
--

INSERT INTO `cliente_assinaturas_contratos` (`id`, `contrato_id`, `assinatura_id`, `ip`, `user_agent`, `data_assinatura`, `created_at`) VALUES
(1, 9, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 20:43:49', '2026-02-24 19:43:49'),
(2, 4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 20:46:32', '2026-02-24 19:46:32'),
(3, 3, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 20:48:07', '2026-02-24 19:48:07'),
(4, 10, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 22:18:38', '2026-02-24 21:18:38'),
(5, 11, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 22:22:53', '2026-02-24 21:22:53'),
(6, 12, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 01:35:50', '2026-02-25 00:35:50'),
(7, 13, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 23:23:21', '2026-02-26 22:23:21'),
(8, 14, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 02:53:58', '2026-02-27 01:53:58'),
(9, 16, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 02:55:22', '2026-02-27 01:55:22'),
(10, 17, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-27 03:17:01', '2026-02-27 02:17:01');

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

--
-- Despejando dados para a tabela `cliente_documentos`
--

INSERT INTO `cliente_documentos` (`id`, `cliente_id`, `tipo`, `titulo`, `descricao`, `arquivo_nome`, `arquivo_path`, `arquivo_tamanho`, `arquivo_tipo`, `referencia_id`, `referencia_numero`, `data_documento`, `data_vencimento`, `valor`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'contrato', 'Contrato NTW-202602-5945', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-5945.pdf', '/uploads/contratos/contrato_3.pdf', NULL, NULL, 3, 'NTW-202602-5945', '2026-02-21', NULL, 770.00, '', '2026-02-21 21:52:53', NULL),
(2, 2, 'contrato', 'Contrato NTW-202602-3817', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-3817.pdf', '/uploads/contratos/contrato_4.pdf', NULL, NULL, 4, 'NTW-202602-3817', '2026-02-21', NULL, 800.00, '', '2026-02-21 22:02:37', NULL),
(3, 2, 'contrato', 'Contrato NTW-202602-3516', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-3516.pdf', '/uploads/contratos/contrato_9.pdf', NULL, NULL, 9, 'NTW-202602-3516', '2026-02-21', NULL, 980.00, '', '2026-02-21 23:26:12', NULL),
(4, 2, 'contrato', 'Contrato NTW-202602-3053', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-3053.pdf', '/uploads/contratos/contrato_10.pdf', NULL, NULL, 10, 'NTW-202602-3053', '2026-02-24', NULL, 980.00, '', '2026-02-24 21:07:34', NULL),
(5, 2, 'contrato', 'Contrato NTW-202602-6602', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-6602.pdf', '/uploads/contratos/contrato_11.pdf', NULL, NULL, 11, 'NTW-202602-6602', '2026-02-24', NULL, 900.00, '', '2026-02-24 21:22:31', NULL),
(6, 2, 'contrato', 'Contrato NTW-202602-6254', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-6254.pdf', '/uploads/contratos/contrato_12.pdf', NULL, NULL, 12, 'NTW-202602-6254', '2026-02-24', NULL, 2000.00, '', '2026-02-25 00:35:18', NULL),
(8, 2, 'contrato', 'Contrato NTW-202602-5828', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-5828.pdf', '/uploads/contratos/contrato_13.pdf', NULL, NULL, 13, 'NTW-202602-5828', '2026-02-26', NULL, 900.00, 'ativo', '2026-02-26 22:22:41', NULL),
(9, 2, 'contrato', 'Contrato NTW-202602-5528', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-5528.pdf', '/uploads/contratos/contrato_14.pdf', NULL, NULL, 14, 'NTW-202602-5528', '2026-02-26', NULL, 430.00, 'ativo', '2026-02-27 01:53:28', NULL),
(10, 2, 'contrato', 'Contrato TEST-20260226-225439', 'Contrato de Teste Automático', 'contrato_TEST-20260226-225439.pdf', '/uploads/contratos/contrato_15.pdf', NULL, NULL, 15, 'TEST-20260226-225439', '2026-02-26', NULL, 1000.00, 'ativo', '2026-02-27 01:54:39', NULL),
(11, 2, 'contrato', 'Contrato NTW-202602-9057', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-9057.pdf', '/uploads/contratos/contrato_16.pdf', NULL, NULL, 16, 'NTW-202602-9057', '2026-02-26', NULL, 2200.00, 'ativo', '2026-02-27 01:54:59', NULL),
(12, 2, 'contrato', 'Contrato NTW-202602-3107', 'Contrato de Prestação de Serviços de Tecnologia', 'contrato_NTW-202602-3107.pdf', '/uploads/contratos/contrato_17.pdf', NULL, NULL, 17, 'NTW-202602-3107', '2026-02-26', NULL, 490.00, 'ativo', '2026-02-27 02:16:05', NULL);

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

--
-- Despejando dados para a tabela `cliente_documentos_categorias`
--

INSERT INTO `cliente_documentos_categorias` (`id`, `nome`, `icone`, `cor`, `ordem`, `created_at`) VALUES
(1, 'Contratos', 'fa-file-contract', '#4361ee', 1, '2026-02-26 22:15:43'),
(2, 'Faturas', 'fa-file-invoice-dollar', '#10b981', 2, '2026-02-26 22:15:43'),
(3, 'Notas Fiscais', 'fa-file-invoice', '#f97316', 3, '2026-02-26 22:15:43'),
(4, 'Recibos', 'fa-receipt', '#8b5cf6', 4, '2026-02-26 22:15:43'),
(5, 'Propostas', 'fa-file-signature', '#ec4899', 5, '2026-02-26 22:15:43');

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

--
-- Despejando dados para a tabela `cliente_logs`
--

INSERT INTO `cliente_logs` (`id`, `cliente_id`, `acao`, `ip`, `user_agent`, `data_hora`) VALUES
(2, 2, 'Cliente cadastrado via admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 17:36:08'),
(4, 2, 'E-mail enviado: Bem-vindo à NTW - New Software!', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 17:56:23'),
(5, 2, 'Senha resetada pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 17:57:42'),
(6, 2, 'Usuário bloqueado pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 17:58:30'),
(7, 2, 'Usuário bloqueado pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 17:59:10'),
(8, 2, 'Senha resetada pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 17:59:31'),
(9, 2, 'Usuário ativado pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:27:15'),
(10, 2, 'Senha resetada pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:28:36'),
(11, 2, 'Senha resetada pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:35:51'),
(12, 2, 'Senha resetada pelo admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:38:30'),
(15, 5, 'Cliente cadastrado via admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 12:20:24'),
(16, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 20:22:12'),
(17, 2, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 20:22:44'),
(18, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 20:36:45'),
(19, 2, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 22:44:50'),
(20, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 22:45:35'),
(21, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 16:17:26'),
(22, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 21:27:58'),
(23, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 18:06:57');

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

--
-- Despejando dados para a tabela `cliente_sistemas`
--

INSERT INTO `cliente_sistemas` (`id`, `cliente_id`, `plano_contratado_id`, `nome_sistema`, `descricao`, `url_acesso`, `login`, `senha`, `status`, `etapa_atual`, `proxima_etapa`, `feedback_cliente`, `responsavel`, `equipe`, `repositorio_url`, `ambiente_teste_url`, `ambiente_producao_url`, `ultima_atualizacao`, `proximo_passo`, `percentual_concluido`, `data_inicio`, `data_previsao_entrega`, `data_entrega`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 2, 0, 'Contrato de Prestação de Serviços de Tecnologia', 'Contrato nº NTW-202602-5945', NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-24', NULL, NULL, NULL, '2026-02-21 21:52:53', NULL),
(2, 2, 0, 'Contrato de Prestação de Serviços de Tecnologia', 'Contrato nº NTW-202602-3817', NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-24', NULL, NULL, NULL, '2026-02-21 22:02:37', NULL),
(3, 2, 0, 'Contrato de Prestação de Serviços de Tecnologia', 'Contrato nº NTW-202602-3516', NULL, NULL, NULL, 'reuniao_inicial', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 18:18:06', NULL, 2, '2026-02-24', NULL, NULL, NULL, '2026-02-21 23:26:12', '2026-02-24 21:18:06'),
(4, 2, 2, 'Contrato de Prestação de Serviços de Tecnologia', NULL, NULL, NULL, NULL, 'desenvolvimento_backend', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 18:38:45', NULL, 30, '2026-02-24', NULL, NULL, NULL, '2026-02-24 21:22:53', '2026-02-24 21:38:45'),
(5, 2, 3, 'Contrato de Prestação de Serviços de Tecnologia', NULL, NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-24', NULL, NULL, NULL, '2026-02-25 00:35:50', NULL),
(6, 2, 4, 'Contrato de Prestação de Serviços de Tecnologia', NULL, NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-26', NULL, NULL, NULL, '2026-02-26 22:23:21', NULL),
(7, 2, 5, 'Contrato de Prestação de Serviços de Tecnologia', NULL, NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-26', NULL, NULL, NULL, '2026-02-27 01:53:58', NULL),
(8, 2, 6, 'Contrato de Prestação de Serviços de Tecnologia', NULL, NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-26', NULL, NULL, NULL, '2026-02-27 01:55:22', NULL),
(9, 2, 7, 'Contrato de Prestação de Serviços de Tecnologia', NULL, NULL, NULL, NULL, 'aguardando_inicio', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-02-26', NULL, NULL, NULL, '2026-02-27 02:17:01', NULL);

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

--
-- Despejando dados para a tabela `cliente_sistemas_historico`
--

INSERT INTO `cliente_sistemas_historico` (`id`, `sistema_id`, `status_anterior`, `status_novo`, `observacao`, `alterado_por`, `ip`, `created_at`) VALUES
(1, 3, 'aguardando_inicio', 'reuniao_inicial', '', 1, '::1', '2026-02-24 21:14:38'),
(2, 3, 'aguardando_inicio', 'reuniao_inicial', '', 1, '::1', '2026-02-24 21:16:01'),
(3, 3, 'aguardando_inicio', 'reuniao_inicial', '', 1, '::1', '2026-02-24 21:18:06'),
(4, 4, 'aguardando_inicio', 'reuniao_inicial', '', 1, '::1', '2026-02-24 21:28:41'),
(5, 4, 'reuniao_inicial', 'desenvolvimento_backend', '', 1, '::1', '2026-02-24 21:38:45');

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

--
-- Despejando dados para a tabela `config_whatsapp`
--

INSERT INTO `config_whatsapp` (`id`, `numero`, `mensagem_padrao`, `ativo`, `created_at`, `updated_at`) VALUES
(1, '5519987111656', 'Olá! Tenho interesse no plano: {plano_nome}', 1, '2026-02-20 00:27:16', NULL);

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
-- Despejando dados para a tabela `contratos`
--

INSERT INTO `contratos` (`id`, `cliente_id`, `plano_contratado_id`, `tipo_contrato`, `numero_contrato`, `versao`, `titulo`, `conteudo`, `valor_total`, `valor_entrada`, `valor_mensal`, `numero_parcelas`, `dia_vencimento`, `data_primeira_parcela`, `data_primeira_mensalidade`, `multa_cancelamento`, `percentual_multa`, `prazo_fidelidade`, `data_assinatura`, `data_vencimento`, `data_cancelamento`, `motivo_cancelamento`, `status`, `pdf_path`, `observacoes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 'adesao', 'NTW-202602-4388', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n                        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n                        \n                        <p style=\"text-align: right;\">Contrato nº: <span id=\"preview-numero\">NTW-202602-9933</span></p>\n                        \n                        <p><strong>CONTRATANTE:</strong> <span id=\"preview-contratante\">[NOME DO CLIENTE]</span>, inscrito no CPF/CNPJ sob nº <span id=\"preview-cpf\">[DOCUMENTO]</span>, com endereço <span id=\"preview-endereco\">[ENDEREÇO]</span>.</p>\n                        \n                        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA], doravante denominada simplesmente CONTRATADA.</p>\n                        \n                        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n                        \n                        <div id=\"preview-clausulas\">\n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 0,00</strong> (0,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 0,00</strong> (0,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até {numero_parcelas}x, com vencimento da primeira parcela em {data_primeira_parcela}.</p>\n<p>2.4. As mensalidades vencerão todo dia {dia_vencimento} de cada mês, com início em {data_primeira_mensalidade}.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 0,00</strong> (0,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        </div>\n                        \n                        <div style=\"margin-top: 50px;\">\n                            <p>Por estarem assim justos e contratados, firmam o presente instrumento em 2 (duas) vias de igual teor e forma, para que produza seus jurídicos e legais efeitos.</p>\n                            \n                            <div style=\"display: flex; justify-content: space-between; margin-top: 80px;\">\n                                <div style=\"text-align: center;\">\n                                    <p>____________________________</p>\n                                    <p><strong>CONTRATANTE</strong></p>\n                                </div>\n                                <div style=\"text-align: center;\">\n                                    <p>____________________________</p>\n                                    <p><strong>CONTRATADA</strong></p>\n                                </div>\n                            </div>\n                            \n                            <p style=\"text-align: center; margin-top: 50px;\">[CIDADE], <span id=\"preview-data\">21 de February de 2026</span></p>\n                        </div>\n                    ', 770.00, NULL, 150.00, 1, 10, NULL, NULL, 154.00, 20.00, 12, NULL, NULL, NULL, NULL, 'rascunho', NULL, '', NULL, '2026-02-21 21:52:07', NULL),
(2, 2, NULL, 'adesao', 'NTW-202602-5564', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n                        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n                        \n                        <p style=\"text-align: right;\">Contrato nº: <span id=\"preview-numero\">NTW-202602-9933</span></p>\n                        \n                        <p><strong>CONTRATANTE:</strong> <span id=\"preview-contratante\">[NOME DO CLIENTE]</span>, inscrito no CPF/CNPJ sob nº <span id=\"preview-cpf\">[DOCUMENTO]</span>, com endereço <span id=\"preview-endereco\">[ENDEREÇO]</span>.</p>\n                        \n                        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA], doravante denominada simplesmente CONTRATADA.</p>\n                        \n                        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n                        \n                        <div id=\"preview-clausulas\">\n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 0,00</strong> (0,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 0,00</strong> (0,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até {numero_parcelas}x, com vencimento da primeira parcela em {data_primeira_parcela}.</p>\n<p>2.4. As mensalidades vencerão todo dia {dia_vencimento} de cada mês, com início em {data_primeira_mensalidade}.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 0,00</strong> (0,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        </div>\n                        \n                        <div style=\"margin-top: 50px;\">\n                            <p>Por estarem assim justos e contratados, firmam o presente instrumento em 2 (duas) vias de igual teor e forma, para que produza seus jurídicos e legais efeitos.</p>\n                            \n                            <div style=\"display: flex; justify-content: space-between; margin-top: 80px;\">\n                                <div style=\"text-align: center;\">\n                                    <p>____________________________</p>\n                                    <p><strong>CONTRATANTE</strong></p>\n                                </div>\n                                <div style=\"text-align: center;\">\n                                    <p>____________________________</p>\n                                    <p><strong>CONTRATADA</strong></p>\n                                </div>\n                            </div>\n                            \n                            <p style=\"text-align: center; margin-top: 50px;\">[CIDADE], <span id=\"preview-data\">21 de February de 2026</span></p>\n                        </div>\n                    ', 770.00, NULL, 150.00, 1, 10, NULL, NULL, 154.00, 20.00, 12, NULL, NULL, NULL, NULL, 'rascunho', NULL, '', NULL, '2026-02-21 21:52:35', NULL),
(3, 2, NULL, 'adesao', 'NTW-202602-5945', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n                        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n                        \n                        <p style=\"text-align: right;\">Contrato nº: <span id=\"preview-numero\">NTW-202602-3748</span></p>\n                        \n                        <p><strong>CONTRATANTE:</strong> <span id=\"preview-contratante\">[NOME DO CLIENTE]</span>, inscrito no CPF/CNPJ sob nº <span id=\"preview-cpf\">[DOCUMENTO]</span>, com endereço <span id=\"preview-endereco\">[ENDEREÇO]</span>.</p>\n                        \n                        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA], doravante denominada simplesmente CONTRATADA.</p>\n                        \n                        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n                        \n                        <div id=\"preview-clausulas\">\n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 0,00</strong> (0,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 0,00</strong> (0,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até {numero_parcelas}x, com vencimento da primeira parcela em {data_primeira_parcela}.</p>\n<p>2.4. As mensalidades vencerão todo dia {dia_vencimento} de cada mês, com início em {data_primeira_mensalidade}.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 0,00</strong> (0,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        </div>\n                        \n                        <div style=\"margin-top: 50px;\">\n                            <p>Por estarem assim justos e contratados, firmam o presente instrumento em 2 (duas) vias de igual teor e forma, para que produza seus jurídicos e legais efeitos.</p>\n                            \n                            <div style=\"display: flex; justify-content: space-between; margin-top: 80px;\">\n                                <div style=\"text-align: center;\">\n                                    <p>____________________________</p>\n                                    <p><strong>CONTRATANTE</strong></p>\n                                </div>\n                                <div style=\"text-align: center;\">\n                                    <p>____________________________</p>\n                                    <p><strong>CONTRATADA</strong></p>\n                                </div>\n                            </div>\n                            \n                            <p style=\"text-align: center; margin-top: 50px;\">[CIDADE], <span id=\"preview-data\">21 de February de 2026</span></p>\n                        </div>\n                    ', 770.00, NULL, 111.00, 1, 10, NULL, NULL, 154.00, 20.00, 12, '2026-02-24 20:48:07', NULL, NULL, NULL, 'assinado', NULL, '', NULL, '2026-02-21 21:52:53', '2026-02-24 19:48:07'),
(4, 2, NULL, 'adesao', 'NTW-202602-3817', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-8705</p>\n        \n        <p><strong>CONTRATANTE:</strong> [NOME DO CLIENTE], inscrito no CPF/CNPJ sob nº [DOCUMENTO], com endereço [ENDEREÇO].</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ R$ 0,00</strong> (0,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ R$ 0,00</strong> (0,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até {numero_parcelas}x, com vencimento da primeira parcela em {data_primeira_parcela}.</p>\n<p>2.4. As mensalidades vencerão todo dia {dia_vencimento} de cada mês, com início em {data_primeira_mensalidade}.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ R$ 0,00</strong> (0,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong></p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            [CIDADE], 21 de fevereiro de 2026\n        </div>\n    ', 800.00, NULL, 200.00, 1, 10, NULL, NULL, 160.00, 20.00, 12, '2026-02-24 20:46:32', NULL, NULL, NULL, 'assinado', NULL, 'test', NULL, '2026-02-21 22:02:37', '2026-02-24 19:46:32');
INSERT INTO `contratos` (`id`, `cliente_id`, `plano_contratado_id`, `tipo_contrato`, `numero_contrato`, `versao`, `titulo`, `conteudo`, `valor_total`, `valor_entrada`, `valor_mensal`, `numero_parcelas`, `dia_vencimento`, `data_primeira_parcela`, `data_primeira_mensalidade`, `multa_cancelamento`, `percentual_multa`, `prazo_fidelidade`, `data_assinatura`, `data_vencimento`, `data_cancelamento`, `motivo_cancelamento`, `status`, `pdf_path`, `observacoes`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 2, NULL, 'adesao', 'NTW-202602-5673', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-6579</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 980,00</strong> (980,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 100,00</strong> (100,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até {numero_parcelas}x, com vencimento da primeira parcela em {data_primeira_parcela}.</p>\n<p>2.4. As mensalidades vencerão todo dia {dia_vencimento} de cada mês, com início em {data_primeira_mensalidade}.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 196,00</strong> (196,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            [CIDADE], 21 de fevereiro de 2026\n        </div>\n    ', 980.00, NULL, 100.00, 1, 10, NULL, NULL, 196.00, 20.00, 12, NULL, NULL, NULL, NULL, 'rascunho', NULL, 'valor a se pagar da multa 20%\n(caso aja cancelamento ou desentendimento)', NULL, '2026-02-21 22:41:20', NULL),
(6, 2, NULL, 'adesao', 'NTW-202602-6270', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-8689</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ R$ 800,00</strong> (800,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ R$ 100,10</strong> (100,10 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 23 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 23 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ R$ 160,00</strong> (160,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            [CIDADE], 21 de fevereiro de 2026\n        </div>\n    ', 800.00, NULL, 100.10, 1, 10, '0000-00-00', '2026-03-24', 160.00, 20.00, 12, NULL, NULL, NULL, NULL, 'rascunho', NULL, '', NULL, '2026-02-21 23:05:29', NULL),
(7, 2, NULL, 'adesao', 'NTW-202602-6629', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-9813</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ R$ 1900,00</strong> (1900,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ R$ 320,00</strong> (320,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 23 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 23 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ R$ 380,00</strong> (380,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            [CIDADE], 21 de fevereiro de 2026\n        </div>\n    ', 1900.00, NULL, 320.00, 1, 10, '0000-00-00', '2026-03-24', 380.00, 20.00, 12, NULL, NULL, NULL, NULL, 'rascunho', NULL, 'TEST', NULL, '2026-02-21 23:06:34', NULL),
(8, 2, NULL, 'adesao', 'NTW-202602-6155', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-5014</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ R$ 1700,00</strong> (1700,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ R$ 1,00</strong> (1,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 23 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 23 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ R$ 340,00</strong> (340,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            [CIDADE], 21 de fevereiro de 2026\n        </div>\n    ', 1700.00, NULL, 1.00, 1, 10, '2026-03-24', '2026-03-24', 340.00, 20.00, 12, NULL, NULL, NULL, NULL, 'rascunho', NULL, '', NULL, '2026-02-21 23:12:40', NULL),
(9, 2, NULL, 'adesao', 'NTW-202602-3516', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-3315</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 980,00</strong> (980,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 790,00</strong> (790,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 23 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 23 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 196,00</strong> (196,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de {cidade_foro} para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 21 de fevereiro de 2026\n        </div>\n    ', 980.00, NULL, 790.00, 1, 10, '2026-03-24', '2026-03-24', 196.00, 20.00, 12, '2026-02-24 20:43:49', NULL, NULL, NULL, 'assinado', NULL, 'TESTEST', NULL, '2026-02-21 23:26:12', '2026-02-24 19:43:49');
INSERT INTO `contratos` (`id`, `cliente_id`, `plano_contratado_id`, `tipo_contrato`, `numero_contrato`, `versao`, `titulo`, `conteudo`, `valor_total`, `valor_entrada`, `valor_mensal`, `numero_parcelas`, `dia_vencimento`, `data_primeira_parcela`, `data_primeira_mensalidade`, `multa_cancelamento`, `percentual_multa`, `prazo_fidelidade`, `data_assinatura`, `data_vencimento`, `data_cancelamento`, `motivo_cancelamento`, `status`, `pdf_path`, `observacoes`, `created_by`, `created_at`, `updated_at`) VALUES
(10, 2, 1, 'adesao', 'NTW-202602-3053', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-9085</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 980,00</strong> (980,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 230,00</strong> (230,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 25 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 25 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 196,00</strong> (196,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 24 de fevereiro de 2026\n        </div>\n    ', 980.00, NULL, 230.00, 1, 10, '2026-03-26', '2026-03-26', 196.00, 20.00, 12, '2026-02-24 22:18:38', NULL, NULL, NULL, 'assinado', NULL, 'test do plano', NULL, '2026-02-24 21:07:34', '2026-02-24 21:18:38'),
(11, 2, 2, 'adesao', 'NTW-202602-6602', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-2901</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 900,00</strong> (900,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 150,00</strong> (150,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 25 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 25 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 180,00</strong> (180,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 24 de fevereiro de 2026\n        </div>\n    ', 900.00, NULL, 150.00, 1, 10, '2026-03-26', '2026-03-26', 180.00, 20.00, 12, '2026-02-24 22:22:53', NULL, NULL, NULL, 'assinado', NULL, 'test assinatura bd alhskj', NULL, '2026-02-24 21:22:31', '2026-02-24 21:22:53'),
(12, 2, 3, 'adesao', 'NTW-202602-6254', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-6405</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 2000,00</strong> (2000,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 1500,00</strong> (1500,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 26 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 26 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 400,00</strong> (400,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 24 de fevereiro de 2026\n        </div>\n    ', 2000.00, NULL, 1500.00, 1, 10, '2026-03-27', '2026-03-27', 400.00, 20.00, 12, '2026-02-25 01:35:50', NULL, NULL, NULL, 'assinado', NULL, '', NULL, '2026-02-25 00:35:18', '2026-02-25 00:35:50'),
(13, 2, 4, 'adesao', 'NTW-202602-5828', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-6758</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 900,00</strong> (900,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 150,00</strong> (150,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 27 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 27 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 180,00</strong> (180,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 26 de fevereiro de 2026\n        </div>\n    ', 900.00, NULL, 150.00, 1, 10, '2026-03-28', '2026-03-28', 180.00, 20.00, 12, '2026-02-26 23:23:21', NULL, NULL, NULL, 'assinado', NULL, '', NULL, '2026-02-26 22:22:41', '2026-02-26 22:23:21'),
(14, 2, 5, 'adesao', 'NTW-202602-5528', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-5278</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 430,00</strong> (430,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 100,00</strong> (100,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 28 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 28 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 86,00</strong> (86,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 26 de fevereiro de 2026\n        </div>\n    ', 430.00, NULL, 100.00, 1, 10, '2026-03-29', '2026-03-29', 86.00, 20.00, 12, '2026-02-27 02:53:58', NULL, NULL, NULL, 'assinado', NULL, '', NULL, '2026-02-27 01:53:28', '2026-02-27 01:53:58'),
(15, 2, NULL, 'adesao', 'TEST-20260226-225439', '1.0', 'Contrato de Teste Automático', '<p>Contrato de teste</p>', 1000.00, NULL, 150.00, 1, 10, NULL, NULL, NULL, NULL, NULL, '2026-02-26 22:54:39', NULL, NULL, NULL, 'assinado', NULL, NULL, NULL, '2026-02-27 01:54:39', '2026-02-27 01:54:39');
INSERT INTO `contratos` (`id`, `cliente_id`, `plano_contratado_id`, `tipo_contrato`, `numero_contrato`, `versao`, `titulo`, `conteudo`, `valor_total`, `valor_entrada`, `valor_mensal`, `numero_parcelas`, `dia_vencimento`, `data_primeira_parcela`, `data_primeira_mensalidade`, `multa_cancelamento`, `percentual_multa`, `prazo_fidelidade`, `data_assinatura`, `data_vencimento`, `data_cancelamento`, `motivo_cancelamento`, `status`, `pdf_path`, `observacoes`, `created_by`, `created_at`, `updated_at`) VALUES
(16, 2, 6, 'adesao', 'NTW-202602-9057', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-9840</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 2200,00</strong> (2200,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 400,00</strong> (400,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 28 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 28 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 440,00</strong> (440,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 26 de fevereiro de 2026\n        </div>\n    ', 2200.00, NULL, 400.00, 1, 10, '2026-03-29', '2026-03-29', 440.00, 20.00, 12, '2026-02-27 02:55:22', NULL, NULL, NULL, 'assinado', NULL, '', NULL, '2026-02-27 01:54:59', '2026-02-27 01:55:22'),
(17, 2, 7, 'adesao', 'NTW-202602-3107', '1.0', 'Contrato de Prestação de Serviços de Tecnologia', '\n        <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>\n        \n        <p style=\"text-align: right;\">Contrato nº: NTW-202602-4795</p>\n        \n        <p><strong>CONTRATANTE:</strong> JOSE WILSON RODRIGUES ALMEIDAsaas, inscrito no CPF/CNPJ sob nº 432543563565, com endereço RUA PEDRO HEREMAN, Casa1, Engenheiro Coelho1 - SP - CEP 13445011.</p>\n        \n        <p><strong>CONTRATADA:</strong> NTW - NEW SOFTWARE, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA].</p>\n        \n        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Prestação de Serviços de Tecnologia, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>\n        \n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ 490,00</strong> (490,00 reais).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 123,00</strong> (123,00 reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até 1x, com vencimento da primeira parcela em 28 de março de 2026.</p>\n<p>2.4. As mensalidades vencerão todo dia 10 de cada mês, com início em 28 de março de 2026.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>3.1. O prazo de desenvolvimento será de 30 dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\n<p>3.3. O período de fidelidade é de 12 meses, contados a partir da data de assinatura deste contrato.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\n<ul>\n    <li>Correção de bugs e erros de funcionamento</li>\n    <li>Atualizações de segurança e compatibilidade</li>\n    <li>Backups periódicos dos dados</li>\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\n    <li>Hospedagem (quando incluída no plano)</li>\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\n</ul>\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA QUINTA - RESCISÃO E MULTA\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ 98,00</strong> (98,00 reais), correspondente a 20% do valor total do contrato.</p>\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\n<ul>\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\n    <li>Descumprimento de obrigações legais ou contratuais</li>\n</ul>\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>7.1. São obrigações da CONTRATADA:</p>\n<ul>\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\n    <li>Entregar o projeto dentro do prazo estipulado</li>\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\n</ul>\n<p>7.2. São obrigações da CONTRATANTE:</p>\n<ul>\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\n</ul>\n                </div>\n            </div>\n        \n            <div class=\"clausula-item\">\n                <div class=\"clausula-titulo\">\n                    <i class=\"fas fa-gavel\"></i> CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS\n                </div>\n                <div class=\"clausula-conteudo\">\n                    \n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>\n                </div>\n            </div>\n        \n        \n        <div class=\"assinaturas\">\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATANTE</strong><br>JOSE WILSON RODRIGUES ALMEIDAsaas</p>\n            </div>\n            <div class=\"assinatura-bloco\">\n                <div class=\"linha-assinatura\"></div>\n                <p><strong>CONTRATADA</strong><br>NTW - NEW SOFTWARE</p>\n            </div>\n        </div>\n        \n        <div class=\"data-local\">\n            © - NTW NEW SOFTWARE, 26 de fevereiro de 2026\n        </div>\n    ', 490.00, NULL, 123.00, 1, 10, '2026-03-29', '2026-03-29', 98.00, 20.00, 12, '2026-02-27 03:17:01', NULL, NULL, NULL, 'assinado', NULL, '', NULL, '2026-02-27 02:16:05', '2026-02-27 02:17:01');

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

--
-- Despejando dados para a tabela `contrato_clausulas`
--

INSERT INTO `contrato_clausulas` (`id`, `titulo`, `conteudo`, `tipo`, `ordem`, `obrigatoria`, `ativa`, `created_at`) VALUES
(1, 'CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO', '\r\n<p>1.1. O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>\r\n<p>1.2. A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>\r\n<p>1.3. As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>', 'todos', 1, 1, 1, '2026-02-21 18:58:47'),
(2, 'CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO', '\n<p>2.1. Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong>R$ {valor_plano}</strong> ({valor_plano_extenso}).</p>\n<p>2.2. Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ {valor_mensal}</strong> ({valor_mensal_extenso}) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>\n<p>2.3. O pagamento do valor de desenvolvimento poderá ser parcelado em até {numero_parcelas}x, com vencimento da primeira parcela em {data_primeira_parcela}.</p>\n<p>2.4. As mensalidades vencerão todo dia {dia_vencimento} de cada mês, com início em {data_primeira_mensalidade}.</p>\n<p>2.5. O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>', 'adesao', 2, 1, 1, '2026-02-21 18:58:47'),
(3, 'CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA', '\r\n<p>3.1. O prazo de desenvolvimento será de {prazo_desenvolvimento} dias úteis, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>\r\n<p>3.2. Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>\r\n<p>3.3. O período de fidelidade é de {fidelidade} meses, contados a partir da data de assinatura deste contrato.</p>', 'adesao', 3, 1, 1, '2026-02-21 18:58:47'),
(4, 'CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES', '\r\n<p>4.1. A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>\r\n<ul>\r\n    <li>Correção de bugs e erros de funcionamento</li>\r\n    <li>Atualizações de segurança e compatibilidade</li>\r\n    <li>Backups periódicos dos dados</li>\r\n    <li>Suporte técnico por e-mail e WhatsApp em horário comercial</li>\r\n    <li>Hospedagem (quando incluída no plano)</li>\r\n    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>\r\n</ul>\r\n<p>4.2. Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>\r\n<p>4.3. O suporte técnico estará disponível de segunda a sexta, das 9h às 18h, exceto feriados.</p>', 'adesao', 4, 1, 1, '2026-02-21 18:58:47'),
(5, 'CLÁUSULA QUINTA - RESCISÃO E MULTA', '\r\n<p>5.1. Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de {fidelidade} meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong>R$ {multa_cancelamento}</strong> ({multa_extenso}), correspondente a {percentual_multa}% do valor total do contrato.</p>\r\n<p>5.2. A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>\r\n<ul>\r\n    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>\r\n    <li>Uso indevido do sistema ou violação de direitos autorais</li>\r\n    <li>Descumprimento de obrigações legais ou contratuais</li>\r\n</ul>\r\n<p>5.3. Em caso de rescisão por iniciativa da CONTRATADA por justa causa da CONTRATANTE, não haverá devolução dos valores já pagos e a multa contratual será devida.</p>', 'adesao', 5, 1, 1, '2026-02-21 18:58:47'),
(6, 'CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL', '\r\n<p>6.1. Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>\r\n<p>6.2. A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>\r\n<p>6.3. É vedado à CONTRATANTE ceder, comercializar ou disponibilizar o código-fonte a terceiros sem autorização expressa da CONTRATADA.</p>', 'adesao', 6, 1, 1, '2026-02-21 18:58:47'),
(7, 'CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES', '\r\n<p>7.1. São obrigações da CONTRATADA:</p>\r\n<ul>\r\n    <li>Desenvolver o sistema conforme especificações acordadas</li>\r\n    <li>Entregar o projeto dentro do prazo estipulado</li>\r\n    <li>Prestar suporte e manutenção conforme cláusula quarta</li>\r\n    <li>Manter sigilo sobre as informações da CONTRATANTE</li>\r\n</ul>\r\n<p>7.2. São obrigações da CONTRATANTE:</p>\r\n<ul>\r\n    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>\r\n    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>\r\n    <li>Efetuar os pagamentos nas datas estipuladas</li>\r\n    <li>Utilizar o sistema de acordo com a legislação vigente</li>\r\n</ul>', 'todos', 7, 1, 1, '2026-02-21 18:58:47'),
(8, 'CLÁUSULA OITAVA - DISPOSIÇÕES GERAIS', '\n<p>8.1. As partes elegem o foro da comarca de Iracemapolis para dirimir quaisquer dúvidas oriundas deste contrato.</p>\n<p>8.2. Este contrato é firmado em caráter irrevogável e irretratável, obrigando as partes e seus sucessores.</p>\n<p>8.3. Qualquer alteração neste contrato deverá ser feita por escrito e assinada por ambas as partes.</p>\n<p>8.4. A tolerância quanto ao descumprimento de qualquer cláusula não constituirá novação ou precedente.</p>', 'todos', 8, 1, 1, '2026-02-21 18:58:47'),
(9, 'CLÁUSULA NONA - CANCELAMENTO', '\r\n<p>9.1. O CONTRATANTE poderá solicitar o cancelamento do contrato a qualquer momento, mediante comunicação por escrito com antecedência mínima de 30 (trinta) dias.</p>\r\n<p>9.2. Em caso de cancelamento, a CONTRATANTE deverá pagar a multa prevista na Cláusula Quinta, se dentro do período de fidelidade.</p>\r\n<p>9.3. Após o cancelamento, a CONTRATADA não terá mais obrigação de manter o sistema online ou fornecer suporte, exceto se acordado de outra forma.</p>\r\n<p>9.4. A CONTRATADA fornecerá uma cópia do banco de dados e arquivos do sistema em até 15 dias após o cancelamento, mediante solicitação formal.</p>', 'cancelamento', 1, 1, 1, '2026-02-21 18:58:47'),
(10, 'CLÁUSULA DEZ - RENOVAÇÃO', '\r\n<p>10.1. Este contrato renova-se automaticamente por prazo indeterminado, permanecendo em vigor enquanto houver o pagamento das mensalidades.</p>\r\n<p>10.2. A renovação mantém todas as cláusulas e condições originais, salvo se houver aditivo contratual modificando-as.</p>\r\n<p>10.3. Anualmente, as partes poderão renegociar os valores das mensalidades com base na inflação e no mercado.</p>', 'renovacao', 1, 1, 1, '2026-02-21 18:58:47');

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

--
-- Despejando dados para a tabela `contrato_historico`
--

INSERT INTO `contrato_historico` (`id`, `contrato_id`, `acao`, `dados_anteriores`, `dados_novos`, `ip`, `user_agent`, `created_by`, `created_at`) VALUES
(1, 4, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-21 22:12:13'),
(2, 4, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-21 22:13:21'),
(3, 3, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-21 22:15:27'),
(4, 3, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-21 22:17:09'),
(5, 9, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-24 02:42:12'),
(6, 10, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-24 21:09:38'),
(7, 11, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-24 21:22:39'),
(8, 12, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-25 00:35:34'),
(9, 13, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-26 22:22:49'),
(10, 14, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-27 01:53:48'),
(11, 16, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-27 01:55:07'),
(12, 17, 'enviado para cliente', NULL, NULL, NULL, NULL, 1, '2026-02-27 02:16:26');

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

--
-- Despejando dados para a tabela `indicacoes_config`
--

INSERT INTO `indicacoes_config` (`id`, `percentual_desconto`, `dias_validade`, `limite_indicacoes`, `desconto_acumulativo`, `mensagem_whatsapp`, `regras`, `created_at`, `updated_at`) VALUES
(1, 10.00, 90, 0, 0, 'Olá! Gostaria de indicar a NTW para você. Use meu código {codigo} e ganhe 10%% de desconto na primeira mensalidade! Acesse: {site_url}', '1. O desconto de 10%% é aplicado na mensalidade do indicado por 3 meses\n2. O indicador ganha 10%% de desconto na sua própria mensalidade por 3 meses\n3. A indicação só é válida após a confirmação do primeiro pagamento\n4. O código tem validade de 90 dias', '2026-02-22 15:00:59', NULL);

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

--
-- Despejando dados para a tabela `metricas_empresas`
--

INSERT INTO `metricas_empresas` (`id`, `ano`, `mes`, `total_empresas`, `novas_empresas`, `empresas_ativas`, `empresas_inativas`, `receita_total`, `receita_mensal_total`, `ticket_medio`, `contratos_novos`, `contratos_renovados`, `contratos_cancelados`, `created_at`) VALUES
(1, 2026, 2, 2, 2, 2, 0, 9470.00, 0.00, 1052.22, 9, 0, 0, '2026-02-22 16:14:46');

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

--
-- Despejando dados para a tabela `newsletter_config`
--

INSERT INTO `newsletter_config` (`id`, `remetente_nome`, `remetente_email`, `limite_por_minuto`, `limite_por_hora`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_secure`, `assinatura_html`, `rodape_html`, `logo_url`, `facebook_url`, `instagram_url`, `whatsapp_numero`, `created_at`, `updated_at`) VALUES
(1, 'NTW - New Software', 'sistemasntw@gmail.com', 30, 500, 'smtp.gmail.com', 587, 'sistemasntw@gmail.com', 'dcvj lezc qwoc yvim', 'tls', '', '', 'logo.png', '', 'https://instagram.com/newsoftwarebr', '5519987111656', '2026-02-20 20:58:16', '2026-02-22 15:11:46');

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

--
-- Despejando dados para a tabela `pagamento_config`
--

INSERT INTO `pagamento_config` (`id`, `gateway`, `modo`, `public_key`, `access_token`, `client_id`, `client_secret`, `webhook_secret`, `webhook_url`, `pix_key`, `pix_key_type`, `pix_qr_code`, `juros_mensal`, `multa_atraso`, `juros_dia`, `status`, `created_at`, `updated_at`) VALUES
(1, 'nenhum', 'teste', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'cnpj', NULL, 2.00, 2.00, 0.33, 1, '2026-02-22 20:21:03', NULL);

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

--
-- Despejando dados para a tabela `pagamento_config_geracao`
--

INSERT INTO `pagamento_config_geracao` (`id`, `dia_geracao`, `dias_antecedencia`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 1, '2026-02-27 01:42:05', NULL);

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

--
-- Despejando dados para a tabela `pagamento_faturas`
--

INSERT INTO `pagamento_faturas` (`id`, `cliente_id`, `contrato_id`, `plano_contratado_id`, `numero_fatura`, `tipo`, `mes_referencia`, `descricao`, `valor`, `desconto`, `juros`, `multa`, `valor_total`, `data_emissao`, `data_vencimento`, `data_pagamento`, `status`, `transacao_id`, `mp_payment_id`, `mp_preference_id`, `link_pagamento`, `pix_qrcode`, `pix_qrcode_base64`, `pix_copiaecola`, `pix_expiracao`, `pdf_path`, `created_at`, `updated_at`) VALUES
(1, 2, 10, 1, 'FAT-202602-0001', 'mensalidade', '2026-02-01', NULL, 150.00, 0.00, 0.00, 0.00, 150.00, '2026-02-01', '2026-02-10', NULL, 'pendente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 01:51:37', NULL),
(2, 2, 10, 1, 'FAT-202601-0001', 'mensalidade', '2026-01-01', NULL, 150.00, 0.00, 0.00, 0.00, 150.00, '2026-01-01', '2026-01-10', NULL, 'paga', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 13:00:00', NULL),
(3, 2, 11, 2, 'FAT-202602-0002', 'mensalidade', '2026-02-01', NULL, 150.00, 0.00, 0.00, 0.00, 150.00, '2026-02-01', '2026-02-05', NULL, 'atrasada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 01:51:37', NULL),
(4, 2, 13, 4, 'FAT-202602-0003', 'mensalidade', '2026-02-01', NULL, 150.00, 0.00, 0.00, 0.00, 150.00, '2026-02-01', '2026-02-15', NULL, 'pendente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 01:51:37', NULL),
(5, 2, 9, NULL, 'FAT-DEV-202602-0001', 'desenvolvimento', NULL, NULL, 980.00, 0.00, 0.00, 0.00, 980.00, '2026-02-01', '2026-02-15', NULL, 'pendente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 01:51:37', NULL),
(6, 2, 17, 7, 'FAT-DEV-202602-0017', 'desenvolvimento', NULL, NULL, 490.00, 0.00, 0.00, 0.00, 490.00, '2026-02-26', '2026-03-05', NULL, 'pendente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 02:17:01', NULL),
(7, 2, 17, 7, 'FAT-202603-0017', 'mensalidade', '2026-03-26', NULL, 123.00, 0.00, 0.00, 0.00, 123.00, '2026-02-26', '2026-03-10', NULL, 'pendente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 02:17:01', NULL);

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

--
-- Despejando dados para a tabela `pagamento_logs`
--

INSERT INTO `pagamento_logs` (`id`, `cliente_id`, `fatura_id`, `transacao_id`, `acao`, `status_anterior`, `status_novo`, `detalhes`, `ip`, `user_agent`, `created_at`) VALUES
(1, 2, NULL, NULL, 'contrato_assinado', NULL, 'assinado', 'Contrato NTW-202602-5528 assinado', NULL, NULL, '2026-02-27 01:53:58'),
(2, 2, NULL, NULL, 'contrato_assinado', NULL, 'assinado', 'Contrato TEST-20260226-225439 assinado', NULL, NULL, '2026-02-27 01:54:39'),
(3, 2, NULL, NULL, 'contrato_assinado', NULL, 'assinado', 'Contrato NTW-202602-9057 assinado', NULL, NULL, '2026-02-27 01:55:22'),
(4, 2, NULL, NULL, 'contrato_assinado', NULL, 'assinado', 'Contrato NTW-202602-3107 assinado', NULL, NULL, '2026-02-27 02:17:01'),
(5, 2, NULL, NULL, 'faturas_geradas', NULL, NULL, 'Faturas geradas para contrato NTW-202602-3107', NULL, NULL, '2026-02-27 02:17:01');

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

--
-- Despejando dados para a tabela `planos`
--

INSERT INTO `planos` (`id`, `categoria_id`, `nome`, `slug`, `descricao_curta`, `descricao_completa`, `preco`, `periodo`, `destaque`, `badge_text`, `prazo_entrega`, `observacao`, `perfil`, `link_whatsapp`, `mensagem_whatsapp`, `ativo`, `ordem`, `created_at`, `updated_at`) VALUES
(1, 1, 'Site Simples', 'site-simples', 'Landing page profissional', NULL, 230.00, 'permanente', 0, NULL, '2 a 4 dias úteis', NULL, 'mei', NULL, NULL, 1, 1, '2026-02-20 00:27:15', NULL),
(2, 1, 'Site Essencial', 'site-essencial', 'Site com até 3 páginas', NULL, 430.00, 'permanente', 1, 'Mais Popular', '5 a 7 dias úteis', NULL, 'mei', NULL, NULL, 1, 2, '2026-02-20 00:27:15', NULL),
(3, 1, 'Site Avançado', 'site-avancado', 'Site completo com até 10 páginas', NULL, 900.00, 'permanente', 0, NULL, '7 a 10 dias úteis', NULL, 'mei', NULL, NULL, 1, 3, '2026-02-20 00:27:15', NULL),
(4, 2, 'Gerador de Documentos', 'gerador-documentos', 'Geração automática de contratos', NULL, 950.00, 'permanente', 0, NULL, 'até 10 dias úteis', NULL, 'mei', NULL, NULL, 1, 4, '2026-02-20 00:27:15', NULL),
(5, 2, 'Sistema de Agendamento', 'sistema-agendamento', 'Agenda digital com WhatsApp', NULL, 550.00, 'permanente', 1, 'Mais Popular', 'até 14 dias úteis', NULL, 'mei', NULL, NULL, 1, 5, '2026-02-20 00:27:15', NULL),
(6, 2, 'Sistema de Newsletter', 'sistema-newsletter', 'Gestão de campanhas de e-mail', NULL, 490.00, 'permanente', 0, NULL, 'até 7 dias úteis', NULL, 'mei', NULL, NULL, 1, 6, '2026-02-20 00:27:15', NULL),
(7, 3, 'Bot IA Básico', 'bot-ia-basico', 'IA com até 30 perguntas', NULL, 770.00, 'permanente', 0, NULL, 'até 20 dias úteis', NULL, 'mei', NULL, NULL, 1, 7, '2026-02-20 00:27:15', NULL),
(8, 3, 'Bot IA Profissional', 'bot-ia-profissional', 'IA com linguagem natural', NULL, 980.00, 'permanente', 1, 'Mais Popular', '10 a 30 dias úteis', NULL, 'mei', NULL, NULL, 1, 8, '2026-02-20 00:27:15', NULL),
(9, 1, 'Business I', 'business-i', 'Site corporativo até 5 páginas', NULL, 490.00, 'permanente', 0, NULL, 'até 10 dias úteis', NULL, 'empresa', NULL, NULL, 1, 9, '2026-02-20 00:27:15', NULL),
(10, 1, 'Business II', 'business-ii', 'Site completo até 8 páginas', NULL, 800.00, 'permanente', 1, 'Mais Popular', 'até 15 dias', NULL, 'empresa', NULL, NULL, 1, 10, '2026-02-20 00:27:15', NULL),
(11, 1, 'Business III', 'business-iii', 'Site premium com CRM', NULL, 1200.00, 'permanente', 0, NULL, 'até 20 dias úteis', NULL, 'empresa', NULL, NULL, 1, 11, '2026-02-20 00:27:15', NULL),
(12, 2, 'Sistema de Ponto', 'sistema-ponto', 'Controle de ponto eletrônico', NULL, 2200.00, 'permanente', 1, 'Mais Popular', 'até 4 semanas', NULL, 'empresa', NULL, NULL, 1, 12, '2026-02-20 00:27:15', NULL),
(13, 2, 'Sistema de Gestão de RH', 'sistema-de-gest-o-de-rh', 'Gestão completa de RH', '', 2750.00, 'permanente', 0, '0', 'até 5 semanas', '', 'empresa', '', '', 1, 13, '2026-02-20 00:27:15', '2026-02-20 00:59:28'),
(14, 2, 'Sistema de Admissão', 'sistema-admissao', 'Gestão de admissão por setores', NULL, 2300.00, 'permanente', 0, NULL, 'até 4 semanas', NULL, 'empresa', NULL, NULL, 1, 14, '2026-02-20 00:27:15', NULL),
(15, 2, 'Gestão para Lojas Físicas', 'gestao-lojas', 'Controle de estoque e vendas', NULL, 3450.00, 'permanente', 0, NULL, 'até 2 semanas', NULL, 'empresa', NULL, NULL, 1, 15, '2026-02-20 00:27:15', NULL),
(16, 2, 'Cadastro de Currículos', 'cadastro-curriculos', 'Sistema de recrutamento', NULL, 670.00, 'permanente', 0, NULL, 'até 10 dias úteis', NULL, 'empresa', NULL, NULL, 1, 16, '2026-02-20 00:27:15', NULL),
(17, 2, 'Comunicação Interna', 'comunicacao-interna', 'Chat e comunicados corporativos', NULL, 1400.00, 'permanente', 0, NULL, 'até 3 semanas', NULL, 'empresa', NULL, NULL, 1, 17, '2026-02-20 00:27:15', NULL),
(18, 3, 'Bot IA Profissional', 'bot-ia-profissional-empresa', 'IA avançada para empresas', NULL, 1350.00, 'permanente', 0, NULL, '7 a 10 dias úteis', NULL, 'empresa', NULL, NULL, 1, 18, '2026-02-20 00:27:15', NULL),
(19, 3, 'Bot RH & Recrutamento', 'bot-rh-recrutamento', 'IA para triagem de currículos', NULL, 1700.00, 'permanente', 1, 'Mais Completo', 'até 10 dias úteis', NULL, 'empresa', NULL, NULL, 1, 19, '2026-02-20 00:27:15', NULL),
(20, 3, 'Bot E-commerce', 'bot-ecommerce', 'IA para lojas virtuais', NULL, 1900.00, 'permanente', 0, NULL, '7 a 10 dias úteis', NULL, 'empresa', NULL, NULL, 1, 20, '2026-02-20 00:27:15', NULL);

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

--
-- Despejando dados para a tabela `planos_caracteristicas`
--

INSERT INTO `planos_caracteristicas` (`id`, `plano_id`, `caracteristica`, `icone`, `ordem`, `created_at`) VALUES
(1, 1, 'Página única (landing page)', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(2, 1, 'Layout responsivo', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(3, 1, 'Hospedagem (não inclusa)', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(4, 1, 'Formulário de contato ou WhatsApp', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(5, 1, 'Sem painel administrativo', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(6, 2, 'Até 3 páginas (Home, Sobre, Contato)', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(7, 2, 'Hospedagem (não inclusa)', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(8, 2, 'Botão flutuante WhatsApp', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(9, 2, 'Integração com redes sociais', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(10, 2, 'Layout responsivo', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(11, 3, 'Até 10 páginas', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(12, 3, 'Dashboard + Relatórios', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(13, 3, 'APIs e integrações', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(14, 3, 'Painel administrativo', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(15, 3, 'Otimização SEO', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(16, 4, 'Geração automática de contratos, orçamentos e propostas', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(17, 4, 'Templates personalizáveis', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(18, 4, 'Exportação em PDF', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(19, 4, 'Cadastro de clientes e serviços', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(20, 4, 'Histórico e controle de documentos', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(21, 4, 'Acesso via web', 'fa-check-circle', 6, '2026-02-20 00:27:15'),
(22, 5, 'Agenda digital com horários disponíveis', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(23, 5, 'Confirmação por WhatsApp', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(24, 5, 'Cadastro de clientes com histórico', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(25, 5, 'Gestão de serviços e preços', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(26, 5, 'Notificações automáticas', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(27, 5, 'Ideal para salões, clínicas e consultórios', 'fa-check-circle', 6, '2026-02-20 00:27:15'),
(28, 6, 'Cadastro e gerenciamento de lista de e-mails', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(29, 6, 'Criação de campanhas com editor simples', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(30, 6, 'Envio em massa com controle de entregas', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(31, 6, 'Botão de descadastro automático', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(32, 6, 'Relatórios de abertura e cliques', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(33, 7, 'IA treinada com até 30 perguntas/respostas', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(34, 7, 'Integração com site ou WhatsApp', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(35, 7, 'Fluxo de conversa automatizado', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(36, 7, 'Suporte a texto e links', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(37, 7, 'Ideal para captação de leads e FAQ', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(38, 8, 'IA com linguagem natural (tipo ChatGPT)', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(39, 8, 'Integração com WhatsApp, site ou sistema próprio', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(40, 8, 'Respostas personalizadas ao seu negócio', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(41, 8, 'Coleta de dados automatizada', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(42, 8, 'Dashboard de interações', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(43, 9, 'Site de até 5 páginas', 'fa-check-circle', 1, '2026-02-20 00:27:15'),
(44, 9, 'Layout com foco em conversão', 'fa-check-circle', 2, '2026-02-20 00:27:15'),
(45, 9, 'Painel administrador básico', 'fa-check-circle', 3, '2026-02-20 00:27:15'),
(46, 9, 'Integração com WhatsApp Business', 'fa-check-circle', 4, '2026-02-20 00:27:15'),
(47, 9, 'SEO básico e Google Analytics', 'fa-check-circle', 5, '2026-02-20 00:27:15'),
(48, 9, 'Ideal para prestadores e consultores', 'fa-check-circle', 6, '2026-02-20 00:27:15'),
(49, 10, 'Site de até 8 páginas ou seções', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(50, 10, 'Painel administrativo completo', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(51, 10, 'Integração Google Maps, Instagram, e-mail', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(52, 10, 'Formulários inteligentes para leads', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(53, 10, 'Alta performance + SSL', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(54, 10, 'Ideal para clínicas, escritórios, lojas', 'fa-check-circle', 6, '2026-02-20 00:27:16'),
(55, 11, 'Design personalizado com copy estratégica', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(56, 11, 'Páginas otimizadas para anúncios', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(57, 11, 'CRM e automações incluídas', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(58, 11, 'SEO avançado + velocidade otimizada', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(59, 11, 'Ideal para empresas em crescimento', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(60, 12, 'Registro de entrada, saída e intervalos', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(61, 12, 'Integração com folha de pagamento', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(62, 12, 'Controle de horas extras, faltas e atrasos', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(63, 12, 'Relatórios personalizados', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(64, 12, 'Acesso via desktop e mobile', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(65, 12, 'Conformidade com CLT', 'fa-check-circle', 6, '2026-02-20 00:27:16'),
(71, 14, 'Acesso dividido por setores (Usuário, RH, R&S e Segurança)', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(72, 14, 'Painel exclusivo para Recrutamento e Seleção', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(73, 14, 'Gestão de documentos pelo RH', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(74, 14, 'Liberação e controle de acesso pela Segurança', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(75, 14, 'Acompanhamento de status pelo usuário comum', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(76, 15, 'Controle de estoque em tempo real', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(77, 15, 'Relatórios de vendas diárias, semanais e mensais', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(78, 15, 'Gestão de clientes e fidelização', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(79, 15, 'Múltiplos usuários com permissões', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(80, 15, 'App de compra para clientes incluso', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(81, 16, 'Formulário personalizado de envio de CV', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(82, 16, 'Upload de PDF e documentos', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(83, 16, 'Filtro por área, experiência e cidade', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(84, 16, 'Organização por vaga ou setor', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(85, 16, 'Visualização e análise rápida', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(86, 17, 'Canal interno de comunicados e avisos', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(87, 17, 'Chat corporativo entre setores', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(88, 17, 'Central de documentos para colaboradores', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(89, 17, 'Área de feedback e sugestões anônimas', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(90, 17, 'Acesso via web e mobile', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(91, 18, 'IA com linguagem natural', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(92, 18, 'Integração com WhatsApp, site ou sistema próprio', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(93, 18, 'Treinamento com até 100 entradas', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(94, 18, 'Coleta de dados e qualificação de leads', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(95, 18, 'Dashboard de interações', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(96, 19, 'Triagem automática de currículos', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(97, 19, 'Responde dúvidas de colaboradores (salário, férias, benefícios)', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(98, 19, 'Registro de solicitações via WhatsApp', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(99, 19, 'Envio automático de testes a candidatos', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(100, 19, 'Feedback automático e coleta de currículos', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(101, 20, 'Atendimento automatizado para loja virtual', 'fa-check-circle', 1, '2026-02-20 00:27:16'),
(102, 20, 'Status de pedidos e rastreamento', 'fa-check-circle', 2, '2026-02-20 00:27:16'),
(103, 20, 'Sugestão de produtos com IA', 'fa-check-circle', 3, '2026-02-20 00:27:16'),
(104, 20, 'Integração com WhatsApp e site', 'fa-check-circle', 4, '2026-02-20 00:27:16'),
(105, 20, 'Redução de chamados e suporte', 'fa-check-circle', 5, '2026-02-20 00:27:16'),
(111, 13, 'Gestão de férias, licenças e benefícios', 'fa-check-circle', 0, '2026-02-20 00:59:28'),
(112, 13, 'Cadastro e manutenção de funcionários', 'fa-check-circle', 1, '2026-02-20 00:59:28'),
(113, 13, 'Recrutamento e seleção', 'fa-check-circle', 2, '2026-02-20 00:59:28'),
(114, 13, 'Relatórios de desempenho', 'fa-check-circle', 3, '2026-02-20 00:59:28'),
(115, 13, 'Aprovação digital de solicitações', 'fa-check-circle', 4, '2026-02-20 00:59:28');

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

--
-- Despejando dados para a tabela `planos_categorias`
--

INSERT INTO `planos_categorias` (`id`, `nome`, `icone`, `descricao`, `perfil`, `ordem`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Sites', 'fa-globe', 'Sites profissionais e institucionais', 'ambos', 1, 1, '2026-02-20 00:27:15', NULL),
(2, 'Sistemas', 'fa-cogs', 'Sistemas personalizados para gestão', 'ambos', 2, 1, '2026-02-20 00:27:15', NULL),
(3, 'Bots com IA', 'fa-robot', 'Automação com inteligência artificial', 'ambos', 3, 1, '2026-02-20 00:27:15', NULL);

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
-- Despejando dados para a tabela `planos_contratados`
--

INSERT INTO `planos_contratados` (`id`, `cliente_id`, `plano_id`, `tipo_plano`, `nome_plano`, `descricao`, `valor_plano`, `valor_mensal`, `forma_pagamento`, `numero_parcelas`, `data_inicio`, `data_fim`, `periodo_teste`, `dia_vencimento`, `data_proxima_fatura`, `ultima_fatura_gerada`, `mp_subscription_id`, `mp_preapproval_id`, `status`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 2, 8, 'sistema', 'Bot IA Profissional', NULL, 980.00, 230.00, 'vista', 1, '2026-02-24', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-24 21:07:34', NULL),
(2, 2, 3, 'sistema', 'Site Avançado', NULL, 900.00, 150.00, 'vista', 1, '2026-02-24', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-24 21:22:31', NULL),
(3, 2, NULL, 'outros', 'SISTEMA TEST HAHA', NULL, 2000.00, 1500.00, 'vista', 1, '2026-02-25', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-25 00:35:18', NULL),
(4, 2, 3, 'sistema', 'Site Avançado', NULL, 900.00, 150.00, 'vista', 1, '2026-02-26', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-26 22:22:41', NULL),
(5, 2, 2, 'sistema', 'Site Essencial', NULL, 430.00, 100.00, 'vista', 1, '2026-02-27', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-27 01:53:28', NULL),
(6, 2, 12, 'sistema', 'Sistema de Ponto', NULL, 2200.00, 400.00, 'vista', 1, '2026-02-27', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-27 01:54:59', NULL),
(7, 2, 6, 'sistema', 'Sistema de Newsletter', NULL, 490.00, 123.00, 'vista', 1, '2026-02-27', NULL, 0, 10, NULL, NULL, NULL, NULL, 'pendente', NULL, '2026-02-27 02:16:05', NULL);

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

--
-- Despejando dados para a tabela `politica_historico`
--

INSERT INTO `politica_historico` (`id`, `politica_id`, `versao`, `conteudo_antigo`, `conteudo_novo`, `alteracoes`, `alterado_por`, `data_alteracao`) VALUES
(1, 1, '1.0', '<h2>1. Informações que coletamos</h2>\r\n<p>Coletamos informações pessoais que você nos fornece voluntariamente ao utilizar nossos serviços, como nome, e-mail, telefone e empresa.</p>\r\n\r\n<h2>2. Como usamos suas informações</h2>\r\n<p>Utilizamos suas informações para:</p>\r\n<ul>\r\n    <li>Fornecer e melhorar nossos serviços</li>\r\n    <li>Enviar comunicações importantes</li>\r\n    <li>Personalizar sua experiência</li>\r\n    <li>Cumprir obrigações legais</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não vendemos, trocamos ou transferimos suas informações pessoais para terceiros sem seu consentimento, exceto quando necessário para fornecer os serviços solicitados ou por exigência legal.</p>\r\n\r\n<h2>4. Segurança dos dados</h2>\r\n<p>Implementamos medidas de segurança técnicas e organizacionais para proteger suas informações contra acesso não autorizado, alteração, divulgação ou destruição.</p>\r\n\r\n<h2>5. Seus direitos</h2>\r\n<p>Você tem direito a acessar, corrigir, atualizar ou solicitar a exclusão de suas informações pessoais a qualquer momento.</p>\r\n\r\n<h2>6. Cookies e tecnologias semelhantes</h2>\r\n<p>Utilizamos cookies para melhorar sua experiência em nosso site, analisar tráfego e personalizar conteúdo.</p>\r\n\r\n<h2>7. Alterações nesta política</h2>\r\n<p>Podemos atualizar esta política periodicamente. Notificaremos sobre alterações significativas através de nosso site ou por e-mail.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas sobre esta política, entre em contato através do e-mail: sistemasntw@gmail.com</p>', '<h2>1. Informações que coletamos</h2>\r\n<p>Coletamos informações pessoais que você nos fornece voluntariamente ao utilizar nossos serviços, como nome, e-mail, telefone e empresa.</p>\r\n\r\n<h2>2. Como usamos suas informações</h2>\r\n<p>Utilizamos suas informações para:</p>\r\n<ul>\r\n    <li>Fornecer e melhorar nossos serviços</li>\r\n    <li>Enviar comunicações importantes</li>\r\n    <li>Personalizar sua experiência</li>\r\n    <li>Cumprir obrigações legais</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não realizamos a venda, troca, transferência, divulgação ou qualquer forma de compartilhamento de informações pessoais dos usuários com terceiros, sob nenhuma circunstância. As informações coletadas são utilizadas exclusivamente para a prestação e funcionamento dos serviços oferecidos, sendo acessadas apenas quando estritamente necessário para cumprimento de obrigações legais ou determinações de autoridades competentes.</p>\r\n\r\n<h2>4. Segurança dos dados</h2>\r\n<p>Implementamos medidas de segurança técnicas e organizacionais para proteger suas informações contra acesso não autorizado, alteração, divulgação ou destruição.</p>\r\n\r\n<h2>5. Seus direitos</h2>\r\n<p>Você tem direito a acessar, corrigir, atualizar ou solicitar a exclusão de suas informações pessoais a qualquer momento.</p>\r\n\r\n<h2>6. Cookies e tecnologias semelhantes</h2>\r\n<p>Utilizamos cookies para melhorar sua experiência em nosso site, analisar tráfego e personalizar conteúdo.</p>\r\n\r\n<h2>7. Alterações nesta política</h2>\r\n<p>Podemos atualizar esta política periodicamente. Notificaremos sobre alterações significativas através de nosso site ou por e-mail.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas sobre esta política, entre em contato através do e-mail: suporte@newsoftwarebr.com.br</p>', 'Editado por administrador', 1, '2026-02-20 21:29:06'),
(2, 1, '1.0', '<h2>1. Informações que coletamos</h2>\r\n<p>Coletamos informações pessoais que você nos fornece voluntariamente ao utilizar nossos serviços, como nome, e-mail, telefone e empresa.</p>\r\n\r\n<h2>2. Como usamos suas informações</h2>\r\n<p>Utilizamos suas informações para:</p>\r\n<ul>\r\n    <li>Fornecer e melhorar nossos serviços</li>\r\n    <li>Enviar comunicações importantes</li>\r\n    <li>Personalizar sua experiência</li>\r\n    <li>Cumprir obrigações legais</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não realizamos a venda, troca, transferência, divulgação ou qualquer forma de compartilhamento de informações pessoais dos usuários com terceiros, sob nenhuma circunstância. As informações coletadas são utilizadas exclusivamente para a prestação e funcionamento dos serviços oferecidos, sendo acessadas apenas quando estritamente necessário para cumprimento de obrigações legais ou determinações de autoridades competentes.</p>\r\n\r\n<h2>4. Segurança dos dados</h2>\r\n<p>Implementamos medidas de segurança técnicas e organizacionais para proteger suas informações contra acesso não autorizado, alteração, divulgação ou destruição.</p>\r\n\r\n<h2>5. Seus direitos</h2>\r\n<p>Você tem direito a acessar, corrigir, atualizar ou solicitar a exclusão de suas informações pessoais a qualquer momento.</p>\r\n\r\n<h2>6. Cookies e tecnologias semelhantes</h2>\r\n<p>Utilizamos cookies para melhorar sua experiência em nosso site, analisar tráfego e personalizar conteúdo.</p>\r\n\r\n<h2>7. Alterações nesta política</h2>\r\n<p>Podemos atualizar esta política periodicamente. Notificaremos sobre alterações significativas através de nosso site ou por e-mail.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas sobre esta política, entre em contato através do e-mail: suporte@newsoftwarebr.com.br</p>', '<h2>1. Informações coletadas</h2>\r\n<p>Coletamos dados pessoais fornecidos diretamente pelo usuário durante a utilização de nossos serviços, incluindo, mas não se limitando a, nome, endereço de e-mail, número de telefone e informações corporativas. Tais dados são coletados exclusivamente para finalidades relacionadas à operação e prestação adequada dos serviços disponibilizados.</p>\r\n\r\n<h2>2. Finalidade do uso das informações</h2>\r\n<p>As informações coletadas são utilizadas exclusivamente para as seguintes finalidades:</p>\r\n<ul>\r\n    <li>Disponibilização, manutenção e aprimoramento dos serviços oferecidos;</li>\r\n    <li>Envio de comunicações institucionais, operacionais e informativas relevantes;</li>\r\n    <li>Melhoria contínua da experiência do usuário na plataforma;</li>\r\n    <li>Cumprimento de obrigações legais, regulatórias e contratuais aplicáveis.</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não realizamos a venda, cessão, transferência, divulgação ou qualquer forma de compartilhamento de dados pessoais com terceiros, sob nenhuma circunstância. As informações coletadas são utilizadas exclusivamente para a execução e funcionamento dos serviços oferecidos, sendo acessadas apenas quando estritamente necessário para o cumprimento de obrigações legais ou mediante determinação de autoridades competentes.</p>\r\n\r\n<h2>4. Segurança das informações</h2>\r\n<p>Adotamos medidas técnicas, administrativas e organizacionais apropriadas para garantir a proteção dos dados pessoais contra acessos não autorizados, perda, alteração, divulgação indevida ou qualquer forma de tratamento inadequado ou ilícito.</p>\r\n\r\n<h2>5. Direitos do titular dos dados</h2>\r\n<p>O usuário poderá, a qualquer momento, solicitar o acesso, correção, atualização ou exclusão de seus dados pessoais, observadas as disposições legais aplicáveis e eventuais obrigações de retenção previstas em lei.</p>\r\n\r\n<h2>6. Cookies e tecnologias similares</h2>\r\n<p>Utilizamos cookies e tecnologias equivalentes com a finalidade de assegurar o correto funcionamento da plataforma, aprimorar a experiência de navegação, realizar análises estatísticas de uso e otimizar conteúdos disponibilizados.</p>\r\n\r\n<h2>7. Atualizações desta política</h2>\r\n<p>Esta Política de Privacidade poderá ser revisada e atualizada periodicamente, a qualquer tempo, visando sua adequação a alterações legais, regulatórias ou operacionais. Alterações relevantes serão comunicadas por meio dos canais oficiais da plataforma.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas, solicitações ou esclarecimentos relacionados a esta Política de Privacidade, o usuário poderá entrar em contato por meio do endereço eletrônico: suporte@newsoftwarebr.com.br.</p>', 'Editado por administrador', 1, '2026-02-20 21:30:33'),
(3, 1, '1.0', '<h2>1. Informações coletadas</h2>\r\n<p>Coletamos dados pessoais fornecidos diretamente pelo usuário durante a utilização de nossos serviços, incluindo, mas não se limitando a, nome, endereço de e-mail, número de telefone e informações corporativas. Tais dados são coletados exclusivamente para finalidades relacionadas à operação e prestação adequada dos serviços disponibilizados.</p>\r\n\r\n<h2>2. Finalidade do uso das informações</h2>\r\n<p>As informações coletadas são utilizadas exclusivamente para as seguintes finalidades:</p>\r\n<ul>\r\n    <li>Disponibilização, manutenção e aprimoramento dos serviços oferecidos;</li>\r\n    <li>Envio de comunicações institucionais, operacionais e informativas relevantes;</li>\r\n    <li>Melhoria contínua da experiência do usuário na plataforma;</li>\r\n    <li>Cumprimento de obrigações legais, regulatórias e contratuais aplicáveis.</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não realizamos a venda, cessão, transferência, divulgação ou qualquer forma de compartilhamento de dados pessoais com terceiros, sob nenhuma circunstância. As informações coletadas são utilizadas exclusivamente para a execução e funcionamento dos serviços oferecidos, sendo acessadas apenas quando estritamente necessário para o cumprimento de obrigações legais ou mediante determinação de autoridades competentes.</p>\r\n\r\n<h2>4. Segurança das informações</h2>\r\n<p>Adotamos medidas técnicas, administrativas e organizacionais apropriadas para garantir a proteção dos dados pessoais contra acessos não autorizados, perda, alteração, divulgação indevida ou qualquer forma de tratamento inadequado ou ilícito.</p>\r\n\r\n<h2>5. Direitos do titular dos dados</h2>\r\n<p>O usuário poderá, a qualquer momento, solicitar o acesso, correção, atualização ou exclusão de seus dados pessoais, observadas as disposições legais aplicáveis e eventuais obrigações de retenção previstas em lei.</p>\r\n\r\n<h2>6. Cookies e tecnologias similares</h2>\r\n<p>Utilizamos cookies e tecnologias equivalentes com a finalidade de assegurar o correto funcionamento da plataforma, aprimorar a experiência de navegação, realizar análises estatísticas de uso e otimizar conteúdos disponibilizados.</p>\r\n\r\n<h2>7. Atualizações desta política</h2>\r\n<p>Esta Política de Privacidade poderá ser revisada e atualizada periodicamente, a qualquer tempo, visando sua adequação a alterações legais, regulatórias ou operacionais. Alterações relevantes serão comunicadas por meio dos canais oficiais da plataforma.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas, solicitações ou esclarecimentos relacionados a esta Política de Privacidade, o usuário poderá entrar em contato por meio do endereço eletrônico: suporte@newsoftwarebr.com.br.</p>', '<h2>1. Informações Coletadas</h2>\r\n<p>\r\nColetamos dados pessoais fornecidos diretamente pelo usuário durante a utilização de nossos serviços, incluindo, mas não se limitando a, nome, endereço de e-mail, número de telefone e informações corporativas. \r\nEsses dados são coletados e tratados exclusivamente para fins relacionados à operação, manutenção e adequada prestação dos serviços disponibilizados, bem como para o cumprimento de obrigações legais e contratuais aplicáveis.\r\n</p>\r\n\r\n<h2>2. Finalidade do uso das informações</h2>\r\n<p>As informações coletadas são utilizadas exclusivamente para as seguintes finalidades:</p>\r\n<ul>\r\n    <li>Disponibilização, manutenção e aprimoramento dos serviços oferecidos;</li>\r\n    <li>Envio de comunicações institucionais, operacionais e informativas relevantes;</li>\r\n    <li>Melhoria contínua da experiência do usuário na plataforma;</li>\r\n    <li>Cumprimento de obrigações legais, regulatórias e contratuais aplicáveis.</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não realizamos a venda, cessão, transferência, divulgação ou qualquer forma de compartilhamento de dados pessoais com terceiros, sob nenhuma circunstância. As informações coletadas são utilizadas exclusivamente para a execução e funcionamento dos serviços oferecidos, sendo acessadas apenas quando estritamente necessário para o cumprimento de obrigações legais ou mediante determinação de autoridades competentes.</p>\r\n\r\n<h2>4. Segurança das informações</h2>\r\n<p>Adotamos medidas técnicas, administrativas e organizacionais apropriadas para garantir a proteção dos dados pessoais contra acessos não autorizados, perda, alteração, divulgação indevida ou qualquer forma de tratamento inadequado ou ilícito.</p>\r\n\r\n<h2>5. Direitos do titular dos dados</h2>\r\n<p>O usuário poderá, a qualquer momento, solicitar o acesso, correção, atualização ou exclusão de seus dados pessoais, observadas as disposições legais aplicáveis e eventuais obrigações de retenção previstas em lei.</p>\r\n\r\n<h2>6. Cookies e tecnologias similares</h2>\r\n<p>Utilizamos cookies e tecnologias equivalentes com a finalidade de assegurar o correto funcionamento da plataforma, aprimorar a experiência de navegação, realizar análises estatísticas de uso e otimizar conteúdos disponibilizados.</p>\r\n\r\n<h2>7. Atualizações desta política</h2>\r\n<p>Esta Política de Privacidade poderá ser revisada e atualizada periodicamente, a qualquer tempo, visando sua adequação a alterações legais, regulatórias ou operacionais. Alterações relevantes serão comunicadas por meio dos canais oficiais da plataforma.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas, solicitações ou esclarecimentos relacionados a esta Política de Privacidade, o usuário poderá entrar em contato por meio do endereço eletrônico: suporte@newsoftwarebr.com.br.</p>', 'Editado por administrador', 1, '2026-02-22 11:30:40');

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

--
-- Despejando dados para a tabela `politica_privacidade`
--

INSERT INTO `politica_privacidade` (`id`, `titulo`, `subtitulo`, `conteudo`, `versao`, `data_publicacao`, `data_atualizacao`, `atualizado_por`, `status`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 'Política de Privacidade', 'Saiba como coletamos, usamos e protegemos suas informações', '<h2>1. Informações Coletadas</h2>\r\n<p>\r\nColetamos dados pessoais fornecidos diretamente pelo usuário durante a utilização de nossos serviços, incluindo, mas não se limitando a, nome, endereço de e-mail, número de telefone e informações corporativas. \r\nEsses dados são coletados e tratados exclusivamente para fins relacionados à operação, manutenção e adequada prestação dos serviços disponibilizados, bem como para o cumprimento de obrigações legais e contratuais aplicáveis.\r\n</p>\r\n\r\n<h2>2. Finalidade do uso das informações</h2>\r\n<p>As informações coletadas são utilizadas exclusivamente para as seguintes finalidades:</p>\r\n<ul>\r\n    <li>Disponibilização, manutenção e aprimoramento dos serviços oferecidos;</li>\r\n    <li>Envio de comunicações institucionais, operacionais e informativas relevantes;</li>\r\n    <li>Melhoria contínua da experiência do usuário na plataforma;</li>\r\n    <li>Cumprimento de obrigações legais, regulatórias e contratuais aplicáveis.</li>\r\n</ul>\r\n\r\n<h2>3. Compartilhamento de informações</h2>\r\n<p>Não realizamos a venda, cessão, transferência, divulgação ou qualquer forma de compartilhamento de dados pessoais com terceiros, sob nenhuma circunstância. As informações coletadas são utilizadas exclusivamente para a execução e funcionamento dos serviços oferecidos, sendo acessadas apenas quando estritamente necessário para o cumprimento de obrigações legais ou mediante determinação de autoridades competentes.</p>\r\n\r\n<h2>4. Segurança das informações</h2>\r\n<p>Adotamos medidas técnicas, administrativas e organizacionais apropriadas para garantir a proteção dos dados pessoais contra acessos não autorizados, perda, alteração, divulgação indevida ou qualquer forma de tratamento inadequado ou ilícito.</p>\r\n\r\n<h2>5. Direitos do titular dos dados</h2>\r\n<p>O usuário poderá, a qualquer momento, solicitar o acesso, correção, atualização ou exclusão de seus dados pessoais, observadas as disposições legais aplicáveis e eventuais obrigações de retenção previstas em lei.</p>\r\n\r\n<h2>6. Cookies e tecnologias similares</h2>\r\n<p>Utilizamos cookies e tecnologias equivalentes com a finalidade de assegurar o correto funcionamento da plataforma, aprimorar a experiência de navegação, realizar análises estatísticas de uso e otimizar conteúdos disponibilizados.</p>\r\n\r\n<h2>7. Atualizações desta política</h2>\r\n<p>Esta Política de Privacidade poderá ser revisada e atualizada periodicamente, a qualquer tempo, visando sua adequação a alterações legais, regulatórias ou operacionais. Alterações relevantes serão comunicadas por meio dos canais oficiais da plataforma.</p>\r\n\r\n<h2>8. Contato</h2>\r\n<p>Em caso de dúvidas, solicitações ou esclarecimentos relacionados a esta Política de Privacidade, o usuário poderá entrar em contato por meio do endereço eletrônico: suporte@newsoftwarebr.com.br.</p>', '1.0', '2026-02-20 21:09:15', NULL, 1, 'publicado', 'Política de Privacidade | NTW - New Software', 'Conheça nossa política de privacidade e saiba como protegemos seus dados na NTW - New Software.', '', '2026-02-21 00:09:15', '2026-02-22 14:30:40');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `blog_categorias`
--
ALTER TABLE `blog_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `blog_comentarios`
--
ALTER TABLE `blog_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_curtidas`
--
ALTER TABLE `blog_curtidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `blog_newsletter`
--
ALTER TABLE `blog_newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cliente_assinaturas`
--
ALTER TABLE `cliente_assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `cliente_assinaturas_contratos`
--
ALTER TABLE `cliente_assinaturas_contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `cliente_documentos`
--
ALTER TABLE `cliente_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `cliente_documentos_categorias`
--
ALTER TABLE `cliente_documentos_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cliente_faturas`
--
ALTER TABLE `cliente_faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_logs`
--
ALTER TABLE `cliente_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `cliente_sistemas`
--
ALTER TABLE `cliente_sistemas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `cliente_sistemas_historico`
--
ALTER TABLE `cliente_sistemas_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `contrato_clausulas`
--
ALTER TABLE `contrato_clausulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `contrato_historico`
--
ALTER TABLE `contrato_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `indicacoes`
--
ALTER TABLE `indicacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `indicacoes_config`
--
ALTER TABLE `indicacoes_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `indicacoes_historico`
--
ALTER TABLE `indicacoes_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `metricas_empresas`
--
ALTER TABLE `metricas_empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `newsletter_campanhas`
--
ALTER TABLE `newsletter_campanhas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `newsletter_config`
--
ALTER TABLE `newsletter_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pagamento_config_geracao`
--
ALTER TABLE `pagamento_config_geracao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pagamento_faturas`
--
ALTER TABLE `pagamento_faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `pagamento_fatura_itens`
--
ALTER TABLE `pagamento_fatura_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamento_logs`
--
ALTER TABLE `pagamento_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `planos_caracteristicas`
--
ALTER TABLE `planos_caracteristicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de tabela `planos_categorias`
--
ALTER TABLE `planos_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `planos_contratados`
--
ALTER TABLE `planos_contratados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `politica_historico`
--
ALTER TABLE `politica_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `politica_privacidade`
--
ALTER TABLE `politica_privacidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_atualizar_metricas` ON SCHEDULE EVERY 1 MONTH STARTS '2026-02-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    CALL sp_atualizar_metricas_mensais();
END$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_gerar_faturas_mensais` ON SCHEDULE EVERY 1 DAY STARTS '2026-02-26 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_plano_id INT;
    DECLARE v_cliente_id INT;
    DECLARE v_contrato_id INT;
    DECLARE v_valor_mensal DECIMAL(10,2);
    DECLARE v_dia_vencimento INT;
    DECLARE v_data_proxima DATE;
    
    DECLARE cur CURSOR FOR 
        SELECT pc.id, pc.cliente_id, c.id as contrato_id, pc.valor_mensal, pc.dia_vencimento
        FROM planos_contratados pc
        JOIN contratos c ON c.plano_contratado_id = pc.id
        WHERE pc.status = 'ativo' 
          AND (pc.data_proxima_fatura <= CURDATE() OR pc.data_proxima_fatura IS NULL);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_plano_id, v_cliente_id, v_contrato_id, v_valor_mensal, v_dia_vencimento;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Gerar nova fatura
        IF v_valor_mensal > 0 THEN
            INSERT INTO pagamento_faturas 
                (cliente_id, contrato_id, plano_contratado_id, numero_fatura, tipo, 
                 mes_referencia, valor, valor_total, data_emissao, data_vencimento, status)
            VALUES (
                v_cliente_id,
                v_contrato_id,
                v_plano_id,
                CONCAT('FAT-', DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y%m'), '-', LPAD(v_plano_id, 4, '0')),
                'mensalidade',
                DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                v_valor_mensal,
                v_valor_mensal,
                CURDATE(),
                DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), CONCAT('%Y-%m-', LPAD(v_dia_vencimento, 2, '0'))),
                'pendente'
            );
            
            -- Atualizar data da próxima fatura
            UPDATE planos_contratados 
            SET data_proxima_fatura = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
            WHERE id = v_plano_id;
        END IF;
    END LOOP;
    
    CLOSE cur;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
