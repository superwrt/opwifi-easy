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
}
