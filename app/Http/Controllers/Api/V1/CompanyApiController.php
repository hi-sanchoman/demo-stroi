<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Http\Resources\ProductResource;
use App\Models\Application;
use App\Models\ApplicationProduct;

class CompanyApiController extends Controller 
{
    public function index(Request $request) {
        $collection = Company::orderBy('name', 'ASC')->get();
        
        return ['data' => $collection];
    }

    public function store(Request $request) {
        $company = Company::create(['name' => $request->name]);

        return $company;
    }
}