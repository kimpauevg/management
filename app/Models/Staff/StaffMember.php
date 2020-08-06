<?php


namespace App\Models\Staff;


interface StaffMember
{
    public static function createRules();

    public static function find($params);

    public function countSalary();
}