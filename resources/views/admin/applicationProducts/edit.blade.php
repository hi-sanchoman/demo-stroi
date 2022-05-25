@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.applicationProduct.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.application-products.update", [$applicationProduct->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="application_id">{{ trans('cruds.applicationProduct.fields.application') }}</label>
                <select class="form-control select2 {{ $errors->has('application') ? 'is-invalid' : '' }}" name="application_id" id="application_id" required>
                    @foreach($applications as $id => $entry)
                        <option value="{{ $id }}" {{ (old('application_id') ? old('application_id') : $applicationProduct->application->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('application'))
                    <div class="invalid-feedback">
                        {{ $errors->first('application') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationProduct.fields.application_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="product_id">{{ trans('cruds.applicationProduct.fields.product') }}</label>
                <select class="form-control select2 {{ $errors->has('product') ? 'is-invalid' : '' }}" name="product_id" id="product_id" required>
                    @foreach($products as $id => $entry)
                        <option value="{{ $id }}" {{ (old('product_id') ? old('product_id') : $applicationProduct->product->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('product'))
                    <div class="invalid-feedback">
                        {{ $errors->first('product') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationProduct.fields.product_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="quantity">{{ trans('cruds.applicationProduct.fields.quantity') }}</label>
                <input class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}" type="number" name="quantity" id="quantity" value="{{ old('quantity', $applicationProduct->quantity) }}" step="0.01" required>
                @if($errors->has('quantity'))
                    <div class="invalid-feedback">
                        {{ $errors->first('quantity') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationProduct.fields.quantity_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="notes">{{ trans('cruds.applicationProduct.fields.notes') }}</label>
                <textarea class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}" name="notes" id="notes">{{ old('notes', $applicationProduct->notes) }}</textarea>
                @if($errors->has('notes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('notes') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationProduct.fields.notes_helper') }}</span>
            </div>
            <div class="form-group">
                <div class="form-check {{ $errors->has('is_delivered_by_us') ? 'is-invalid' : '' }}">
                    <input type="hidden" name="is_delivered_by_us" value="0">
                    <input class="form-check-input" type="checkbox" name="is_delivered_by_us" id="is_delivered_by_us" value="1" {{ $applicationProduct->is_delivered_by_us || old('is_delivered_by_us', 0) === 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_delivered_by_us">{{ trans('cruds.applicationProduct.fields.is_delivered_by_us') }}</label>
                </div>
                @if($errors->has('is_delivered_by_us'))
                    <div class="invalid-feedback">
                        {{ $errors->first('is_delivered_by_us') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.applicationProduct.fields.is_delivered_by_us_helper') }}</span>
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