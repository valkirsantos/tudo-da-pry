# PROMPT INICIAL — Cole no Claude Code para gerar o scaffold completo

Copie o bloco abaixo e cole diretamente no terminal do Claude Code
dentro da pasta onde quer criar o projeto:

────────────────────────────────────────────────────────────────────
PROMPT 1 — Scaffold Laravel (cole no terminal com `claude`)
────────────────────────────────────────────────────────────────────

claude "Crie o scaffold completo do projeto Laravel 11 chamado
tudo-da-pry-api seguindo exatamente o CLAUDE.md deste repositório.

Execute na ordem:

1. composer create-project laravel/laravel tudo-da-pry-api
2. Entre na pasta e instale: laravel/sanctum, predis/predis,
   spatie/laravel-permission, intervention/image,
   league/flysystem-aws-s3-v3, minishlink/web-push
3. Configure o .env com as variáveis do CLAUDE.md (valores locais de dev)
4. Crie todas as migrations nesta ordem:
   - create_users_table (adicionar: celular, role, push_subscription, ativo)
   - create_otp_codes_table
   - create_products_table
   - create_product_photos_table
   - create_orders_table
   - create_order_items_table
   - create_installments_table
   - create_payment_proofs_table
   - create_notifications_table
   - create_notification_broadcasts_table
5. Crie os Models com relationships completas (User, Product,
   ProductPhoto, Order, OrderItem, Installment, PaymentProof,
   Notification, NotificationBroadcast)
6. Crie os Seeders:
   - UserSeeder: 1 vendedora (role:vendedor, celular:11999999999)
                 + 3 clientes de exemplo
   - ProductSeeder: 8 produtos (bolsas, sapatos, perfumes, relógios)
7. Crie o arquivo routes/api.php com todas as rotas do CLAUDE.md
8. Crie os controllers vazios com os métodos listados no CLAUDE.md
9. Crie OtpService.php e InstallmentService.php com assinaturas
10. Rode php artisan migrate --seed
11. Configure cors.php para aceitar FRONTEND_URL do .env"


────────────────────────────────────────────────────────────────────
PROMPT 2 — Implementar autenticação OTP (cole após o Prompt 1)
────────────────────────────────────────────────────────────────────

claude "Implemente o fluxo completo de autenticação OTP no projeto:

1. OtpService.php:
   - generate(string $celular): string
     → gera código 6 dígitos
     → salva hash bcrypt no Redis com key 'otp:{celular}' TTL 300s
     → salva tentativas em 'otp_tries:{celular}' TTL 300s (max 3)
     → retorna o código em texto puro (para enviar via SMS)

   - verify(string $celular, string $codigo): bool
     → verifica rate limit (lança TooManyOtpAttemptsException se > 3)
     → busca hash no Redis
     → bcrypt check
     → se válido: deleta chave do Redis, retorna true

2. OtpController.php:
   - send(Request $request):
     → valida celular (regex BR)
     → chama OtpService::generate()
     → dispatcha job SendOtpSms::dispatch($celular, $codigo)
     → retorna { message: 'Código enviado', expires_in: 300 }

   - verify(Request $request):
     → valida celular + codigo
     → chama OtpService::verify()
     → se inválido: retorna 422 { error: 'Código inválido ou expirado' }
     → busca ou cria User pelo celular (role padrão: cliente)
     → cria Sanctum token com nome 'pwa-token'
     → retorna { token, user: { id, nome, celular, role } }

3. Job SendOtpSms.php:
   → usa Twilio SMS API (credenciais do .env)
   → mensagem: 'Tudo da Pry: seu código é {codigo}. Válido por 5 minutos.'
   → implementar fallback de log se TWILIO_SID estiver vazio (dev mode)

4. Middleware EnsureRole.php:
   → recebe roles permitidas como parâmetro
   → verifica $request->user()->role
   → retorna 403 se não autorizado
   → registrar no bootstrap/app.php como 'role'"


────────────────────────────────────────────────────────────────────
PROMPT 3 — Produtos e Catálogo (cole após o Prompt 2)
────────────────────────────────────────────────────────────────────

claude "Implemente o CRUD completo de produtos:

1. ProductController.php:
   - index(): lista produtos ativos com fotos, filtro por categoria
     e busca por nome. Paginação de 12 por página.
     Público (sem auth).
   - show($id): produto + fotos + estoque. Público.
   - store(Request): cria produto + faz upload das fotos para S3
     usando URL pré-assinada. Apenas role:vendedor.
   - update(Request, $id): atualiza dados + gerencia fotos.
   - destroy($id): soft delete (campo ativo = false).

2. S3PresignedService.php:
   - generateUploadUrl(string $path, string $mimeType): string
     → gera URL pré-assinada para PUT no S3 com expiração 5min
   - generateDownloadUrl(string $path): string
     → gera URL pré-assinada para GET com expiração 1h

