Произведена следующая оплата<br>
<br>
Компания: {{ $payment->company->name }}<br>
Товар/Услуга: {{ $payment->applicationProduct->product }}<br>
Сумма: {{ $paid }}<br>
<br>

<a href="{{ env('APP_URL') }}/payments">Открыть реест платежей</a>