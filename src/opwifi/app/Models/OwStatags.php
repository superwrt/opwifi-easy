<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStatags extends Model
{
    protected $table = 'ow_statags';

    public function staRss()
    {
    	return $this->hasMany('App\Models\OwStatagRelationchips', 'tag_id');
	}

    public function group()
    {
        return $this->belongsTo('App\Models\OwDevgroups', 'group_id');
    }
}
