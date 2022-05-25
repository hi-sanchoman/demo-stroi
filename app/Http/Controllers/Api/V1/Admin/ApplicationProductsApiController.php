<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationProductRequest;
use App\Http\Requests\UpdateApplicationProductRequest;
use App\Http\Resources\Admin\ApplicationProductResource;
use App\Models\ApplicationProduct;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationProductsApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationProductResource(ApplicationProduct::with(['application', 'product'])->get());
    }

    public function store(StoreApplicationProductRequest $request)
    {
        $applicationProduct = ApplicationProduct::create($request->all());

        return (new ApplicationProductResource($applicationProduct))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationProductResource($applicationProduct->load(['application', 'product']));
    }

    public function update(UpdateApplicationProductRequest $request, ApplicationProduct $applicationProduct)
    {
        $applicationProduct->update($request->all());

        return (new ApplicationProductResource($applicationProduct))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationProduct->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
