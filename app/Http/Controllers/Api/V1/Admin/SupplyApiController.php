<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
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
            ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.category', 'applicationProduct.unit', 'applicationEquipment', 'applicationEquipment.equipment',])
            ->get();

        foreach ($supplies as $item) {
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
            }
        }

        $result = [];

        foreach ($collection as $item) {
            $result[] = $item;
        }

        return ['data' => $result];
    }


    public function history(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $result = [];

        $collection = Supply::query()
            ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.category', 'applicationProduct.unit'])
            ->get();

        foreach ($collection as $item) {
            if ($item->applicationProduct->product->id == $product->id) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
