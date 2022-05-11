<?php

namespace App\Http\Requests;

use App\Models\Stage;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreStageRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('stage_create');
    }

    public function rules()
    {
        return [
            'business_process_id' => [
                'required',
                'integer',
            ],
            'name' => [
                'string',
                'max:120',
                'required',
            ],
            'order' => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'document' => [
                'array',
            ],
        ];
    }
}
