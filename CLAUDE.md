# CLAUDE.md — Tudo da Pry
> Este arquivo é lido automaticamente pelo Claude Code em cada sessão.
> Nunca remova ou renomeie este arquivo.

---

## Identidade do projeto

**Nome da loja:** Tudo da Pry  
**Tipo:** PWA (Progressive Web App) — sem instalação, apenas atalho na tela inicial  
**Stack:** Laravel 11 (API) + Vue 3 + Vite (frontend)  
**Banco:** PostgreSQL + Redis  
**Autenticação:** OTP via SMS (celular) + Laravel Sanctum (token)

---

## Paleta de cores (SEMPRE usar estas)

```
--pry-dark:    #3D1F0D   ← marrom escuro — topbar, botões primários, textos
--pry:         #E8C9A0   ← bege/creme    — logo, textos em fundo escuro
--pry-accent:  #C4965A   ← dourado       ← preços, destaques, ícones ativos
--pry-mid:     #8B5E3C   ← marrom médio  — textos secundários, ícones inativos
--pry-light:   #F5E6D3   ← bege claro    — superfícies, cards leves
--pry-surface: #FAF3EA   ← creme fundo   — background geral das telas
--pry-border:  #D4A96A   ← dourado suave — bordas, divisores
--pry-muted:   #7A5C44   ← marrom acinz. — placeholders, labels secundários
```

---

## Perfis de usuário

| Perfil | Acesso | Autenticação |
|--------|--------|--------------|
| **cliente** | App PWA — catálogo, carrinho, pedidos, parcelas, notificações | OTP SMS → Sanctum token |
| **vendedor** | Painel completo — produtos, pedidos, comprovantes, entregas, relatórios, nova venda, notificar clientes | OTP SMS → Sanctum token |

---

## Regras de negócio críticas

### Autenticação
- Cliente informa celular → API gera código 6 dígitos → salva hash (bcrypt) no Redis com TTL 5 min
- SMS enviado via Twilio (driver configurável em .env)
- Código validado → Sanctum emite token → retorna `{ token, user, role }`
- Se celular não existir no banco → cadastro automático (role: cliente)

### Formas de pagamento
| Tipo | Fluxo |
|------|-------|
| **Pix** | Cliente finaliza → envia comprovante → vendedora valida → pedido confirmado |
| **Dinheiro** | Cliente finaliza → envia comprovante → vendedora valida → pedido confirmado |
| **Parcelado** | Cliente escolhe nº parcelas (2–5) + dia do vencimento → sistema gera installments → cliente envia comprovante por parcela → vendedora valida cada uma |

### Comprovantes
- Upload direto para S3 via URL pré-assinada (arquivo não passa pelo servidor)
- `payment_proofs` referencia `order_id` (à vista) ou `installment_id` (parcela)
- Status: `pending` → `approved` | `rejected`
- Ao validar: dispara notificação push + notificação interna para o cliente

### Parcelas
- Campos obrigatórios: `numero_parcela`, `valor`, `data_vencimento`, `status`
- `data_vencimento` calculada: mês atual + N meses, dia = dia escolhido pelo cliente
- Job agendado (diário) verifica vencimentos dos próximos 2 dias → dispara notificação

### Nova venda pela vendedora
- Vendedora busca cliente por nome ou celular
- Se não encontrar → cadastra nova cliente (nome + celular obrigatórios)
- Monta pedido selecionando produtos + quantidades
- Define forma de pagamento e (se parcelado) nº de parcelas + dia do vencimento
- Pedido associado à cliente → notificação enviada para ela

### Notificações da vendedora
- Tipos: `novo_produto`, `promocao`, `aviso_geral`
- Destinos: todas as clientes | clientes do último mês | clientes com parcelas em aberto | clientes sem compra há 30+ dias
- Canais: notificação interna (tabela `notifications`) + Web Push (VAPID)

---

## Estrutura de banco de dados

