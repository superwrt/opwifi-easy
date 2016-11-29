<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwSystem extends Model
{
    protected $table = 'ow_system';

    protected $guarded = ['id'];

    static public function getValue($name) {
    	if (is_array($name)) {
	    	$cfgs = [];
	    	foreach ($name as $m) {
	    		$it = OwSystem::where('name', $m)->first();
	    		$cfgs[$m] = $it?$it['value']:'';
	    	}
	    	return $cfgs;
    	} else {
	    	$it = self::where('name',$name)->first();
	    	if ($it) {
	    		return $it['value'];
	    	}
	    }
    	return null;
    }

    static public function getAllValue() {
    	$cfgs = [];
    	$all = self::all();
    	foreach ($all as $it) {
    		$cfgs[$it['name']] = $it['value'];
    	}
    	return $cfgs;
    }

    static public function saveValues(Array $cfgs) {
    	foreach ($cfgs as $k => $v) {
	    	$it = self::where('name',$k)->first();
	    	if ($it) {
                if($v != $it['value']) {
    	    		$it['value'] = $v;
    	    		$it->save();
                }
	    	} else {
	    		self::create(['name'=>$k, 'value'=>$v, 'module'=>'auto']);
	    	}
    	}
    }
}
