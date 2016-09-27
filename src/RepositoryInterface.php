<?php

namespace SedpMis\BaseRepository;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Set eagerload relations.
     *
     * @param  array  $relations
     * @return $this
     */
    public function with($relations);

    /**
     * Return all models.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($attributes = ['*']);

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function find($id, $attributes = ['*']);

    /**
     * Get the models for the given attributes.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findWhere(array $whereAttributes, $attributes = ['*']);

    /**
     * Find a model by its primary key or return new model.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function findOrNew($id, $attributes = ['*']);

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function findOrFail($id, $attributes = ['*']);

    /**
     * Get the first model or the first model for the given attributes.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first(array $attributes = null);

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes);

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes);

    /**
     * Create and save a new model.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes);

    /**
     * Update the model attributes.
     *
     * @param  array  $attributes
     * @param  int|null  $id
     * @throws \Exception  When id is not given
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes, $id = null);

    /**
     * Save the model.
     *
     * @param  \Illuminate\Database Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function save(Model $model);

    /**
     * Delete a model by the following:
     *     $model               itself when a model is given
     *     [$model[,...]]       array or collection of models
     *     $id                  id of the model
     *     [id1[, id2, ...]]    array of ids.
     *
     * @param  mixed  $model
     * @return bool|int  Boolean when model is deleted or the number of models deleted
     */
    public function delete($model);

    /**
     * Query model if it has a given relation.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return $this
     */
    public function has($relation, $operator = '>=', $count = 1);

    /**
     * Return a collection of models by paginated approach.
     *
     * @param  int  $perPage
     * @param  int|null  $page
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function paginate($perPage = 15, $page = null, $attributes = ['*']);

    /**
     * Set attributes to be selected.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function attributes($attributes = ['*']);

    /**
     * Set hasRelations.
     *
     * @return bool
     */
    public function hasRelations($hasRelations = []);

    /**
     * Set basic filters.
     *
     * @param  array  $filters
     * @return $this
     */
    public function filters($filters = []);

    /**
     * Set sort.
     *
     * @param  array  $sort
     * @return $this
     */
    public function sort($sort = []);

    /**
     * Set limit.
     *
     * @param  int  $limit
     * @return $this
     */
    public function limit($limit);

    /**
     * Set offset.
     *
     * @param  int  $offset
     * @return $this
     */
    public function offset($offset);

    /**
     * Get models with applied query.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($attributes = ['*']);

    /**
     * Search any input against the given attributes.
     *
     * @param  string  $input
     * @param  array  $compareAttributes
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search($text, $compareAttributes = ['*'], $attributes = ['*']);
}
