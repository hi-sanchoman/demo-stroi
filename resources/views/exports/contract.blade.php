<table style="width: 1000px">
  <tbody>
    {{-- header --}}
    @if ($contract->owner)
    <tr>
      <td style="font-weight: bold">Инициатор</td>
      <td style="font-weight: bold">
        {{ $contract->owner->name }}
      </td>
    </tr>
    @endif

    @if ($contract->date_start)
    <tr>
      <td style="font-weight: bold">Дата начала оказания услуг</td>
      <td style="font-weight: bold">{{ $contract->date_start }}</td>
    </tr>
    @endif
    
    @if ($contract->date_end)
    <tr>
      <td style="font-weight: bold">Дата окончания оказания услуг</td>
      <td style="font-weight: bold">{{ $contract->date_end }}</td>
    </tr>
    @endif
    
    @if ($contract->price)
    <tr>
      <td style="font-weight: bold">Общая стоимость договора</td>
      <td style="font-weight: bold">{{ $contract->price }}</td>
    </tr>
    @endif

    @if ($contract->address)
    <tr>
      <td style="font-weight: bold">Название ЖК (либо место проведения работ, точный адрес)</td>
      <td style="font-weight: bold">{{ $contract->address }}</td>
    </tr>
    @endif
    
    @if ($contract->company_bin)
    <tr>
      <td style="font-weight: bold">БИН субподрядчика</td>
      <td style="font-weight: bold">{{ $contract->company_bin }}</td>
    </tr>
    @endif
    
    @if ($contract->company_address)
    <tr>
      <td style="font-weight: bold">Адрес субподрядчика</td>
      <td style="font-weight: bold">{{ $contract->company_address }}</td>
    </tr>
    @endif
    
    @if ($contract->company_bank)
    <tr>
      <td style="font-weight: bold">Наименование банка</td>
      <td style="font-weight: bold">{{ $contract->company_bank }}</td>
    </tr>
    @endif
    
    @if ($contract->company_ceo)
    <tr>
      <td style="font-weight: bold">ФИО директора</td>
      <td style="font-weight: bold">{{ $contract->company_ceo }}</td>
    </tr>
    @endif
    
    @if ($contract->payment_method)
    <tr>
      <td style="font-weight: bold">Способ оплаты</td>
      <td style="font-weight: bold">{{ $contract->payment_method }}</td>
    </tr>
    @endif
    
    @if ($contract->nds)
    <tr>
      <td style="font-weight: bold">НДС</td>
      <td style="font-weight: bold">{{ $contract->nds }}</td>
    </tr>
    @endif
    
    @if ($contract->warranty_amount)
    <tr>
      <td style="font-weight: bold">Сумма удержания в период гарантийного срока</td>
      <td style="font-weight: bold">{{ $contract->warranty_amount }}</td>
    </tr>
    @endif
    
    @if ($contract->warranty_job_period)
    <tr>
      <td style="font-weight: bold">Гарантийный срок на работы</td>
      <td style="font-weight: bold">{{ $contract->warranty_job_period }}</td>
    </tr>
    @endif
    
    @if ($contract->warranty_materials_period)
    <tr>
      <td style="font-weight: bold">Гарантийный срок на материалы</td>
      <td style="font-weight: bold">{{ $contract->warranty_materials_period }}</td>
    </tr>
    @endif
    
    @if ($contract->certificate)
    <tr>
      <td style="font-weight: bold">Наличие сертификата качества на товар/материал:</td>
      <td style="font-weight: bold">{{ $contract->certificate }}</td>
    </tr>
    @endif
    
    @if ($contract->delivery_place)
    <tr>
      <td style="font-weight: bold">Самовызов/доставка до пункта назначения (до куда?)</td>
      <td style="font-weight: bold">{{ $contract->delivery_place }}</td>
    </tr>
    @endif
    
    @if ($contract->deposit)
    <tr>
      <td style="font-weight: bold">Сумма депозита</td>
      <td style="font-weight: bold">{{ $contract->deposit }}</td>
    </tr>
    @endif
    
    @if ($contract->rent_reason)
    <tr>
      <td style="font-weight: bold">Назначение аренды</td>
      <td style="font-weight: bold">{{ $contract->rent_reason }}</td>
    </tr>
    @endif
    
    @if ($contract->rent_addons)
    <tr>
      <td style="font-weight: bold">Дополнительные расходы (какие и на кого)</td>
      <td style="font-weight: bold">{{ $contract->equipment_crew }}</td>
    </tr>
    @endif
    
    @if ($contract->equipment_crew)
    <tr>
      <td style="font-weight: bold">С экипажем или без</td>
      <td style="font-weight: bold">{{ $contract->equipment_crew }}</td>
    </tr>
    @endif
    
    @if ($contract->equipment_price_addons)
    <tr>
      <td style="font-weight: bold">Дополнительные расходы</td>
      <td style="font-weight: bold">{{ $contract->equipment_price_addons }}</td>
    </tr>
    @endif
    
    @if ($contract->equipment_working_hours)
    <tr>
      <td style="font-weight: bold">Время оказания услуг (например, с 9:00 утра до 19:00 вечера)</td>
      <td style="font-weight: bold">{{ $contract->equipment_working_hours }}</td>
    </tr>
    @endif
    
    @if ($contract->overrate)
    <tr>
      <td style="font-weight: bold">Сумма за переработку 1 часа (10%)</td>
      <td style="font-weight: bold">{{ $contract->overrate }}</td>
    </tr>
    @endif
    
    @if ($contract->equipment_responsible)
    <tr>
      <td style="font-weight: bold">Сторона, ответственная за поломку техники</td>
      <td style="font-weight: bold">{{ $contract->equipment_responsible }}</td>
    </tr>
    @endif
    
    @if ($contract->requisites)
    <tr>
      <td style="font-weight: bold">Реквизиты</td>
      <td style="font-weight: bold">{{ $contract->requisites }}</td>
    </tr>
    @endif
    
    @if ($contract->notes)
    <tr>
      <td style="font-weight: bold">Особые условия</td>
      <td style="font-weight: bold">{{ $contract->notes }}</td>
    </tr>
    @endif





  </tbody>
</table>