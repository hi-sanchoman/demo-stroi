<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InventoryResource;
use App\Models\Inventory;
use App\Models\InventoryApplication;
use App\Models\InventoryStock;
use App\Models\ApplicationProduct;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;

class InventoryApplicationApiController extends Controller
{


    public function update(Request $request, $inventoryId) {
        $inventoryApplication = InventoryApplication::whereId($inventoryId)->firstOrFail();
        // dd($inventoryApplication->toArray());

        $mode = $request->mode;

        if ($mode == 'accept') {
            $inventoryApplication->accepted = $inventoryApplication->prepared;
            $inventoryApplication->declined = 0;
            $inventoryApplication->status = 'accepted';
            $inventoryApplication->save();
        
            $stock = InventoryStock::firstOrCreate([
                'inventory_id' => $inventoryApplication->inventory_id,
                'application_product_id' => $inventoryApplication->application_product_id,
            ]);
            $stock->quantity += $inventoryApplication->accepted;
            $stock->save();
        } else {
            $inventoryApplication->declined = $inventoryApplication->prepared;
            $inventoryApplication->accepted = 0;
            $inventoryApplication->status = 'declined';
            $inventoryApplication->reason = $request->reason;
            $inventoryApplication->save();

            $productApplication = ApplicationProduct::where('id', $inventoryApplication->application_product_id)->first();
            $productApplication->prepared -= $inventoryApplication->declined;
            $productApplication->save();
        }

        return $inventoryApplication;
    }
}
