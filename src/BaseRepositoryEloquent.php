<?php

namespace SedpMis\BaseRepository;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class BaseRepositoryEloquent implements RepositoryInterface
{
    /**
     * Eloquent model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Contain model's relation to be eagerload.
     *
     * @var array
     */
    protected $eagerLoadRelations = [];

    /**
     * Attributes to be selected.
     *
     * @var array
     */
    protected $attributes = ['*'];

    /**
     * Filters for retrieving models.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Sort rules for retrieving models.
     *
     * @var array
     */
    protected $sort = [];

    /**
     * Limit models retrieval.
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * Offset models retrieval.
     *
     * var integer
     */
    protected $offset = 0;

    /**
     * Has relation checking.
     *
     * @var array
     */
    protected $hasRelations = [];

    /**
     * Update model when id or primary key exists in model attributes, instead inserting new model.
     *
     * @var bool
     */
    protected $updateWhenIdExists = true;

    /**
     * Whether to save model recursively on its relations or aggregates models.
     *
     * @var bool
     */
    protected $isSaveRecursive = false;

    /**
     * Hold the validation instance.
     *
     * @var \SedpMis\BaseRepository\ValidationInterface
     */
    protected $validation;

    /**
     * Set the repository model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Eagerload relations.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Eloquent
     */
    protected function eagerLoadRelations()
    {
        $query = $this->model;

        if (is_array($this->eagerLoadRelations)) {
            $eagerLoads = [];

            foreach ($this->eagerLoadRelations as $relation => $rules) {
                // case for relations[]=relation_name
                // with relation that is numeric
                $relation = (is_integer($relation)) ? $rules : $relation;

                // case for relations[relation_name][attributes][]=attrib_name
                // with relation that is a relation name
                if (is_array($rules) && array_key_exists('attributes', $rules)) {
                    $eagerLoads[$relation] = function ($q) use ($rules) {
                        // If query is instance of HasOneOrMany,
                        // make sure to select the parent key name as foreign key.
                        if (
                            $q instanceof HasOneOrMany &&
                            !in_array($fk = $q->getParent()->getForeignKey(), $rules['attributes'])
                        ) {
                            array_push($rules['attributes'], $fk);
                        }

                        $q->select($rules['attributes']);
                    };
                } else {
                    array_push($eagerLoads, $relation);
                }
            }

            return $query->with($eagerLoads);
        }

        return $query->with($this->eagerLoadRelations);
    }

    /**
     * Set eagerload relations.
     *
     * @param  array  $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->eagerLoadRelations = $relations;

        return $this;
    }

    /**
     * Return all models.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($attributes = array('*'))
    {
        return $this->eagerLoadRelations()->get($attributes);
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function find($id, $attributes = array('*'))
    {
        return $this->eagerLoadRelations()->find($id, $attributes);
    }

    /**
     * Get the models for the given attributes.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findWhere(array $whereAttributes, $attributes = array('*'))
    {
        return $this->eagerLoadRelations()->where($whereAttributes)->get($attributes);
    }

    /**
     * Find a model by its primary key or return new model.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function findOrNew($id, $attributes = array('*'))
    {
        return $this->eagerLoadRelations()->findOrNew($id, $attributes);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function findOrFail($id, $attributes = array('*'))
    {
        return $this->eagerLoadRelations()->findOrFail($id, $attributes);
    }

    /**
     * Get the first model or the first model for the given attributes.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first(array $attributes = null)
    {
        $query = $this->eagerLoadRelations();

        if (!is_null($attributes)) {
            $query = $query->where($attributes);
        }

        return $query->first();
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes)
    {
        return $this->eagerLoadRelations()->firstOrCreate($attributes);
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes)
    {
        return $this->eagerLoadRelations()->firstOrNew($attributes);
    }

    /**
     * Make a new instance of the model from the attributes.
     *
     * @param  array                   $attributes
     * @throws \ModelNotFoundException When model not found by the given id
     * @return\Illuminate\Database\Eloquent\Model
     */
    public function makeModel(array $attributes)
    {
        $model = $this->model->newInstance($attributes);

        if ($this->updateWhenIdExists && array_key_exists($pk = $this->model->getKeyName(), $attributes)) {
            $model = $this->model->findOrFail($id = $attributes[$pk], array_merge([$pk], $this->filterFillables(array_keys($attributes))));

            $model->fill($attributes);
        }

        return $model;
    }

    /**
     * Filter fillable attributes of a model.
     *
     * @param  array $attributes
     * @return array
     */
    protected function filterFillables(array $attributes)
    {
        return array_filter($attributes, function ($attribute) {
            return $this->model->isFillable($attribute);
        });
    }

    /**
     * Return the validation.
     *
     * @return \SedpMis\BaseRepository\ValidationInterface
     */
    public function validation()
    {
        return $this->validation ?: $this->validation = new Validation($this->model);
    }

