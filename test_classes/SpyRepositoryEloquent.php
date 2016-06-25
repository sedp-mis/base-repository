<?php

use SedpMis\BaseRepository\BaseRepositoryEloquent;
use SedpMis\BaseRepository\RepositoryInterface;

class SpyRepositoryEloquent extends BaseRepositoryEloquent implements RepositoryInterface
{
    public function __construct(Spy $model)
    {
        $this->model = $model;
    }
}