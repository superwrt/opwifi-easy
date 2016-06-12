<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwWebportalUsers extends Model
{
    protected $table = 'ow_webportal_users';

    protected $hidden = ['shadow'];

    protected $guarded = ['id', 'shadow'];

    public function setPasswordAttribute($value)
    {
    	if ($value) $this->attributes['shadow'] = md5($value);
    	unset($this->attributes['password']);
    }
}
