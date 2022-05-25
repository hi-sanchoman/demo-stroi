<?php

namespace App\Http\Requests;

use App\Models\ApplicationLog;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyApplicationLogRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('application_log_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:application_logs,id',
        ];
    }
}
