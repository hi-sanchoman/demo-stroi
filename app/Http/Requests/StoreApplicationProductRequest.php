<?php

namespace App\Http\Requests;

use App\Models\ApplicationProduct;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreApplicationProductRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('application_product_create');
    }

    public function rules()
    {
        return [
            'application_id' => [
                'required',
                'integer',
            ],
            'product_id' => [
                'required',
                'integer',
            ],
            'quantity' => [
                'numeric',
                'required',
            ],
        ];
    }
}
