<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $numero_parcela
 * @property numeric $valor
 * @property \Illuminate\Support\Carbon $data_vencimento
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $validado_em
 * @property int|null $validado_por
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentProof> $paymentProofs
 * @property-read int|null $payment_proofs_count
 * @property-read \App\Models\User|null $validatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereDataVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereNumeroParcela($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereValidadoEm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereValidadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereValor($value)
 */
	class Installment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $tipo
 * @property string $titulo
 * @property string $mensagem
 * @property string|null $ref_type
 * @property int|null $ref_id
 * @property bool $lida
 * @property bool $enviada_push
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereEnviadaPush($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereLida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereRefType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $created_by
 * @property string $tipo
 * @property string $titulo
 * @property string $mensagem
 * @property array<array-key, mixed> $publico_alvo
 * @property \Illuminate\Support\Carbon|null $enviado_em
 * @property int $total_enviados
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereEnviadoEm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast wherePublicoAlvo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereTotalEnviados($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationBroadcast whereUpdatedAt($value)
 */
	class NotificationBroadcast extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $created_by
 * @property numeric $total
 * @property string $tipo_pagamento
 * @property int|null $num_parcelas
 * @property int|null $dia_vencimento
 * @property string $status_pedido
 * @property string $status_pagamento
 * @property array<array-key, mixed>|null $endereco_entrega
 * @property string|null $observacoes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Installment> $installments
 * @property-read int|null $installments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentProof> $paymentProofs
 * @property-read int|null $payment_proofs_count
 * @property-read \App\Models\User|null $seller
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiaVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereEnderecoEntrega($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNumParcelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereObservacoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatusPagamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatusPedido($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTipoPagamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantidade
 * @property numeric $preco_unitario
 * @property numeric $subtotal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem wherePrecoUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $celular
 * @property string $codigo_hash
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereCodigoHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtpCode whereUsedAt($value)
 */
	class OtpCode extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int|null $installment_id
 * @property string $path_s3
 * @property string $nome_arquivo
 * @property int $tamanho_bytes
 * @property string $status
 * @property string|null $motivo_rejeicao
 * @property \Illuminate\Support\Carbon|null $validado_em
 * @property int|null $validado_por
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Installment|null $installment
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\User|null $validatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereInstallmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereMotivoRejeicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereNomeArquivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof wherePathS3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereTamanhoBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereValidadoEm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereValidadoPor($value)
 */
	class PaymentProof extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property numeric $preco
 * @property int $estoque
 * @property string $categoria
 * @property bool $ativo
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductPhoto> $photos
 * @property-read int|null $photos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product ativo()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEstoque($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePreco($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string $path_s3
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto wherePathS3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhoto whereUpdatedAt($value)
 */
	class ProductPhoto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nome
 * @property string $celular
 * @property string|null $email
 * @property string $role
 * @property array<array-key, mixed>|null $push_subscription
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotificationBroadcast> $broadcasts
 * @property-read int|null $broadcasts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $createdOrders
 * @property-read int|null $created_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePushSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

