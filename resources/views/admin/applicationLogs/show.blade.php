@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.applicationLog.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-logs.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationLog.fields.id') }}
                        </th>
                        <td>
                            {{ $applicationLog->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationLog.fields.application') }}
                        </th>
                        <td>
                            {{ $applicationLog->application->issued_at ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationLog.fields.log') }}
                        </th>
                        <td>
                            {{ $applicationLog->log }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.applicationLog.fields.user') }}
                        </th>
                        <td>
                            {{ $applicationLog->user->name ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.application-logs.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection