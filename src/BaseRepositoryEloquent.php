<?php

namespace SedpMis\BaseRepository;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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
        $relations = !is_array($relations) ? [$relations] : $relations;

        $this->eagerLoadRelations = array_merge($this->eagerLoadRelations, $relations);

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
     * Return the validation.
     *
     * @return \SedpMis\BaseRepository\ValidationInterface
     */
    public function validation()
    {
        return $this->validation ?: $this->validation = new Validation($this->model);
    }

    /**
     * Create and store a new model.
     *
     * @param  array  $attributes
     * @throws \InvalidArgumentException
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes)
    {
        /*
         * Check if is single associate array item.
         */
        if (!array_is_assoc($attributes)) {
            throw new InvalidArgumentException('Trying to pass multiple items in create() method. Please use createMany() instead.');
        }

        $model = $this->model->newInstance($attributes);

        $this->validation()->validate($model, $attributes);

        $this->beforeSaveModel($model);

        $model->save();

        return $model;
    }

    /**
     * Create and store multiple new models.
     *
     * @param  array  $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMany(array $items)
    {
        $models = collection();

        foreach ($items as $item) {
            $models[] = $this->create($item);
        }

        return $models;
    }

    /**
     * Update the model attributes in the storage.
     *
     * @param  int|null  $id
     * @param  array  $attributes
     * @throws \Exception  When id is not given
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $attributes)
    {
        $model = $this->model->findOrFail($id);

        $model->fill($attributes);

        $this->validation()->validate($model);

        $model->save();

        return $model;
    }

    /**
     * Update multiple models attributes in the storage.
     *
     * @param  array  $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function updateMany(array $items)
    {
        $ids    = array_pluck($items, $this->model->getKeyName());
        $models = $this->model->find($ids);

        foreach ($models as $model) {
            $attributes = array_first($items, function ($i, $attributes) use ($model) {
                if (!array_key_exists($model->getKeyName(), $attributes)) {
                    return false;
                }

                return $attributes[$model->getKeyName()] == $model->getKey();
            });

            $model->fill($attributes);

            $this->validation()->validate($model);

            $model->save();
        }

        return $models;
    }

    /**
     * Save the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function save($model)
    {
        if ($model instanceof Collection || is_array($model)) {
            throw new InvalidArgumentException('Parameter $model must be an instance of \Illuminate\Database\Eloquent\Model');
        }

        $this->validation()->validate($model);

        $this->beforeSaveModel($model);

        return $model->save();
    }

    /**
     * Save multiple models.
     *
     * @param  array|\Illuminate\Database\Eloquent\Collection  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function saveMany($models)
    {
        // Convert to collection if array
        $models = is_array($models) ? collection($models) : $models;

        foreach ($models as &$model) {
            $this->save($model);
        }

        return $models;
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
            // Support for single key-value pair filter.
            $filter = !is_array($filter) ? [$filter] : $filter;

            foreach ($filter as $operator => $values) {
                $values = is_array($values) ? $values : [$values];

                $operator = is_numeric($operator) ? 'equals' : $operator;

                $operator = $this->replaceOperatorAlias($operator);

                if ($operator == 'equals') {
                    $query->whereIn($key, $values);
                } elseif ($operator == 'not_equals') {
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
     * Return the operator of an alias.
     *
     * @param  string $alias
     * @return string
     */
    protected function replaceOperatorAlias($alias)
    {
        $aliases = [
            'e'  => 'equals',
            'ne' => 'not_equals',
            'n'  => 'null',
            'nn' => 'not_null',
        ];

        return array_key_exists($alias, $aliases) ? $aliases[$alias] : $alias;
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
        $this->hasRelations = array_merge($this->hasRelations, $hasRelations);

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
        $this->filters = array_merge($this->filters, $filters);

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
        $this->sort = array_merge($this->sort, $sort);

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
        if ($limit) {
            $this->limit = $limit;
        }

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
        if ($offset) {
            $this->offset = $offset;
        }

        return $this;
    }

    /**
     * Apply query params to set query when fetching records.
     *
     * @param  \Illuminate\Http\Request $request
     * @return $this
     */
    public function applyQueryParams($request)
    {
        $pagelo = new PageLimitOffset($request->get('per_page', 15), $request->get('page'));

        $this->with($request->get('relations', []))
            ->attributes($request->get('attributes', ['*']))
            ->filters($request->get('filters', []))
            ->sort($request->get('sort', []))
            ->limit($pagelo->limit())
            ->offset($pagelo->offset());

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

        // filters
        $this->queryFilters($query);

        // sort
        $this->querySort($query);

        // limit and offset
        $this->queryLimitOffset($query);

        return $query;
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
     * Return the final attributes to be selected.
     *
     * @param  array $attributes
     * @return array
     */
    protected function finalAttributes($attributes = ['*'])
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
        return $this->query()->get($this->finalAttributes($attributes));
    }

    /**
     * Return a collection of models by paginated approach.
     *
     * @param  int                                      $page
     * @param  int                                      $perPage
     * @param  array                                    $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function paginate($page = 1, $perPage = 15, $attributes = ['*'])
    {
        $pagelo = new PageLimitOffset($perPage, $page);

        return $this->limit($pagelo->limit())
            ->offset($pagelo->offset())
            ->get($attributes);
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

        $sqls = [];

        foreach ($compareAttributes as $column) {
            $sqls[] = "{$column} like '%".join('%', str_split($input))."%'";
        }

        $query->whereRaw('('.join(' OR ', $sqls).')');

        return $query->get($this->finalAttributes($attributes));
    }
}
