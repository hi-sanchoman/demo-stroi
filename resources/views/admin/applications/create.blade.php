@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.application.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.applications.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="construction_id">{{ trans('cruds.application.fields.construction') }}</label>
                <select class="form-control select2 {{ $errors->has('construction') ? 'is-invalid' : '' }}" name="construction_id" id="construction_id" required>
                    @foreach($constructions as $id => $entry)
                        <option value="{{ $id }}" {{ old('construction_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('construction'))
                    <div class="invalid-feedback">
                        {{ $errors->first('construction') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.application.fields.construction_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="issued_at">{{ trans('cruds.application.fields.issued_at') }}</label>
                <input class="form-control datetime {{ $errors->has('issued_at') ? 'is-invalid' : '' }}" type="text" name="issued_at" id="issued_at" value="{{ old('issued_at') }}" required>
                @if($errors->has('issued_at'))
                    <div class="invalid-feedback">
                        {{ $errors->first('issued_at') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.application.fields.issued_at_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required">{{ trans('cruds.application.fields.kind') }}</label>
                <select class="form-control {{ $errors->has('kind') ? 'is-invalid' : '' }}" name="kind" id="kind" required>
                    <option value disabled {{ old('kind', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\Application::KIND_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('kind', '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('kind'))
                    <div class="invalid-feedback">
                        {{ $errors->first('kind') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.application.fields.kind_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required">{{ trans('cruds.application.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status" required>
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\Application::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', 'draft') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.application.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <div class="form-check {{ $errors->has('is_urgent') ? 'is-invalid' : '' }}">
                    <input type="hidden" name="is_urgent" value="0">
                    <input class="form-check-input" type="checkbox" name="is_urgent" id="is_urgent" value="1" {{ old('is_urgent', 0) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_urgent">{{ trans('cruds.application.fields.is_urgent') }}</label>
                </div>
                @if($errors->has('is_urgent'))
                    <div class="invalid-feedback">
                        {{ $errors->first('is_urgent') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.application.fields.is_urgent_helper') }}</span>
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