<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InventoryResource;
use App\Models\Inventory;
use App\Models\InventoryApplication;
use App\Models\InventoryStock;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;
use App\Models\Application;

class InventoryStockApiController extends Controller
{
    public function index(Request $request, $inventoryId)
    {
        $collection = [];

        $inventories = InventoryStock::query()
            ->where('inventory_id', $inventoryId)
            ->where('application_equipment_id', null)
            ->with(['inventory', 'inventory.construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.unit'])
            ->get();

        return ['data' => $inventories];
    }

    public function products(Request $request, $inventoryId)
    {
        $collection = [];

        $inventories = InventoryStock::query()
            ->where('inventory_id', $inventoryId)
            ->where('application_equipment_id', null)
            ->where('application_service_id', null)
            ->with(['inventory', 'inventory.construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.unit'])
            ->get();

        return ['data' => $inventories];
    }

    public function equipments(Request $request, $inventoryId)
    {
        $collection = [];

        $inventories = InventoryStock::query()
            ->where('inventory_id', $inventoryId)
            ->where('application_product_id', null)
            ->where('application_service_id', null)
            ->with(['inventory', 'inventory.construction', 'applicationEquipment', 'applicationEquipment.equipment', 'applicationEquipment.notes'])
            ->get();

        return ['data' => $inventories];
    }

    public function services(Request $request, $inventoryId)
    {
        $collection = [];

        $inventories = InventoryStock::query()
            ->where('inventory_id', $inventoryId)
            ->where('application_product_id', null)
            ->where('application_equipment_id', null)
            ->with(['inventory', 'inventory.construction', 'applicationService'])
            ->get();

        return ['data' => $inventories];
    }


    public function incoming(Request $request, $applicationId)
    {
        $collection = [];

        // get application
        $application = Application::with(['construction'])->findOrFail($applicationId);

        // get main inventory
        $inventory = Inventory::where('construction_id', $application->construction->id)
            ->where('is_main', 1)
            ->firstOrFail();

        // get prihod
        $inventoryApplications = InventoryApplication::query()
            ->where('inventory_id', $inventory->id)
            ->where('status', 'waiting')
            ->with(['inventory', 'inventory.construction', 'applicationProduct', 'applicationProduct.category', 'applicationProduct.product', 'applicationProduct.application'])
            ->get();

        return ['data' => $inventoryApplications];
    }

    public function show(Request $request, $inventoryId)
    {
        // dd($inventoryId);

        $inventory = Inventory::with(['stocks', 'stocks.applicationProduct', 'stocks.applicationProduct.product', 'stocks.applicationProduct.unit'])
            ->where('id', $inventoryId)
            ->firstOrFail();

        $stocks = [];

        foreach ($inventory->stocks as $stock) {
            if ($stock->application_product_id != null) {
                $stocks[] = $stock;
            }
        }

        return $stocks;
    }


    public function history(Request $request, $id)
    {
        $inventory = Inventory::with(['logs', 'logs.user'])->findOrFail($id);

        return $inventory->logs()->with('user')->orderBy('id', 'DESC')->get();
    }
}
