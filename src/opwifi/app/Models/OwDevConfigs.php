<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwDevConfigs extends Model
{
    protected $table = 'ow_dev_configs';

    protected $fillable = ['name', 'comment', 'config'];

    public function devices()
    {
        return $this->hasMany('App\Models\OwDevicemeta', 'op_config_id');
    }

    public function setConfigAttribute($value)
    {
        $this->attributes['config'] = $value;
        $this->attributes['md5'] = md5($value);
    }

}
