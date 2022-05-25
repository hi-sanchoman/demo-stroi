<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyApplicationProductRequest;
use App\Http\Requests\StoreApplicationProductRequest;
use App\Http\Requests\UpdateApplicationProductRequest;
use App\Models\Application;
use App\Models\ApplicationProduct;
use App\Models\Product;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationProductsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('application_product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationProducts = ApplicationProduct::with(['application', 'product'])->get();

        $applications = Application::get();

        $products = Product::get();

        return view('admin.applicationProducts.index', compact('applicationProducts', 'applications', 'products'));
    }

    public function create()
    {
        abort_if(Gate::denies('application_product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::pluck('issued_at', 'id')->prepend(trans('global.pleaseSelect'), '');

        $products = Product::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.applicationProducts.create', compact('applications', 'products'));
    }

    public function store(StoreApplicationProductRequest $request)
    {
        $applicationProduct = ApplicationProduct::create($request->all());

        return redirect()->route('admin.application-products.index');
    }

    public function edit(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applications = Application::pluck('issued_at', 'id')->prepend(trans('global.pleaseSelect'), '');

        $products = Product::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $applicationProduct->load('application', 'product');

        return view('admin.applicationProducts.edit', compact('applicationProduct', 'applications', 'products'));
    }

    public function update(UpdateApplicationProductRequest $request, ApplicationProduct $applicationProduct)
    {
        $applicationProduct->update($request->all());

        return redirect()->route('admin.application-products.index');
    }

    public function show(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationProduct->load('application', 'product');

        return view('admin.applicationProducts.show', compact('applicationProduct'));
    }

    public function destroy(ApplicationProduct $applicationProduct)
    {
        abort_if(Gate::denies('application_product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationProduct->delete();

        return back();
    }

    public function massDestroy(MassDestroyApplicationProductRequest $request)
    {
        ApplicationProduct::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
