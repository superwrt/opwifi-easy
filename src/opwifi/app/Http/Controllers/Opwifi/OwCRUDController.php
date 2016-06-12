<?php

namespace App\Http\Controllers\Opwifi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Input;

use App\Http\Helpers\Opwifi\SqlFakeScheduler;

abstract class OwCRUDController extends Controller
{
	public function __construct() {
		SqlFakeScheduler::update();
	}

	public function getSelect(Request $request) {
		if (!$request->wantsJson()) {
			return;
		}
		$limit = $request->input('limit');
		$offset = $request->input('offset');
		$order = $request->input('order');
		$sort = $request->input('sort');
		$tag_ids = $request->input('tag_ids');

		if ($offset) {
			$currentPage = floor($offset/$limit) + 1;
			Paginator::currentPageResolver(function() use ($currentPage) {
				return $currentPage;
			});
		}
		$query = $this->newOwnModel();
		if ($tag_ids && isset($this->indexOwnModelTag)) {
			$ids = array();
			$rales = $this->newOwnModelTagRelationships()->whereIn('tag_id', explode(',',$tag_ids))->get();
			foreach ($rales as $ra) {
				$ids[] = $ra->dev_id;
			}
			$ids = array_unique($ids);
			$query = $query->whereIn($this->indexOwnModelTag, $ids);
		}
		if ($sort) {
			$query = $query->orderBy($sort, $order);
		}
		if ($limit) {
			$cfgs = $query->paginate($limit);
		} else {
			$cfgs = $query->get();
		}
		return response()->json($cfgs);
	}

	public function postAdd(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if ($this->indexOwnModel && !$cfg[$this->indexOwnModel]) continue;
			if (isset($this->hasOwnModelDefault) && $this->hasOwnModelDefault) {
				if ($this->newOwnModel()->where('id', 1)->count() == 0) {
					$cfg['id'] = 1;
				}
			}
			$this->newOwnModel()->create($cfg);
		}
		return response()->json(['success'=>true]);
	}

	public function postAddRoot(Request $request) {
		$root = $this->rootOwnModel;
		if (!$request->isJson() || !$root) {
			return ;
		}
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if ($this->indexOwnModel && !$cfg[$this->indexOwnModel]) continue;
			if (isset($this->hasOwnModelDefault) && $this->hasOwnModelDefault) {
				if ($this->newOwnModel()->where('id', 1)->count() == 0) {
					$cfg['id'] = 1;
				}
			}
			$this->newOwnModelRoot()->create($cfg);
		}
		return response()->json(['success'=>true]);
	}

	public function postDelete(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if (!$cfg['id']) continue;
			$this->newOwnModel()->where('id', $cfg['id'])->delete();
		}
		return response()->json(['success'=>true]);
	}

	public function postDeleteRoot(Request $request) {
		$root = $this->rootOwnModel;
		if (!$request->isJson() || !$root) {
			return ;
		}
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if (!$cfg['id']) continue;
			$meta = $this->newOwnModel()->where('id', $cfg['id'])->first();
			if ($meta)
				$meta->$root()->delete();
		}
		return response()->json(['success'=>true]);
	}

	public function postUpdate(Request $request) {
		$id = $request->get('id');
		if (!$id) {
			$id = $request->get('pk');
			if ($id) {
				$names = explode('.', $request->get('name'));
				$input = ['id'=>$id];
				$in = &$input;
				foreach ($names as $name) {
					$in[$name] = [];
					$last = &$in;
					$in = &$in[$name];
				}
				$last[$name] = $request->get('value');
				$cfgs = [$input];
			} else {
				$cfgs = $request->json()->all();
			}
		} else {
			$cfgs = [$request->all()];
		}

		foreach ($cfgs as $cfg) {
			if (!$cfg['id']) continue;
			$it = $this->newOwnModel()->where('id', $cfg['id'])->first();
			if (!$it) return response()->json(['success'=>false]);
			$sub = [];
			foreach ($cfg as $k=>&$v) {
				if (is_array($v)) {
					$sub[$k] = $v;
					unset($cfg[$k]);
				}
			}
			unset($cfg['id']);
			if (count($cfg)) {
				$it->update($cfg);
			}
			if (count($sub)) {
				foreach ($sub as $k=>$v) {
					$sit = $it->$k()->first();
					if ($sit) {
						$sit->update($v);
					} else {
						$it->$k()->create($v);
					}
				}
			}
		}
		return response()->json(['success'=>true]);
	}

}
