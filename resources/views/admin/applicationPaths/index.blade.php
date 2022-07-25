@extends('layouts.admin')
@section('content')
@can('application_path_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.application-paths.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.applicationPath.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.applicationPath.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ApplicationPath">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.applicationPath.fields.order') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationPath.fields.position') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationPath.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationPath.fields.construction') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationPath.fields.responsible') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicationPaths as $key => $applicationPath)
                        <tr data-entry-id="{{ $applicationPath->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $applicationPath->order ?? '' }}
                            </td>
                            <td>
                                {{ $applicationPath->position ?? '' }}
                            </td>
                            
                            <td>
                                {{ $applicationPath->type ?? '' }}
                            </td>

                            <td>
                                {{ $applicationPath->construction->name ?? '' }}
                            </td>
                            <td>
                                {{ $applicationPath->responsible->name ?? '' }}
                            </td>
                            <td>
                                @can('application_path_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.application-paths.show', $applicationPath->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('application_path_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.application-paths.edit', $applicationPath->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('application_path_delete')
                                    <form action="{{ route('admin.application-paths.destroy', $applicationPath->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('application_path_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.application-paths.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'asc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-ApplicationPath:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection