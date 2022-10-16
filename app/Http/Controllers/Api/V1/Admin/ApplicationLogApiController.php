<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationLogRequest;
use App\Http\Requests\UpdateApplicationLogRequest;
use App\Http\Resources\Admin\ApplicationLogResource;
use App\Models\ApplicationLog;
use App\Models\Application;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationLogApiController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('application_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $allowedConstructions = $request->user()->constructions->pluck('id');
        $applicationIds = Application::whereIn('construction_id', $allowedConstructions)->pluck('id');

        $logs = ApplicationLog::query()
            ->with(['application', 'user'])
            ->whereIn('application_id', $applicationIds)
            ->orderBy('created_at', 'DESC')
            ->limit(75)
            ->get();

        return new ApplicationLogResource($logs);
    }

    public function store(StoreApplicationLogRequest $request)
    {
        $applicationLog = ApplicationLog::create($request->all());

        return (new ApplicationLogResource($applicationLog))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationLog $applicationLog)
    {
        abort_if(Gate::denies('application_log_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationLogResource($applicationLog->load(['application', 'user']));
    }

    public function update(UpdateApplicationLogRequest $request, ApplicationLog $applicationLog)
    {
        $applicationLog->update($request->all());

        return (new ApplicationLogResource($applicationLog))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ApplicationLog $applicationLog)
    {
        abort_if(Gate::denies('application_log_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationLog->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
