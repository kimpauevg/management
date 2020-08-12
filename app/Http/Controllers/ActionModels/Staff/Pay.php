<?php

namespace App\Http\Controllers\ActionModels\Staff;

use App\Models\Staff\Employee;
use App\Models\Staff\Manager;
use Illuminate\Foundation\Http\FormRequest;

class Pay extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'staff_to_pay' => [
                'required',
                'array',
            ],
            'staff_to_pay.*' => [
                'integer',
                'exists:employee,id'
            ],

        ];
    }
}