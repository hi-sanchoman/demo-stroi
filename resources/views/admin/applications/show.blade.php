@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.application.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.applications.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.id') }}
                        </th>
                        <td>
                            {{ $application->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.construction') }}
                        </th>
                        <td>
                            {{ $application->construction->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.issued_at') }}
                        </th>
                        <td>
                            {{ $application->issued_at }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.kind') }}
                        </th>
                        <td>
                            {{ App\Models\Application::KIND_SELECT[$application->kind] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\Application::STATUS_SELECT[$application->status] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.is_urgent') }}
                        </th>
                        <td>
                            <input type="checkbox" disabled="disabled" {{ $application->is_urgent ? 'checked' : '' }}>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.applications.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.relatedData') }}
    </div>
    <ul class="nav nav-tabs" role="tablist" id="relationship-tabs">
        <li class="nav-item">
            <a class="nav-link" href="#application_application_products" role="tab" data-toggle="tab">
                {{ trans('cruds.applicationProduct.title') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#application_application_statuses" role="tab" data-toggle="tab">
                {{ trans('cruds.applicationStatus.title') }}
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" role="tabpanel" id="application_application_products">
            @includeIf('admin.applications.relationships.applicationApplicationProducts', ['applicationProducts' => $application->applicationApplicationProducts])
        </div>
        <div class="tab-pane" role="tabpanel" id="application_application_statuses">
            @includeIf('admin.applications.relationships.applicationApplicationStatuses', ['applicationStatuses' => $application->applicationApplicationStatuses])
        </div>
    </div>
</div>

@endsection