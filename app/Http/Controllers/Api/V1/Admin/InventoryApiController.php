<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryStock;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;
use App\Models\User;

class InventoryApiController extends Controller
{
    public function history(Request $request, $productId)
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
        $inventories = Inventory::query()
            ->with(['construction', 'owner'])
            ->whereOwnerId($request->user()->id)
            ->get();

        return ['data' => $inventories];

        // $collection = [];
        
        // $inventories = Inventory::query()
        //     ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.product.categories'])
        //     ->get();
        
        // foreach ($inventories as $item) {
        //     if (!isset($collection[$item->applicationProduct->product->id])) {
        //         $collection[$item->applicationProduct->product->id] = [
        //             'item' => $item,
        //             'total' => $item->quantity,
        //         ];

        //         continue;
        //     }

        //     $collection[$item->applicationProduct->product->id]['total'] += $item->quantity;
        // }

        // $result = [];

        // foreach ($collection as $item) {
        //     $result[] = $item;
        // }

        // return $result;
    }

    public function show($id)
    {
        $inventory = Inventory::query()
            ->with(['construction'])
            ->where('id', $id)
            ->firstOrFail();

        return ['data' => $inventory];
    }


    public function getForemans() {
        $users = User::with(['roles'])->get();

        $result = [];

        foreach ($users as $user) {
            foreach ($user->roles as $role) {
                if ($role->title == 'Foreman') {
                    $result[] = $user;
                }
            }
        }

        return $result;
    }

    public function moveStocks(Request $request) {
        $stock = InventoryStock::findOrFail($request->stock['stock_id']);
        $stock->quantity -= $request->quantity;
        $stock->save();

        return 1;
    }
}
