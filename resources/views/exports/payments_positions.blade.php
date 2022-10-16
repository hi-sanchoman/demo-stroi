<table transition="slide-x-transition" style="overflow-x:auto;">
  <thead>
    <tr>
      <th class="text-left">
        Объект
      </th>
      <th class="text-left">
        № заявки
      </th>
      <th class="text-left">
        Компания
      </th>
      <th>
        Статья расходов
      </th>
      <th>
        Название позиции
      </th>
      <th>
        Кол-во
      </th>
      <th>
        Цена за ед.
      </th>
      <th>
        Общая сумма
      </th>
    </tr>
  </thead>
  <tbody>
    @if (count($data) <= 0)
      <tr >
        <td colspan="8">Нет данных.</td>
      </tr>

    @else
      @foreach ($data as $payment)
        <tr>
          <td>{{ $payment['construction']['name'] }}</td>
          
          <td style="cursor:pointer">
            <span class="">{{ $payment['application']['id']}}</span>
            <br />
          </td>
          
          <td width="30%">
            {{ $payment['company']['name'] }}<br>
          </td>

          <td>
            {{ $payment['category']}}
          </td>
          <td>
            {{ $payment['name']}}
          </td>
          <td>
            {{ $payment['quantity']}}
          </td>
          <td>
            {{ $payment['price']}}
          </td>
          <td>
            {{ $payment['quantity'] * $payment['price'] }}
          </td>
        </tr>
      @endforeach
    @endif

    @if (count($data) > 0)
      <tr>
        <td colspan="7" class="text-right">ИТОГ</td>
        <td>{{ $totalAmount }} ₸</td>
      </tr>
    @endif
  </tbody>
</table>
