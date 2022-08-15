<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationService;
use App\Models\Equipment;
use App\Models\Supply;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;

class SupplyApiController extends Controller
{
    public function index(Request $request)
    {
        $collection = [];

        $supplies = Supply::query()
            ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.category', 'applicationProduct.unit', 'applicationEquipment', 'applicationEquipment.unit', 'applicationEquipment.equipment', 'applicationService'])
            ->get();

        foreach ($supplies as $item) {
            if ($request->has('construction_id') && $item->construction->id !== intval($request->construction_id)) {
                continue;
            }

            if ($item->applicationProduct) {
                if (!isset($collection[$item->applicationProduct->product->id])) {
                    $collection[$item->applicationProduct->product->id] = [
                        'item' => $item,
                        'total' => $item->quantity,
                    ];

                    continue;
                }
                $collection[$item->applicationProduct->product->id]['total'] += $item->quantity;
            } else if ($item->applicationEquipment) {
                if (!isset($collection[$item->applicationEquipment->equipment->id])) {
                    $collection[$item->applicationEquipment->equipment->id] = [
                        'item' => $item,
                        'total' => $item->quantity,
                    ];

                    continue;
                }
                $collection[$item->applicationEquipment->equipment->id]['total'] += $item->quantity;
            } else if ($item->applicationService) {
                if (!isset($collection[$item->applicationService->id])) {
                    $collection[$item->applicationService->id] = [
                        'item' => $item,
                        'total' => $item->quantity,
                    ];

                    continue;
                }
                $collection[$item->applicationService->id]['total'] += $item->quantity;
            }
        }

        $result = [];

        foreach ($collection as $item) {
            $result[] = $item;
        }

        return ['data' => $result];
    }


    public function history(Request $request, $id, $kind)
    {
        $result = [];

        if ($kind == 'product') {
            $product = Product::findOrFail($id);

            // TODO: refactor! 
            $collection = Supply::query()
                ->where('application_equipment_id', null)
                ->where('application_service_id', null)
                ->whereNot('application_product_id', null)
                ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.category', 'applicationProduct.unit'])
                ->get();
            // dd($collection->toArray());

            foreach ($collection as $item) {
                if ($item->applicationProduct->product->id == $product->id) {
                    $result[] = $item;
                }
            }
        } else if ($kind == 'equipment') {
            $equipment = Equipment::findOrFail($id);

            // TODO: refactor! 
            $collection = Supply::query()
                ->where('application_product_id', null)
                ->where('application_service_id', null)
                ->whereNot('application_equipment_id', null)
                ->with(['construction', 'applicationEquipment', 'applicationEquipment.unit', 'applicationEquipment.equipment'])
                ->get();

            foreach ($collection as $item) {
                if ($item->applicationEquipment->equipment->id == $equipment->id) {
                    $result[] = $item;
                }
            }
        } else if ($kind == 'service') {
            $serviceApplication = ApplicationService::findOrFail($id);

            // TODO: refactor! 
            $collection = Supply::query()
                ->where('application_equipment_id', null)
                ->where('application_product_id', null)
                ->whereNot('application_service_id', null)
                ->with(['construction', 'applicationService'])
                ->get();

            foreach ($collection as $item) {
                if ($item->applicationService->id == $serviceApplication->id) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }
}
