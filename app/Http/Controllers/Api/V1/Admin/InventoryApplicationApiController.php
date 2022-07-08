<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InventoryResource;
use App\Models\Inventory;
use App\Models\InventoryApplication;
use App\Models\InventoryStock;
use App\Models\ApplicationProduct;
use App\Models\Application;
use App\Models\InventoryLog;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;

class InventoryApplicationApiController extends Controller
{


    public function update(Request $request, $inventoryId)
    {
        $inventoryApplication = InventoryApplication::whereId($inventoryId)->firstOrFail();
        // dd($inventoryApplication->toArray());

        $mode = $request->mode;

        if ($mode == 'accept') {
            $inventoryApplication->accepted = $inventoryApplication->prepared;
            $inventoryApplication->declined = 0;
            $inventoryApplication->status = 'accepted';
            $inventoryApplication->save();

            $stock = InventoryStock::with(['applicationProduct', 'applicationProduct.product', 'applicationProduct.application'])->firstOrCreate([
                'inventory_id' => $inventoryApplication->inventory_id,
                'application_product_id' => $inventoryApplication->application_product_id,
            ]);
            $stock->quantity += $inventoryApplication->accepted;
            $stock->save();

            $log = $request->user()->email . ' принял товар (' . $stock->applicationProduct->product->name . ') в количестве: ' . $inventoryApplication->prepared . ' ' . $stock->applicationProduct->product->unit . ' по заявке №' . $stock->applicationProduct->application_id;

            if ($inventoryApplication->reason != null) {
                $log .= ' с примечанием: ' . $inventoryApplication->reason;
            }

            InventoryLog::create([
                'inventory_id' => $stock->inventory_id,
                'user_id' => $request->user()->id,
                'log' => $log,
            ]);

            // check whether application should be closed
            $application = Application::with(['applicationApplicationProducts'])->whereId($stock->applicationProduct->application_id)->firstOrFail();

            $closed = true;

            foreach ($application->applicationApplicationProducts as $product) {
                if (abs(($product->quantity - $product->prepared) / $product->prepared) > 0.00001) {
                    $closed = false;
                    break;
                }
            }

            if ($closed) {
                $application->status = 'completed';
                $application->save();
            }
        } else {
            $inventoryApplication->declined = $inventoryApplication->prepared;
            $inventoryApplication->accepted = 0;
            $inventoryApplication->status = 'declined';
            $inventoryApplication->reason = $request->reason;
            $inventoryApplication->save();

            $productApplication = ApplicationProduct::with(['application', 'product'])->where('id', $inventoryApplication->application_product_id)->first();
            $productApplication->prepared -= $inventoryApplication->declined;
            $productApplication->save();

            InventoryLog::create([
                'inventory_id' => $inventoryApplication->inventory_id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->email . ' отказался принимать товар (' . $productApplication->product->name . ') в количестве: ' . $inventoryApplication->prepared . ' ' . $productApplication->product->unit . ' по заявке №' . $productApplication->application_id,
            ]);
        }

        return $inventoryApplication;
    }
}
