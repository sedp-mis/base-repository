<?php

namespace SedpMis\BaseRepository;

interface RepositoryInterface
{
    public function with($relations);

    public function all($attributes = array('*'));

    public function find($id, $attributes = array('*'));

    public function findWhere(array $whereAttributes, $attributes = array('*'));

    public function findOrNew($id, $attributes = array('*'));

    public function findOrFail($id, $attributes = array('*'));

    public function first(array $attributes = null);

    public function firstOrCreate(array $attributes);

    public function firstOrNew(array $attributes);

    public function save($model);

    public function create(array $attributes);

    public function update(array $attributes, $id = null);

    public function delete($model);

    public function has($relation, $operator = '>=', $count = 1);

    // New Methods
    public function fetch($attributes = ['*'], $filters = [], $sort = [], $limit = null, $skip = 0);

    public function paginate($attributes = ['*'], $filters = [], $sort = [], $perPage = null, $page = 1);
}
