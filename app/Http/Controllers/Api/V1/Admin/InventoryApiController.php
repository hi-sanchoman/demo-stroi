<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InventoryResource;
use App\Models\Inventory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;

class InventoryApiController extends Controller
{
    public function historyInventories(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $result = [];

        $collection = Inventory::query()
            ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.product.categories'])
            ->get();

        foreach ($collection as $item) {
            if ($item->applicationProduct->product->id == $product->id) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function index(Request $request)
    {
        $collection = [];
        
        $inventories = Inventory::query()
            ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.product.categories'])
            ->get();
        
        foreach ($inventories as $item) {
            if (!isset($collection[$item->applicationProduct->product->id])) {
                $collection[$item->applicationProduct->product->id] = [
                    'item' => $item,
                    'total' => $item->quantity,
                ];

                continue;
            }

            $collection[$item->applicationProduct->product->id]['total'] += $item->quantity;
        }

        $result = [];

        foreach ($collection as $item) {
            $result[] = $item;
        }

        return $result;
    }
}