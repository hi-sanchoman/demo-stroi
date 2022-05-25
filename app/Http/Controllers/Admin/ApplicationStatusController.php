<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationStatusRequest;
use App\Http\Requests\StoreApplicationStatusRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Application;
use App\Models\ApplicationPath;
use App\Models\ApplicationStatus;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationStatusController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_status_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationStatuses = ApplicationStatus::with(['application', 'application_path'])->get();

        return view('admin.applicationStatuses.index', compact('applicationStatuses'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_status_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::pluck('issued_at', 'id')->prepend(trans('global.pleaseSelect'), '');

        $application_paths = ApplicationPath::pluck('position', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.applicationStatuses.create', compact('application_paths', 'applications'));
    }

    public function store(StoreApplicationStatusRequest $request)
    {
        $applicationStatus = ApplicationStatus::create($request->all());

        return redirect()->route('admin.application-statuses.index');
    }

    public function edit(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::pluck('issued_at', 'id')->prepend(trans('global.pleaseSelect'), '');

        $application_paths = ApplicationPath::pluck('position', 'id')->prepend(trans('global.pleaseSelect'), '');

        $applicationStatus->load('application', 'application_path');

        return view('admin.applicationStatuses.edit', compact('applicationStatus', 'application_paths', 'applications'));
    }

    public function update(UpdateApplicationStatusRequest $request, ApplicationStatus $applicationStatus)
    {
        $applicationStatus->update($request->all());

        return redirect()->route('admin.application-statuses.index');
    }

    public function show(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationStatus->load('application', 'application_path');

        return view('admin.applicationStatuses.show', compact('applicationStatus'));
    }

    public function destroy(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationStatus->delete();

        return back();
    }

    public function massDestroy(MassDestroyApplicationStatusRequest $request)
    {
        ApplicationStatus::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
