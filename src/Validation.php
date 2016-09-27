<?php

namespace SedpMis\BaseRepository;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class Validation implements ValidationInterface
{
    /**
     * Eloquent model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Other validations to perform.
     *
     * @var array
     */
    protected $validations = [];

    /**
     * Constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null
     */
    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Validate model attributes before saving.
     * Throw an exception when validation fails.
     *
     * @param  string  $operation
     * @param  array|\Illuminate\Database\Eloquent\Model $model
     * @throws \Exception
     * @return void
     */
    public function validate($operation, $model)
    {
        $model = is_array($model) ? $model : $model->getAttributes();

        $rules = $this->model->rules($operation == 'update' ? array_keys($model) : null, $operation);

        $rules = $this->interpolateValidationRules($rules, $model);

        $validator = Validator::make($model, $rules);

        $messages = [];

        if ($validator->fails()) {
            $messages = $validator->messages()->all();
        }

        // Perform other validations
        foreach ($this->validations as $validationMethod) {
            if ($message = $this->{$validationMethod}($model)) {
                $messages[] = $message;
            }
        }

        if (count($messages)) {
            throw App::make('sedp-mis.base-repository.validationException', $messages);
        }
    }

    /**
     * Interpolate model attribute values on validation rules.
     *
     * @param  array $rules
     * @param  array $model
     * @return array
     */
    protected function interpolateValidationRules($rules, $model)
    {
        foreach ($rules as &$rule) {
            $attrs = last(chars_within($rule, ['{', '}']));

            foreach ($attrs as $attr) {
                $rule = str_replace(
                    '{'.$attr.'}',
                    // Useful for unique validation rule, which lets you check unique with exceptId parameter and
                    // other column keys combination. Example:
                    // 'name' => 'unique:parishes,name,{id},id,parish_category_id,{parish_category_id}'
                    is_null($model[$attr]) && $attr == $this->model->getKeyName() ? 'NULL' : $model[$attr],
                    $rule
                );
            }
        }

        return $rules;
    }
}
