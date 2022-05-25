<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationRequest;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Models\Application;
use App\Models\Construction;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::with(['construction'])->get();

        $constructions = Construction::get();

        return view('admin.applications.index', compact('applications', 'constructions'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $constructions = Construction::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.applications.create', compact('constructions'));
    }

    public function store(StoreApplicationRequest $request)
    {
        $application = Application::create($request->all());

        return redirect()->route('admin.applications.index');
    }

    public function edit(Application $application)
    {
        abort_if(Gate::denies('application_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $constructions = Construction::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $application->load('construction');

        return view('admin.applications.edit', compact('application', 'constructions'));
    }

    public function update(UpdateApplicationRequest $request, Application $application)
    {
        $application->update($request->all());

        return redirect()->route('admin.applications.index');
    }

    public function show(Application $application)
    {
        abort_if(Gate::denies('application_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->load('construction', 'applicationApplicationProducts', 'applicationApplicationStatuses');

        return view('admin.applications.show', compact('application'));
    }

    public function destroy(Application $application)
    {
        abort_if(Gate::denies('application_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $application->delete();

        return back();
    }

    public function massDestroy(MassDestroyApplicationRequest $request)
    {
        Application::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
