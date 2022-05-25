<?php

namespace App\Http\Requests;

use App\Models\Construction;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateConstructionRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('construction_edit');
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
