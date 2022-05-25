@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.applicationLog.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.application-logs.update", [$applicationLog->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="application_id">{{ trans('cruds.applicationLog.fields.application') }}</label>
                <select class="form-control select2 {{ $errors->has('application') ? 'is-invalid' : '' }}" name="application_id" id="application_id" required>
                    @foreach($applications as $id => $entry)
                        <option value="{{ $id }}" {{ (old('application_id') ? old('application_id') : $applicationLog->application->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('application'))
                    <div class="invalid-feedback">
                        {{ $errors->first('application') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationLog.fields.application_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="log">{{ trans('cruds.applicationLog.fields.log') }}</label>
                <textarea class="form-control {{ $errors->has('log') ? 'is-invalid' : '' }}" name="log" id="log" required>{{ old('log', $applicationLog->log) }}</textarea>
                @if($errors->has('log'))
                    <div class="invalid-feedback">
                        {{ $errors->first('log') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationLog.fields.log_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="user_id">{{ trans('cruds.applicationLog.fields.user') }}</label>
                <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                    @foreach($users as $id => $entry)
                        <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $applicationLog->user->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('user'))
                    <div class="invalid-feedback">
                        {{ $errors->first('user') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationLog.fields.user_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection