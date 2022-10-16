<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\ContractSigned as EventsContractSigned;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractStatusRequest;
use App\Http\Requests\UpdateContractStatusRequest;
use App\Http\Resources\Admin\ContractStatusResource;
use App\Mail\ContractDeclined;
use App\Mail\ContractSigned;
use App\Models\Contract;
use App\Models\ContractEquipment;
use App\Models\ContractLog;
use App\Models\ContractOffer;
use App\Models\ContractOpenedStatus;
use App\Models\ContractPath;
use App\Models\ContractProduct;
use App\Models\ContractService;
use App\Models\ContractStatus;
use App\Models\Badge;
use App\Models\EquipmentOffer;
use App\Models\ServiceOffer;
use Gate;
use Mail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use DB;
use Illuminate\Support\Facades\Storage;

class ContractStatusApiController extends Controller
{
    public function update(Request $request, ContractStatus $contractStatus)
    {
        DB::beginTransaction();

        $messaging = app('firebase.messaging');
        $input = $request->all();
        $totalSteps = ContractStatus::query()
            ->with(['contract_path'])
            ->where('contract_id', $contractStatus->contract_id)
            ->orderBy('id', 'asc')->get();
        // dd($totalSteps);

        if ($input['method'] == 'sign') {            
          // set accepted
          $contractStatus->status = 'accepted';
          
          if (isset($input['type'])) {
            $contractStatus->signature = $input['type'];
          }

          $contractStatus->save();

          // set next responsible's status to 'incoming'
          $nextUsers = $this->_getNextSigners($contractStatus, $totalSteps);
          // dd($nextUsers->toArray());

          if ($nextUsers) {
            // make them as next signers
            ContractStatus::query()
              ->where('contract_id', $contractStatus->contract_id)
              ->where('kind', $contractStatus->kind)
              ->whereIn('contract_path_id', $nextUsers->pluck('id'))
              ->update(['status' => 'incoming']);
              
            foreach ($nextUsers as $next) {
              // add to inbox
              $openedStatus = ContractOpenedStatus::firstOrCreate([
                  'user_id' => $next->responsible->id,
                  'contract_id' => $contractStatus->contract->id,
              ]);
              $openedStatus->status = 'unread';
              $openedStatus->save();

              // // notify next via email
              // Mail::to($next->responsible->email)->send(new ContractSigned($сontractStatus->contract));

              // // notify via push
              // if ($next->responsible->device_token != null) {
              //     $message = CloudMessage::withTarget('token', $next->responsible->device_token)
              //         ->withNotification(Notification::create('Новый договор', 'у вас новый договор на рассмотрение'))
              //         ->withData(['key' => 'value']);
              //     $messaging->send($message);
              // }
            }
          }

          // TODO: hardcoded: kurtayev -> SET A ROLE, not an email!!!
          if ($request->user()->email == 'kurtayev.meirzhan@mail.ru') {
              $contractStatus->contract->status = 'in_progress';
              // final responsible
          } else if (!$nextUsers) {
            if ($contractStatus->kind == 'application') {
              $contractStatus->contract->status = 'accepted';

              // create flow for 'check'
              $this->_startCheckFlow($contractStatus);
            } else if ($contractStatus->kind == 'contract') {
              $contractStatus->contract->status = 'signed_by_ceo';

              // save CMS
              if ($request->cms) {
                $path = time() . '.cms';
                Storage::disk('public_uploads')->put($path, $request->cms);
                $contractStatus->contract->file_singed = $path;
                // dd($contractStatus->contract);
                $contractStatus->contract->save();
              }

            }
          } else {
              // set contract's status to 'in_review'
              $contractStatus->contract->status = 'in_review';
          }
          
          // log to history
          // ContractLog::create([
          //     'contract_id' => $ContractStatus->contract_id,
          //     'user_id' => $request->user()->id,
          //     'log' => $request->user()->name . ' одобрил заявку под №' . $contractStatus->contract_id,
          // ]);

          // save contract
          $contractStatus->contract->save();
            
        } else if ($input['method'] == 'decline') {
            $contractStatus->status = 'declined';
            $contractStatus->declined_reason = $input['declined_reason'];
            $contractStatus->save();
            
            // set contract's status to 'declined'
            $contractStatus->contract->status = 'declined';
            $contractStatus->contract->save();

            // prev step
            $prevUsers = $this->_getPrevSigners($contractStatus, $totalSteps);

            if ($prevUsers) {
                ContractStatus::query()
                    ->where('contract_id', $contractStatus->contract_id)
                    ->whereIn('contract_path_id', $prevUsers->pluck('id'))
                    ->update(['status' => 'incoming']);

                foreach ($prevUsers as $prev) {
                  $openedStatus = ContractOpenedStatus::firstOrCreate([
                    'user_id' => $prev->responsible->id,
                    'contract_id' => $contractStatus->contract->id,
                  ]);
                  $openedStatus->status = 'unread';
                  $openedStatus->save();
    
                  // // notify prev via email that request was declined
                  // Mail::to($prev->responsible->email)->send(new ContractDeclined($contractStatus->contract));
  
                  // // notify via push
                  // if ($prev->responsible->device_token != null) {
                  //     $message = CloudMessage::withTarget('token', $prev->responsible->device_token)
                  //         ->withNotification(Notification::create('Договор отклонен', 'Ваш договор отклонен'))
                  //         ->withData(['key' => 'value']);
                  //     $messaging->send($message);
                  // }
                }
            }

            // no prev
            else {
              // set owner's unread
              $openedStatus = ContractOpenedStatus::firstOrCreate([
                'user_id' => $contractStatus->contract->owner_id,
                'contract_id' => $contractStatus->contract->id,
              ]);
              $openedStatus->status = 'unread';
              $openedStatus->save();

              $contractStatus->contract->status = 'draft';
              $contractStatus->contract->save();
            }
        }

        DB::commit();

        return 1;
    }

