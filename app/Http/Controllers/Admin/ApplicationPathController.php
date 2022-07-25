<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationPathRequest;
use App\Http\Requests\StoreApplicationPathRequest;
use App\Http\Requests\UpdateApplicationPathRequest;
use App\Models\ApplicationPath;
use App\Models\Application;
use App\Models\Construction;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationPathController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_path_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationPaths = ApplicationPath::with(['construction', 'responsible'])->orderBy('order', 'ASC')->get();

        return view('admin.applicationPaths.index', compact('applicationPaths'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_path_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $constructions = Construction::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $applicationTypes = Application::TYPES;

        $responsibles = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.applicationPaths.create', compact('constructions', 'responsibles', 'applicationTypes'));
    }

    public function store(StoreApplicationPathRequest $request)
    {
        $applicationPath = ApplicationPath::create($request->all());

        return redirect()->route('admin.application-paths.index');
    }

    public function edit(ApplicationPath $applicationPath)
    {
        abort_if(Gate::denies('application_path_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $constructions = Construction::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $applicationTypes = Application::TYPES;

        $responsibles = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $applicationPath->load('construction', 'responsible');

        return view('admin.applicationPaths.edit', compact('applicationPath', 'constructions', 'responsibles', 'applicationTypes'));
    }

    public function update(UpdateApplicationPathRequest $request, ApplicationPath $applicationPath)
    {
        $applicationPath->update($request->all());

        return redirect()->route('admin.application-paths.index');
    }

    public function show(ApplicationPath $applicationPath)
    {
        abort_if(Gate::denies('application_path_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationPath->load('construction', 'responsible');

        return view('admin.applicationPaths.show', compact('applicationPath'));
    }

    public function destroy(ApplicationPath $applicationPath)
    {
        abort_if(Gate::denies('application_path_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationPath->delete();

        return back();
    }

    public function massDestroy(MassDestroyApplicationPathRequest $request)
    {
        ApplicationPath::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
