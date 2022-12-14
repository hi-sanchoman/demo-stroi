<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Models\Application;
use App\Models\ApplicationProduct;

class ProductApiController extends Controller 
{
    public function index(Request $request) {
        if ($request->has('q')) {
            return Product::query()
                ->where('name', 'like', '%' . $request->q . '%')
                ->get();
        }

        $collection = Product::query()
            // ->with(['categories'])
            // ->limit(100)
            ->get();
        
        return new ProductResource($collection);
    }
}