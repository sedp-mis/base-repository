<?php

namespace SedpMis\BaseRepository;

use Services\Validation\ValidationFailedException;
use Validator;

class Validation implements ValidationInterface
{
    /**
     * The validation rules for create or update.
     *
     * @var array
     */
    protected $validationRules = [];

    /**
     * Other validations to perform.
     *
     * @var array
     */
    protected $validations = [];

    /**
     * Constructor.
     *
     * @param array $validationRules
     */
    public function __construct(array $validationRules)
    {
        $this->validationRules = $validationRules;
    }

    /**
     * Identify if validation is empty or has no rules.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->validationRules);
    }

    /**
     * Validate model attributes before saving.
     * Throw an exception when validation fails.
     *
     * @param  \BaseModel
     * @throws \Services\Validation\ValidationFailedException
     * @return void
     */
    public function validate($model)
    {
        $validationRules = $this->interpolateValidationRules($model);

        $validator = Validator::make($model->getAttributes(), $validationRules);

        $messages = [];

        if ($validator->fails()) {
            $messages = $validator->messages()->all('<p>:message</p>');
        }

        // Perform other validations
        foreach ($this->validations as $validationMethod) {
            if ($message = $this->{$validationMethod}($model)) {
                $messages[] = "<p>{$message}</p>";
            }
        }

        if (count($messages)) {
            throw new ValidationFailedException(join('', $messages));
        }
    }

    protected function interpolateValidationRules($model)
    {
        $validationRules = $this->validationRules;

        foreach ($validationRules as &$rule) {
            $attrs = last(chars_within($rule, ['{', '}']));

            foreach ($attrs as $attr) {
                $rule = str_replace(
                    '{'.$attr.'}',
                    // Useful for unique validation rule, which lets you check unique with exceptId parameter and
                    // other column keys combination. Example:
                    // 'name' => 'unique:parishes,name,{id},id,parish_category_id,{parish_category_id}'
                    is_null($model->{$attr}) && $attr == $model->getKeyName() ? 'NULL' : $model->{$attr},
                    $rule
                );
            }
        }

        return $validationRules;
    }
}
