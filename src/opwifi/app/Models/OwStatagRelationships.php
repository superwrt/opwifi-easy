<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwStatagRelationships extends Model
{
    protected $table = 'ow_statag_relationships';

    public function tag()
    {
        return $this->belongsTo('App\Models\OwStatags', 'tag_id');
    }

    public function sta()
    {
        return $this->belongsTo('App\Models\OwStations', 'sta_id');
    }

    static public function bind($ops)
    {
    	$done = 0;
		/*
		 * [{tag:'tag_name', add:["xx:xx:xx:xx:xx:xx","xx:xx:xx:xx:xx:xx"], remove:["xx:xx:xx:xx:xx:xx"]}]
		 */
		foreach ($ops as $op) {
			$tag_id = null;
			if (isset($op['id'])) {
				$tag_id = $op['id'];
			} else if (isset($op['name'])) {
				$tag = OwStatags::where('name', $op['name'])->first();
				if ($tag) {
					$tag_id = $tag->id;
				}
			} else {
				continue;
			}

			if (!$tag_id)
				continue;
			if (isset($op['add'])) {
				foreach ($op['add'] as $mac) {
					$sta_id = null;
					if (strstr($mac, ':')) {
						$sta = OwStations::where('mac', $mac)->first();
						if ($sta) {
							$sta_id = $sta->id;
						}
					} else {
						$sta_id = $mac;
					}
					$data = ['tag_id' => $tag_id, 'sta_id' => $sta_id];
					if (self::where($data)->count() > 0) {
						//Has one, skip.
					} else if ($sta_id && self::insertGetId($data)) {
						$done++;
					}
				}
			}
			if (isset($op['remove'])) {
				foreach ($op['remove'] as $mac) {
					$sta_id = null;
					if (strstr($mac, ':')) {
						$sta = OwStations::where('mac', $mac)->first();
						if ($sta) {
							$sta_id = $sta->id;
						}
					} else {
						$sta_id = $mac;
					}
					if ($sta_id && self::where(['tag_id' => $tag_id, 'sta_id' => $sta_id])->delete()) {
						$done++;
					}
				}
			}
		}
		return $done;
    }
}
