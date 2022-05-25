<?php

namespace App\Http\Requests;

use App\Models\Construction;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreConstructionRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('construction_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'min:2',
                'max:120',
                'required',
            ],
        ];
    }
}
