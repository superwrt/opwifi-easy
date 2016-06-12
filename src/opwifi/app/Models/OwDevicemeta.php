<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwDevicemeta extends Model
{
    protected $table = 'ow_devicemeta';

    protected $guarded = ['id', 'dev_id'];

	public function device()
    {
        return $this->belongsTo('App\Models\OwDevices', 'dev_id');
    }

	public function upgrade()
    {
        return $this->belongsTo('App\Models\OwDevFirmwares', 'op_upgrade_id');
    }

	public function config()
    {
        return $this->belongsTo('App\Models\OwDevConfigs', 'op_config_id');
    }


}
