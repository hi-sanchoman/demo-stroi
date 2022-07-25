<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\InventoryStock;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use App\Models\Product;
use App\Models\TempInventoryNote;
use App\Models\User;

class InventoryApiController extends Controller
{
    // public function history(Request $request, $productId)
    // {
    //     $product = Product::findOrFail($productId);

    //     $result = [];

    //     $collection = Inventory::query()
    //         ->with(['construction', 'applicationProduct', 'applicationProduct.product', 'applicationProduct.product.categories'])
    //         ->get();

    //     foreach ($collection as $item) {
    //         if ($item->applicationProduct->product->id == $product->id) {
    //             $result[] = $item;
    //         }
    //     }

    //     return $result;
    // }

    public function index(Request $request)
    {
        $inventories = $request->user()->inventories()->with(['construction'])->get();

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

    public function dropdown(Request $request)
    {
        $inventories = Inventory::query()
            ->with(['construction', 'owner'])
            // ->whereNot('owner_id', $request->user()->id)
            ->get();

        return ['data' => $inventories];
    }

    public function show($id)
    {
        $inventory = Inventory::query()
            ->with(['construction'])
            ->where('id', $id)
            ->firstOrFail();

        return ['data' => $inventory];
    }


    public function foremans()
    {
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

    public function moveStocks(Request $request)
    {
        DB::beginTransaction();

        $stock = InventoryStock::with(['applicationProduct', 'applicationProduct.product', 'applicationProduct.unit'])->findOrFail($request->stock['stock_id']);
        $stock->quantity -= $request->quantity;
        $stock->save();

        InventoryLog::create([
            'inventory_id' => $stock->inventory_id,
            'user_id' => $request->user()->id,
            'log' => $request->user()->email . ' переместил из склада к (' . $request->where . ') товар (' . $stock->applicationProduct->product->name . ') в количестве: ' . $request->quantity . ' ' . $stock->applicationProduct->unit->name,
        ]);

        DB::commit();

        return 1;
    }

    public function moveStocksOutside(Request $request)
    {
        DB::beginTransaction();

        // remove quantity 
        $stock = InventoryStock::with(['applicationProduct', 'applicationProduct.product', 'applicationProduct.unit'])->findOrFail($request->stock['stock_id']);
        $stock->quantity -= $request->quantity;
        $stock->save();

        // move to temp
        TempInventoryNote::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->where['id'],
            'stock_id' => $request->stock['stock_id'],
            'quantity' => $request->quantity,
        ]);

        $senderInvetory = Inventory::with(['construction'])->whereId($request->sender_id)->firstOrFail();

        // log
        $log = $request->user()->email . ' переместил из склада (' . $senderInvetory->construction->name . ') в (' . $request->where['construction']['name'] . ') товар (' . $stock->applicationProduct->product->name . ') в количестве: ' . $request->quantity . ' ' . $stock->applicationProduct->unit->name;

        InventoryLog::create([
            'inventory_id' => $stock->inventory_id,
            'user_id' => $request->user()->id,
            'log' => $log,
        ]);

        DB::commit();

        return 1;
    }

    public function moveStocksForeman(Request $request)
    {
        DB::beginTransaction();

        // remove quantity 
        $stock = InventoryStock::with(['applicationProduct', 'applicationProduct.product', 'applicationProduct.unit'])->findOrFail($request->stock['stock_id']);
        $stock->quantity -= $request->quantity;
        $stock->save();

        // move to temp
        TempInventoryNote::create([
            'sender_id' => $request->sender_id,
            'foreman_id' => $request->where['id'],
            'stock_id' => $request->stock['stock_id'],
            'quantity' => $request->quantity,
        ]);

        $senderInvetory = Inventory::with(['construction'])->whereId($request->sender_id)->firstOrFail();

        // log
        $log = $request->user()->email . ' переместил из склада (' . $senderInvetory->construction->name . ') прорабу (' . $request->where['name'] . ') товар (' . $stock->applicationProduct->product->name . ') в количестве: ' . $request->quantity . ' ' . $stock->applicationProduct->unit->name;

        InventoryLog::create([
            'inventory_id' => $stock->inventory_id,
            'user_id' => $request->user()->id,
            'log' => $log,
        ]);

        DB::commit();

        return 1;
    }


    public function getIncoming(Request $request, $id)
    {
        $inventory = Inventory::with(['construction', 'owner'])->whereId($id)->firstOrFail();

        $tempNotes = TempInventoryNote::query()
            ->with(['sender', 'sender.construction', 'receiver', 'receiver.construction', 'stock', 'stock.applicationProduct', 'stock.applicationProduct.product', 'stock.applicationProduct.unit'])
            ->where('receiver_id', $inventory->id)
            ->orWhere('foreman_id', $request->user()->id)
            ->where('status', 'incoming')
            ->orderBy('id', 'DESC')
            ->get();

        return ['data' => $tempNotes];
    }

    public function acceptIncoming(Request $request, $id)
    {
        DB::beginTransaction();

        // accept temp note
        $tempNote = TempInventoryNote::with(['receiver', 'stock', 'stock.applicationProduct', 'stock.applicationProduct.application', 'stock.applicationProduct.product', 'stock.applicationProduct.unit'])->whereId($id)->firstOrFail();
        $tempNote->status = 'accepted';
        $tempNote->save();

        // add quantity
        $inventoryId = $tempNote->receiver_id != null ? $tempNote->receiver_id : -1;

        if ($inventoryId == -1) {
            $foremanInventory = $request->user()->inventories()->where('construction_id', $tempNote->stock->applicationProduct->application->construction_id)->first();
            $inventoryId = $foremanInventory->id;
        }

        $stock = InventoryStock::firstOrNew([
            'inventory_id' => $inventoryId,
            'application_product_id' => $tempNote->stock->application_product_id
        ]);
        $stock->quantity += $tempNote->quantity;
        $stock->save();

        // save to log
        $log = $request->user()->email . ' принял товар (' . $tempNote->stock->applicationProduct->product->name . ') в количестве: ' . $tempNote->quantity . ' ' . $tempNote->stock->applicationProduct->unit->name;

        InventoryLog::create([
            'inventory_id' => $tempNote->receiver_id,
            'user_id' => $request->user()->id,
            'log' => $log,
        ]);

        DB::commit();

        return 1;
    }


    public function declineIncoming(Request $request, $id)
    {
        DB::beginTransaction();

        // accept temp note
        $tempNote = TempInventoryNote::with(['receiver', 'stock', 'stock.applicationProduct', 'stock.applicationProduct.application', 'stock.applicationProduct.product', 'stock.applicationProduct.unit'])->whereId($id)->firstOrFail();
        $tempNote->status = 'declined';
        $tempNote->save();

        // restore quantity
        $tempNote->stock->quantity += $tempNote->quantity;
        $tempNote->stock->save();

        // save to log
        $log = $request->user()->email . ' отказал в принятии товара (' . $tempNote->stock->applicationProduct->product->name . ') в количестве: ' . $tempNote->quantity . ' ' . $tempNote->stock->applicationProduct->unit->name;

        // find inventory id
        $inventoryId = $tempNote->receiver_id != null ? $tempNote->receiver_id : -1;

        if ($inventoryId == -1) {
            $foremanInventory = $request->user()->inventories()->where('construction_id', $tempNote->stock->applicationProduct->application->construction_id)->first();
            $inventoryId = $foremanInventory->id;
        }

        InventoryLog::create([
            'inventory_id' => $inventoryId,
            'user_id' => $request->user()->id,
            'log' => $log,
        ]);

        DB::commit();

        return 1;
    }
}
