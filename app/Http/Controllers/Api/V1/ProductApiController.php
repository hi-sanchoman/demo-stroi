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
        $collection = Product::with(['categories'])->get();
        
        return new ProductResource($collection);
    }

    public function payments() {
        $applications = ApplicationProduct::with(['application', 'product', 'category'])->get();
        // dd($applications->toArray());

        $data = [];

        foreach ($applications as $product) {
            if ($product->application != null && $product->application->status == 'in_progress') {
                if ($product->price > 0 && $product->company != null) {
                    $data[] = $product;
                }
            }
        }
        
        return [
            'data' => $data,
        ];
    }
}