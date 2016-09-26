<?php

class ValidationExceptionProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind('sedp-mis.base-repository.validationException', function ($app, $messages) {
            return new ValidationException(join('', $messages));
        });
    }
}