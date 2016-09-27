<?php

namespace SedpMis\BaseRepository;

trait ValidatingTrait
{
    public function defaultRules()
    {
        return property_exists($this, 'rules') ? $this->rules : [];
    }

    public function createRules()
    {
        return $this->defaultRules();
    }

    public function updateRules()
    {
        return $this->defaultRules();
    }

    public function rules($key = null, $operation = null)
    {
        $ruleName = $operation ? "{$operation}Rules" : 'defaultRules';

        $rules = $this->{$ruleName}();

        if (is_null($key)) {
            return $this->rules;
        }

        return is_array($key) ? array_only($this->rules, $key) : $this->rules[$key];
    }
}
