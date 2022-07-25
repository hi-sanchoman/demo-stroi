<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Resources\Admin\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationProduct;
use App\Models\ApplicationLog;
use App\Models\ApplicationOpenedStatus;
use App\Models\ApplicationPath;
use App\Models\ApplicationStatus;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;

class ApplicationApiController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('application_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $status = $request->has('status') ? $request->status : 'draft';

        // responsible user watching applications to review
        if ($status == 'all') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'incoming') {
            $path = ApplicationPath::query()
                ->where('responsible_id', $request->user()->id)
                // ->where('construction_id', $application->construction_id)
                ->first();
            // dd($path);

            $statuses = ApplicationStatus::query()
                ->where('status', 'incoming')
                ->where('application_path_id', $path->id)->get();
            // dd($statuses);

            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->whereIn('id', $statuses->pluck('application_id'))
                ->whereNot('status', 'draft')
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'declined_by_me') {
            $path = ApplicationPath::where('responsible_id', $request->user()->id)->first();

            $statuses = ApplicationStatus::query()
                ->where('status', 'declined')
                ->where('application_path_id', $path->id)->get();

            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->whereIn('id', $statuses->pluck('application_id'))
                ->orderBy('id', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'in_progress_supplier') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'in_progress')
                ->orderBy('id', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'in_progress_economist') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'in_review')
                ->orderBy('id', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'in_progress_warehouse') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'in_progress')
                ->orWhere('status', 'in_review')
                ->orWhere('status', 'signed')
                ->orderBy('id', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'completed') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'completed')
                ->orderBy('id', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        }

        // user watches own draft applications
        $collection = Application::query()
            ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                return $q
                    ->where('user_id', $request->user()->id)
                    ->where('status', 'unread');
            }])
            ->where('status', $status)
            ->where('owner_id', $request->user()->id)
            ->orderBy('id', 'DESC')
            ->get();

        return new ApplicationResource($collection);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $input = $request->all();
            $input['status'] = 'draft';
            $input['kind'] = $request->kind;
            $input['owner_id'] = $request->user()->id;
            $input['is_urgent'] = $request->is_urget ? 1 : 0;
            $input['issued_at'] = Carbon\Carbon::now();

            // application
            $application = Application::create($input);

            // depends on kind
            if ($application->kind == 'product') {
                // application products
                foreach ($input['products'] as $product) {
                    ApplicationProduct::create([
                        'application_id' => $application->id,
                        'product_id' => $product['product']['id'],
                        'product_category_id' => $product['category']['id'],
                        'unit_id' => $product['unit']['id'],
                        'quantity' => $product['quantity'],
                        'notes' => $product['notes'],
                        'is_delivered_by_us' => 0,
                    ]);
                }
            } else if ($application->kind == 'equipment') {
            } else if ($application->kind == 'service') {
            }
            // else -> throw error

            // application statuses
            $path = ApplicationPath::where('type', $application->kind)->orderBy('order', 'asc')->get();

            foreach ($path as $step) {
                ApplicationStatus::create([
                    'application_id' => $application->id,
                    'application_path_id' => $step->id,
                    'status' => $step->id == 1 ? 'incoming' : 'waiting',
                    'declined_reason' => '',
                ]);
            }

            // application log
            ApplicationLog::create([
                'application_id' => $application->id,
                'user_id' => $application->owner_id,
                'log' => $request->user()->name . ' создал новую заявку под №' . $application->id,
            ]);

            DB::commit();

            return (new ApplicationResource($application))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }
    }

    public function show(Request $request, Application $application)
    {
        abort_if(Gate::denies('application_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // make read status for a user
        ApplicationOpenedStatus::query()
            ->where('application_id', $application->id)
            ->where('user_id', $request->user()->id)
            ->update(['status' => 'read']);

        return new ApplicationResource($application->load(['construction', 'applicationApplicationProducts', 'applicationApplicationProducts.category', 'applicationApplicationProducts.unit', 'applicationApplicationProducts.offers', 'applicationApplicationProducts.inventoryApplications', 'applicationApplicationProducts.inventoryApplications.applicationProduct', 'applicationApplicationProducts.inventoryApplications.applicationProduct.product', 'applicationApplicationProducts.inventoryApplications.applicationProduct.category', 'applicationApplicationProducts.offers.company', 'applicationApplicationProducts.product.categories', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path', 'applicationApplicationStatuses.application_path.responsible']));
    }

    public function update(Request $request, Application $application)
    {
        // dd($request->all());

        try {
            DB::beginTransaction();

            $input = $request->all();
            $input['is_urgent'] = $request->is_urget ? 1 : 0;

            // application
            $application->update($input);

            $roles = $request->user()->roles()->pluck('title');

            // is supplier or supervisor
            if (array_intersect(['Supplier', 'Supervisor'], $roles->toArray()) > 0) {
                // clear statuses till user's id
                $statuses = ApplicationStatus::query()
                    ->with(['application_path'])
                    ->where('application_id', $application->id)
                    ->get();

                $setWaiting = false;

                foreach ($statuses as $status) {
                    if ($status->application_path->responsible_id == $request->user()->id) {
                        $status->status = 'incoming';
                        $status->save();

                        $setWaiting = true;

                        continue;
                    }

                    if ($setWaiting == true) {
                        $status->status = 'waiting';
                        $status->save();
                    }
                }
            }

            // update products / equipments / services
            else {
                if ($application->kind == 'product') {
                    // application products
                    ApplicationProduct::where('application_id', $application->id)->delete();

                    foreach ($input['products'] as $product) {
                        ApplicationProduct::create([
                            'application_id' => $application->id,
                            'product_id' => $product['product']['id'],
                            'product_category_id' => $product['category']['id'],
                            'unit_id' => $product['unit']['id'],
                            'quantity' => $product['quantity'],
                            'notes' => $product['notes'],
                            'is_delivered_by_us' => 0,
                        ]);
                    }
                } else if ($application->kind == 'equipment') {
                    // special equipment
                } else if ($application->kind == 'service') {
                    // service 
                }
            }


            // application log
            ApplicationLog::create([
                'application_id' => $application->id,
                'user_id' => $application->owner_id,
                'log' => $request->user()->name . ' отредактировал заявку под №' . $application->id,
            ]);

            DB::commit();

            return (new ApplicationResource($application))
                ->response()
                ->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Exception $e) {
            // DB::rollback();
            dd($e->getMessage());
        }
    }

    public function destroy(Application $application)
    {
        abort_if(Gate::denies('application_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // TODO: delete all logs
        // TODO: delete all application products
        // TODO: delete other related table rows

        $application->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
