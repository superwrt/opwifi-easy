<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwDevFirmwares extends Model
{
    protected $table = 'ow_dev_firmwares';

    protected $fillable = ['name', 'mnger_id'];

    public function devices()
    {
        return $this->hasMany('App\Models\OwDevicemeta', 'op_upgrade_id');
    }

    public function mnger()
    {
        return $this->belongsTo('App\Models\OwUsers', 'mnger_id');
    }

}