3. ProductResource.php e ProductCollection.php:
   → incluir: id, nome, descricao, preco, estoque, categoria,
              ativo, fotos (array de URLs pré-assinadas), created_at

4. StoreProductRequest.php e UpdateProductRequest.php:
   → validações completas com mensagens em português"


────────────────────────────────────────────────────────────────────
PROMPT 4 — Pedidos e Parcelamento (cole após o Prompt 3)
────────────────────────────────────────────────────────────────────

claude "Implemente o fluxo completo de pedidos:

1. InstallmentService.php:
   - generate(Order $order): array
     → recebe order com num_parcelas e dia_vencimento
     → calcula valor de cada parcela (total / num_parcelas, arredonda)
     → primeira parcela absorve diferença de centavos
     → calcula data_vencimento: mês atual + N, dia = dia_vencimento
       (se dia não existe no mês ex: 31/fev → último dia do mês)
     → cria registros Installment e retorna array

2. OrderController.php:
   - store(StoreOrderRequest): cria pedido do cliente
     → valida estoque de cada produto
     → cria Order + OrderItems dentro de DB::transaction
     → decrementa estoque
     → se parcelado: chama InstallmentService::generate()
     → retorna OrderResource com items e installments

   - storeBySeller(StoreSellerOrderRequest): venda direta pela vendedora
     → mesmo fluxo do store() mas recebe user_id do cliente
     → se cliente não existir por celular: cria com role:cliente
     → salva created_by = vendedora autenticada
     → dispara notificação para o cliente

   - updateStatus(Request, $id): atualiza status_pedido
     → apenas role:vendedor
     → dispara evento PedidoStatusAtualizado
     → evento dispara notificação para cliente

3. OrderResource.php: serializa pedido completo com items,
   installments e payment_proofs"


────────────────────────────────────────────────────────────────────
PROMPT 5 — Comprovantes e Validação (cole após o Prompt 4)
────────────────────────────────────────────────────────────────────

claude "Implemente o fluxo de comprovantes de pagamento:

1. PaymentProofController.php:
   - store(Request): cliente envia comprovante
     → gera URL pré-assinada S3 para o arquivo
     → cria registro PaymentProof com status 'pendente'
     → path: 'comprovantes/{order_id}/{uuid}.{ext}'
     → dispara evento ComprovantEnviado
     → retorna { upload_url, proof_id } para o cliente fazer PUT direto no S3

   - index(): vendedora lista comprovantes pendentes
     → filtro por tipo (avista | parcela) e status
     → inclui URL de download pré-assinada

   - update(Request, $id): vendedora aprova ou rejeita
     → recebe { action: 'approve'|'reject', motivo? }
     → atualiza status + validado_em + validado_por
     → se approve:
         → se à vista: atualiza order.status_pagamento = 'pago'
         → se parcela: atualiza installment.status = 'pago'
                        verifica se todas as parcelas pagas
                        → se sim: order.status_pagamento = 'pago'
     → dispara ComprovantValidadoNotification para o cliente

2. Notification system:
   - NotificationController@index: lista notificações do usuário autenticado
   - NotificationController@markRead: marca como lida
   - NotificationController@subscribe: salva push_subscription no user

3. Job EnviarAlertaVencimento.php:
   → busca installments onde data_vencimento = amanhã e status = 'pendente'
   → para cada um: cria Notification interna + envia Web Push"


────────────────────────────────────────────────────────────────────
PROMPT 6 — Scaffold Vue 3 PWA (cole em nova janela na pasta /frontend)
────────────────────────────────────────────────────────────────────

claude "Crie o projeto frontend PWA com Vue 3 + Vite dentro da pasta frontend/:

1. npm create vue@latest frontend -- --typescript=false --router=true
   --pinia=true --eslint=true

2. Instale: axios, @vueuse/core, vite-plugin-pwa

3. Configure vite.config.js com VitePWA:
   - name: 'Tudo da Pry'
   - short_name: 'TudoPry'
   - theme_color: '#3D1F0D'
   - background_color: '#FAF3EA'
   - display: 'standalone'
   - start_url: '/'
   - icons 192x192 e 512x512

4. Crie src/styles/variables.css com todas as variáveis CSS do CLAUDE.md
   e importe no main.js

5. Crie src/services/api.js:
   - instância axios com baseURL do .env (VITE_API_URL)
   - interceptor de request: adiciona Bearer token do localStorage
   - interceptor de response: redireciona para /login se 401

6. Crie src/stores/auth.js (Pinia):
   - state: { user, token, isAuthenticated }
   - actions: sendOtp, verifyOtp, logout

