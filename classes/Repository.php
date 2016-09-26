<?php

class Repository extends \SedpMis\BaseRepository\RepositoryWrapper
{
    public function __construct(
        \SpyRepositoryInterface $spy
    ) {
        $this->addRepositories(get_defined_vars());
    }
}