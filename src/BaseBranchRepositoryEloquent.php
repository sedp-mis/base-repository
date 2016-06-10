<?php

namespace SedpMis\BaseRepository;

abstract class BaseBranchRepositoryEloquent extends BaseRepositoryEloquent implements RepositoryInterface
{
    /**
     * Branch id to be prefix when creating new item to storage.
     * @var int
     */
    protected $branchId;

    /**
     * Set the branch id to be prefixed when creating an id.
     *
     * @param int $branchId
     * @return $this
     */
    public function setBranchId($branchId)
    {
        $this->branchId = $branchId;

        return $this;
    }

    /**
     * Alias of setBranchId() method.
     *
     * @param int $branchId
     * @return $this
     */
    public function setBranch($branchId)
    {
        return $this->setBranchId($branchId);
    }

    /**
     * Return the branch_id for branch-inserts.
     *
     * @return int
     */
    public function branchId()
    {
        return $this->branchId ?: get_branch_session();
    }

    /**
     * Override. Manipulate model before final save, setting branchId which is required for branch-inserts.
     *
     * @param  \BaseModel $model
     * @return \BaseModel
     */
    protected function beforeSaveModel($model)
    {
        return $model instanceof \BaseBranchModel ? $model->setBranchId($this->branchId()) : $model;
    }
}
