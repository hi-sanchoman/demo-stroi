<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreResponsibleRequest;
use App\Http\Requests\UpdateResponsibleRequest;
use App\Http\Resources\Admin\ResponsibleResource;
use App\Models\Responsible;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponsibleApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('responsible_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ResponsibleResource(Responsible::with(['stage', 'role', 'specific_user'])->get());
    }

    public function store(StoreResponsibleRequest $request)
    {
        $responsible = Responsible::create($request->all());

        return (new ResponsibleResource($responsible))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Responsible $responsible)
    {
        abort_if(Gate::denies('responsible_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ResponsibleResource($responsible->load(['stage', 'role', 'specific_user']));
    }

    public function update(UpdateResponsibleRequest $request, Responsible $responsible)
    {
        $responsible->update($request->all());

        return (new ResponsibleResource($responsible))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Responsible $responsible)
    {
        abort_if(Gate::denies('responsible_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $responsible->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
