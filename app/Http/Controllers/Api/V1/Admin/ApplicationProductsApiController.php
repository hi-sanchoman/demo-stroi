<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationProductRequest;
use App\Http\Requests\UpdateApplicationProductRequest;
use App\Http\Resources\Admin\ApplicationProductResource;
use App\Models\ApplicationLog;
use App\Models\ApplicationProduct;
use App\Models\Inventory;
use App\Models\InventoryApplication;
use App\Models\Supply;
use Gate;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationProductsApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationProductResource(ApplicationProduct::with(['application', 'product'])->get());
    }

    public function store(StoreApplicationProductRequest $request)
    {
        $applicationProduct = ApplicationProduct::create($request->all());

        return (new ApplicationProductResource($applicationProduct))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationProductResource($applicationProduct->load(['application', 'product']));
    }

    public function update(Request $request, ApplicationProduct $applicationProduct)
    {

        // add to log
        if ($request->mode == 'prepare') {
            $applicationProduct->update($request->all());

            ApplicationLog::create([
                'application_id' => $applicationProduct->application_id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->name . ' подготовил ' . $applicationProduct->prepared . ' ' . $applicationProduct->product->name,
            ]);
        } else if ($request->mode == 'receive') {
            $applicationProduct->update([
                'delivered' => $applicationProduct->delivered + $request->delivered,
            ]);

            Supply::create([
                'construction_id' => $applicationProduct->application->construction_id,
                'application_product_id' => $applicationProduct->id,
                'quantity' => $request->delivered,
            ]);

            ApplicationLog::create([
                'application_id' => $applicationProduct->application_id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->name . ' принял ' . $applicationProduct->delivered . ' ' . $applicationProduct->product->name,
            ]);
        }



        return (new ApplicationProductResource($applicationProduct))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationProduct->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }


    public function prepare(Request $request, $id)
    {
        DB::beginTransaction();

        $applicationProduct = ApplicationProduct::with(['application', 'application.construction'])
            ->where('id', $id)->firstOrFail();
        $applicationProduct->prepared += $request->product['toBePrepared'];
        $applicationProduct->save();

        // get main inventory
        $inventory = Inventory::where('construction_id', $applicationProduct->application->construction->id)
            ->where('is_main', 1)
            ->firstOrFail();

        // create application request for a WareHouse manager
        $inventoryApplication = InventoryApplication::create([
            'inventory_id' => $inventory->id,
            'application_product_id' => $applicationProduct->id,
            'prepared' => $request->product['toBePrepared'],
            'reason' => $request->notes,
        ]);

        // add to supply
        // TODO: check by product_id & product_category_id & construction_id
        $supplyNote = Supply::create([
            'construction_id' => $applicationProduct->application->construction->id,
            'application_product_id' => $applicationProduct->id,
            'quantity' => $request->product['toBePrepared'],
        ]);

        DB::commit();

        return [
            'prepared' => $applicationProduct->prepared,
            'inventory' => $inventoryApplication,
        ];
    }
}
