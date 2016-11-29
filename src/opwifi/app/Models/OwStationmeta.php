<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStationmeta extends Model
{
    protected $table = 'ow_stationmeta';

    protected $guarded = ['id', 'dev_id'];

	public function station()
    {
        return $this->belongsTo('App\Models\OwStations', 'sta_id');
    }
}
