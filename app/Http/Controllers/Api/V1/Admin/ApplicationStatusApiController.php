<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationStatusRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Resources\Admin\ApplicationStatusResource;
use App\Models\ApplicationStatus;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationStatusApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_status_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationStatusResource(ApplicationStatus::with(['application', 'application_path'])->get());
    }

    public function store(StoreApplicationStatusRequest $request)
    {
        $applicationStatus = ApplicationStatus::create($request->all());

        return (new ApplicationStatusResource($applicationStatus))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationStatusResource($applicationStatus->load(['application', 'application_path']));
    }

    public function update(UpdateApplicationStatusRequest $request, ApplicationStatus $applicationStatus)
    {
        $applicationStatus->update($request->all());

        return (new ApplicationStatusResource($applicationStatus))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationStatus->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
