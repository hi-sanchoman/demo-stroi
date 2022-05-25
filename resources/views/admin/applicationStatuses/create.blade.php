@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.applicationStatus.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.application-statuses.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="application_id">{{ trans('cruds.applicationStatus.fields.application') }}</label>
                <select class="form-control select2 {{ $errors->has('application') ? 'is-invalid' : '' }}" name="application_id" id="application_id" required>
                    @foreach($applications as $id => $entry)
                        <option value="{{ $id }}" {{ old('application_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('application'))
                    <div class="invalid-feedback">
                        {{ $errors->first('application') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationStatus.fields.application_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="application_path_id">{{ trans('cruds.applicationStatus.fields.application_path') }}</label>
                <select class="form-control select2 {{ $errors->has('application_path') ? 'is-invalid' : '' }}" name="application_path_id" id="application_path_id" required>
                    @foreach($application_paths as $id => $entry)
                        <option value="{{ $id }}" {{ old('application_path_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('application_path'))
                    <div class="invalid-feedback">
                        {{ $errors->first('application_path') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationStatus.fields.application_path_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required">{{ trans('cruds.applicationStatus.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status" required>
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\ApplicationStatus::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', 'incoming') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationStatus.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="declined_reason">{{ trans('cruds.applicationStatus.fields.declined_reason') }}</label>
                <textarea class="form-control {{ $errors->has('declined_reason') ? 'is-invalid' : '' }}" name="declined_reason" id="declined_reason" required>{{ old('declined_reason') }}</textarea>
                @if($errors->has('declined_reason'))
                    <div class="invalid-feedback">
                        {{ $errors->first('declined_reason') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationStatus.fields.declined_reason_helper') }}</span>
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