<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Unit;

class UnitApiController extends Controller
{
    public function index(Request $request) {
        $collection = Unit::orderBy('name')->get();
        
        return new UnitResource($collection);
    }
}
