@extends('layouts.admin')
@section('content')
@can('application_log_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.application-logs.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.applicationLog.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.applicationLog.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ApplicationLog">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.applicationLog.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationLog.fields.application') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationLog.fields.log') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationLog.fields.user') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicationLogs as $key => $applicationLog)
                        <tr data-entry-id="{{ $applicationLog->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $applicationLog->id ?? '' }}
                            </td>
                            <td>
                                {{ $applicationLog->application->issued_at ?? '' }}
                            </td>
                            <td>
                                {{ $applicationLog->log ?? '' }}
                            </td>
                            <td>
                                {{ $applicationLog->user->name ?? '' }}
                            </td>
                            <td>
                                @can('application_log_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.application-logs.show', $applicationLog->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('application_log_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.application-logs.edit', $applicationLog->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('application_log_delete')
                                    <form action="{{ route('admin.application-logs.destroy', $applicationLog->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('application_log_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.application-logs.massDestroy') }}",
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
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-ApplicationLog:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection