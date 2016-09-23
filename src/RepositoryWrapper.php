<?php

namespace SedpMis\BaseRepository;

use Illuminate\Support\Facades\App;

abstract class RepositoryWrapper
{
    protected $repositories;

    protected static $instance;

    public function addRepository($repository, $name = null)
    {
        $this->repositories[$name ?: $this->getRepositoryName($repository)] = $repository;
    }

    /**
     * You can use get_defined_vars() php native function if you want your repositories to be renamed by its argument variable names.
     *
     * @param array $repositories
     */
    public function addRepositories(array $repositories)
    {
        foreach ($repositories as $name => $repository) {
            $this->addRepository($repository, (is_string($name) ? $name : null));
        }
    }

    public function getRepositoryName($repository)
    {
        // Remove 'RepositoryInterface' suffix
        $className = get_class($repository);

        $array     = explode('\\', $className);
        $className = end($array);

        return snake_case(str_replace('RepositoryEloquent', '', $className));
    }

    public function __get($repositoryName)
    {
        $repositoryName = snake_case($repositoryName);

        return $this->repositories[$repositoryName];
    }

    /**
     * Create a shared singleton instance.
     *
     * @return $instance
     */
    public static function shared()
    {
        return static::$instance ?: static::$instance = App::make(get_called_class());
    }

    /**
     * Instantiate a new repository instance.
     *
     * @return new
     */
    public static function make()
    {
        return App::make(get_called_class());
    }

    public static function __callStatic($repository, $args)
    {
        return static::make()->$repository;
    }

    public function __call($repository, $args)
    {
        return $this->{$repository};
    }
}
