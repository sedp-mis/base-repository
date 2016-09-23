<?php

namespace SedpMis\BaseRepository;

interface ValidationInterface
{
    /**
     * Validate model attributes before saving.
     * Throw an exception when validation fails.
     *
     * @param  \Eloquent
     * @throws \Exception
     * @return void
     */
    public function validate($model);

    /**
     * Identify if validation is empty or has no rules.
     *
     * @return bool
     */
    public function isEmpty();
}
