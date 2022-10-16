<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Resources\ProductResource;
use App\Mail\TaskAssigned;
use App\Mail\TaskStarted;
use App\Mail\TaskCompleted;
use App\Models\Application;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use App\Models\EquipmentOffer;
use App\Models\PivotTaskUser;
use App\Models\ServiceOffer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class TaskApiController extends Controller 
{
    public function index(Request $request) {
      $ids = PivotTaskUser::where('user_id', $request->user()->id)->pluck('task_id');

      $collection = Task::query()
        ->with(['owner', 'responsibles'])
        ->where('owner_id', $request->user()->id)
        ->orWhereIn('id', $ids)
        ->orderBy('due_date', 'ASC')
        ->get();

      return ['data' => $collection];
    }

    public function store(Request $request) {
        $input = $request->all();

        $input['is_hurry'] = $input['is_hurry'] == true ? 1 : 0;
        $input['status'] = 'new';
        $input['owner_id'] = $request->user()->id;
        $input['due_date'] = $input['due_date'] ? Carbon::createFromFormat('Y-m-d\TH:i', $input['due_date'])->timestamp : null;

        // responsibles
        $responsibles = [];

        if ($input['responsibles']) {
          foreach ($input['responsibles'] as $user) {
            $responsibles[] = $user;
          }

          unset($input['responsibles']);
        }
        // dd($responsibles);

        // dd($input);
        $task = Task::create($input);
        // dd($task);
        
        if($responsibles) {
          foreach ($responsibles as $user) {
            // dd($user);
            PivotTaskUser::create(['task_id' => $task->id, 'user_id' => $user['id']]);
            $userDb = User::find($user['id']);

            // // notify via email
            // Mail::to($userDb->email)->send(new TaskAssigned($task));

            // // notify via push
            // if ($userDb->device_token != null) {
            //   $messaging = app('firebase.messaging');
            //   $message = CloudMessage::withTarget('token', $userDb->device_token)
            //       ->withNotification(Notification::create('Новая задача', 'Вам назначили новую задачу'));
            //   $messaging->send($message);
            // }
          }
        }

        return $task;
    }

    public function start(Request $request) {
      $task = Task::with(['owner'])->findOrFail($request->id);
      $task->started_at = now();
      $task->status = 'in_progress';
      $task->save();
      
      // notify via email
      Mail::to($task->owner->email)->send(new TaskStarted($task, $request->user()));

      // notify via push
      if ($task->owner->device_token != null) {
        $messaging = app('firebase.messaging');
        $message = CloudMessage::withTarget('token', $task->owner->device_token)
            ->withNotification(Notification::create('Старт задачи', 'Начали выполнение вашей задачи: "' . $task->name . '"'));
        $messaging->send($message);
      }

      return $task;
    }

    public function complete(Request $request) {
      $task = Task::with(['owner'])->findOrFail($request->id);
      // $task->started_at = now();
      $task->status = 'completed';
      $task->save();
      
      // notify via email
      Mail::to($task->owner->email)->send(new TaskCompleted($task, $request->user()));

      // notify via push
      if ($task->owner->device_token != null) {
        $messaging = app('firebase.messaging');
        $message = CloudMessage::withTarget('token', $task->owner->device_token)
            ->withNotification(Notification::create('Задача завершена', 'Ваша задача "' . $task->name . '" закончена'));
        $messaging->send($message);
      }

      return 1;
    }


    public function destroy(Request $request, Task $task)
    {
        if ($task->owner_id != $request->user()->id) {
            return 0;
        }

        $task->delete();
        return 1;
    }

    public function show(Request $request, $id) {
        $task = Task::with(['owner', 'responsibles'])->findOrFail($id);
        return $task;
    }

    public function edit(Request $request, Task $task) {
        return $task;
    }

    public function update(Request $request, Task $task) {
        $input = $request->all();
        $input['status'] = $input['status']['value'];
        $task->update($input);

        return $task;
    }
}