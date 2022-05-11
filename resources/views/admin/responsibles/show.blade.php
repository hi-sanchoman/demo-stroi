@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.responsible.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.responsibles.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.id') }}
                        </th>
                        <td>
                            {{ $responsible->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.stage') }}
                        </th>
                        <td>
                            {{ $responsible->stage->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.role') }}
                        </th>
                        <td>
                            {{ $responsible->role->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.specific_user') }}
                        </th>
                        <td>
                            {{ $responsible->specific_user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.order') }}
                        </th>
                        <td>
                            {{ $responsible->order }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\Responsible::STATUS_SELECT[$responsible->status] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.reason') }}
                        </th>
                        <td>
                            {!! $responsible->reason !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.notes') }}
                        </th>
                        <td>
                            {{ $responsible->notes }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.responsible.fields.reviewed_at') }}
                        </th>
                        <td>
                            {{ $responsible->reviewed_at }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.responsibles.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection