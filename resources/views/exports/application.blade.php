<table style="width: 1000px">
  <thead>
    {{-- header --}}
    <tr>
      <td colspan="5" style="font-weight: bold">Директору</td>
      <td style="font-weight: bold">{{ $application->created_at->format('d/m/Y') }}</td>
    </tr>
    <tr>
      <td colspan="6" style="font-weight: bold">ТОО "Онтүстік-құрылыс-сервис"</td>
    </tr>
    <tr>
      <td colspan="6" style="font-weight: bold">Құртаеву А.Ә.</td>
    </tr>
    
    {{-- line break --}}
    <tr>
      <td colspan="6">&nbsp;</td>
    </tr>

    {{--  --}}
    <tr>
      <td colspan="6" style="text-align: center; font-weight: bold">Заявка №{{ $application->id }}</td>
    </tr>
    <tr>
      <td colspan="6" style="text-align: center; font-weight: bold">на приобретение товарно-материальных ценностей</td>
    </tr>

    {{-- construction --}}
    <tr>
      <td colspan="6" style="font-weight: bold">кому: {{ $application->construction->name }}</td>
    </tr>

    {{-- line break --}}
    <tr>
      <td colspan="6">&nbsp;</td>
    </tr>

    {{-- items --}}
    <tr style="border: 1px solid black;">
      <th style="border: 1px solid black; font-weight: bold">№</th>
      <th style="border: 1px solid black; font-weight: bold">Статья расходов</th>
      <th style="border: 1px solid black; font-weight: bold">Наименование ресурсов</th>
      <th style="border: 1px solid black; font-weight: bold">Ед. изм.</th>
      <th style="border: 1px solid black; font-weight: bold">Кол-во</th>
      <th style="border: 1px solid black; font-weight: bold">Примечание</th>
    </tr>
  </thead>
  <tbody>
    @if ($application->kind == 'product')
      @foreach($items as $item)
        <tr style="border: 1px solid black;">
          <td style="border: 1px solid black;">{{ $loop->index + 1 }}</td>
          <td style="border: 1px solid black;">{{ $item->category->name }}</td>
          <td style="border: 1px solid black;">{{ $item->product->name }}</td>
          <td style="border: 1px solid black;">{{ $item->unit->name }}</td>
          <td style="border: 1px solid black;">{{ $item->quantity }}</td>
          <td style="border: 1px solid black;">{{ $item->notes }}</td>
        </tr>
      @endforeach
    @endif

    @if ($application->kind == 'equipment')
      @foreach($items as $item)
        <tr style="border: 1px solid black;">
          <td style="border: 1px solid black;">{{ $loop->index + 1 }}</td>
          <td style="border: 1px solid black;">Спец. техника</td>
          <td style="border: 1px solid black;">{{ $item->equipment->name }}</td>
          <td style="border: 1px solid black;">{{ $item->unit->name }}</td>
          <td style="border: 1px solid black;">{{ $item->quantity }}</td>
          <td style="border: 1px solid black;">{{ $item->notes }}</td>
        </tr>
      @endforeach
    @endif

    @if ($application->kind == 'service')
      @foreach($items as $item)
        <tr style="border: 1px solid black;">
          <td style="border: 1px solid black;">{{ $loop->index + 1 }}</td>
          <td style="border: 1px solid black;">{{ $item->category }}</td>
          <td style="border: 1px solid black;">{{ $item->service }}</td>
          <td style="border: 1px solid black;">{{ $item->unit }}</td>
          <td style="border: 1px solid black;">{{ $item->quantity }}</td>
          <td style="border: 1px solid black;">{{ $item->notes }}</td>
        </tr>
      @endforeach
    @endif
  </tbody>
</table>