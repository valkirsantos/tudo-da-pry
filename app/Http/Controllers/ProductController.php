<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Services\S3PresignedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly S3PresignedService $s3Service,
    ) {}

    /**
     * Lista produtos ativos com filtros e paginação.
     * Públicos (sem autenticação).
     */
    public function index(Request $request): ProductCollection
    {
        $query = Product::ativo();

        // Filtro por categoria
        if ($request->has('categoria')) {
            $query->where('categoria', $request->string('categoria'));
        }

        // Busca por nome
        if ($request->has('search')) {
            $query->where('nome', 'ilike', '%' . $request->string('search') . '%');
        }

        $products = $query
            ->with('photos')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return new ProductCollection($products);
    }

    /**
     * Exibe um produto com fotos e estoque.
     * Público (sem autenticação).
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('photos')->findOrFail($id);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Cria um novo produto com fotos.
     * Apenas role:vendedor.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Cria o produto
        $product = Product::create([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'],
            'preco' => $validated['preco'],
            'estoque' => $validated['estoque'],
            'categoria' => $validated['categoria'],
            'ativo' => true,
            'created_by' => $request->user()->id,
        ]);

        // Cria as fotos
        foreach ($validated['fotos'] as $index => $fotoPath) {
            ProductPhoto::create([
                'product_id' => $product->id,
                'path_s3' => $fotoPath,
                'ordem' => $index + 1,
            ]);
        }

        $product->load('photos');

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Produto criado com sucesso.',
        ], 201);
    }

    /**
     * Atualiza um produto e gerencia suas fotos.
     * Apenas role:vendedor.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validated();

        // Atualiza campos do produto
        if (isset($validated['nome'])) {
            $product->nome = $validated['nome'];
        }
        if (isset($validated['descricao'])) {
            $product->descricao = $validated['descricao'];
        }
        if (isset($validated['preco'])) {
            $product->preco = $validated['preco'];
        }
        if (isset($validated['estoque'])) {
            $product->estoque = $validated['estoque'];
        }
        if (isset($validated['categoria'])) {
            $product->categoria = $validated['categoria'];
        }

        $product->save();

        // Remove fotos se solicitado
        if (isset($validated['fotos_remove'])) {
            ProductPhoto::whereIn('id', $validated['fotos_remove'])
                ->where('product_id', $product->id)
                ->delete();
        }

        // Adiciona novas fotos
        if (isset($validated['fotos_add'])) {
            $maxOrdem = $product->photos()->max('ordem') ?? 0;

            foreach ($validated['fotos_add'] as $index => $fotoPath) {
                ProductPhoto::create([
                    'product_id' => $product->id,
                    'path_s3' => $fotoPath,
                    'ordem' => $maxOrdem + $index + 1,
                ]);
            }
        }

        $product->load('photos');

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Produto atualizado com sucesso.',
        ]);
    }

    /**
     * Soft deleta um produto (ativo = false).
     * Apenas role:vendedor.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $product->ativo = false;
        $product->save();

        return response()->json([
            'message' => 'Produto removido com sucesso.',
        ]);
    }
}
