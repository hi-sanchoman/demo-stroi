@extends('layouts.admin')
@section('content')
@can('application_status_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.application-statuses.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.applicationStatus.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.applicationStatus.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ApplicationStatus">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.application') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.application_path') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.status') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationStatus.fields.declined_reason') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicationStatuses as $key => $applicationStatus)
                        <tr data-entry-id="{{ $applicationStatus->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $applicationStatus->id ?? '' }}
                            </td>
                            <td>
                                {{ $applicationStatus->application->issued_at ?? '' }}
                            </td>
                            <td>
                                {{ $applicationStatus->application_path->position ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\ApplicationStatus::STATUS_SELECT[$applicationStatus->status] ?? '' }}
                            </td>
                            <td>
                                {{ $applicationStatus->declined_reason ?? '' }}
                            </td>
                            <td>
                                @can('application_status_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.application-statuses.show', $applicationStatus->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('application_status_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.application-statuses.edit', $applicationStatus->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('application_status_delete')
                                    <form action="{{ route('admin.application-statuses.destroy', $applicationStatus->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('application_status_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.application-statuses.massDestroy') }}",
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
  let table = $('.datatable-ApplicationStatus:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection