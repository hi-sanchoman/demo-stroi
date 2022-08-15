<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Http\Resources\ProductResource;
use App\Models\Application;
use App\Models\ApplicationOffer;
use App\Models\ApplicationProduct;
use App\Models\EquipmentOffer;
use App\Models\ServiceOffer;

class CompanyApiController extends Controller 
{
    public function index(Request $request) {
        $collection = Company::orderBy('name', 'ASC')->get();
        
        return ['data' => $collection];
    }

    public function store(Request $request) {
        $input = $request->all();
        $input['status'] = $input['status']['value'];
        $input['responsible_id'] = $request->user()->id;

        $company = Company::create($input);

        return $company;
    }

    public function destroy(Request $request, Company $company)
    {
        if ($company->responsible_id != $request->user()->id) {
            return 0;
        }

        $company->delete();
        return 1;
    }

    public function show(Request $request, $id) {
        $company = Company::with(['responsible'])->findOrFail($id);
        return $company;
    }

    public function edit(Request $request, Company $company) {
        return $company;
    }

    public function update(Request $request, Company $company) {
        $input = $request->all();
        $input['status'] = $input['status']['value'];
        $company->update($input);
        return $company;
    }


    public function offers(Request $request, $id) {
        $offers = [];

        $applicationOffers = ApplicationOffer::where('company_id', $id)
            ->with(['applicationProduct', 'applicationProduct.product', 'applicationProduct.unit', 'applicationProduct.application', 'applicationProduct.application.construction'])
            ->get();

        $equipmentOffers = EquipmentOffer::where('company_id', $id)
            ->with(['applicationEquipment', 'applicationEquipment.equipment', 'applicationEquipment.unit', 'applicationEquipment.application', 'applicationEquipment.application.construction'])
            ->get();

        $serviceOffers = ServiceOffer::where('company_id', $id)
            ->with(['applicationService', 'applicationService.application', 'applicationService.application.construction'])
            ->get();
        // dd($serviceOffers);
        $index = 0;

        foreach ($applicationOffers as $item) {
            $offers[] = [
                'id' => $index,
                'type' => 'application',
                'data' => $item->applicationProduct,
                'offer' => $item,
                'product' => $item->applicationProduct->product,
                'application' => $item->applicationProduct->application,
                'construction' => $item->applicationProduct->application->construction, 
            ];

            $index += 1;
        }

        foreach ($equipmentOffers as $item) {
            $offers[] = [
                'id' => $index,
                'type' => 'equipment',
                'data' => $item->applicationEquipment,
                'offer' => $item,
                'product' => $item->applicationEquipment->equipment,
                'application' => $item->applicationEquipment->application,
                'construction' => $item->applicationEquipment->application->construction, 
            ];

            $index += 1;
        }

        foreach ($serviceOffers as $item) {
            $offers[] = [
                'id' => $index,
                'type' => 'service',
                'data' => $item->applicationService,
                'offer' => $item,
                'product' => $item->applicationService,
                'application' => $item->applicationService->application,
                'construction' => $item->applicationService->application->construction, 
            ];

            $index += 1;
        }

        return $offers;
    }
}