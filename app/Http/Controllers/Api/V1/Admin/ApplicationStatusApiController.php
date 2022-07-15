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
use App\Models\ApplicationLog;
use App\Models\ApplicationOpenedStatus;
use App\Models\ApplicationPath;
use App\Models\ApplicationStatus;
use App\Models\Badge;
use Gate;
use Mail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;


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
        $messaging = app('firebase.messaging');

        // dd($messaging);

        // $notification_id = 'dRMJYf1B1M3Xpj2CUEqtFG:APA91bGBUf2yeoWGjm8ZkgDPOG8-YvvU2eHfy6JH7KY2Z3v6UzL1HMIE_1OV5gM7yiB2GF0KN07agFQk2-AZTY4bKdn3iBEifXbrzDt7htb67TmWpaSDP6Em6EK50vpmdwAuFU7-4ZV4';
        // $title = "Новая заявка";
        // $message = "у вас новая заявка на рассмотрение";

        // $id = 1;
        // $type = "basic";

        // $res = sendNotificationFCM($notification_id, $title, $message, $id, $type);

        // if ($res === 'ok') {
        //     // success code
        //     dd('sent');
        // } else {
        //     // fail code
        //     dd($res);
        // }



        $input = $request->all();

        $totalSteps = ApplicationPath::count();

        if ($input['method'] == 'sign') {
            // set accepted
            $applicationStatus->status = 'accepted';
            $applicationStatus->save();

            // set next responsible's status to 'incoming'
            $nextStep = $applicationStatus->application_path_id + 1;
            $nextUserNote = ApplicationPath::with(['responsible'])->whereId($nextStep)->first();

            // first responsible just signed up
            if ($nextStep == 2) {
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->whereNot('application_path_id', 1)
                    ->update(['status' => 'waiting', 'declined_reason' => '']);
            }

            if ($nextStep <= $totalSteps) {
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->where('application_path_id', $nextStep)
                    ->update(['status' => 'incoming']);
            }

            // TODO: hardcoded: kurtayev -> SET A ROLE, not an email!!!
            if ($request->user()->email == 'kurtayev.meirzhan@gmail.com') {
                $applicationStatus->application->status = 'in_progress';
                // final responsible
            } else if ($nextStep == $totalSteps + 1) {
                $applicationStatus->application->status = 'in_progress';
                // $applicationStatus->application->status = 'signed';
            } else {
                // set application's status to 'in_review'
                $applicationStatus->application->status = 'in_review';
            }

            $applicationStatus->application->save();

            // add new +1 badge
            if ($nextUserNote != null) {
                // $badge = Badge::firstOrCreate([
                //     'user_id' => $nextUserNote->responsible_id,
                //     'type' => 'applications'
                // ]);
                // $badge->quantity += 1;
                // $badge->save();

                $openedStatus = ApplicationOpenedStatus::firstOrCreate([
                    'user_id' => $nextUserNote->responsible_id,
                    'application_id' => $applicationStatus->application->id,
                ]);
                $openedStatus->status = 'unread';
                $openedStatus->save();

                // notify next via email
                Mail::to($nextUserNote->responsible->email)->send(new ApplicationSigned($applicationStatus->application));
                // Mail::to('noreply.oks@yandex.kz')->send(new ApplicationSigned($applicationStatus->application));

                // notify via push
                $message = CloudMessage::withTarget('token', $nextUserNote->responsible->device_token)
                    ->withNotification(Notification::create('Новая заявка', 'у вас новая заявка на рассмотрение'))
                    ->withData(['key' => 'value']);
                $messaging->send($message);
            }



            // log to history
            ApplicationLog::create([
                'application_id' => $applicationStatus->application_id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->name . ' одобрил заявку под №' . $applicationStatus->application_id,
            ]);
        } else if ($input['method'] == 'decline') {
            $applicationStatus->status = 'declined';
            $applicationStatus->declined_reason = $input['declined_reason'];
            $applicationStatus->save();

            // prev step
            $prevStep = $applicationStatus->application_path_id - 1;
            $prevUserNote = ApplicationPath::with(['responsible'])->whereId($prevStep)->first();

            if ($prevStep > 0) {
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->where('application_path_id', $prevStep)
                    ->update(['status' => 'incoming']);
            }

            // set application's status to 'declined'
            $applicationStatus->application->status = 'declined';
            $applicationStatus->application->save();

            // add new +1 badge
            if ($prevUserNote != null) {
                // $badge = Badge::firstOrCreate([
                //     'user_id' => $prevUserNote->responsible_id,
                //     'type' => 'applications'
                // ]);
                // $badge->quantity += 1;
                // $badge->save();

                $openedStatus = ApplicationOpenedStatus::firstOrCreate([
                    'user_id' => $prevUserNote->responsible_id,
                    'application_id' => $applicationStatus->application->id,
                ]);
                $openedStatus->status = 'unread';
                $openedStatus->save();

                // notify prev via email that request was declined
                Mail::to($prevUserNote->responsible->email)->send(new ApplicationDeclined($applicationStatus->application));
                // Mail::to('noreply.oks@yandex.kz')->send(new ApplicationDeclined($applicationStatus->application));

                // notify via push
                $message = CloudMessage::withTarget('token', $prevUserNote->responsible->device_token)
                    ->withNotification(Notification::create('Заявка отклонена', 'Ваша заявка отклонена'))
                    ->withData(['key' => 'value']);
                $messaging->send($message);
            }



            // log to history
            ApplicationLog::create([
                'application_id' => $applicationStatus->application_id,
                'user_id' => $request->user()->id,
                'log' => $request->user()->name . ' отклонил заявку под №' . $applicationStatus->application_id . ' по причине: ' . $input['declined_reason'],
            ]);
        }

        return new ApplicationStatusResource(ApplicationStatus::with(['application', 'application_path'])->where('application_id', $applicationStatus->application_id)->get());
    }

    public function destroy(ApplicationStatus $applicationStatus)
    {
        abort_if(Gate::denies('application_status_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $applicationStatus->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
