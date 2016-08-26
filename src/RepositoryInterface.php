<?php

namespace SedpMis\BaseRepository;

interface RepositoryInterface
{
    public function with($relations);

    public function all($attributes = ['*']);

    public function find($id, $attributes = ['*']);

    public function findWhere(array $whereAttributes, $attributes = ['*']);

    public function findOrNew($id, $attributes = ['*']);

    public function findOrFail($id, $attributes = ['*']);

    public function first(array $attributes = null);

    public function firstOrCreate(array $attributes);

    public function firstOrNew(array $attributes);

    public function save($model);

    public function create(array $attributes);

    public function update(array $attributes, $id = null);

    public function delete($model);

    public function has($relation, $operator = '>=', $count = 1);

    public function fetch($attributes = ['*'], $filters = [], $sort = [], $limit = null, $skip = 0);

    public function paginate($attributes = ['*'], $filters = [], $sort = [], $perPage = null, $page = 1);

    public function attributes($attributes = ['*']);
    
    public function hasRelations($hasRelations = []);

    public function filters($filters = []);

    public function sort($sort = []);

    public function limit($limit);

    public function offset($offset);

    public function get($attributes = ['*']);

    public function search($text, $compareAttributes = ['*'], $attributes = ['*']);
}
