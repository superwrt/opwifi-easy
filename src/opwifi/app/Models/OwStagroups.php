<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStagroups extends Model
{
    protected $table = 'ow_stagroups';

    public function staRss()
    {
    	return $this->hasMany('App\Models\OwStatagRelationships', 'group_id');
	}
}
