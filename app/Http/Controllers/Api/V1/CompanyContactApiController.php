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
use App\Models\ApplicationProduct;
use App\Models\CompanyContact;

class CompanyContactApiController extends Controller 
{
    public function index(Request $request, $companyId) {
        $collection = CompanyContact::where('company_id', $companyId)->get();
        
        return ['data' => $collection];
    }

    public function store(Request $request) {
        $input = $request->all();
        $contact = CompanyContact::create($input);

        return $contact;
    }

    public function destroy(Request $request, $companyId, $contactId)
    {
        $contact = CompanyContact::findOrFail($contactId);
        $contact->delete();
        return 1;
    }

    public function show(Request $request, $id) {
        $contact = CompanyContact::with(['company'])->findOrFail($id);
        return $contact;
    }

    public function edit(Request $request, CompanyContact $contact) {
        return $contact;
    }

    public function update(Request $request, CompanyContact $contact) {
        $input = $request->all();
        $contact->update($input);
        return $contact;
    }
}