Произведена следующая оплата<br>
<br>
Компания: {{ $payment->company->name }}<br>
Товар/Услуга: {{ $payment->applicationProduct->product->name }}<br>
Сумма: {{ $paid }}<br> тг
<br>

<a href="{{ env('APP_URL') }}/payments">Открыть реест платежей</a>