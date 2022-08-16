<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Resources\ProductResource;
use App\Models\Application;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use App\Models\EquipmentOffer;
use App\Models\PivotTaskUser;
use App\Models\ServiceOffer;
use Carbon\Carbon;

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
            $responsibles[] = $user['id'];
          }

          unset($input['responsibles']);
        }
        // dd($responsibles);

        // dd($input);
        $task = Task::create($input);
        // dd($task);
        
        if($responsibles) {
          foreach ($responsibles as $userId) {
            PivotTaskUser::create(['task_id' => $task->id, 'user_id' => $userId]);
          }
        }

        return $task;
    }

    public function start(Request $request) {
      $task = Task::findOrFail($request->id);
      $task->started_at = now();
      $task->status = 'in_progress';
      $task->save();

      return $task;
    }

    public function complete(Request $request) {
      $task = Task::findOrFail($request->id);
      // $task->started_at = now();
      $task->status = 'completed';
      $task->save();

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