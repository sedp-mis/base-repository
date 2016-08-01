<?php

namespace SedpMis\BaseRepository;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class BaseRepositoryEloquent implements RepositoryInterface
{
    /**
     * Eloquent model.
     *
     * @var \BaseModel
     */
    protected $model;

    /**
     * Contain model's relation to be eagerload.
     *
     * @var array
     */
    protected $eagerLoadRelations = [];

    /**
     * Query to fetch model.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

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
     * Validation rules before saving model.
     *
     * @var array
     */
    protected $validationRules = [];

    /**
     * Hold the validation instance.
     *
     * @var \Abstractions\Repository\ValidationInterface
     */
    protected $validation;

    /**
     * Return the validation rules.
     *
     * @return array
     */
    public function validationRules()
    {
        return $this->validationRules;
    }

    /**
     * Return the validation.
     *
     * @return \Abstractions\Repository\ValidationInterfaced
     */
    public function validation()
    {
        return $this->validation ?: $this->validation = new Validation($this->validationRules());
    }

    /**
     * Eagerload relations.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\BaseModel
     */
    protected function eagerLoadRelations()
    {
        $query = $this->query ?: $this->model;

        return $query->with($this->eagerLoadRelations);
    }

    /**
     * Set eagerload relations.
     *
     * @param  array $relations
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
     * @return \Illuminate\Support\Collection
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
     * @return \Illuminate\Support\Collection|\BaseModel
     */
    public function find($id, $attributes = array('*'))
    {
        return $this->eagerLoadRelations()->find($id, $attributes);
    }

    /**
     * Get the models for the given attributes.
     *
     * @param  array  $attributes
     * @return \BaseModel|null
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
     * @return \Illuminate\Support\Collection|\BaseModel
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
     * @return \Illuminate\Support\Collection|\BaseModel
     */
    public function findOrFail($id, $attributes = array('*'))
    {
        return $this->eagerLoadRelations()->findOrFail($id, $attributes);
    }

    /**
     * Get the first model or the first model for the given attributes.
     *
     * @param  array  $attributes
     * @return \BaseModel|null
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
     * @return \BaseModel
     */
    public function firstOrCreate(array $attributes)
    {
        return $this->eagerLoadRelations()->firstOrCreate($attributes);
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @return \BaseModel
     */
    public function firstOrNew(array $attributes)
    {
        return $this->eagerLoadRelations()->firstOrNew($attributes);
    }

    /**
     * Make a new instance of the model from the attributes.
     *
     * @param  array  $attributes
     * @throws \ModelNotFoundException When model not found by the given id
     * @return \BaseModel
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
     * Save the model or data, array or collection of the model.
     *
     * @param  array|\BaseModel|\Illuminate\Database\Eloquent\Collection $model
     * @return \BaseModel|\Illuminate\Database\Eloquent\Collection
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
                ($first = head($model)) instanceof EloquentModel ||
                array_is_assoc($first)
            )
        ) {
            return $this->saveCollection($model);
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
    public function saveCollection($models)
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
     * @param  \BaseModel $model
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
     * @param  \BaseModel $model
     * @return \BaseModel
     */
    protected function beforeSaveModel($model)
    {
        return $model;
    }

    /**
     * Create and save the model.
     *
     * @param  array  $attributes
     * @return \BaseModel|\Illuminate\Support\Collection
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

        return $this->save($attributes);
    }

    /**
     * Update the model attributes.
     *
     * @param  array  $attributes
     * @param  int|null $id
     * @throws \Exception When id is not given
     * @throws \ModelNotFoundException
     * @return \BaseModel|\Illuminate\Support\Collection
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
     * @param  mixed $model
     * @return bool|int      Boolean when model is deleted or the number of models deleted.
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
     * @param  string  $relation
     * @param  string  $operator
     * @param  int     $count
     * @return $this
     */
    public function has($relation, $operator = '>=', $count = 1)
    {
        $query = $this->query ?: $this->model;

        $this->query = $query->has($relation, $operator, $count);

        return $this;
    }



    // New methods

    /**
     * Fetching data from Eloquent with pagination, filtering, sorting and limit/offset
     *
     * @param array $attributes
     * @param array $fiters
     * @param array $sort
     * @param int|null $limit
     * @param int|0 $offset
     * @return array
     */
    public function fetch($attributes = ['*'], $filters = [], $sort = [], $limit = null, $offset = 0)
    {
        $query = $this->eagerLoadRelations();
        
        //filters
        if (!empty($filters)) {
            foreach ($filters as $key => $filter) {
                foreach ($filter as $operator => $values) {
                    $values = is_array($values) ? $values : [$values];

                    if ($operator == "=") {
                        $query->whereIn($key, $values);
                    } else if ($operator == "!=") {
                        $query->whereNotIn($key, $values);
                    } else {
                        $query->where($key, $operator, head($values));
                    }
                }
            }
        }

        //sort
        if (!empty($sort)) {
            // sort models 
            foreach ($sort as $attribute => $order) {
                $query->orderBy($attribute, $order);
            }
        }

        //limit and offset
        if (!empty($limit)){
            $query->take($limit)->offset($offset);
        }

        //attributes
        $attributes = empty($attributes) ? ['*'] : $attributes;
        return $query->get($attributes);
    }

    /**
    * Return a collection of models base from the attribute filters and by paginated approach.
    *
    * @param array $attributes
    * @param array $fiters
    * @param array $sort
    * @param int|null $perPage
    * @param int|1 $page
    * @return array
    */
    public function paginate($attributes = ['*'], $filters = [], $sort = [], $perPage = null, $page = 1)
    {
        return $this->fetch($attributes, $filters, $sort, $perPage, ($page - 1) * $perPage);
    }

    /**
    * Return a collection of models base from search comparison.
    *
    * @param string $comparison
    * @param array $attributes
    * @param array $fiters
    * @param array $sort
    * @param int|null $perPage
    * @param int|1 $page
    * @return array
     */
    public function search($comparison, $attributes = ['*'], $filters = [], $sort = [], $limit = null, $offset = 0)
    {  
        $fetchResult = $this->fetch($attributes, $filters, $sort, $limit, $offset);
        $searchReturn = null;
        if (!empty($comparison)) {
            foreach ($fetchResult as $result) {
               $found = array_search($comparison, $result->toArray());
               if ($found) {
                    $searchReturn = $found;
                    break;
               }
            }
            return $searchReturn;
        }
    }

    /**
    * Return a collection of models base from search comparison using paginate approach.
    *
    * @param string $comparison
    * @param array $attributes
    * @param array $fiters
    * @param array $sort
    * @param int|null $perPage
    * @param int|1 $page
    * @return array
    */
    public function searchPaginate($comparison, $attributes = ['*'], $filters = [], $sort = [], $perPage = null, $page = 1)
    {
        $fetchResult = $this->paginate($attributes, $filters, $sort, $perPage, $page);
        $searchPaginateReturn = null;
        foreach ($fetchResult as $result) {
            $found = array_search($comparison, $result->toArray());
            if ($found) {
                $searchPaginateReturn = $found;
                break;
            }
        }
        return $searchPaginateReturn;
    }
}