    /**
     * Create and save a new model or models.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function create(array $attributes)
    {
        /*
         * Polymorphic to handle multiple array of attributes
         */
        if (array_is_assoc(head($attributes))) {
            $arrayAttributes = $attributes;
            $models          = collection();
            foreach ($arrayAttributes as $attributes) {
                $models[] = $this->create($attributes);
            }

            return $models;
        }

        /*
         * Main logic handling create on single array
         */
        // Unset primary key when $updateWhenIdExists is true, to make sure to create new record in database.
        if (is_array($attributes) && array_is_assoc($attributes) && $this->updateWhenIdExists && array_key_exists($pk = $this->model->getKeyName(), $attributes)) {
            unset($attributes[$pk]);
        }

        $this->validation()->validate('create', $attributes);

        return $this->model->create($attributes);
    }

    /**
     * Update the model or models attributes.
     *
     * @param  array  $attributes
     * @param  int|null  $id
     * @throws \Exception  When id is not given
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes, $id = null)
    {
        /*
         * Polymorphic to handle multiple array of attributes
         */
        if (array_is_assoc(head($attributes))) {
            $arrayAttributes = $attributes;
            $models          = collection();
            foreach ($arrayAttributes as $attributes) {
                $models[] = $this->update($attributes);
            }

            return $models;
        }

        /*
         * Main logic handling of update on single array
         */
        $id = $id ?: $this->getIdFromAttributes($attributes);

        if (is_null($id)) {
            throw new \Exception("The `{$this->model->getKeyName()}` does not exist from the given attributes, cannot update {$this->model->getClass()}. ".
                'Attributes: '.json_encode($attributes));
        }

        $model = $this->model->findOrFail($id);

        if ($model instanceof Collection) {
            $model->each(function ($model) use ($attributes) {
                $model->fill($attributes);
            });
        } else {
            $model->fill($attributes);
        }

        return $this->save($model);
    }

    /**
     * Save the model or models.
     *
     * @param  \Illuminate\DatabaseEloquent\Model|\Illuminate\DatabaseEloquent\Collection|array  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function save($model)
    {
        /*
         * Polymorphic to handle collection or array of models
         */
        if (
            $model instanceof Collection ||
            is_array($model) &&
            (
                ($first = head($model)) instanceof Model ||
                array_is_assoc($first)
            )
        ) {
            return $this->saveMany($model);
        }

        /*
         * Main logic of handling save on single model
         */
        if (is_array($model) && array_is_assoc($model)) {
            $model = $this->makeModel($model);
        }

        if (!$this->validation()->isEmpty()) {
            $this->validation()->validate($model);
        }

        $this->saveModel($model);

        return $model;
    }

    /**
     * Save collection or array of models.
     *
     * @param  array|\Illuminate\Support\Collection $models
     * @return \Illuminate\Support\Collection
     */
    public function saveMany($models)
    {
        // Convert to collection if array
        $models = is_array($models) ? collection($models) : $models;

        foreach ($models as &$model) {
            $model = $this->save($model);
        }

        return $models;
    }

    /**
     * Save the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    protected function saveModel($model)
    {
        // Run some manipulation before saving model.
        $this->beforeSaveModel($model);

        $saved = $model->save();

        if (!$this->isSaveRecursive) {
            return $saved;
        }

        foreach ($model->getRelations() as $relation) {
            $saved = $this->saveModel($relation);
        }

        return $saved;
    }

    /**
     * Manipulate model before final save.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return\Illuminate\Database\Eloquent\Model
     */
    protected function beforeSaveModel($model)
    {
        return $model;
    }

    /**
     * Get the id from attributes.
     *
     * @param  array $attributes
     * @return int
     */
    public function getIdFromAttributes(array $attributes)
    {
        if (array_key_exists($keyName = $this->model->getKeyName(), $attributes) && !empty($attributes[$keyName])) {
            return $attributes[$keyName];
        }
    }

    /**
     * Delete a model by the following:
     *     $model               itself when a model is given
     *     [$model[,...]]       array or collection of models
     *     $id                  id of the model
     *     [id1[, id2, ...]]    array of ids.
     *
     * @param  mixed    $model
     * @return bool|int Boolean when model is deleted or the number of models deleted
     */
    public function delete($model)
    {
        // First use-case, itself when a model is given
        if ($model instanceof $this->model) {
            return $model->delete();
        }

        // Second use-case, array or collection of models
        $models = $model;
        if (is_array($models) && head($models) instanceof $this->model) {
            $models = $this->model->newCollection($models);
        }

        if ($models instanceof Collection) {
            $model = $models->pluck('id'); // Pass ids to $model to be deleted below on third and fourth use-case
        }

        // Third and fourth use-case, id or array of ids
        $ids = $model;
        if (is_int($model)) {
            $ids = [$ids];
        }

        if (!empty($ids)) {
            return $this->model->destroy($ids);
        }
    }

    /**
     * Query model if it has a given relation.
     *
     * @param  string $relation
     * @param  string $operator
     * @param  int    $count
     * @return $this
     */
    public function has($relation, $operator = '>=', $count = 1)
    {
        $this->hasRelations[$relation] = [$operator, $count];

        return $this;
    }

    /**
     * Query has relations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $hasRelations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function queryHasRelations($query, $hasRelations = [])
    {
        $hasRelations = $hasRelations ?: $this->hasRelations;

        foreach ($hasRelations as $relation => $operatorCount) {
            list($operator, $count) = $operatorCount;
            $query->has($relation, $operator, $count);
        }

        return $query;
    }

    /**
     * Query filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function queryFilters($query, $filters = [])
    {
        $filters = $filters ?: $this->filters;

        foreach ($filters as $key => $filter) {
            foreach ($filter as $operator => $values) {
                $values = is_array($values) ? $values : [$values];

                $operator = is_numeric($operator) ? '=' : $operator;

                if ($operator == '=') {
                    $query->whereIn($key, $values);
                } elseif ($operator == '!=') {
                    $query->whereNotIn($key, $values);
                } elseif ($operator == 'null') {
                    $query->whereNull($key);
                } elseif ($operator == 'not_null') {
                    $query->whereNotNull($key);
                } else {
                    $query->where($key, $operator, head($values));
                }
            }
        }

        return $query;
    }

    /**
     * Query sort.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $sort
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function querySort($query, $sort = [])
    {
        $sort = $sort ?: $this->sort;

        foreach ($sort as $attribute => $order) {
            $query->orderBy($attribute, $order);
        }

        return $query;
    }

    /**
     * Query limit and offset.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int|null                              $limit
     * @param  int                                   $offset
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function queryLimitOffset($query, $limit = null, $offset = 0)
    {
        $limit  = $limit ?: $this->limit;
        $offset = $offset ?: $this->offset;

        if ($limit) {
            $query->take($limit)->skip($offset);
        }

        return $query;
    }

    /**
     * Fetching eloquent models with filtering, sorting and limit-offset.
     *
     * @deprecated Use builder pattern, get() method
     * @param  array    $attributes
     * @param  array    $fiters
     * @param  array    $sort
     * @param  int|null $limit
     * @param  int      $offset
     * @return array
     */
    public function fetch($attributes = ['*'], $filters = [], $sort = [], $limit = null, $offset = 0)
    {
        $query = $this->eagerLoadRelations();

        //filters
        $this->queryFilters($query, $filters);

        //sort
        $this->querySort($query, $sort);

        //limit and offset
        $this->queryLimitOffset($query, $limit, $offset);

        return $query->get($attributes ?: ['*']);
    }

    /**
     * Return a collection of models by paginated approach.
     *
     * @param  int                                      $perPage
     * @param  int|null                                 $page
     * @param  array                                    $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function paginate($perPage = 15, $page = null, $attributes = ['*'])
    {
        $pagelo = new PageLimitOffset($perPage, $page);

        return $this->limit($pagelo->limit())
            ->offset($pagelo->offset())
            ->get($attributes);
    }

    /**
     * Return the underlying query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return $this->prepareQuery();
    }

    /**
     * Return the underlying model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Set attributes to be selected.
     *
     * @param  array $attributes
     * @return $this
     */
    public function attributes($attributes = ['*'])
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set hasRelations.
     *
     * @return bool
     */
    public function hasRelations($hasRelations = [])
    {
        $this->hasRelations = $hasRelations;

        return $this;
    }

    /**
     * Set basic filters.
     *
     * @param  array $filters
     * @return $this
     */
    public function filters($filters = [])
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Set sort.
     *
     * @param  array $sort
     * @return $this
     */
    public function sort($sort = [])
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Set limit.
     *
     * @param  int   $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set offset.
     *
     * @param  int   $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Prepare query to fetch models.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function prepareQuery()
    {
        // relations
        $query = $this->eagerLoadRelations();

        // has relations
        $this->queryHasRelations($query);

        //filters
        $this->queryFilters($query);

        //sort
        $this->querySort($query);

        //limit and offset
        $this->queryLimitOffset($query);

        return $query;
    }

    /**
     * Return the final attributes to be selected.
     *
     * @param  array $attributes
     * @return array
     */
    protected function selectAttributes($attributes = ['*'])
    {
        $attributes = $attributes ?: ['*'];

        if ($attributes == ['*'] && $this->attributes != ['*']) {
            $attributes = $this->attributes;
        }

        return $attributes;
    }

    /**
     * Get models with applied query.
     *
     * @param  array                                    $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($attributes = ['*'])
    {
        return $this->query()->get($this->selectAttributes($attributes));
    }

    /**
     * Search any input against the given attributes.
     *
     * @param  string                                   $input
     * @param  array                                    $compareAttributes
     * @param  array                                    $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search($input, $compareAttributes = ['*'], $attributes = ['*'])
    {
        $query = $this->query();

        $compareAttributes = $compareAttributes ?: ['*'];

        if ($compareAttributes == ['*']) {
            $compareAttributes = Schema::getColumnListing($this->model->getTable());
        }

        foreach ($compareAttributes as $column) {
            $query->orWhere($column, 'like', '%'.join('%', str_split($input)).'%');
        }

        return $query->get($this->selectAttributes($attributes));
    }
}
