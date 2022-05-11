<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyBusinessProcessRequest;
use App\Http\Requests\StoreBusinessProcessRequest;
use App\Http\Requests\UpdateBusinessProcessRequest;
use App\Models\BusinessProcess;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessProcessController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('business_process_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $businessProcesses = BusinessProcess::all();

        return view('admin.businessProcesses.index', compact('businessProcesses'));
    }

    public function create()
    {
        abort_if(Gate::denies('business_process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.businessProcesses.create');
    }

    public function store(StoreBusinessProcessRequest $request)
    {
        $businessProcess = BusinessProcess::create($request->all());

        return redirect()->route('admin.business-processes.index');
    }

    public function edit(BusinessProcess $businessProcess)
    {
        abort_if(Gate::denies('business_process_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.businessProcesses.edit', compact('businessProcess'));
    }

    public function update(UpdateBusinessProcessRequest $request, BusinessProcess $businessProcess)
    {
        $businessProcess->update($request->all());

        return redirect()->route('admin.business-processes.index');
    }

    public function show(BusinessProcess $businessProcess)
    {
        abort_if(Gate::denies('business_process_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.businessProcesses.show', compact('businessProcess'));
    }

    public function destroy(BusinessProcess $businessProcess)
    {
        abort_if(Gate::denies('business_process_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $businessProcess->delete();

        return back();
    }

    public function massDestroy(MassDestroyBusinessProcessRequest $request)
    {
        BusinessProcess::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
