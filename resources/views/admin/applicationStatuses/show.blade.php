@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.applicationStatus.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-statuses.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.id') }}
                        </th>
                        <td>
                            {{ $applicationStatus->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.application') }}
                        </th>
                        <td>
                            {{ $applicationStatus->application->issued_at ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.application_path') }}
                        </th>
                        <td>
                            {{ $applicationStatus->application_path->position ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\ApplicationStatus::STATUS_SELECT[$applicationStatus->status] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.declined_reason') }}
                        </th>
                        <td>
                            {{ $applicationStatus->declined_reason }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-statuses.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection