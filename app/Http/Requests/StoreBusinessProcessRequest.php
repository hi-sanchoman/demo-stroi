<?php

namespace App\Http\Requests;

use App\Models\BusinessProcess;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreBusinessProcessRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('business_process_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'max:120',
                'required',
            ],
        ];
    }
}
