<?php

namespace SedpMis\BaseRepository;

/**
 * Validating trait to get rules.
 */
trait ValidatingTrait
{
    /**
     * Return the default rules for validation.
     *
     * @return array
     */
    public function defaultRules()
    {
        return property_exists($this, 'rules') ? $this->rules : [];
    }

    /**
     * Return the create rules for validation.
     *
     * @return array
     */
    public function createRules()
    {
        return property_exists($this, 'createRules') ? $this->createRules : $this->defaultRules();
    }

    /**
     * Return the update rules for validation.
     *
     * @return array
     */
    public function updateRules()
    {
        return property_exists($this, 'updateRules') ? $this->updateRules : $this->defaultRules();
    }

    /**
     * Return the rules for validation.
     *
     * @param  string|array  $key
     * @param  string  $operation
     * @return array
     */
    public function rules($key = null, $operation = null)
    {
        $ruleName = $operation ? "{$operation}Rules" : 'defaultRules';

        $rules = $this->{$ruleName}();

        if (is_null($key)) {
            return $this->rules;
        }

        return is_array($key) ? array_only($this->rules, $key) : $this->rules[$key];
    }

    /**
     * Set the rules.
     *
     * @param  array  $rules
     * @param  string|null  $operation
     * @return $this
     */
    public function setRules($rules = [], $operation = null)
    {
        $rulesProperty = $operation ? $operation.'Rules' : 'rules';

        $this->{$rulesProperty} = $rules;

        return $this;
    }
}
