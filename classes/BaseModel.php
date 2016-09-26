<?php

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    protected $rules = [];

    public function rules($key = null)
    {
        if (is_null($key)) {
            return $this->rules;
        }

        return is_array($key) ? array_only($this->rules, $key) : $this->rules[$key];
    }
}