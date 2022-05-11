@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.responsible.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.responsibles.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="stage_id">{{ trans('cruds.responsible.fields.stage') }}</label>
                <select class="form-control select2 {{ $errors->has('stage') ? 'is-invalid' : '' }}" name="stage_id" id="stage_id" required>
                    @foreach($stages as $id => $entry)
                        <option value="{{ $id }}" {{ old('stage_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('stage'))
                    <div class="invalid-feedback">
                        {{ $errors->first('stage') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.stage_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="role_id">{{ trans('cruds.responsible.fields.role') }}</label>
                <select class="form-control select2 {{ $errors->has('role') ? 'is-invalid' : '' }}" name="role_id" id="role_id" required>
                    @foreach($roles as $id => $entry)
                        <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('role'))
                    <div class="invalid-feedback">
                        {{ $errors->first('role') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.role_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="specific_user_id">{{ trans('cruds.responsible.fields.specific_user') }}</label>
                <select class="form-control select2 {{ $errors->has('specific_user') ? 'is-invalid' : '' }}" name="specific_user_id" id="specific_user_id">
                    @foreach($specific_users as $id => $entry)
                        <option value="{{ $id }}" {{ old('specific_user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('specific_user'))
                    <div class="invalid-feedback">
                        {{ $errors->first('specific_user') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.specific_user_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="order">{{ trans('cruds.responsible.fields.order') }}</label>
                <input class="form-control {{ $errors->has('order') ? 'is-invalid' : '' }}" type="number" name="order" id="order" value="{{ old('order', '0') }}" step="1" required>
                @if($errors->has('order'))
                    <div class="invalid-feedback">
                        {{ $errors->first('order') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.order_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required">{{ trans('cruds.responsible.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status" required>
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\Responsible::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', 'waiting_for_review') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="reason">{{ trans('cruds.responsible.fields.reason') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('reason') ? 'is-invalid' : '' }}" name="reason" id="reason">{!! old('reason') !!}</textarea>
                @if($errors->has('reason'))
                    <div class="invalid-feedback">
                        {{ $errors->first('reason') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.reason_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="notes">{{ trans('cruds.responsible.fields.notes') }}</label>
                <textarea class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}" name="notes" id="notes">{{ old('notes') }}</textarea>
                @if($errors->has('notes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('notes') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.notes_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="reviewed_at">{{ trans('cruds.responsible.fields.reviewed_at') }}</label>
                <input class="form-control datetime {{ $errors->has('reviewed_at') ? 'is-invalid' : '' }}" type="text" name="reviewed_at" id="reviewed_at" value="{{ old('reviewed_at') }}">
                @if($errors->has('reviewed_at'))
                    <div class="invalid-feedback">
                        {{ $errors->first('reviewed_at') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.responsible.fields.reviewed_at_helper') }}</span>
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

@section('scripts')
<script>
    $(document).ready(function () {
  function SimpleUploadAdapter(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
      return {
        upload: function() {
          return loader.file
            .then(function (file) {
              return new Promise(function(resolve, reject) {
                // Init request
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route('admin.responsibles.storeCKEditorImages') }}', true);
                xhr.setRequestHeader('x-csrf-token', window._token);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.responseType = 'json';

                // Init listeners
                var genericErrorText = `Couldn't upload file: ${ file.name }.`;
                xhr.addEventListener('error', function() { reject(genericErrorText) });
                xhr.addEventListener('abort', function() { reject() });
                xhr.addEventListener('load', function() {
                  var response = xhr.response;

                  if (!response || xhr.status !== 201) {
                    return reject(response && response.message ? `${genericErrorText}\n${xhr.status} ${response.message}` : `${genericErrorText}\n ${xhr.status} ${xhr.statusText}`);
                  }

                  $('form').append('<input type="hidden" name="ck-media[]" value="' + response.id + '">');

                  resolve({ default: response.url });
                });

                if (xhr.upload) {
                  xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                      loader.uploadTotal = e.total;
                      loader.uploaded = e.loaded;
                    }
                  });
                }

                // Send request
                var data = new FormData();
                data.append('upload', file);
                data.append('crud_id', '{{ $responsible->id ?? 0 }}');
                xhr.send(data);
              });
            })
        }
      };
    }
  }

  var allEditors = document.querySelectorAll('.ckeditor');
  for (var i = 0; i < allEditors.length; ++i) {
    ClassicEditor.create(
      allEditors[i], {
        extraPlugins: [SimpleUploadAdapter]
      }
    );
  }
});
</script>

@endsection