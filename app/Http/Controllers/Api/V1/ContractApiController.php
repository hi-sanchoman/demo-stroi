<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ApplicationResource;
use App\Models\Contract;
use App\Http\Resources\ProductResource;
use App\Models\Application;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use App\Models\ContractOpenedStatus;
use App\Models\ContractPath;
use App\Models\ContractStatus;
use App\Models\EquipmentOffer;
use App\Models\Notification;
use App\Models\ServiceOffer;
use DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Mail;

class ContractApiController extends Controller 
{
    public function index(Request $request) {
        $status = $request->has('status') ? $request->status : 'draft';

        // responsible user watching applications to review
        if ($status == 'all') {
            $ids = ContractOpenedStatus::query()
                ->where('user_id', $request->user()->id)
                ->pluck('contract_id');
            // dd($ids);

            if (!empty($ids->toArray())) {
                $collection = Contract::query()
                    ->with([
                        'owner', 
                        'openedStatuses' => function ($q) use ($request) {
                            return $q
                                ->where('user_id', $request->user()->id)
                                ->where('status', 'unread');
                        }])                
                    ->whereIn('id', $ids);

                // check if PTD -> show only his applications
                $roles = $request->user()->roles->pluck('title');
                
                if (in_array($roles[0], ['PTD Engineer', 'Supplier', 'Supervisor', 'Accountant', 'Material Accountant', 'PTD Manager', 'Chief Engineer', 'Section Manager'])) {
                // if (in_array('PTD Engineer', $roles->toArray()) || in_array('Supplier', $roles->toArray()) || in_array('Supervisor', $roles->toArray()) || in_array('Accountant', $roles->toArray())) {
                    $collection = $collection
                        ->orWhere('owner_id', $request->user()->id)
                        ->orderBy('created_at', 'DESC')
                        ->get();
                } else {
                    $collection = $collection
                        ->whereNot('status', 'draft')
                        ->orWhere('owner_id', $request->user()->id)
                        ->orderBy('created_at', 'DESC')
                        ->get();
                }

                return new ApplicationResource($collection);
            }
        } else if ($status == 'incoming') {
            // dd($request->user()->id);
            $path = ContractPath::query()
                ->where('responsible_id', $request->user()->id)
                ->first();
            // dd($path);

            $statuses = ContractStatus::query()
                ->where('status', 'incoming')
                ->where('contract_path_id', $path->id)->get();
            // dd($statuses);

            $collection = Contract::query()
                ->with(['owner', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->whereIn('id', $statuses->pluck('contract_id'))
                ->whereNot('status', 'draft');

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'declined_by_me') {
            $path = ContractPath::where('responsible_id', $request->user()->id)->first();

            $statuses = ContractStatus::query()
                ->where('status', 'declined')
                ->where('contract_path_id', $path->id)->get();

            $collection = Contract::query()
                ->with(['owner', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->whereIn('id', $statuses->pluck('contract_id'));
            
            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        } else if ($status == 'completed') {
            $collection = Contract::query()
                ->with(['owner', 'openedStatuses' => function ($q) use ($request) {
                    return $q
                        ->where('user_id', $request->user()->id)
                        ->where('status', 'unread');
                }])
                ->where('status', 'completed');

            $collection = $collection
                ->orderBy('updated_at', 'DESC')
                ->get();

            return new ApplicationResource($collection);
        }

        // dd('here');

        // user watches own draft applications
        $collection = Contract::query()
            ->with(['owner', 'openedStatuses'])
            // ->where('status', 'draft')
            ->where('owner_id', $request->user()->id);

        $collection = $collection
            ->orderBy('updated_at', 'DESC')
            ->get();

        return new ApplicationResource($collection);
    }

    public function store(Request $request) {
        $input = $request->all();        
        // dd($input);

        // file price
        if (isset($input['file_price'])) {
          $file = request()->file('file_price');
          $path = time() . '.' . $file->getClientOriginalExtension();
          $file->move(public_path('/uploads'), $path);
          $input['file_price'] = $path;
        }

        // file smeta
        if (isset($input['file_smeta'])) {
          $file = request()->file('file_smeta');
          $path = time() . '.' . $file->getClientOriginalExtension();
          $file->move(public_path('/uploads'), $path);
          $input['file_smeta'] = $path;
        }

        // file passport
        if (isset($input['file_passport'])) {
          $file = request()->file('file_passport');
          $path = time() . '.' . $file->getClientOriginalExtension();
          $file->move(public_path('/uploads'), $path);
          $input['file_passport'] = $path;
        }

        $input['status'] = 'draft';
        $input['owner_id'] = $request->user()->id;

        $contract = Contract::create($input);

        return $contract;
    }

    public function destroy(Request $request, Contract $contract)
    {
        if ($contract->owner != $request->user()->id) {
            return 0;
        }

        $contract->delete();
        return 1;
    }

    public function show(Request $request, $id) {
        $contract = Contract::with(['owner', 'comments', 'comments.user', 'contractContractStatuses', 'contractContractStatuses.contract_path', 'contractContractStatuses.contract_path.responsible'])->findOrFail($id);
        
        // make read status for a user
        ContractOpenedStatus::query()
            ->where('contract_id', $contract->id)
            ->where('user_id', $request->user()->id)
            ->update(['status' => 'read']);
        
        return $contract;
    }

    public function edit(Request $request, Contract $contract) {
        return $contract;
    }

    public function update(Request $request, Contract $contract) {
        
    }








    // first time signing
    public function signFirst(Request $request, $id) {
        DB::beginTransaction();

        $messaging = app('firebase.messaging');

        $contract = Contract::findOrFail($id);
        $input = $request->all();

        $totalSteps = ContractStatus::query()
            ->with(['contract_path'])
            ->where('contract_id', $contract->id)
            ->where('kind', 'application')
            ->orderBy('id', 'asc')->get();

        if ($input['method'] == 'sign') {
            // statuses
            $path = ContractPath::query()
                ->with(['responsible'])
                ->where('kind', 'application')
                ->orderBy('order', 'asc')
                ->get();

            // dd($path);

            $index = 0;
            foreach ($path as $step) {
                ContractStatus::create([
                    'contract_id' => $contract->id,
                    'contract_path_id' => $step->id,
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
                    //         ->withNotification(Notification::create('Новый договор', 'у вас новый договор на рассмотрение'))
                    //         ->withData(['key' => 'value']);
                    //     $messaging->send($message);
                    // }
                }

                $index += 1;
            }
        }

        $contract->status = 'in_review';
        $contract->save();

        DB::commit();

        return 1;
    }




    // filter
    public function filter(Request $request)
    {
        $input = json_decode($request->q);
        // dd($input);
        
        $collection = Contract::query()
            ->with([
                'owner', 
                'contractContractStatuses', 'contractContractStatuses.contract_path',
                'contractContractStatuses.contract_path.responsible', 
                'openedStatuses'
            ]);                
        
        if (isset($input->owners) && count($input->owners) > 0) {
            $collection = $collection->whereIn('owner_id', $input->owners);
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

        if (isset($input->name) && $input->name) {
            $collection = $collection->where('company_name', 'like', '%'.$input->name.'%');
        }

        if (isset($input->bin) && $input->bin) {
            $collection = $collection->where('company_bin', 'like', '%'.$input->bin.'%');
        }

        if (isset($input->no) && $input->no) {
            $collection = $collection->where('num', 'like', '%'.$input->no.'%');
        }

        if (isset($input->object) && $input->object) {
            $collection = $collection->where('address', 'like', '%'.$input->object.'%');
        }

        if (isset($input->subject) && $input->subject) {
            $collection = $collection->where('subject', 'like', '%'.$input->subject.'%');
        }

        if (isset($input->price) && $input->price) {
            $collection = $collection->where('price', floatval($input->price));
        }

        // filter by roles?
        $roles = $request->user()->roles->pluck('title');
                
        // get contracts
        $collection = $collection->orderBy('updated_at', 'DESC')->get();
        // dd($collection->toArray());

        // filter by status
        $responseCollection = collect();
        
        if (isset($input->statuses) && count($input->statuses) > 0 && !in_array('*', $input->statuses)) {
            $filtered = collect();

            if (in_array('mine', $input->statuses)) {
                $filtered = $collection->filter(function($item) use ($request) {
                    return $item->owner_id === $request->user()->id;
                });
            }

            $responseCollection = $responseCollection->merge($filtered);
            $filtered = collect();

            if (in_array('incoming', $input->statuses)) {
                $filtered = $collection->filter(function($item) use ($request) {
                    foreach ($item->contractContractStatuses as $status) {
                        if ($status->status == 'incoming' && $status->contract_path != null && $status->contract_path->responsible->id == $request->user()->id) {
                            return true;
                        }
                    }
                });
                // dd($collection);
            }

            $responseCollection = $responseCollection->merge($filtered);
            $filtered = collect();

            if (in_array('in_progress', $input->statuses)) {
                $filtered = $collection->filter(function($item) use ($request) {
                    $allowed = false;

                    foreach ($item->contractContractStatuses as $status) {
                        if ($allowed && in_array($status->status, ['accepted', 'incoming', 'declined']) && $item->status != 'signed_by_ceo') {
                            // dd($status->toArray());
                            return true;
                        }

                        if ($status->contract_path != null && $status->contract_path->responsible->id == $request->user()->id && $status->status === 'accepted') {
                            $allowed = true;
                        }
                    }
                });
            }

            $responseCollection = $responseCollection->merge($filtered);
            $filtered = collect();

            if (in_array('signed_by_ceo', $input->statuses)) {
                $filtered = $collection->filter(function($item) use ($request) {
                    return $item->status === 'signed_by_ceo';
                });
            }

            $responseCollection = $responseCollection->merge($filtered);
        } else {
            $responseCollection = $collection->filter(function($item) use ($request) {
                if ($item->owner_id == $request->user()->id) return true;

                // dd($request->user()->id);
                foreach ($item->contractContractStatuses as $status) {
                    if ($status->status == 'incoming' && $status->contract_path != null && $status->contract_path->responsible->id == $request->user()->id) {
                        return true;
                    }
                }
            });

            // dd($responseCollection->toArray());
        }

        return new ApplicationResource($responseCollection);
    }

    public function getOwners() {
        $owners = [];
        $added = [];
        
        foreach (Contract::with('owner')->get() as $contract) {
            if (!isset($added[$contract->owner_id])) {
                $added[$contract->owner_id] = 1;
                $owners[] = $contract->owner;
            }
        }

        return $owners;
    }
}