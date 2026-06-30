# Plano de Desenvolvimento — Gestor Arcon Admin + Arcon SaaS

> Atualizado em: 30/06/2026

---

## ✅ Concluído

### Infraestrutura
- [x] Deploy Docker com PHP 8.2 + Apache + MariaDB
- [x] Traefik com SSL automático (`sistemas.digitalfive.com.br`)
- [x] Bootstrap automático: cria banco, schema e usuário admin no primeiro boot
- [x] Composer integrado: `mercadopago/dx-php` + `pagseguro/pagseguro-php-sdk`

### Painel Admin
- [x] Login com proteção contra brute-force
- [x] Dashboard com métricas
- [x] Gestão de clientes (criar, editar, visualizar, exportar, importar)
- [x] Gestão de contratos e gerador
- [x] Módulo de planos
- [x] Módulo de pagamentos (configuração de gateway MP/PagBank)
- [x] Blog com categorias
- [x] Newsletter
- [x] Política de privacidade e Termos de uso
- [x] Módulo de status de sistemas
- [x] Módulo de indicações e empresas
- [x] Módulo de avisos (com push ao Arcon via Supabase)
- [x] Páginas placeholder para módulos em desenvolvimento

### Integração Arcon ↔ Admin
- [x] `arcon-sync.php` — API GET (Arcon puxa status do Gestor)
- [x] `arcon-push.php` — Serviço PHP para push Admin→Supabase
- [x] `arcon-action.php` — Endpoint AJAX para ações no cliente
- [x] Card de integração Arcon na tela de visualizar cliente
- [x] Ações: vincular, ativar, suspender, cancelar, sync, atualizar plano SaaS
- [x] Avisos publicados em tempo real no Supabase (`avisos_sistema`)
- [x] `SUPABASE_SERVICE_KEY` configurada no `.env`

---

## 🔲 Próximos Passos

### P1 — Crítico (esta semana)

- [ ] **Migration Supabase** — aplicar colunas na tabela `empresas`:
  ```sql
  ALTER TABLE public.empresas
    ADD COLUMN IF NOT EXISTS gestor_cliente_id           bigint,
    ADD COLUMN IF NOT EXISTS gestor_plano_contratado_id  bigint,
    ADD COLUMN IF NOT EXISTS assinatura_status           text DEFAULT 'pendente',
    ADD COLUMN IF NOT EXISTS assinatura_atualizada_em    timestamptz,
    ADD COLUMN IF NOT EXISTS assinatura_cliente          text;
  ```
- [ ] **Testar integração end-to-end**: criar cliente no Admin → ativar assinatura → verificar no Arcon
- [ ] **Webhook de pagamento**: `webhook/mercadopago.php` e `webhook/pagbank.php` — ao receber pagamento confirmado, chamar `arcon-action.php` automaticamente

### P2 — Importante (próximas 2 semanas)

- [ ] **Módulo Planos (boletos)** — criar planos com geração de boleto bancário
- [ ] **Módulo Relatórios (boletos)** — listagem e status de cobranças
- [ ] **Módulo Lucro (empresa)** — receita bruta, custos, MRR, churn
- [ ] **Módulo API** — documentação e geração de chaves de API para clientes
- [ ] **Módulo Apoiadores** — cadastro de parceiros/afiliados
- [ ] **E-mail automático** ao ativar assinatura (aviso de boas-vindas + credenciais)
- [ ] **Bloqueio automático** no Arcon ao suspender/cancelar assinatura
- [ ] **Trial automático** — N dias gratuitos ao criar conta, expira e suspende

### P3 — Melhorias (próximo mês)

- [ ] **Dashboard com MRR/ARR** — gráficos de receita recorrente
- [ ] **Alerta de vencimento** — notificar cliente 7, 3 e 1 dia antes
- [ ] **Portal do cliente** (`/cliente`) — cliente vê faturas, paga online, acessa Arcon
- [ ] **Relatório de usuários** — quantos usam o Arcon por empresa, tempo médio de sessão
- [ ] **Integração WhatsApp** (já tem wuzapi rodando) — cobrar e notificar via WhatsApp
- [ ] **Módulo Avisos targetado** — aviso para empresa específica (não global)
- [ ] **Multi-produto** — suporte a mais de um sistema SaaS por cliente

---

## Arquitetura da Integração

```
[Admin PHP] ──POST──► [arcon-push.php] ──REST──► [Supabase]
                                                      │
[Arcon Vue] ──GET───► [arcon-sync.php] ◄─────────────┘
                                                      │
                                              [empresas table]
                                              [avisos_sistema]
                                              [profiles]
```

### Fluxo de ativação
1. Admin cria cliente no Gestor
2. Admin cria plano contratado e vincula ao cliente
3. Admin abre tela do cliente → card Arcon → seleciona plano SaaS → clica **Vincular ao Arcon**
4. Admin clica **Ativar Assinatura** → Supabase atualiza `assinatura_status=ativo`
5. Cliente abre o Arcon → sistema já está liberado com o plano correto

### Variáveis de ambiente (.env)
| Variável | Descrição |
|---|---|
| `SUPABASE_URL` | URL do projeto Supabase |
| `SUPABASE_SERVICE_KEY` | Service role key (nunca expor no frontend) |
| `ARCON_SYNC_KEY` | Chave compartilhada para a API de sync |
| `VITE_GESTOR_API_URL` | URL do Gestor (no .env do Arcon) |
| `VITE_GESTOR_API_KEY` | Mesma chave ARCON_SYNC_KEY (no .env do Arcon) |
