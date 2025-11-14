<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Giay;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/giay
    public function index(Request $request)
    {
        $query = Giay::query();

        // basic filters
        if ($request->filled('ten_loai_giay')) {
            $query->where('ten_loai_giay', $request->ten_loai_giay);
        }
        if ($request->filled('ten_thuong_hieu')) {
            $query->where('ten_thuong_hieu', $request->ten_thuong_hieu);
        }

        $perPage = $request->get('per_page', 12);

        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    // GET /api/giay/{id}
    public function show($id)
    {
        $product = Giay::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }
}
