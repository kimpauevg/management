<?php

namespace App\Models;

abstract class Model
{

    public function __construct($array)
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($array as $attribute => $value)
        {
            if (in_array($attribute, $class_vars)) {
                $this->$attribute = $value;
            }
        }
    }

}