```sql
-- Usuários (clientes e vendedoras)
users: id, nome, celular (unique), email?, role (cliente|vendedor),
       push_subscription (json), ativo, timestamps

-- OTP temporário (Redis, mas também tabela de log)
otp_codes: id, celular, codigo_hash, expires_at, used_at, timestamps

-- Produtos
products: id, nome, descricao, preco, estoque, categoria
          (bolsas|sapatos|perfumes|relogios|outros),
          ativo, created_by (FK users), timestamps
product_photos: id, product_id, path_s3, ordem, timestamps

-- Pedidos
orders: id, user_id (cliente), created_by (FK users — null se cliente, vendedora se venda direta),
        total, tipo_pagamento (pix|dinheiro|parcelado),
        num_parcelas?, dia_vencimento?,
        status_pedido (aguardando_pagamento|confirmado|separando|em_entrega|entregue|cancelado),
        status_pagamento (pendente|parcial|pago),
        endereco_entrega (json)?, observacoes?, timestamps

-- Itens do pedido
order_items: id, order_id, product_id, quantidade, preco_unitario, subtotal, timestamps

-- Parcelas (apenas para tipo_pagamento = parcelado)
installments: id, order_id, numero_parcela, valor,
              data_vencimento, status (pendente|aguardando_validacao|pago|atrasado),
              validado_em?, validado_por (FK users)?, timestamps

-- Comprovantes de pagamento
payment_proofs: id, order_id, installment_id (nullable — null = à vista),
                path_s3, nome_arquivo, tamanho_bytes,
                status (pendente|aprovado|rejeitado),
                motivo_rejeicao?,
                validado_em?, validado_por (FK users)?, timestamps

-- Notificações internas
notifications: id, user_id, tipo, titulo, mensagem,
               ref_type (order|installment|payment_proof|broadcast)?,
               ref_id?, lida (bool), enviada_push (bool), timestamps

-- Broadcasts da vendedora
notification_broadcasts: id, created_by (FK users),
                          tipo (novo_produto|promocao|aviso_geral),
                          titulo, mensagem, publico_alvo (json),
                          enviado_em, total_enviados, timestamps
```

---

## Estrutura de pastas Laravel

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/          OtpController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── PaymentProofController.php
│   │   ├── InstallmentController.php
│   │   ├── DeliveryController.php
│   │   ├── NotificationController.php
│   │   ├── BroadcastController.php
│   │   ├── ReportController.php
│   │   └── ClientController.php
│   ├── Requests/          (Form Requests por recurso)
│   └── Resources/         (API Resources por recurso)
├── Models/
│   User, Product, ProductPhoto, Order, OrderItem,
│   Installment, PaymentProof, Notification, NotificationBroadcast
├── Notifications/
│   ParcelaVencendoNotification.php
│   PedidoConfirmadoNotification.php
│   ComprovantValidadoNotification.php
│   NovidadeLojaNotification.php
├── Events/
│   PagamentoValidado.php
│   PedidoStatusAtualizado.php
│   ComprovantEnviado.php
├── Jobs/
│   SendOtpSms.php
│   EnviarAlertaVencimento.php
│   ProcessarNotificacaoPush.php
│   EnviarBroadcastClientes.php
├── Policies/
│   OrderPolicy.php  ProductPolicy.php  InstallmentPolicy.php
└── Services/
    OtpService.php  S3PresignedService.php  InstallmentService.php
```

---

## Rotas da API (prefixo /api/v1)

```
# Autenticação (pública)
POST /auth/send-otp          → OtpController@send
POST /auth/verify-otp        → OtpController@verify

# Catálogo (pública)
GET  /products               → ProductController@index
GET  /products/{id}          → ProductController@show

# Área do cliente (auth:sanctum, role:cliente|vendedor)
GET  /orders                 → OrderController@index
POST /orders                 → OrderController@store
GET  /orders/{id}            → OrderController@show
GET  /orders/{id}/installments → InstallmentController@index
POST /payment-proofs         → PaymentProofController@store  (upload URL pré-assinada)
GET  /notifications          → NotificationController@index
POST /notifications/read     → NotificationController@markRead
POST /push/subscribe         → NotificationController@subscribe

