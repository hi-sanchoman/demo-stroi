<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use App\Http\Resources\Admin\ApplicationResource;
use App\Mail\ApplicationSigned;
use App\Mail\PriceChanged;
use App\Models\Application;
use App\Models\ApplicationEquipment;
use App\Models\ApplicationProduct;
use App\Models\ApplicationLog;
use App\Models\ApplicationOffer;
use App\Models\ApplicationOpenedStatus;
use App\Models\ApplicationPath;
use App\Models\ApplicationService;
use App\Models\ApplicationStatus;
use App\Models\Construction;
use App\Models\EquipmentOffer;
use App\Models\ServiceOffer;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use Carbon;
use Carbon\Carbon as CarbonCarbon;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ApplicationApiController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('application_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $status = $request->has('status') ? $request->status : 'draft';

        // responsible user watching applications to review
        if ($status == 'all') {
            // check if PTD -> show only his applications
            $roles = $request->user()->roles->pluck('title');
                
            if (in_array('PTD Engineer', $roles->toArray())) {
                $collection = Application::query()
                    ->with([
                        'construction', 
                        'applicationApplicationStatuses', 
                        'openedStatuses',
                        'applicationApplicationStatuses.application_path.responsible', 
                        'applicationApplicationProducts', 'applicationApplicationProducts.product', 'applicationApplicationProducts.category', 
                        'applicationServices',
                        'applicationEquipments', 'applicationEquipments.equipment',
                    ])      
                    ->where('construction_id', $request->construction_id)
                    ->where('owner_id', $request->user()->id)
                    ->where('parent_id', null)
                    ->orderBy('updated_at', 'DESC')
                    ->get();

                return new ApplicationResource($collection);
            } 

            else if (in_array('Warehouse Manager', $roles->toArray())) {
                $collection = Application::query()
                    ->with([
                        'construction', 
                        'applicationApplicationStatuses', 
                        'openedStatuses',
                        'applicationApplicationStatuses.application_path.responsible', 
                        'applicationApplicationProducts', 'applicationApplicationProducts.product', 'applicationApplicationProducts.category', 
                        'applicationServices',
                        'applicationEquipments', 'applicationEquipments.equipment',
                    ])      
                    ->where('construction_id', $request->construction_id)
                    ->where('kind', 'product')
                    ->where('parent_id', null)
                    // ->orWhere('is_signed', 1)
                    ->where('status', 'in_progress')
                    ->orderBy('updated_at', 'DESC')
                    ->get();
                
                // dd($collection->toArray());

                return new ApplicationResource($collection);
            } 

            else {
                // $applicationStatuses = ApplicationStatus::query()
                //     ->with(['application'])
                //     ->where('status', 'incoming')
                //     ->whereHas('application_path', function ($q) use ($request) {
                //         return $q->where('responsible_id', $request->user()->id);
                //     })
                //     ->get();

                // $ids = [];
                // foreach ($applicationStatuses as $status) {
                //     if ($status->application->parent_id != null) {
                //         $ids[] = $status->application->parent_id;
                //     }
                // }

                // $ids = array_unique($ids);

                // $collection = Application::query()
                //     ->with([
                //         'construction', 
                //         'applicationApplicationStatuses', 
                //         'openedStatuses',
                //         'applicationApplicationStatuses.application_path.responsible', 
                //         'applicationApplicationProducts', 'applicationApplicationProducts.product', 'applicationApplicationProducts.category', 
                //         'applicationServices',
                //         'applicationEquipments', 'applicationEquipments.equipment',
                //     ])                
                //     ->whereIn('id', $ids);
                
                // // allowed constructions
                // $allowedConstructions = $request->user()->constructions->pluck('id');
                // // $collection = $collection->whereIn('construction_id', $allowedConstructions);

                // if (!empty($allowedConstructions)) {
                //     // dd($allowedConstructions);
                //     $collection = $collection
                //         ->whereIn('construction_id', $allowedConstructions->filter(function($el) use ($request) { 
                //             return $el == $request->construction_id; 
                //         }));      
                // }

                // $collection = $collection
                //     // ->where('parent_id', null)
                //     ->whereNot('status', 'draft')
                //     ->orderBy('updated_at', 'DESC')
                //     ->get();

                // // dd($collection->toArray());

                // return new ApplicationResource($collection);

                $collection = Application::query()
                    ->with([
                        'construction', 
                        'applicationApplicationStatuses', 
                        'applicationApplicationStatuses.application_path.responsible', 
                        'applicationApplicationProducts', 'applicationApplicationProducts.product', 'applicationApplicationProducts.category',
                        'applicationServices', 
                        'applicationEquipments', 'applicationEquipments.equipment',
                        'openedStatuses'
                    ]);           

                // allowed constructions
                $allowedConstructions = $request->user()->constructions->pluck('id');
                // $collection = $collection->whereIn('construction_id', $allowedConstructions);

                if (!empty($allowedConstructions)) {
                    // dd($allowedConstructions);
                    $collection = $collection
                        ->whereIn('construction_id', $allowedConstructions->filter(function($el) use ($request) { 
                            return $el == $request->construction_id; 
                        }));      
                }

                $collection = $collection
                    // ->with(['application'])
                    ->whereNot('status', 'draft')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                
                $responseCollection = $collection->filter(function($item) use ($request) {
                foreach ($item->applicationApplicationStatuses as $status) {
                    if ($status->status == 'incoming' && $status->application_path != null && $status->application_path->responsible->id == $request->user()->id) {
                            return true;
                        }
                    }
                });
        
                return new ApplicationResource($responseCollection);
            }
            
            
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
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses'])
                ->whereIn('id', $statuses->pluck('application_id'))
                ->whereNot('status', 'draft');

            // allowed constructions
            $allowedConstructions = $request->user()->constructions->pluck('id');
            $collection = $collection->whereIn('construction_id', $allowedConstructions);

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource([]);
        } else if ($status == 'declined_by_me') {
            $path = ApplicationPath::where('responsible_id', $request->user()->id)->first();

            $statuses = ApplicationStatus::query()
                ->where('status', 'declined')
                ->where('application_path_id', $path->id)->get();

            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses'])
                ->whereIn('id', $statuses->pluck('application_id'));
            
            // allowed constructions
            $allowedConstructions = $request->user()->constructions->pluck('id');
            $collection = $collection->whereIn('construction_id', $allowedConstructions);

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource([]);
        } else if ($status == 'in_progress_supplier') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'in_progress');

            // allowed constructions
            $allowedConstructions = $request->user()->constructions->pluck('id');
            $collection = $collection->whereIn('construction_id', $allowedConstructions);

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'in_progress_economist') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'in_review');

            // allowed constructions
            $allowedConstructions = $request->user()->constructions->pluck('id');
            $collection = $collection->whereIn('construction_id', $allowedConstructions);

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
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
                ->orWhere('status', 'signed');

            // allowed constructions
            $allowedConstructions = $request->user()->constructions->pluck('id');
            $collection = $collection->whereIn('construction_id', $allowedConstructions);

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'completed') {
            $collection = Application::query()
                ->with(['construction', 'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path.responsible', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'completed');

            // allowed constructions
            $allowedConstructions = $request->user()->constructions->pluck('id');
            $collection = $collection->whereIn('construction_id', $allowedConstructions);

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        }

        // user watches own draft applications
        $collection = Application::query()
            ->with([
                'construction', 
                'applicationApplicationStatuses', 
                'openedStatuses',
                'applicationApplicationStatuses.application_path.responsible', 
                'applicationApplicationProducts', 'applicationApplicationProducts.product', 'applicationApplicationProducts.category', 
                'applicationServices',
                'applicationEquipments', 'applicationEquipments.equipment',
            ])      
            ->where('status', 'draft')
            ->where('parent_id', null)
            ->where('owner_id', $request->user()->id);

        // allowed constructions
        // $allowedConstructions = $request->user()->constructions->pluck('id');
        // $collection = $collection->whereIn('construction_id', $allowedConstructions);

        $collection = $collection
            ->orderBy('updated_at', 'DESC')
            ->get();

        return new ApplicationResource($collection);
    }

    public function store(Request $request)
    {
        // dd($request->all());

        try {
            DB::beginTransaction();

            $input = $request->all();
            $input['status'] = 'draft';
            $input['kind'] = $request->kind;
            $input['owner_id'] = $request->user()->id;
            $input['is_urgent'] = $request->is_urget ? 1 : 0;
            $input['issued_at'] = Carbon\Carbon::now();

            $construction = Construction::findOrFail($request->construction_id);
            $construction->num += 1;
            $construction->save();

            $input['num'] = $construction->num;

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
                foreach ($input['equipments'] as $equipment) {
                    ApplicationEquipment::create([
                        'application_id' => $application->id,
                        'equipment_id' => $equipment['equipment']['id'],
                        'quantity' => $equipment['quantity'],
                        'notes' => $equipment['notes'],
                        'unit_id' => $equipment['unit']['id'],
                        'is_delivered_by_us' => 0,
                    ]);
                }
            } else if ($application->kind == 'service') {
                foreach ($input['services'] as $item) {
                    ApplicationService::create([
                        'application_id' => $application->id,
                        'service' => $item['service'],
                        'category' => $item['category'],
                        'unit' => $item['unit'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'],
                        'is_delivered_by_us' => 0,
                    ]);
                }
            }
            // else -> throw error

            // application log
            ApplicationLog::create([
                'application_id' => $application->id,
                'user_id' => $application->owner_id,
                'log' => $request->user()->name . ' создал новую заявку под №' . $application->num,
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

        $subApplications = Application::query()
            ->with([
                'parent',
                'applicationApplicationProducts', 'applicationApplicationProducts.offers',
                'applicationEquipments', 'applicationEquipments.offers',
                'applicationServices', 'applicationServices.offers',
                'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path', 
                'applicationApplicationStatuses.application_path.responsible'
            ])
            ->whereNot('id', $application->id)
            ->where('parent_id', $application->id)
            ->get();
        // dd($subApplications->toArray());

        $positions = [];

        foreach ($subApplications as $sub) {
            if ($sub->kind == 'product') {
                foreach ($sub->applicationApplicationProducts as $product) {
                    $positions[] = $product->parent_id;
                }
            }
            else if ($sub->kind == 'equipment') {
                foreach ($sub->applicationEquipments as $equipment) {
                    $positions[] = $equipment->parent_id;
                }
            }
            else if ($sub->kind == 'service') {
                foreach ($sub->applicationServices as $service) {
                    $positions[] = $service->parent_id;
                }
            }
        }

        // dd($positions);

        $application = Application::query()
            ->with([
                'parent',
                'construction', 'comments', 'comments.user', 
                'applicationApplicationProducts' => function ($q) use ($positions) {
                    return $q->whereNotIn('id', $positions);
                }, 
                'applicationServices' => function ($q) use ($positions) {
                    return $q->whereNotIn('id', $positions);
                },  
                'applicationServices.offers', 'applicationServices.offers.company', 
                'applicationEquipments' => function ($q) use ($positions) {
                    return $q->whereNotIn('id', $positions);
                }, 
                'applicationEquipments.history', 'applicationEquipments.equipment', 
                'applicationEquipments.offers', 'applicationEquipments.offers.company', 'applicationEquipments.unit', 
                'applicationApplicationProducts.category', 'applicationApplicationProducts.unit', 
                'applicationApplicationProducts.offers', 'applicationApplicationProducts.inventoryApplications', 
                'applicationApplicationProducts.inventoryApplications.applicationProduct', 
                'applicationApplicationProducts.inventoryApplications.applicationProduct.product', 
                'applicationApplicationProducts.inventoryApplications.applicationProduct.category', 
                'applicationApplicationProducts.offers.company', 'applicationApplicationProducts.product.categories', 
                'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path', 
                'applicationApplicationStatuses.application_path.responsible'
            ])
            ->find($application->id);
        
        return new ApplicationResource($application);
    }

    public function getSubApplication(Request $request, $id)
    {
        
    }

    public function update(Request $request, Application $application)
    {
        // dd($request->all());

        try {
            DB::beginTransaction();

            $input = $request->all();
            $input['is_urgent'] = $request->is_urget ? 1 : 0;

            // make it editable again
            $roles = $request->user()->roles()->pluck('title');
            // dd($roles->toArray());

            // TODO: редактировать заявку или внести корректировку
            if (false && count(array_intersect(['PTD Engineer', 'Supplier', 'Supervisor', 'Section Manager'], $roles->toArray())) > 0) {
                // dd('test');

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
            else {
                // dd($request->all());

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
                    // is supplier or supervisor
                    ApplicationEquipment::where('application_id', $application->id)->delete();

                    foreach ($input['equipments'] as $item) {
                        ApplicationEquipment::create([
                            'application_id' => $application->id,
                            'equipment_id' => $item['equipment']['id'],
                            'quantity' => $item['quantity'],
                            'notes' => $item['notes'],
                            'unit_id' => $item['unit']['id'],
                            'is_delivered_by_us' => 0,
                        ]);
                    }
                } else if ($application->kind == 'service') {
                    // application services
                    ApplicationService::where('application_id', $application->id)->delete();
                    
                    foreach ($input['services'] as $item) {
                        ApplicationService::create([
                            'application_id' => $application->id,
                            'service' => $item['service'],
                            'category' => $item['category'],
                            'unit' => $item['unit'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                            'notes' => $item['notes'],
                            'is_delivered_by_us' => 0,
                        ]);
                    }

                    // TODO: редактирование цены снабженцем
                    // $ids = [];
                    // foreach ($input['services'] as $item) {


                    //     $ids[] = $item['id'];

                    //     unset($item['offers']); // TODO: how to keep them?
                    //     $service = ApplicationService::with(['application', 'application.owner'])->find($item['id']);
                        
                    //     if ($item['price'] && abs(($service->price - $item['price']) / $item['price']) > 0.00001) {   
                    //         if ($request->user()->roles[0]->title == 'Supplier' || $request->user()->roles[0]->title == 'Supervisor') {
                    //             // dd('price change');
                    //             Mail::to($service->application->owner->email)->send(new PriceChanged($service));
                    //         } 
                    //     }
                        
                    //     $service->update($item);

                    //     // if ($item['price'])
                    // }

                    // ApplicationService::whereNotIn('id', $ids)->delete();
                }
            }

            // application log
            ApplicationLog::create([
                'application_id' => $application->id,
                'user_id' => $application->owner_id,
                'log' => $request->user()->name . ' отредактировал заявку под №' . $application->num,
            ]);

            // application
            $application->update($input);

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

    public function filter(Request $request)
    {
        $input = json_decode($request->q);
        // dd($input);
        
        $collection = Application::query()
            ->with([
                'construction', 
                'applicationApplicationStatuses', 
                'applicationApplicationStatuses.application_path.responsible', 
                'applicationApplicationProducts', 'applicationApplicationProducts.product', 'applicationApplicationProducts.category',
                'applicationServices', 
                'applicationEquipments', 'applicationEquipments.equipment',
                'openedStatuses',

                'parent', 'parent.construction', 
                'parent.applicationApplicationStatuses', 
                'parent.applicationApplicationStatuses.application_path.responsible', 
                'parent.applicationApplicationProducts', 'parent.applicationApplicationProducts.product', 'parent.applicationApplicationProducts.category',
                'parent.applicationServices', 
                'parent.applicationEquipments', 'parent.applicationEquipments.equipment',
                'parent.openedStatuses',
            ]);                
        
        if (isset($input->constructions) && count($input->constructions) > 0) {
            $collection = $collection->whereIn('construction_id', $input->constructions);
        }

        if (isset($input->kinds) && count($input->kinds) > 0) {
            $collection = $collection->whereIn('kind', $input->kinds);
        }

        if (isset($input->period_from)) {
            $collection = $collection->where('created_at', '>=', $input->period_from);
        }

        if (isset($input->period_to)) {
            $collection = $collection->where('created_at', '<=', $input->period_to);
        }

        
        // allowed constructions
        // $allowedConstructions = $request->user()->constructions->pluck('id');
        // // $collection = $collection->whereIn('construction_id', $allowedConstructions);

        // if (!empty($allowedConstructions)) {
        //     // dd($allowedConstructions);
        //     $collection = $collection
        //         ->whereIn('construction_id', $allowedConstructions->filter(function($el) use ($request) { 
        //             return $el == $request->construction_id; 
        //         }));      
        // }

        // check if PTD -> show only his applications
        $roles = $request->user()->roles->pluck('title');
        
        if (in_array('PTD Engineer', $roles->toArray())) {
            $collection = $collection
                ->where('owner_id', $request->user()->id)
                ->orderBy('updated_at', 'DESC')
                ->get();
        } else if (in_array('Warehouse Manager', $roles->toArray())) {
            $collection = $collection
                ->where('is_accepted', 1)
                ->where('parent_id', null)
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if (in_array('Section Manager', $roles->toArray())) {
            $collection = $collection
                ->where('is_accepted', 1)
                ->where('parent_id', null)
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else {
            $collection = $collection
                // ->with(['application'])
                ->whereNot('status', 'draft')
                ->orderBy('updated_at', 'DESC')
                ->get();
        }

        $responseCollection = collect();
        
        if (isset($input->statuses) && count($input->statuses) > 0 && !in_array('*', $input->statuses)) {
            $filtered = collect();

            if (in_array('incoming', $input->statuses)) {
                foreach ($collection as $item) {
                    foreach ($item->applicationApplicationStatuses as $status) {
                        if (
                            $status->status == 'incoming' && 
                            // $item->parent_id == null &&
                            $status->application_path != null && 
                            $status->application_path->responsible->id == $request->user()->id
                        ) {
                            if ($item->parent_id != null) {
                                $filtered->push($item->parent);
                            } else {
                                $filtered->push($item);
                            }
                        }
                    }
                }
            }
            
            $filtered = $filtered->unique('id');

            $responseCollection = $responseCollection->merge($filtered);
            $filtered = collect();
            $rank = -1;

            if (in_array('in_progress', $input->statuses)) {
                $allowed = false;

                foreach ($collection as $item) {
                    foreach ($item->applicationApplicationStatuses as $status) {
                        if ($allowed && in_array($status->status, ['accepted', 'incoming', 'declined']) && $item->is_signed != 1 && $item->status != 'completed') {
                            if ($item->parent_id != null) {
                                $filtered->push($item->parent);
                            } else {
                                $filtered->push($item);
                            }
                            break;
                        }

                        if ($status->application_path != null && $status->application_path->responsible->id == $request->user()->id && $status->status === 'accepted') {
                            $allowed = true;
                        }
                    }
                }

                $filtered = $filtered->unique('id');
                
            }

            $responseCollection = $responseCollection->merge($filtered);
            $filtered = collect();

            if (in_array('signed', $input->statuses)) {
                $filtered = $collection->filter(function($item) use ($request) {
                    return $item->is_signed === 1;
                });
            }

            $responseCollection = $responseCollection->merge($filtered);
            $filtered = collect();

            if (in_array('completed', $input->statuses)) {
                $filtered = $collection->filter(function($item) use ($request) {
                    return $item->status === 'completed';
                });
            }

            $responseCollection = $responseCollection->merge($filtered);
        } else {
            $responseCollection = [];

            // by default
            foreach ($collection as $item) {
                foreach ($item->applicationApplicationStatuses as $status) {
                    if (
                        $status->status == 'incoming' && 
                        // $item->parent_id == null &&
                        $status->application_path != null && 
                        $status->application_path->responsible->id == $request->user()->id
                    ) {
                        if ($item->parent_id != null) {
                            $responseCollection[] = $item->parent;
                        } else {
                            $responseCollection[] = $item;
                        }
                    }
                }
            }

            $filtered = collect($responseCollection);
            $filtered = $filtered->unique('id');
            $responseCollection = $filtered;
            // dd($responseCollection);
        }

        return new ApplicationResource($responseCollection);
    }

    public function signFirstTime(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        // dd($application->toArray());

        // application statuses
        $path = ApplicationPath::query()
            ->with(['responsible'])
            ->where('type', $application->kind)
            ->where('construction_id', $application->construction_id)
            ->orderBy('order', 'asc')
            ->orderBy('is_main', 'asc')
            ->get();

        // TODO: if no $path rows -> error (setup signing flow)

        $index = 0;
        $secondSigner = null;
        
        DB::beginTransaction();

        foreach ($path as $step) {
            // status
            $status = 'waiting';
            if ($index == 0) $status = 'accepted';
            if ($index == 1) {
                $status = 'incoming';
                $secondSigner = $step->responsible;
            }

            $days = 1;
            if (in_array($step->responsible->roles()->first()->title, ['Supplier', 'Supervisor'])) {
                $days = 3;
            }

            // create rows for statuses
            $applicationStatus = ApplicationStatus::create([
                'application_id' => $application->id,
                'application_path_id' => $step->id,
                'status' => $status,
                'declined_reason' => '',
                'deadline_at' => $index == 1 ? Carbon\Carbon::now()->addDays($days) : null
            ]);

            $index += 1;
        }
        // dd($secondSigner);

        // add to inbox of next signer
        $openedStatus = ApplicationOpenedStatus::firstOrCreate([
            'user_id' => $secondSigner->id,
            'application_id' => $application->id,
        ]);
        $openedStatus->status = 'unread';
        $openedStatus->save();

        // notify next signer
        // $messaging = app('firebase.messaging');
        // Mail::to($secondSigner->email)->send(new ApplicationSigned($application));

        // // notify via push
        // if ($secondSigner->device_token != null) {
        //     $message = CloudMessage::withTarget('token', $secondSigner->device_token)
        //         ->withNotification(Notification::create('Новая заявка', 'у вас новая заявка на подпись'))
        //         ->withData(['key' => 'value']);
        //     $messaging->send($message);
        // }
        
        // save new status
        $application->status = 'in_review';
        $application->save();

        DB::commit();
    }

    public function makeEditable(Request $request, $id)
    {
        DB::beginTransaction();

        // delete statuses
        ApplicationStatus::where('application_id', $id)->delete();

        // delete inboxes
        ApplicationOpenedStatus::where('application_id', $id)->delete();

        // return to draft status
        $application = Application::findOrFail($id);
        $application->status = 'draft';
        $application->save();
    
        DB::commit();
    }

    public function checkOffers(Request $request)
    {
        $application = Application::with([
                'applicationEquipments', 'applicationEquipments.offers', 
                'applicationServices', 'applicationServices.offers', 
                'applicationApplicationProducts', 'applicationApplicationProducts.offers', 
                'applicationApplicationStatuses'
            ])->findOrFail($request->id);
        $positionIds = $request->positions;
        // dd($application->toArray());;

        $offers = [];
        if ($application->kind == 'product') {
            $offers = ApplicationOffer::query()
                ->whereIn('application_product_id', $positionIds)
                ->get();
                
            
        } else if ($application->kind == 'service') {
            $offers = ServiceOffer::query()
                ->whereIn('application_service_id', $positionIds)
                ->get();
        } else if ($application->kind == 'equipment') {
            $offers = EquipmentOffer::query()
                ->whereIn('application_equipment_id', $positionIds)
                ->get();
        }

        // TODO: check that each offer is valid (fully completed)
        // $valid = true;

        // foreach ($offers as $offer) {
        //     // TODO: fix for equipemnt 
        //     // if (!$offer->file) $valid = false;
        // }
        // if (!$valid || count($offers) <= 0) {
        //     return -1;
        // }

        // dd($offers->toArray());

        

        DB::beginTransaction();

        // create an application with parent_id = (id)
        $subApplication = Application::create([
            "kind" => $application->kind,
            "status" => $application->status,
            'issued_at' => Carbon\Carbon::now(),
            "is_urgent" => $application->is_urgent,
            "construction_id" => $application->construction_id,
            "owner_id" => $application->owner_id,
            "num" => $application->num,
            "is_signed" => $application->is_signed,
            "is_accepted" => $application->is_accepted,
            "parent_id" => $application->id
        ]);
        // dd($subApplication->toArray());
        
        // copy positions with offers
        if ($application->kind == 'product') {
            // application products
            foreach ($application->applicationApplicationProducts as $product) {
                if (!in_array($product->id, $positionIds)) continue;

                $subProduct = ApplicationProduct::create([
                    'application_id' => $subApplication->id,
                    'product_id' => $product->product_id,
                    'product_category_id' => $product->product_category_id,
                    'unit_id' => $product->unit_id,
                    'quantity' => $product->quantity,
                    'notes' => $product->notes,
                    'is_delivered_by_us' => 0,
                    'parent_id' => $product->id,
                ]);
                // dd($subProduct->toArray());

                foreach ($product->offers as $offer) {
                    $subOffer = ApplicationOffer::create([
                        'application_product_id' => $subProduct->id,        
                        'company_id' => $offer->company_id,
                        'quantity' => $offer->quantity,
                        'price' => $offer->price,
                        'paidTotal' => $offer->paidTotal,
                        'status' => $offer->status,
                        'file' => $offer->file,
                    ]);
                    // dd($subOffer->toArray());
                }
            }
        } else if ($application->kind == 'equipment') {
            foreach ($application->applicationEquipments as $equipment) {
                if (!in_array($equipment->id, $positionIds)) continue;

                $subEquipment = ApplicationEquipment::create([
                    'application_id' => $subApplication->id,
                    'equipment_id' => $equipment->equipment_id,
                    'quantity' => $equipment->quantity,
                    'notes' => $equipment->notes,
                    'unit_id' => $equipment->unit_id,
                    'is_delivered_by_us' => 0,
                    'parent_id' => $equipment->id,
                ]);

                foreach ($equipment->offers as $offer) {
                    EquipmentOffer::create([
                        'application_equipment_id' => $subEquipment->id,        
                        'company_id' => $offer->company_id,
                        'quantity' => $offer->quantity,
                        'price' => $offer->price,
                        'paidTotal' => $offer->paidTotal,
                        'status' => $offer->status,
                        'file' => $offer->file,
                    ]);
                }
            }
        } else if ($application->kind == 'service') {
            foreach ($application->applicationServices as $service) {
                if (!in_array($service->id, $positionIds)) continue;

                $subService = ApplicationService::create([
                    'application_id' => $subApplication->id,
                    'service' => $service->service,
                    'category' => $service->category,
                    'unit' => $service->unit,
                    'price' => $service->price,
                    'quantity' => $service->quantity,
                    'notes' => $service->notes,
                    'is_delivered_by_us' => 0,
                    'parent_id' => $service->id,
                ]);
                // dd($subService->toArray());

                foreach ($service->offers as $offer) {
                    $subOffer = ServiceOffer::create([
                        'application_service_id' => $subService->id,        
                        'company_id' => $offer->company_id,
                        'quantity' => $offer->quantity,
                        'price' => $offer->price,
                        'paidTotal' => $offer->paidTotal,
                        'status' => $offer->status,
                        'file' => $offer->file,
                    ]);
                    // dd($subOffer->toArray());
                }
            }
        }

        // create sign statuses
            // if main
                // accept prev signers + himselves
                // incoming next
        // create inbox for next signer

        $currentStep = ApplicationPath::query()
            ->where('type', $application->kind)
            ->where('construction_id', $application->construction_id)
            ->where('responsible_id', $request->user()->id)
            ->get();

        if (count($currentStep) <= 0) return -1;    // TODO: error, that no signer    
        $currentStep = $currentStep->first();
        // dd($currentStep->toArray());

        $path = ApplicationPath::query()
            ->with(['responsible'])
            ->where('type', $application->kind)
            ->where('construction_id', $application->construction_id)
            ->where('order', '>=', $currentStep->order)
            ->orderBy('order', 'asc')
            ->orderBy('is_main', 'asc')
            ->get();
        // dd($path->toArray());

        // TODO: if no $path rows -> error (setup signing flow)

        $index = 0;
        $secondSigner = null;
        $appStatuses = [];

        foreach ($path as $step) {
            // status
            $status = 'waiting';

            if ($step->id == $currentStep->id || ($step->order == $currentStep->order && $currentStep->is_main == 1)) {
                $status = 'accepted';
            }

            if ($index == 1 && !$secondSigner) {
                $status = 'incoming';
                $secondSigner = $step->responsible;
            } 

            // create rows for statuses
            $appStatus = ApplicationStatus::create([
                'application_id' => $subApplication->id,
                'application_path_id' => $step->id,
                'status' => $status,
                'declined_reason' => '',
                'deadline_at' => $index == 1 ? Carbon\Carbon::now()->addDays(3) : null, // TODO: hard-coded 3 days!
            ]);
            $appStatuses[] = $appStatus->toArray();

            $index += 1;
        }
        // dd($appStatuses);

        // add to inbox of next signer
        if ($secondSigner != null) {
            $openedStatus = ApplicationOpenedStatus::firstOrCreate([
                'user_id' => $secondSigner->id,
                'application_id' => $application->id,
            ]);
            $openedStatus->status = 'unread';
            $openedStatus->save();
    
            // notify next signer
            // $messaging = app('firebase.messaging');
            // Mail::to($secondSigner->email)->send(new ApplicationSigned($subApplication));
    
            // // notify via push
            // if ($secondSigner->device_token != null) {
            //     $message = CloudMessage::withTarget('token', $secondSigner->device_token)
            //         ->withNotification(Notification::create('Новая заявка', 'у вас новая заявка на подпись'))
            //         ->withData(['key' => 'value']);
            //     $messaging->send($message);
            // }
        }

        // set sub created application as in_review
        $subApplication->status = 'in_review';
        $subApplication->save();

        DB::commit();
    }

    private function _validOffers($offers, $kind) {
        return true;
    }


    public function getSubApplications(Request $request, $id) {
        $subapps = Application::query()
            ->with([
                'construction', 'comments', 'comments.user', 
                'applicationApplicationProducts', 
                'applicationServices',  
                'applicationServices.offers', 'applicationServices.offers.company', 
                'applicationEquipments', 
                'applicationEquipments.history', 'applicationEquipments.equipment', 
                'applicationEquipments.offers', 'applicationEquipments.offers.company', 'applicationEquipments.unit', 
                'applicationApplicationProducts.category', 'applicationApplicationProducts.unit', 
                'applicationApplicationProducts.offers', 'applicationApplicationProducts.inventoryApplications', 
                'applicationApplicationProducts.inventoryApplications.applicationProduct', 
                'applicationApplicationProducts.inventoryApplications.applicationProduct.product', 
                'applicationApplicationProducts.inventoryApplications.applicationProduct.category', 
                'applicationApplicationProducts.offers.company', 'applicationApplicationProducts.product.categories', 
                'applicationApplicationStatuses', 'applicationApplicationStatuses.application_path', 
                'applicationApplicationStatuses.application_path.responsible'
            ])
            ->where('parent_id', $id)
            ->orderBy('updated_at', 'DESC')
            ->get();

        // foreach ($subapps as $sub) {
        //     $sub->application_application_statuses = [];
        //     // dd($sub->toArray());
        // }

        return $subapps;
    }

    public function getParentStatuses(Request $request, $id)
    {
        $statuses = ApplicationStatus::query()
            ->with(['application', 'application_path', 'application_path.responsible'])
            ->where('application_id', $id)
            ->get();

        return $statuses;
    }

    public function getSigners(Request $request, $id) {
        $application = Application::findOrFail($id);

        $statuses = ApplicationStatus::query()
            ->with([
                'application_path',
                'application_path.responsible',
            ])
            ->where('application_id', $id)
            ->get();

        $parentStatuses = ApplicationStatus::query()
            ->with([
                'application_path',
                'application_path.responsible',
            ])
            ->where('application_id', $application->parent_id)
            ->get();

        // dd($application->applicationApplicationStatuses->toArray());

        return collect()->merge($parentStatuses->filter(function($s) {
            $skipRoles = ['PTD Engineer', 'Section Manager', 'PTD Manager', 'Chief Engineer', 'Vice President'];
            if ($s->application_path == null || $s->application_path->responsible == null) return false;

            $role = $s->application_path->responsible->roles()->first();
            // dd($role);

            return in_array($role->title, $skipRoles);
        }))->merge($statuses);
    }
}
