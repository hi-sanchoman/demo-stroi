@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.applicationPath.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.application-paths.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="position">{{ trans('cruds.applicationPath.fields.position') }}</label>
                <input class="form-control {{ $errors->has('position') ? 'is-invalid' : '' }}" type="text" name="position" id="position" value="{{ old('position', '') }}" required>
                @if($errors->has('position'))
                    <div class="invalid-feedback">
                        {{ $errors->first('position') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationPath.fields.position_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="type">{{ trans('cruds.applicationPath.fields.type') }}</label>
                <select class="form-control select2 {{ $errors->has('type') ? 'is-invalid' : '' }}" name="type" id="type" required>
                    @foreach($applicationTypes as $id => $entry)
                        <option value="{{ $id }}" {{ old('type') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('type'))
                    <div class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationPath.fields.type_helper') }}</span>
            </div>

            <div class="form-group">
                <label class="required" for="construction_id">{{ trans('cruds.applicationPath.fields.construction') }}</label>
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
                <span class="help-block">{{ trans('cruds.applicationPath.fields.construction_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="responsible_id">{{ trans('cruds.applicationPath.fields.responsible') }}</label>
                <select class="form-control select2 {{ $errors->has('responsible') ? 'is-invalid' : '' }}" name="responsible_id" id="responsible_id" required>
                    @foreach($responsibles as $id => $entry)
                        <option value="{{ $id }}" {{ old('responsible_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('responsible'))
                    <div class="invalid-feedback">
                        {{ $errors->first('responsible') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationPath.fields.responsible_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="order">{{ trans('cruds.applicationPath.fields.order') }}</label>
                <input class="form-control {{ $errors->has('order') ? 'is-invalid' : '' }}" type="number" name="order" id="order" value="{{ old('order', 0) }}" required>
                @if($errors->has('order'))
                    <div class="invalid-feedback">
                        {{ $errors->first('order') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationPath.fields.order_helper') }}</span>
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