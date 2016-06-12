<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStatagRelationships extends Model
{
    protected $table = 'ow_statag_relationships';

    public function tag()
    {
        return $this->belongsTo('App\Models\OwStatags', 'tag_id');
    }

    public function sta()
    {
        return $this->belongsTo('App\Models\OwStations', 'sta_id');
    }
}
