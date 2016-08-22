<?php

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $guarded = [];   

    public function spy()
    {
      return $this->belongsTo('Spy');
    } 
}
