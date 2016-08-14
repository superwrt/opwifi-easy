<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwWebportalStationStatus extends Model
{
    protected $table = 'ow_webportal_station_status';

    protected $guarded = ['id'];

	public function user()
    {
        return $this->belongsTo('App\Models\OwWebportalUsers', 'user_id');
    }
   	public function authdev()
    {
        return $this->belongsTo('App\Models\OwWebportalDevices', 'authdev_id');
    }
}