7. Configure o router com as rotas:
   - Públicas: /catalogo, /produto/:id, /login
   - Protegidas (cliente): /carrinho, /pagamento, /pedidos,
     /pedidos/:id/parcelas, /notificacoes
   - Protegidas (vendedor): /vendedor/dashboard, /vendedor/nova-venda,
     /vendedor/pedidos, /vendedor/comprovantes, /vendedor/notificar,
     /vendedor/clientes, /vendedor/produtos, /vendedor/entregas,
     /vendedor/relatorios
   - Navigation guard: verifica token + role

8. Crie os componentes base em src/components/ui/:
   - PryButton.vue (variantes: primary, outline, danger)
   - PryCard.vue
   - PryTag.vue (variantes: novo, promo, ok, warn, pendente)
   - PryInput.vue
   - PryDivider.vue
   - PryTopBar.vue (com logo Tudo da Pry e slot de ações)
   - PryBottomNav.vue (com navegação por role)
   - PryTimeline.vue (recebe steps array, destaca o ativo)
   Todos usando exclusivamente as variáveis CSS do CLAUDE.md"


────────────────────────────────────────────────────────────────────
PROMPT 7 — Páginas do cliente (cole após o Prompt 6)
────────────────────────────────────────────────────────────────────

claude "Crie as páginas do cliente usando os componentes base:

1. CatalogoPage.vue:
   - Banner de novidades/promoções (busca broadcasts da API)
   - Campo de busca com debounce 300ms
   - Filtro de categorias (chips horizontais com scroll)
   - Grid 2 colunas de ProductCard
   - Infinite scroll (VueUse useIntersectionObserver)

2. LoginOtpPage.vue:
   - Campo de celular com máscara BR
   - Botão enviar código
   - Após envio: 6 inputs de dígito com foco automático
   - Contador regressivo de 60s para reenvio
   - Ao confirmar: redireciona para rota de origem (redirect query param)

3. CarrinhoPage.vue:
   - PryTimeline com 5 passos
   - Lista de itens com botão remover e ajuste de quantidade
   - Resumo com total
   - Botão continuar → /login?redirect=/pagamento

4. PagamentoPage.vue:
   - Seleção de método: Pix | Dinheiro | Parcelado
   - Se Parcelado: componente InstallmentPicker.vue
     (seleção de nº parcelas + dia de vencimento + tabela de previsão)
   - Se Pix ou Dinheiro: vai para ComprovantePage.vue após confirmar
   - Botão confirmar chama POST /orders

5. ComprovantePage.vue:
   - Mostra total e método
   - Área de upload com drag-and-drop
   - Chama POST /payment-proofs → recebe upload_url → faz PUT no S3
   - Progresso de upload
   - Ao concluir: vai para /pedidos

6. PedidosPage.vue:
   - Lista de pedidos com barra de progresso de entrega
   - Tag de status
   - Clique abre detalhe com parcelas se parcelado

7. ParcelasPage.vue:
   - Lista de parcelas com status e data
   - Para parcelas 'aguardando': mostra upload de comprovante
   - Integrado com S3 pré-assinado"


────────────────────────────────────────────────────────────────────
PROMPT 8 — Painel da vendedora (cole após o Prompt 7)
────────────────────────────────────────────────────────────────────

claude "Crie as páginas do painel da vendedora:

1. DashboardPage.vue:
   - 4 cards de métricas (vendas hoje, pedidos abertos,
     parcelas a vencer, notificações enviadas)
   - Gráfico de barras horizontais por categoria (usando CSS puro)
   - Lista de comprovantes pendentes com ações rápidas

2. NovaVendaPage.vue — fluxo em 3 seções:
   Seção 1 — Cliente:
     - Input de busca com debounce → GET /clients?q=
     - Lista de resultados clicáveis
     - Se não encontrado: formulário inline de cadastro rápido
     - Cliente selecionado exibido com chip e botão de troca

   Seção 2 — Produtos:
     - Lista de produtos do catálogo
     - Controle de quantidade (+ / −) por produto
     - Total calculado em tempo real

   Seção 3 — Pagamento:
     - Botões Pix | Dinheiro | Parcelado
     - Se parcelado: selects de nº parcelas e dia vencimento
     - Botão Confirmar venda → POST /orders/seller

3. ComprovantesPage.vue:
   - Filtro: Todos | À vista | Parcelas
   - Card por comprovante com preview do arquivo (imagem ou PDF)
   - Botões Validar / Rejeitar com modal de confirmação
   - Campo motivo ao rejeitar

4. NotificarPage.vue:
   - Seleção de tipo (chips)
   - Select de público-alvo
   - Campos título + mensagem com contador
   - Preview em tempo real no estilo do app do cliente
   - Histórico de broadcasts enviados

5. RelatoriosPage.vue:
   - Select de período (mês/ano)
   - Cards de métricas (total, ticket médio, pedidos, inadimplência)
   - Gráfico de produtos mais vendidos (barras CSS)
   - Tabela de parcelas em aberto
   - Botão exportar PDF (usa window.print() com CSS @media print)"
