<div align="center">

# 🚀 Digital Five — Sistema de Gestão para Desenvolvimento de Software

<p align="center">
  <img src="https://img.shields.io/badge/version-2.0.0-blue.svg"/>
  <img src="https://img.shields.io/badge/PHP-8.0-777bb4.svg"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479a1.svg"/>
  <img src="https://img.shields.io/badge/JavaScript-ES6+-f7df1e.svg"/>
  <img src="https://img.shields.io/badge/jQuery-3.6-0769ad.svg"/>
  <img src="https://img.shields.io/badge/Chart.js-4.4-ff6384.svg"/>
  <img src="https://img.shields.io/badge/status-em%20desenvolvimento-orange.svg"/>
  <img src="https://img.shields.io/badge/license-MIT-green.svg"/>
</p>

<p align="center">
  <img src="https://i.ibb.co/hp1bmj8/vecteezy-coding-3d-rendering-icon-illustration-28587717.png" width="100"/>
</p>

<b>🇧🇷 Plataforma de gestão SaaS e assinaturas da Digital Five</b><br>
<i>Painel administrativo + Portal do cliente + Assinatura digital + Pagamentos automatizados + Hub multi-produto</i>

</div>

---

## 📋 Sobre o Projeto

A **Digital Five** é uma plataforma SaaS desenvolvida para automatizar todo o ciclo de gestão de empresas de software:

- Gestão de clientes
- Contratos digitais
- Faturamento recorrente
- Pagamentos automáticos
- Acompanhamento de projetos
- Portal completo do cliente
- Gestão multi-produto para assinaturas de sistemas como Arcon e futuros SaaS

O sistema possui áreas segregadas para **Administradores** e **Clientes**, integradas ao mesmo banco de dados.

---

## 🔗 Integração Multi-Produto SaaS

O Gestor é a fonte de verdade para planos, contratos, faturas, pagamentos e assinaturas. O Arcon é o primeiro produto SaaS plugado ao Gestor, mas a arquitetura já suporta outros sistemas por meio das tabelas `produtos_saas`, `planos_saas`, `assinaturas_saas` e `eventos_assinatura`.

Fluxo publicado:

- Admin cria ou localiza o cliente no Gestor.
- Admin vincula o cliente ao produto Arcon pelo card de integração.
- Admin escolhe o plano SaaS, ativa, suspende ou cancela a assinatura.
- O Gestor envia o status ao Supabase do Arcon.
- O Arcon exibe os planos espelhados do Gestor na tela de Configurações.
- O clique em **Assinar** abre WhatsApp com mensagem pronta para o desenvolvedor e dados do cliente/plano.

Endpoint público de planos:

```text
GET /planos-api.php?perfil=mei&limit=6
GET /planos-api.php?perfil=empresa&limit=6
```

Campos relevantes retornados para os apps:

- `nome`, `slug`, `descricao`, `preco_formatado`, `periodo`
- `caracteristicas`
- `assinar_url`
- `detalhes_url`
- `link_whatsapp`

---

# ✨ Funcionalidades

## 🔧 Painel Administrativo

### 📊 Planos
- Categorias (Sites, Sistemas, Bots IA)
- Características dinâmicas
- Perfis MEI/Empresa
- Badges e ordenação

### 📝 Blog
- Categorias e comentários
- SEO (meta tags)
- Agendamento
- Status de publicação

### 📧 Newsletter
- Campanhas em massa
- Templates
- Estatísticas
- Importação/exportação CSV

### 📄 Contratos
- Adesão / Renovação / Cancelamento
- PDF automático
- Preview em tempo real
- Multas automáticas

### 👥 Clientes
- PF/PJ
- Usuário automático
- Histórico de atividades

### 🤝 Indicações
- Código automático
- Desconto 10%
- Ranking de indicadores

### 💰 Pagamentos
- Mercado Pago
- PagBank
- PIX e Cartão
- Webhooks
- Faturas automáticas

### 📊 Status de Sistemas
- 21 status
- Percentual de progresso
- Histórico completo

---

## 👤 Área do Cliente

### 🔐 Login Seguro
- Email ou username
- Bloqueio por tentativas
- Tema dark/light

### 📊 Dashboard
- Estatísticas
- Alertas
- Sistemas em andamento

### ✍️ Assinatura Digital
- Canvas para assinatura
- Registro IP/data
- Preview

### 💰 Faturas
- PIX QR Code
- Cartão
- Histórico

