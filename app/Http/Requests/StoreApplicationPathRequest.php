<?php

namespace App\Http\Requests;

use App\Models\ApplicationPath;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreApplicationPathRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('application_path_create');
    }

    public function rules()
    {
        return [
            'position' => [
                'string',
                'required',
            ],
            'construction_id' => [
                'required',
                'integer',
            ],
            'responsible_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
