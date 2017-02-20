<?php

namespace SedpMis\BaseRepository;

interface ValidationInterface
{
    /**
     * Validate model attributes before saving.
     * Throw an exception when validation fails.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $attributes Array attributes passed in the repository for create validation
     * @throws \Exception
     * @return void
     */
    public function validate($model, $attributes = []);
}
