<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConstructionRequest;
use App\Http\Requests\UpdateConstructionRequest;
use App\Http\Resources\Admin\ConstructionResource;
use App\Models\Construction;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConstructionApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('construction_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ConstructionResource(Construction::all());
    }

    public function store(StoreConstructionRequest $request)
    {
        $construction = Construction::create($request->all());

        return (new ConstructionResource($construction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Construction $construction)
    {
        abort_if(Gate::denies('construction_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ConstructionResource($construction);
    }

    public function update(UpdateConstructionRequest $request, Construction $construction)
    {
        $construction->update($request->all());

        return (new ConstructionResource($construction))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Construction $construction)
    {
        abort_if(Gate::denies('construction_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $construction->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
