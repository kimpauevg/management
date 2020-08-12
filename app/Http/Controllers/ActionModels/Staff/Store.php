<?php


namespace App\Http\Controllers\ActionModels\Staff;


use App\Models\Staff\Employee;
use App\Models\Staff\Manager;
use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
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
        $rules = array_merge(Employee::createRules(), (new Manager())->subordinateRules());
        return $rules;
    }

    public function messages()
    {
        $msgs = array_merge(parent::messages(), Manager::subordinateMessages());
        return $msgs;
    }

    public function attributes()
    {
        $attrs = array_merge(parent::attributes(), Manager::subordinateAttributes());
        return $attrs;
    }

}