# Área da vendedora (auth:sanctum, role:vendedor)
POST /products               → ProductController@store
PUT  /products/{id}          → ProductController@update
DELETE /products/{id}        → ProductController@destroy
GET  /clients                → ClientController@index
POST /clients                → ClientController@store
GET  /payment-proofs         → PaymentProofController@index
PUT  /payment-proofs/{id}    → PaymentProofController@update  (validar/rejeitar)
PUT  /orders/{id}/status     → OrderController@updateStatus
PUT  /orders/{id}/delivery   → DeliveryController@update
GET  /reports/sales          → ReportController@sales
GET  /reports/installments   → ReportController@installments
POST /broadcasts             → BroadcastController@store
GET  /broadcasts             → BroadcastController@index
POST /orders/seller          → OrderController@storeBySeller  (venda direta)
```

---

## Estrutura de pastas Vue 3

```
frontend/
├── public/
│   ├── manifest.json        (PWA)
│   └── icons/               (ícones 192x192, 512x512)
├── src/
│   ├── main.js
│   ├── router/index.js
│   ├── stores/              (Pinia)
│   │   auth.js  cart.js  orders.js  notifications.js
│   ├── services/
│   │   api.js  push.js
│   ├── composables/
│   │   useToast.js  useUpload.js  useInstallments.js
│   ├── components/
│   │   ui/       Button, Card, Tag, Divider, Modal
│   │   layout/   TopBar, BottomNav, Timeline
│   │   product/  ProductCard, ProductGrid
│   │   order/    OrderCard, DeliveryProgress
│   │   payment/  PaymentSelector, InstallmentPicker, UploadProof
│   └── pages/
│       cliente/
│         CatalogoPage.vue  ProdutoPage.vue  CarrinhoPage.vue
│         LoginOtpPage.vue  PagamentoPage.vue  ComprovantePage.vue
│         ParceladoPage.vue  PedidosPage.vue  ParcelasPage.vue
│         NotificacoesPage.vue
│       vendedor/
│         DashboardPage.vue  NovaVendaPage.vue  PedidosPage.vue
│         ComprovantesPage.vue  NotificarPage.vue  ClientesPage.vue
│         ProdutosPage.vue  EntregasPage.vue  RelatoriosPage.vue
```

---

## Variáveis de ambiente (.env)

```env
APP_NAME="Tudo da Pry"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tudo_da_pry
DB_USERNAME=postgres
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# SMS — Twilio
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_FROM=

# Storage S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=tudo-da-pry

# Web Push VAPID
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:contato@tudodapry.com.br

# Frontend
FRONTEND_URL=http://localhost:5173
```

---

## Convenções de código

- **PHP:** PSR-12, tipagem estrita (`declare(strict_types=1)`)
- **Respostas API:** sempre via `ApiResource` com `{ data, meta?, message? }`
- **Erros:** `{ error: true, message, errors? }` com HTTP status correto
- **Vue:** Composition API + `<script setup>` em todos os componentes
- **CSS:** apenas variáveis CSS definidas acima — nunca hardcode de cor
- **Commits:** `feat:`, `fix:`, `chore:`, `docs:` (Conventional Commits)

---

## Jobs agendados (app/Console/Kernel.php)

```php
// Roda diariamente às 08:00
$schedule->job(new EnviarAlertaVencimento)->dailyAt('08:00');
```

O job `EnviarAlertaVencimento`:
1. Busca installments com `data_vencimento` = hoje + 1 dia e status `pendente`
2. Para cada um, dispara `ParcelaVencendoNotification` (canal: database + push)

---

## Notas de segurança

- OTP: máximo 3 tentativas por celular por janela de 5 min (rate limit no Redis)
- Upload: validar mimetype server-side (apenas image/jpeg, image/png, application/pdf)
- Tamanho máximo de upload: 5MB
- Políticas Laravel protegem todos os recursos por role
- CORS configurado apenas para `FRONTEND_URL`
- Sanctum tokens expiram em 30 dias
