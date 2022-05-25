<?php

namespace App\Http\Requests;

use App\Models\Application;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('application_create');
    }

    public function rules()
    {
        return [
            'construction_id' => [
                'required',
                'integer',
            ],
            'issued_at' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'kind' => [
                'required',
            ],
            'status' => [
                'required',
            ],
        ];
    }
}
