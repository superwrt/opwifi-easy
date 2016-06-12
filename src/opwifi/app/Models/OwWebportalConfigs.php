<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwWebportalConfigs extends Model
{
    protected $table = 'ow_webportal_configs';

    protected $guarded = [];

	public function devs()
    {
    	return $this->hasMany('App\Models\OwWebportalDevices', 'cfg_id');
	}
}