### 📁 Documentos
- Contratos + Faturas unificados
- Busca inteligente

---

# 🗺️ Roadmap

## ✅ Concluído
- Módulo de Planos
- Blog
- Newsletter
- Política de Privacidade
- Gerador de Contratos
- Clientes
- Indicações
- Status de Sistemas
- Área do Cliente
- Assinatura Digital
- Documentos
- Mercado Pago
- PagBank
- Faturas automáticas
- Tema Dark/Light

## 🔄 Em Desenvolvimento
- Sistema de Tickets
- API REST
- Relatórios Financeiros
- Dashboard em tempo real
- WebSockets
- WhatsApp Business API
- Notas Fiscais
- Backup automático

## 📅 Planejado
- App Mobile (React Native)
- Integração Asaas
- Contabilidade
- Google Analytics
- Sistema de metas
- Chat interno
- Pesquisa NPS
- Integração Trello
- Certificado A1
- RBAC

---

# 🐛 Bugs Conhecidos

## 🚨 Críticos

| ID | Descrição | Status |
|----|-----------|--------|
| BUG-001 | Webhook Mercado Pago falha | 🟡 Em análise |

## ⚠️ Médios

| ID | Descrição | Status |
|----|-----------|--------|
| BUG-003 | Assinatura não renderiza no Safari | 🟡 Em análise |
| BUG-005 | Email de boas-vindas não enviado | 🟡 Em análise |

## 📝 Baixos

| ID | Descrição | Status |
|----|-----------|--------|
| BUG-007 | Máscara CPF falha | 🟢 Corrigido |

---

# 🛠️ Tecnologias

## Backend
| Tecnologia | Uso |
|------------|-----|
| PHP 8+ | Core |
| MySQL | Banco |
| PHPMailer | Emails |
| Mercado Pago SDK | Pagamentos |
| PagBank SDK | Pagamentos |
| Composer | Dependências |

## Frontend
| Tecnologia |
|------------|
| HTML5 |
| CSS3 |
| JavaScript ES6 |
| jQuery |
| Chart.js |
| Font Awesome |

---

# 🗄️ Banco de Dados

- Engine: InnoDB
- Charset: utf8mb4
- +45 tabelas

Principais:

| Tabela | Função |
|--------|--------|
| clientes | Usuários |
| contratos | Contratos |
| pagamento_faturas | Cobranças |
| cliente_assinaturas | Assinaturas |
| cliente_sistemas | Projetos |

---

# 📸 Capturas (Cliente)

| Dashboard | Planos |
|:---:|:---:|
| <img src="https://i.ibb.co/jvxbNbWp/image.png"/> | <img src="https://i.ibb.co/QF3TNhcR/image.png"/> |

---

# 📸 Capturas (Admin)

| Dashboard | Planos |
|:---:|:---:|
| <img src="https://i.ibb.co/qY4zKPrD/image.png"/> | <img src="https://i.ibb.co/tTFg5GJp/image.png"/> |

---



# 📄 Licença

Distribuído sob a licença **MIT**.  
Veja o arquivo `LICENSE` para mais detalhes.

---

# 👨‍💻 Autor

**Renan Barbosa** — Desenvolvedor Full Stack

- GitHub: https://github.com/bosadevv
- LinkedIn: https://www.linkedin.com/in/renan-barbosa-6100393b4/
- Email: (em breve)
- Discord: barbosa.dev

---

# 🙏 Agradecimentos

- Mercado Pago
- PagBank
- PHPMailer
- Chart.js
- Font Awesome

---

<p align="center">
  <img src="https://i.ibb.co/pjt214CY/png-transparent-black-cat-kitten-cat-mammal-cat-like-mammal-animals-thumbnail-removebg-preview.png" width="200"/>
</p>

<p align="center">
  <b>Feito por Guilherme Augusto</b><br>
  <i>Em desenvolvimento — Versão 1.0.0</i>
</p>

<p align="center">
  <a href="#-digital-five--sistema-de-gestão-para-desenvolvimento-de-software">
    ⬆️ Voltar ao topo
  </a>
</p>

---

## ⚠️ Aviso

> 🚧 **Versão em desenvolvimento**
>
> Provavelmente você poderá encontrar alguns bugs durante o uso.  
> Caso encontre algum, por favor me relate 😄

<br>

💬 **Dúvidas ou sugestões?**  
Estou à disposição! Fique totalmente à vontade para entrar em contato.

---
