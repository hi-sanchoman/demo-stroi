<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\ApplicationSigned as EventsApplicationSigned;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationStatusRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Resources\Admin\ApplicationStatusResource;
use App\Mail\ApplicationDeclined;
use App\Mail\ApplicationSigned;
use App\Models\Application;
use App\Models\ApplicationEquipment;
use App\Models\ApplicationLog;
use App\Models\ApplicationOffer;
use App\Models\ApplicationOpenedStatus;
use App\Models\ApplicationPath;
use App\Models\ApplicationProduct;
use App\Models\ApplicationService;
use App\Models\ApplicationStatus;
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
use Carbon;

class ApplicationStatusApiController extends Controller
{
    // protected $messaging;

    // public function __construct(Messaging $messaging)
    // {
    //     $this->messaging = $messaging;
    // }

    public function index()
    {
        abort_if(Gate::denies('application_status_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationStatusResource(ApplicationStatus::with(['application', 'application_path'])->get());
    }

    public function store(StoreApplicationStatusRequest $request)
    {
        $applicationStatus = ApplicationStatus::create($request->all());

        return (new ApplicationStatusResource($applicationStatus))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ApplicationStatusResource($applicationStatus->load(['application', 'application_path']));
    }

    public function update(Request $request, ApplicationStatus $applicationStatus)
    {
        DB::beginTransaction();

        $messaging = app('firebase.messaging');
        $input = $request->all();
        $totalSteps = ApplicationStatus::query()
            ->with(['application_path'])
            ->where('application_id', $applicationStatus->application_id)
            ->orderBy('id', 'asc')->get();

        if ($input['method'] == 'sign') {            
            // if it is supplier or supervisor
            if (in_array($request->user()->roles[0]->title, ['Supervisor', 'Supplier'])) {                
                $application = Application::findOrFail($applicationStatus->application_id);

                // check for empty offers?
                $unsigned = 0;
                $productIds = 0;
                $serviceIds = 0;
                $equipmentIds = 0;

                if ($application->kind == 'product') {
                    $productIds = ApplicationProduct::whereApplicationId($application->id)->pluck('id')->toArray();
                    $unsigned = ApplicationOffer::whereIn('application_product_id', $productIds)->where('file', null)->count();
                }

                if ($application->kind == 'equipment') {
                    $equipmentIds = ApplicationEquipment::whereApplicationId($application->id)->pluck('id')->toArray();
                    $unsigned = EquipmentOffer::whereIn('application_offer_id', $equipmentIds)->where('file', null)->count();
                }

                if ($application->kind == 'service') {
                    $serviceIds = ApplicationService::whereApplicationId($application->id)->pluck('id')->toArray();
                    $unsigned = ServiceOffer::whereIn('application_service_id', $serviceIds)->where('file', null)->count();
                }

                if ($unsigned > 0) {
                    return -1;
                }
            }

            // set accepted
            $applicationStatus->status = 'accepted';
            $applicationStatus->save();

            // set next responsible's status to 'incoming'
            $nextUsers = $this->_getNextSigners($applicationStatus, $totalSteps);            

            if ($nextUsers) {
                // make them as next signers
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->whereIn('application_path_id', $nextUsers->pluck('id'))
                    ->update([
                        'status' => 'incoming',
                    ]);
                
                foreach ($nextUsers as $next) {
                    // days for deadline
                    $days = 1;
                    if (in_array($next->responsible->roles()->first()->title, ['Supplier', 'Supervisor'])) {
                        $days = 3;
                    }

                    ApplicationStatus::query()
                        ->where('application_id', $applicationStatus->application_id)
                        ->where('application_path_id', $next->id)
                        ->update([                            
                            'deadline_at' => Carbon\Carbon::now()->addDays($days)
                        ]);

                    // add to inbox
                    $openedStatus = ApplicationOpenedStatus::firstOrCreate([
                        'user_id' => $next->responsible->id,
                        'application_id' => $applicationStatus->application->parent_id ? $applicationStatus->application->parent_id : $applicationStatus->application->id,
                    ]);
                    $openedStatus->status = 'unread';
                    $openedStatus->save();

                    // notify next via email
                    Mail::to($next->responsible->email)->send(new ApplicationSigned($applicationStatus->application));

                    // notify via push
                    if ($next->responsible->device_token != null) {
                        $message = CloudMessage::withTarget('token', $next->responsible->device_token)
                            ->withNotification(Notification::create('Новая заявка', 'у вас новая заявка на рассмотрение'))
                            ->withData(['key' => 'value']);
                        $messaging->send($message);
                    }
                }
            }

            // TODO: hardcoded: kurtayev -> SET A ROLE, not an email!!!
            if ($request->user()->email == 'kurtayev.meirzhan@mail.ru') {
                $applicationStatus->application->status = 'in_progress';
                $applicationStatus->application->is_accepted = 1;
            } 
            // TODO: hardcoded email of fin dir
            else if ($request->user()->email == 'gulzhans81@mail.ru') {
                $this->_signAllPayments($applicationStatus->application);
            }
            // final responsible
            else if (!$nextUsers) {
                if ($applicationStatus->application->parent) {
                    $applicationStatus->application->parent->status = 'signed';
                    $applicationStatus->application->parent->is_signed = 1;
                    $applicationStatus->application->parent->save();
                } else {
                    $applicationStatus->application->status = 'signed';
                    $applicationStatus->application->is_signed = 1;
                    $applicationStatus->application->save();
                }
            } 
            
            else {
                // set application's status to 'in_review'
                $applicationStatus->application->status = 'in_review';
            }
            
            // log to history
            ApplicationLog::create([
                'application_id' => $applicationStatus->application->parent_id ? $applicationStatus->application->parent_id : $applicationStatus->application->id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->name . ' одобрил заявку под №' . $applicationStatus->application->num,
            ]);

            // save application
            $applicationStatus->application->save();
            
        } else if ($input['method'] == 'decline') {
            $applicationStatus->status = 'declined';
            $applicationStatus->declined_reason = $input['declined_reason'];
            $applicationStatus->save();
            
            // set application's status to 'declined'
            $applicationStatus->application->status = 'declined';
            $applicationStatus->application->save();

            // prev step
            $prevUsers = $this->_getPrevSigners($applicationStatus, $totalSteps);
            
            // make waiting for next users
            $nextUsers = $this->_getNextSigners($applicationStatus, $totalSteps);

            if ($nextUsers) {
                // make them as next signers
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->whereIn('application_path_id', $nextUsers->pluck('id'))
                    ->update(['status' => 'waiting']);
            }

            if ($prevUsers) {
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->whereIn('application_path_id', $prevUsers->pluck('id'))
                    ->update(['status' => 'incoming']);

                foreach ($prevUsers as $prev) {
                    $openedStatus = ApplicationOpenedStatus::firstOrCreate([
                        'user_id' => $prev->responsible->id,
                        'application_id' => $applicationStatus->application->parent_id ? $applicationStatus->application->parent_id : $applicationStatus->application->id,
                    ]);
                    $openedStatus->status = 'unread';
                    $openedStatus->save();
    
                    // notify prev via email that request was declined
                    Mail::to($prev->responsible->email)->send(new ApplicationDeclined($applicationStatus->application));
    
                    // notify via push
                    if ($prev->responsible->device_token != null) {
                        $message = CloudMessage::withTarget('token', $prev->responsible->device_token)
                            ->withNotification(Notification::create('Заявка отклонена', 'Ваша заявка отклонена'))
                            ->withData(['key' => 'value']);
                        $messaging->send($message);
                    }
                }
            }

            // log to history
            ApplicationLog::create([
                'application_id' => $applicationStatus->application->parent_id ? $applicationStatus->application->parent_id : $applicationStatus->application->id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->name . ' отклонил заявку под №' . $applicationStatus->application->num . ' по причине: ' . $input['declined_reason'],
            ]);
        }

        DB::commit();

        return new ApplicationStatusResource(ApplicationStatus::with(['application', 'application_path'])->where('application_id', $applicationStatus->application_id)->get());
    }

    public function destroy(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationStatus->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function _getNextStep($status, $steps)
    {
        foreach ($steps as $step) {
            if ($step->id > $status->id) {
                return $step->application_path_id;
            }
        }

        return 0;
    }

    private function _getNextSigners($status, $steps)
    {
        if ($status->application_path->is_main == 1) {
            $next = ApplicationPath::query()
                ->with(['responsible'])
                ->where('construction_id', $status->application_path->construction_id)
                ->where('type', $status->application_path->type)
                ->where('order', '>', $status->application_path->order)
                ->orderBy('order', 'ASC')
                ->first();

            if ($next != null) {
                return ApplicationPath::query()
                    ->with(['responsible'])
                    ->where('construction_id', $status->application_path->construction_id)
                    ->where('type', $status->application_path->type)
                    ->where('order', $next->order)
                    ->get();
            }

            return null;
        }

        return ApplicationPath::query()
            ->with(['responsible'])
            ->where('construction_id', $status->application_path->construction_id)
            ->where('type', $status->application_path->type)
            ->where('order', $status->application_path->order)
            ->where('id', '!=', $status->application_path->id)
            ->get();
    }

    // private function _getSameLevelSigners($status, $steps) {
    //     if ($status->application_path->is_main == 1) {
    //         $same = ApplicationPath::query()
    //             ->where('construction_id', $status->application_path->construction_id)
    //             ->where('type', $status->application_path->type)
    //             ->where('order', $status->application_path->order)
    //             ->orderBy('order', 'DESC')
    //             ->get();

    //         // // make 'waiting' for same levels
    //         if ($prev != null) {
    //             return ApplicationPath::query()
    //                 ->with(['responsible'])
    //                 ->where('construction_id', $status->application_path->construction_id)
    //                 ->where('type', $status->application_path->type)
    //                 ->where('order', $prev->order)
    //                 ->get();
    //         }

    //         return null;
    //     }

    //     return ApplicationPath::query()
    //         ->with(['responsible'])
    //         ->where('construction_id', $status->application_path->construction_id)
    //         ->where('type', $status->application_path->type)
    //         ->where('order', $status->application_path->order)
    //         ->where('id', '!=', $status->application_path->id)
    //         ->get();
    // }

    private function _getPrevSigners($status, $steps)
    {
        if ($status->application_path->is_main == 1) {
            $prev = ApplicationPath::query()
                ->with(['responsible'])
                ->where('construction_id', $status->application_path->construction_id)
                ->where('type', $status->application_path->type)
                ->where('order', '<', $status->application_path->order)
                ->orderBy('order', 'DESC')
                ->first();

            // // make 'waiting' for same levels
            if ($prev != null) {
                return ApplicationPath::query()
                    ->with(['responsible'])
                    ->where('construction_id', $status->application_path->construction_id)
                    ->where('type', $status->application_path->type)
                    ->where('order', $prev->order)
                    ->get();
            }

            return null;
        }

        return ApplicationPath::query()
            ->with(['responsible'])
            ->where('construction_id', $status->application_path->construction_id)
            ->where('type', $status->application_path->type)
            ->where('order', $status->application_path->order)
            ->where('id', '!=', $status->application_path->id)
            ->get();
    }

    private function _getPrevStep($status, $steps)
    {
        $prev = 0;

        foreach ($steps as $step) {
            if ($step->id == $status->id) {
                return $prev;
            }

            $prev = $step->application_path_id;
        }

        return $prev;
    }

    private function _signAllPayments($application)
    {
        if ($application->parent_id !=null) {
            $application = Application::findOrFail($application->parent_id);
        }
        // dd($application->payments()->get()->toArray());

        foreach ($application->payments()->get() as $payment) {
            $payment->status = 'completed';
            $payment->save();
        }
    }
}
