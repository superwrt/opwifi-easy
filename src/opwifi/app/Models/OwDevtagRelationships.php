<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwDevtagRelationships extends Model
{
    protected $table = 'ow_devtag_relationships';

    public function tag()
    {
        return $this->belongsTo('App\Models\OwDevtags', 'tag_id');
    }

    public function dev()
    {
        return $this->belongsTo('App\Models\OwDevices', 'dev_id');
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
				$tag = OwDevtags::where('name', $op['name'])->first();
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
					$dev_id = null;
					if (strstr($mac, ':')) {
						$dev = OwDevices::where('mac', $mac)->first();
						if ($dev) {
							$dev_id = $dev->id;
						}
					} else {
						$dev_id = $mac;
					}
					$data = ['tag_id' => $tag_id, 'dev_id' => $dev_id];
					if (self::where($data)->count() > 0) {
						//Has one, skip.
					} else if ($dev_id && self::insertGetId($data)) {
						$done++;
					}
				}
			}
			if (isset($op['remove'])) {
				foreach ($op['remove'] as $mac) {
					$dev_id = null;
					if (strstr($mac, ':')) {
						$dev = OwDevices::where('mac', $mac)->first();
						if ($dev) {
							$dev_id = $dev->id;
						}
					} else {
						$dev_id = $mac;
					}
					if ($dev_id && self::where(['tag_id' => $tag_id, 'dev_id' => $dev_id])->delete()) {
						$done++;
					}
				}
			}
		}
		return $done;
    }
}
