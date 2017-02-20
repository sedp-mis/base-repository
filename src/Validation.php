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
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $attributes Array attributes passed in the repository for create validation
     * @throws \Exception
     * @return void
     */
    public function validate($model, $attributes = [])
    {
        $attributes = $model->exists ? $model->getDirty() : $attributes;

        $rules = $this->model->rules(($model->exists ? array_keys($attributes) : null), ($model->exists ? 'update' : 'create'));

        $rules = $this->interpolateValidationRules($rules, $attributes);

        $validator = Validator::make($attributes, $rules);

        $messages = [];

        if ($validator->fails()) {
            $messages = $validator->messages()->all();
        }

        // Perform other validations
        foreach ($this->validations as $validationMethod) {
            if ($message = $this->{$validationMethod}($attributes)) {
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
     * @param  array $attributes
     * @return array
     */
    protected function interpolateValidationRules($rules, $attributes)
    {
        foreach ($rules as &$rule) {
            $attrs = last(chars_within($rule, ['{', '}']));

            foreach ($attrs as $attr) {
                $rule = str_replace(
                    '{'.$attr.'}',
                    // Useful for unique validation rule, which lets you check unique with exceptId parameter and
                    // other column keys combination. Example:
                    // 'name' => 'unique:parishes,name,{id},id,parish_category_id,{parish_category_id}'
                    (!array_key_exists($attr, $attributes) || is_null($attributes[$attr])) && $attr == $this->model->getKeyName() ?
                        'NULL' :
                        $attributes[$attr],
                    $rule
                );
            }
        }

        return $rules;
    }
}
