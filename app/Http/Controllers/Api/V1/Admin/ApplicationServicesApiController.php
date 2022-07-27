<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationServiceRequest;
use App\Http\Requests\UpdateApplicationServiceRequest;
use App\Http\Resources\Admin\ApplicationServiceResource;
use App\Models\ApplicationLog;
use App\Models\ApplicationService;
use App\Models\ServiceNote;
use App\Models\Inventory;
use App\Models\InventoryApplication;
use App\Models\Supply;
use Gate;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationServicesApiController extends Controller
{
  public function index()
  {
    // abort_if(Gate::denies('application_Service_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // return new ApplicationServiceResource(ApplicationService::with(['application', 'Service'])->get());
  }

  public function store(Request $request)
  {
    // $applicationService = ApplicationService::create($request->all());

    // return (new ApplicationServiceResource($applicationService))
    //     ->response()
    //     ->setStatusCode(Response::HTTP_CREATED);
  }

  public function show(ApplicationService $applicationService)
  {
    // abort_if(Gate::denies('application_Service_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // return new ApplicationServiceResource($applicationService->load(['application', 'Service']));
  }

  public function update(Request $request, ApplicationService $applicationService)
  {
    // $applicationService->update(['status' => $request->status]);

    // return 1;

    // // add to log
    // if ($request->mode == 'prepare') {
    //     $applicationService->update($request->all());

    //     ApplicationLog::create([
    //         'application_id' => $applicationService->application_id,
    //         'user_id' => $request->user()->id,
    //         'log' => $request->user()->name . ' подготовил ' . $applicationService->prepared . ' ' . $applicationService->Service->name,
    //     ]);
    // } else if ($request->mode == 'receive') {
    //     $applicationService->update([
    //         'delivered' => $applicationService->delivered + $request->delivered,
    //     ]);

    //     Supply::create([
    //         'construction_id' => $applicationService->application->construction_id,
    //         'application_Service_id' => $applicationService->id,
    //         'quantity' => $request->delivered,
    //     ]);

    //     ApplicationLog::create([
    //         'application_id' => $applicationService->application_id,
    //         'user_id' => $request->user()->id,
    //         'log' => $request->user()->name . ' принял ' . $applicationService->delivered . ' ' . $applicationService->Service->name,
    //     ]);
    // }



    // return (new ApplicationServiceResource($applicationService))
    //     ->response()
    //     ->setStatusCode(Response::HTTP_ACCEPTED);
  }

  public function destroy(ApplicationService $applicationService)
  {
    // abort_if(Gate::denies('application_Service_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // $applicationService->delete();

    // return response(null, Response::HTTP_NO_CONTENT);
  }


  public function prepare(Request $request, $id)
  {
    DB::beginTransaction();

    $applicationService = ApplicationService::with(['application', 'application.construction'])
      ->where('id', $id)->firstOrFail();
    $applicationService->prepared += $request->service['toBePrepared'];
    $applicationService->save();

    // get main inventory
    $inventory = Inventory::where('construction_id', $applicationService->application->construction->id)
      ->where('is_main', 1)
      ->firstOrFail();

    // create application request for a WareHouse manager
    $inventoryApplication = InventoryApplication::create([
      'inventory_id' => $inventory->id,
      'application_service_id' => $applicationService->id,
      'prepared' => $request->service['toBePrepared'],
      'reason' => $request->notes,
    ]);

    // add to supply
    // TODO: check by Service_id & Service_category_id & construction_id
    $supplyNote = Supply::create([
      'construction_id' => $applicationService->application->construction->id,
      'application_service_id' => $applicationService->id,
      'quantity' => $request->service['toBePrepared'],
    ]);

    DB::commit();

    return [
      'prepared' => $applicationService->prepared,
      'inventory' => $inventoryApplication,
    ];
  }
}
