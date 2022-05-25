<?php

namespace App\Http\Requests;

use App\Models\ApplicationLog;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateApplicationLogRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('application_log_edit');
    }

    public function rules()
    {
        return [
            'application_id' => [
                'required',
                'integer',
            ],
            'log' => [
                'required',
            ],
            'user_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
