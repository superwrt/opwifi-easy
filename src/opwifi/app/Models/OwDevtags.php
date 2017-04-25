<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB, PDO;

class OwDevtags extends Model
{
    protected $table = 'ow_devtags';

    public function devRss()
    {
    	return $this->hasMany('App\Models\OwDevtagRelationchips', 'tag_id');
	}

    public function devices()
    {
        return $this->belongsToMany('App\Models\OwDevices', 'ow_devtag_relationships', 'tag_id', 'dev_id');
    }

    static public function groups($nameAlias = null)
    {
    	DB::setFetchMode(PDO::FETCH_ASSOC);

		$groups = DB::table('ow_devgroups')->get();
		$tags = OwDevtags::get()->toArray();

		DB::setFetchMode(PDO::FETCH_CLASS);

    	$tree = array();
    	foreach($groups as &$group) {
    		$group['type'] = 'group';
    		if ($nameAlias) {
    			$group[$nameAlias] = $group['name'];
    		}

    		if ($group['parent_id']) {
    			foreach($groups as &$p) {
    				if ($p != $group && $group['parent_id'] == $p['id']) {
    					if (!isset($p['children'])) {
    						$p['children'] = array();
    					}
    					$p['children'][] = &$group;
    					continue 2;
    				}
    			}
    		}
			$tree[] = &$group;
    	}
    	foreach($tags as &$tag) {
    		$tag['type'] = 'tag';
    		if ($nameAlias) {
    			$tag[$nameAlias] = $tag['name'];
    		}
    		foreach($groups as &$p) {
				if ($tag['group_id'] == $p['id']) {
					if (!isset($p['children'])) {
						$p['children'] = array();
					}
					$p['children'][] = &$tag;
					continue 2;
				}
    		}
    		$tree[] = &$tag;
    	}

    	foreach($groups as &$group) {
    		$group['gid'] = $group['id'];
    		$group['id'] = 'group_'.$group['id'];
    	}
    	foreach($tags as &$tag) {
    		$tag['tid'] = $tag['id'];
    		$tag['id'] = 'tag_'.$tag['id'];
    	}

    	return $tree;
    }

    static private function treeApply($n, $o, $nameAlias, $idName, $table, &$idMap)
    {
    	foreach($n as &$v) {
    		foreach($v as $k => $e) {
    			if (substr($k,1) == '_') {
    				unset($v[$k]);
    			}
    		}
    	}

	    foreach($n as &$v) {
	    	foreach ($o as &$ov) {
	    		if ($v['id'] == $ov['id']) {
	    			if ($v[$nameAlias] != $ov['name'] ||
	    					$v[$idName] != $ov[$idName]) {
	    				$v['_modify'] = true;
	    			}
	    			$ov['_skip'] = true;
	    			continue 2;
	    		}
	    	}
	    	
	    	$v['_new'] = true;
	    }

	    foreach ($o as &$ov) {
	    	if (isset($ov['_skip'])) {
	    		continue;
	    	}
	    	DB::table($table)->where('id', $ov['id'])->delete();
	    }

	    foreach ($n as &$v) {
	    	if (is_string($v[$idName]) && substr($v[$idName],0,1) == 'n') {
	    		$v[$idName] = $idMap[$v[$idName]];
	    	}
	    	if (isset($v['_modify'])) {
	    		DB::table($table)->where('id', $v['id'])->update(
	    			['name'=>$v[$nameAlias], $idName=>$v[$idName]]);
	    	} else if (isset($v['_new'])) {
	    		$id = DB::table($table)->insertGetId(
	    			['name'=>$v[$nameAlias], $idName=>$v[$idName]]);
	    		$idMap[$v['id']] = $id;
	    	}
	    }
    }

    static private function flat_item(&$groups, &$tags, &$newNum, &$p, $pId = 0)
    {
    	foreach($p as &$i) {
    		if (!isset($i['type'])) {
    			return false;
    		}
    		if (substr($i['id'], 0, 4) == "tag_" || substr($i['id'], 0, 6) == "group_" ) {
    			$i['id'] = substr(strstr($i['id'],'_'), 1);
    		} else {
    			$i['id'] = 'n'.$newNum++;
    			$i['new'] = true;
    		}
    		switch($i['type']) {
    		case 'group':
    			$i['parent_id'] = $pId;
    			$groups[] = $i;
    			/* new id must before parent_id */
    			self::flat_item($groups, $tags, $newNum, $i['children'], $i['id']);
    			break;
    		case 'tag':
    			$i['group_id'] = $pId;
    			$tags[] = $i;
    			break;
    		default:
    			return false;
    		}
    	}
    	return true;
	}

    static public function modifyGroups($r, $nameAlias = null)
    {
    	DB::setFetchMode(PDO::FETCH_ASSOC);

		$oldGroups = DB::table('ow_devgroups')->get();
		$oldTags = OwDevtags::get()->toArray();

		DB::setFetchMode(PDO::FETCH_CLASS);

    	$groups = array();
    	$tags = array();
    	$newNum = 0;
    	$idMap = array();

	    if (!self::flat_item($groups, $tags, $newNum, $r))
	    	return false;
	    self::treeApply($groups, $oldGroups, $nameAlias, 'parent_id', 'ow_devgroups', $idMap);
	    self::treeApply($tags, $oldTags, $nameAlias, 'group_id', 'ow_devtags', $idMap);

	    return true;
    }

}
