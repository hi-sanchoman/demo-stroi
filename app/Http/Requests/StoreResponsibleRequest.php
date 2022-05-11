<?php

namespace App\Http\Requests;

use App\Models\Responsible;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreResponsibleRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('responsible_create');
    }

    public function rules()
    {
        return [
            'stage_id' => [
                'required',
                'integer',
            ],
            'role_id' => [
                'required',
                'integer',
            ],
            'order' => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'status' => [
                'required',
            ],
            'reviewed_at' => [
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'nullable',
            ],
        ];
    }
}
