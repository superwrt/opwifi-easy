<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwWebportalDevices extends Model
{
    protected $table = 'ow_webportal_devices';

    protected $guarded = ['id'];

    public function device()
    {
        return $this->belongsTo('App\Models\OwDevices', 'dev_id');
    }

    public function config()
    {
        return $this->belongsTo('App\Models\OwWebportalConfigs', 'config_id');
    }

    public function mnger()
    {
        return $this->belongsTo('App\Models\OwUsers', 'mnger_id');
    }
}
