<?php

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    protected static $rules = [];

    public static function rules($key = null)
    {
        if (is_null($key)) {
            return static::$rules;
        }

        return is_array($key) ? array_only(static::$rules, $key) : static::$rules[$key];
    }
}