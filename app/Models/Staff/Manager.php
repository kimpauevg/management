<?php

namespace App\Models\Staff;


use App\Models\Model;

class Manager extends Model implements StaffMember
{
    public $table = 'manager';

    public static function createRules()
    {
        return Employee::createRules();
    }


    public static function asEmployee()
    {

    }
    public function countSalary()
    {
        // TODO: Implement countSalary() method.
    }

    public static function find($params)
    {
        // TODO: Implement find() method.
    }
}