<?php

namespace SedpMis\BaseRepository;

interface ValidationInterface
{
    /**
     * Validate model attributes before saving.
     * Throw an exception when validation fails.
     *
     * @param  string  $operation
     * @param  array|\Illuminate\Database\Eloquent\Model $model
     * @throws \Exception
     * @return void
     */
    public function validate($operation, $model);
}
