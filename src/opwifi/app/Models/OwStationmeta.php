<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStationmeta extends Model
{
    protected $table = 'ow_stationmeta';

	public function station()
    {
        return $this->belongsTo('App\Models\OwStations', 'sta_id');
    }
}
