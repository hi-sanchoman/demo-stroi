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
        На оплате
      </th>
    </tr>
  </thead>
  <tbody>
    @if (count($data) <= 0)
      <tr >
        <td colspan="4">Нет данных.</td>
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

          <td>
            {{ $payment->to_be_paid ? $payment->to_be_paid . '₸' : '' }}
          </td>
        </tr>
      @endforeach
    @endif
  </tbody>
</table>
