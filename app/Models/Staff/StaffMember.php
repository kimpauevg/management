<?php


namespace App\Models\Staff;


interface StaffMember
{
    /**
     * Returns salary amount
     * @return string
     */
    public function countSalary(): string;

    /**
     * Returns staff's position name
     * @return string
     */
    public function getPositionName(): string;
}