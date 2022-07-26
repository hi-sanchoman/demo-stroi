<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationEquipmentRequest;
use App\Http\Requests\UpdateApplicationEquipmentRequest;
use App\Http\Resources\Admin\ApplicationEquipmentResource;
use App\Models\ApplicationLog;
use App\Models\ApplicationEquipment;
use App\Models\Inventory;
use App\Models\InventoryApplication;
use App\Models\Supply;
use Gate;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationEquipmentsApiController extends Controller
{
    public function index()
    {
      // abort_if(Gate::denies('application_Equipment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

      // return new ApplicationEquipmentResource(ApplicationEquipment::with(['application', 'Equipment'])->get());
    }

    public function store(Request $request)
    {
      // $applicationEquipment = ApplicationEquipment::create($request->all());

      // return (new ApplicationEquipmentResource($applicationEquipment))
      //     ->response()
      //     ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationEquipment $applicationEquipment)
    {
      // abort_if(Gate::denies('application_Equipment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

      // return new ApplicationEquipmentResource($applicationEquipment->load(['application', 'Equipment']));
    }

    public function update(Request $request, ApplicationEquipment $applicationEquipment)
    {

        // // add to log
        // if ($request->mode == 'prepare') {
        //     $applicationEquipment->update($request->all());

        //     ApplicationLog::create([
        //         'application_id' => $applicationEquipment->application_id,
        //         'user_id' => $request->user()->id,
        //         'log' => $request->user()->name . ' подготовил ' . $applicationEquipment->prepared . ' ' . $applicationEquipment->Equipment->name,
        //     ]);
        // } else if ($request->mode == 'receive') {
        //     $applicationEquipment->update([
        //         'delivered' => $applicationEquipment->delivered + $request->delivered,
        //     ]);

        //     Supply::create([
        //         'construction_id' => $applicationEquipment->application->construction_id,
        //         'application_Equipment_id' => $applicationEquipment->id,
        //         'quantity' => $request->delivered,
        //     ]);

        //     ApplicationLog::create([
        //         'application_id' => $applicationEquipment->application_id,
        //         'user_id' => $request->user()->id,
        //         'log' => $request->user()->name . ' принял ' . $applicationEquipment->delivered . ' ' . $applicationEquipment->Equipment->name,
        //     ]);
        // }



        // return (new ApplicationEquipmentResource($applicationEquipment))
        //     ->response()
        //     ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ApplicationEquipment $applicationEquipment)
    {
      // abort_if(Gate::denies('application_Equipment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

      // $applicationEquipment->delete();

      // return response(null, Response::HTTP_NO_CONTENT);
    }


    public function prepare(Request $request, $id)
    {
        DB::beginTransaction();

        $applicationEquipment = ApplicationEquipment::with(['application', 'application.construction'])
          ->where('id', $id)->firstOrFail();
        $applicationEquipment->prepared += $request->equipment['toBePrepared'];
        $applicationEquipment->save();

        // get main inventory
        $inventory = Inventory::where('construction_id', $applicationEquipment->application->construction->id)
          ->where('is_main', 1)
          ->firstOrFail();

        // create application request for a WareHouse manager
        $inventoryApplication = InventoryApplication::create([
          'inventory_id' => $inventory->id,
          'application_equipment_id' => $applicationEquipment->id,
          'prepared' => $request->equipment['toBePrepared'],
          'reason' => $request->notes,
        ]);

        // add to supply
        // TODO: check by Equipment_id & Equipment_category_id & construction_id
        $supplyNote = Supply::create([
          'construction_id' => $applicationEquipment->application->construction->id,
          'application_equipment_id' => $applicationEquipment->id,
          'quantity' => $request->equipment['toBePrepared'],
        ]);

        DB::commit();

        return [
          'prepared' => $applicationEquipment->prepared,
          'inventory' => $inventoryApplication,
        ];
    }
}
