Вам назначили новую задачу<br>

<br>
<strong>Постановщик:</strong> {{ $task->owner->name }}<br>
<strong>Задача:</strong> {{ $task->name }}<br>
<strong>Дедлайн:</strong> {{ $task->due_date }}

<br><br>
<a href="{{ env('APP_URL') }}/tasks/{{ $task->id }}/edit">Посмотреть</a>