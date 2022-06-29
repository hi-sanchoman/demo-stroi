<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InventoryResource;
use App\Models\Inventory;
use App\Models\InventoryStock;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;

class InventoryStockApiController extends Controller
{
    public function index(Request $request, $inventoryId)
    {
        $collection = [];
        
        $inventories = InventoryStock::query()
            ->where('inventory_id', $inventoryId)
            ->with(['inventory', 'inventory.construction', 'applicationProduct', 'applicationProduct.product'])
            ->get();
        
        return ['data' => $inventories];
    }
}
