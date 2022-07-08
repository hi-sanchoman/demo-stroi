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
    public function history(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $result = [];

        $collection = Supply::query()
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

        $supplies = Supply::query()
            ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.product.categories'])
            ->get();

        foreach ($supplies as $item) {
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

        return ['data' => $result];
    }
}
