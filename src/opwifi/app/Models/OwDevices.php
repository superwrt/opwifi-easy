<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwDevices extends Model
{
    protected $table = 'ow_devices';

    protected $guarded = ['id'];

    public function meta()
    {
        return $this->hasOne('App\Models\OwDevicemeta', 'dev_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\OwDevtags', 'ow_devtag_relationships', 'dev_id', 'tag_id');
    }

    public function webportal()
    {
    	return $this->hasOne('App\Models\OwWebportalDevices', 'dev_id');
    }

    public function setMacAttribute($value)
    {
        $this->attributes['mac'] = strtolower($value);
    }
}
