<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'price',
                'tax_percentage',
                'stock_quantity',
            ]);

        return response()->json([
            'data' => $products,
        ]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $threshold = $request->integer('threshold', 5);

        $products = Product::query()
            ->where('stock_quantity', '<', $threshold)
            ->orderBy('stock_quantity')
            ->get([
                'id',
                'name',
                'code',
                'price',
                'tax_percentage',
                'stock_quantity',
            ]);

        return response()->json([
            'threshold' => $threshold,
            'data' => $products,
        ]);
    }
}
