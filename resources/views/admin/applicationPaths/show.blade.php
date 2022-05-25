@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.applicationPath.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-paths.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationPath.fields.id') }}
                        </th>
                        <td>
                            {{ $applicationPath->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationPath.fields.position') }}
                        </th>
                        <td>
                            {{ $applicationPath->position }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationPath.fields.construction') }}
                        </th>
                        <td>
                            {{ $applicationPath->construction->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationPath.fields.responsible') }}
                        </th>
                        <td>
                            {{ $applicationPath->responsible->name ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-paths.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection