<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationStatusRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Resources\Admin\ApplicationStatusResource;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\ApplicationPath;
use App\Models\ApplicationStatus;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationStatusApiController extends Controller
{
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
        $input = $request->all();

        $totalSteps = ApplicationPath::count();

        if ($input['method'] == 'sign') {
            // set accepted
            $applicationStatus->status = 'accepted';
            $applicationStatus->save();

            // set next responsible's status to 'incoming'
            $nextStep = $applicationStatus->application_path_id + 1;

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


            // notify next via email that he has an incoming request


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

            if ($prevStep > 0) {
                ApplicationStatus::query()
                    ->where('application_id', $applicationStatus->application_id)
                    ->where('application_path_id', $prevStep)
                    ->update(['status' => 'incoming']);
            }

            // set application's status to 'declined'
            $applicationStatus->application->status = 'declined';
            $applicationStatus->application->save();

            // notify prev via email that request was declined

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
