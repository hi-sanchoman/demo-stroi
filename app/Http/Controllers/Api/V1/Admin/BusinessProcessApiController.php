<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessProcessRequest;
use App\Http\Requests\UpdateBusinessProcessRequest;
use App\Http\Resources\Admin\BusinessProcessResource;
use App\Models\BusinessProcess;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessProcessApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('business_process_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new BusinessProcessResource(BusinessProcess::all());
    }

    public function store(StoreBusinessProcessRequest $request)
    {
        $businessProcess = BusinessProcess::create($request->all());

        return (new BusinessProcessResource($businessProcess))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(BusinessProcess $businessProcess)
    {
        abort_if(Gate::denies('business_process_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new BusinessProcessResource($businessProcess);
    }

    public function update(UpdateBusinessProcessRequest $request, BusinessProcess $businessProcess)
    {
        $businessProcess->update($request->all());

        return (new BusinessProcessResource($businessProcess))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(BusinessProcess $businessProcess)
    {
        abort_if(Gate::denies('business_process_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $businessProcess->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
