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
        Сумма
      </th>
      <th>
        Оплачено
      </th>
      <th>
        Остаток
      </th>
      <th>
        На оплате
      </th>
    </tr>
  </thead>
  <tbody>
    @if (count($data) <= 0)
      <tr >
        <td colspan="7">Нет данных.</td>
      </tr>

    @else
      @foreach ($data as $payment)
        <tr>
          <td>{{ $payment->application->construction->name }}</td>
          
          <td style="cursor:pointer">
            <span class="">{{ $payment->application->id}}</span>
            <br />
          </td>
          
          <td width="30%">
            {{ $payment->company->name }}<br>
          </td>
          <td>{{ $payment->amount }} ₸</td>
          <td>{{ $payment->paid }} ₸</td>
          <td>{{ ($payment->amount - $payment->paid) }} ₸</td>

          <td>
            {{ $payment->to_be_paid ? $payment->to_be_paid . '₸' : '' }}
          </td>
        </tr>
      @endforeach
    @endif

    @if (count($data) > 0)
      <tr>
        <td colspan="3" class="text-right">ИТОГ</td>
        <td>{{ $totalAmount }} ₸</td>
        <td>{{ $totalPaid }} ₸</td>
        <td>{{ $totalAmount - $totalPaid }} ₸</td>
        <td colspan="1"></td>
      </tr>
    @endif
  </tbody>
</table>
