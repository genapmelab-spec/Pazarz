<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getProducts($request->all());

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Product not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}
