<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Http\Resources\ProductCategoryResource;

class CategoryApiController extends Controller 
{
    public function index(Request $request) {
        $collection = ProductCategory::get();
        
        return new ProductCategoryResource($collection);
    }
}