    private function _getNextSigners($status, $steps)
    {
      if ($status->contract_path->is_main == 1) {
        $next = ContractPath::query()
          ->where('type', $status->contract_path->type)
          ->where('kind', $status->kind)
          ->where('order', '>', $status->contract_path->order)
          ->orderBy('order', 'ASC')
          ->first();

        if ($next) {
          return ContractPath::query()
            ->with(['responsible'])
            ->where('type', $status->contract_path->type)
            ->where('order', $next->order)
            ->get();
        }

        return null;
      }

      return ContractPath::query()
        ->with(['responsible'])
        ->where('type', $status->contract_path->type)
        ->where('kind', $status->kind)
        ->where('order', $status->contract_path->order)
        ->where('id', '!=', $status->contract_path->id)
        ->get();
    }

  private function _getPrevSigners($status, $steps)
  {
    if ($status->contract_path->is_main == 1) {
      $prev = ContractPath::query()
        ->where('type', $status->contract_path->type)
        ->where('order', '<', $status->contract_path->order)
        ->where('kind', $status->kind)
        ->orderBy('order', 'DESC')
        ->first();

      if ($prev != null) {
        return ContractPath::query()
          ->with(['responsible'])
          ->where('type', $status->contract_path->type)
          ->where('kind', $status->kind)
          ->where('order', $prev->order)
          ->get();
      }

      return null;
    }

    return ContractPath::query()
      ->with(['responsible'])
      ->where('type', $status->contract_path->type)
      ->where('kind', $status->kind)
      ->where('order', $status->contract_path->order)
      ->where('id', '!=', $status->contract_path->id)
      ->get();
  }



  private function _startCheckFlow(ContractStatus $contractStatus) {
    DB::beginTransaction();

    $messaging = app('firebase.messaging');

    $contract = $contractStatus->contract;

    // statuses
    $path = ContractPath::query()
      ->with(['responsible'])
      ->where('kind', 'contract')
      ->orderBy('order', 'asc')
      ->get();

    // dd($path);

    $index = 0;
    foreach ($path as $step) {
      ContractStatus::create([
        'contract_id' => $contract->id,
        'contract_path_id' => $step->id,
        'kind' => 'contract',
        'status' => $index == 0 ? 'incoming' : 'waiting',
        'declined_reason' => '',
      ]);

      // first user
      if ($index === 0) {
        // add to inbox
        $openedStatus = ContractOpenedStatus::firstOrCreate([
            'user_id' => $step->responsible_id,
            'contract_id' => $contract->id,
        ]);
        $openedStatus->status = 'unread';
        $openedStatus->save();

        // notify via email
        // Mail::to($step->responsible->email)->send(new ContractSigned($contract));

        // notify via push
        // if ($step->responsible->device_token != null) {
        //     $message = CloudMessage::withTarget('token', $step->responsible->device_token)
        //         ->withNotification(Notification::create('Нужно утвердить договор', 'у вас договор для проверки и утверждения'))
        //         ->withData(['key' => 'value']);
        //     $messaging->send($message);
        // }
      }

      $index += 1;
    }

    DB::commit();

    return 1;
  }
}
