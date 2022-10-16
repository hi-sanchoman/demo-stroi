<table transition="slide-x-transition" style="overflow-x:auto;">
  <thead>
    <tr>
      <th class="text-left">
        № заявки
      </th>
      <th class="text-left">
        Объект
      </th>
      <th class="text-left">
        Статья расходов
      </th>
      <th>
        Тип заявки
      </th>
      <th>
        Дата
      </th>
      <th>
        Статус
      </th>
    </tr>
  </thead>
  <tbody>
    @if (count($data) <= 0)
      <tr >
        <td colspan="6">Нет данных.</td>
      </tr>

    @else
      @foreach ($data as $item)
        <tr>
          <td>{{ $item->num ? $item->num : '('.$item->id.')' }}</td>
          
          <td>
            {{ $item->construction->name }}
          </td>
          
          <td>
            @if ($item->kind == 'product')
              {{ $item->application_application_products[0]->category->name }}
            @elseif ($item->kind == 'service')
              {{ $item->application_services[0]->category }}
            @elseif ($item->kind == 'equipment')
              Аренда спец. техники
            @else
              --
            @endif
          </td>
          <td>{{ $item->kindLabel }}</td>
          <td>{{ $item->created_at }}</td>
          
          <td>
            {{ $item->statusLabel }}
          </td>
        </tr>
      @endforeach
    @endif
  </tbody>
</table>
