<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationLogRequest;
use App\Http\Requests\StoreApplicationLogRequest;
use App\Http\Requests\UpdateApplicationLogRequest;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationLogController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationLogs = ApplicationLog::with(['application', 'user'])->get();

        return view('admin.applicationLogs.index', compact('applicationLogs'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_log_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::pluck('issued_at', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.applicationLogs.create', compact('applications', 'users'));
    }

    public function store(StoreApplicationLogRequest $request)
    {
        $applicationLog = ApplicationLog::create($request->all());

        return redirect()->route('admin.application-logs.index');
    }

    public function edit(ApplicationLog $applicationLog)
    {
        abort_if(Gate::denies('application_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::pluck('issued_at', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $applicationLog->load('application', 'user');

        return view('admin.applicationLogs.edit', compact('applicationLog', 'applications', 'users'));
    }

    public function update(UpdateApplicationLogRequest $request, ApplicationLog $applicationLog)
    {
        $applicationLog->update($request->all());

        return redirect()->route('admin.application-logs.index');
    }

    public function show(ApplicationLog $applicationLog)
    {
        abort_if(Gate::denies('application_log_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationLog->load('application', 'user');

        return view('admin.applicationLogs.show', compact('applicationLog'));
    }

    public function destroy(ApplicationLog $applicationLog)
    {
        abort_if(Gate::denies('application_log_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationLog->delete();

        return back();
    }

    public function massDestroy(MassDestroyApplicationLogRequest $request)
    {
        ApplicationLog::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
