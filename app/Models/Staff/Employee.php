<?php

namespace App\Models\Staff;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model implements StaffMember
{
    public $fillable = [
        'name', 'salary', 'phone', 'belongs_to_manager'
    ];

    public $table = 'employee';

    public static function createRules()
    {
        return  [
            'name' => [
                'required',
                'string',
            ],
            'salary' => [
                'required',
                'numeric',
                'min:0',
            ],
            'phone' => [
                'string',
                'unique:employee'
            ],
        ];
    }

    public function getManager()
    {
        return $this->belongsTo(Manager::class, 'manager_id', 'id');
    }

    public function isManager()
    {
        return $this->hasOne(Manager::class, 'employee_id', 'id');
    }

    public static function find($params)
    {

    }

    public function countSalary()
    {

    }

}