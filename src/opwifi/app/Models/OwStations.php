<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStations extends Model
{
    protected $table = 'ow_stations';

    protected $guarded = ['id'];

    public function meta()
    {
        return $this->hasOne('App\Models\OwStationmeta', 'sta_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\OwStatags', 'ow_statag_relationships', 'sta_id', 'tag_id');
    }
}
