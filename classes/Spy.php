<?php

use Illuminate\Database\Eloquent\Model;

class Spy extends Model
{
    protected $guarded = [];    

    public function target()
    {
      return $this->hasOne('Target', 'spy_id');
    }
}
