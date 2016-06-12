<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwWebportalTokens extends Model
{
    protected $table = 'ow_webportal_tokens';

    protected $guarded = ['id'];

	public function user()
	{
		return $this->belongsTo('App\Models\OwWebportalUsers', 'user_id');
	}
}
