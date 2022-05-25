@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.applicationProduct.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-products.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationProduct.fields.id') }}
                        </th>
                        <td>
                            {{ $applicationProduct->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationProduct.fields.application') }}
                        </th>
                        <td>
                            {{ $applicationProduct->application->issued_at ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationProduct.fields.product') }}
                        </th>
                        <td>
                            {{ $applicationProduct->product->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationProduct.fields.quantity') }}
                        </th>
                        <td>
                            {{ $applicationProduct->quantity }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationProduct.fields.notes') }}
                        </th>
                        <td>
                            {{ $applicationProduct->notes }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationProduct.fields.is_delivered_by_us') }}
                        </th>
                        <td>
                            <input type="checkbox" disabled="disabled" {{ $applicationProduct->is_delivered_by_us ? 'checked' : '' }}>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-products.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection