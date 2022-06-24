<?php

namespace App\Http\Requests;

use App\Models\ApplicationStatus;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('application_status_edit');
    }

    public function rules()
    {
        return [
            // 'application_id' => [
            //     'required',
            //     'integer',
            // ],
            // 'application_path_id' => [
            //     'required',
            //     'integer',
            // ],
            // 'status' => [
            //     'required',
            // ],
            // 'declined_reason' => [
            //     'required',
            // ],
        ];
    }
}
