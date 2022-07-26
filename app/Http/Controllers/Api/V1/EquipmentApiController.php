<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Http\Resources\EquipmentResource;

class EquipmentApiController extends Controller
{
  public function index(Request $request)
  {
    $collection = Equipment::orderBy('name')->get();

    return new EquipmentResource($collection);
  }
}
