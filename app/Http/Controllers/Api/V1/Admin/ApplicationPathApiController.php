<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationPathRequest;
use App\Http\Requests\UpdateApplicationPathRequest;
use App\Http\Resources\Admin\ApplicationPathResource;
use App\Models\ApplicationPath;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationPathApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_path_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationPathResource(ApplicationPath::with(['construction', 'responsible'])->get());
    }

    public function store(StoreApplicationPathRequest $request)
    {
        $applicationPath = ApplicationPath::create($request->all());

        return (new ApplicationPathResource($applicationPath))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationPath $applicationPath)
    {
        abort_if(Gate::denies('application_path_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationPathResource($applicationPath->load(['construction', 'responsible']));
    }

    public function update(UpdateApplicationPathRequest $request, ApplicationPath $applicationPath)
    {
        $applicationPath->update($request->all());

        return (new ApplicationPathResource($applicationPath))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ApplicationPath $applicationPath)
    {
        abort_if(Gate::denies('application_path_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationPath->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
