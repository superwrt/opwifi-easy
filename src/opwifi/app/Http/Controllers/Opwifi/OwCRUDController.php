<?php

namespace App\Http\Controllers\Opwifi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Input, Auth;

use App\Http\Helpers\Opwifi\SqlFakeScheduler;

abstract class OwCRUDController extends Controller
{
	public function __construct() {
		SqlFakeScheduler::update();
	}

	private function getLimitUserId() {
		if (isset($this->limitUserId) &&
				Auth::User()['right'] != 'admin')
			return Auth::User()['id'];
		return 0;
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

		$userId = $this->getLimitUserId();
		if ($userId > 0)
			$query = $query->where($this->limitUserId, $userId);

		if ($tag_ids && isset($this->indexOwnModelTag)) {
			$ids = array();
			$rales = $this->newOwnModelTagRelationships()->whereIn('tag_id', explode(',',$tag_ids))->get();
			foreach ($rales as $ra) {
				$ids[] = $ra[$this->indexOwnModelTag];
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
		$userId = $this->getLimitUserId();
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if ($this->indexOwnModel && !isset($cfg[$this->indexOwnModel])) continue;
			if (isset($this->hasOwnModelDefault) && $this->hasOwnModelDefault) {
				if ($this->newOwnModel()->where('id', 1)->count() == 0) {
					$cfg['id'] = 1;
				}
			}
			if ($userId > 0) {
				$cfg[$this->limitUserId] = $userId;
				/* Try get first, if empty in limitUserId, update it */
				$old = $this->newOwnModel()->where($this->indexOwnModel, $cfg[$this->indexOwnModel])->first();
				if ($old && $old[$this->limitUserId] == null) {
					$old->update($cfg);
					continue;
				}
			}
			if (method_exists($this, 'createOwnModel')) {
				$this->createOwnModel($cfg);
			} else {
				$this->newOwnModel()->create($cfg);
			}
		}
		return response()->json(['success'=>true]);
	}

	public function postAddRoot(Request $request) {
		$root = $this->rootOwnModel;
		if (!$request->isJson() || !$root) {
			return ;
		}
		$userId = $this->getLimitUserId();
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if ($this->indexOwnModel && !isset($cfg[$this->indexOwnModel])) continue;
			if (isset($this->hasOwnModelDefault) && $this->hasOwnModelDefault) {
				if ($this->newOwnModelRoot()->where('id', 1)->count() == 0) {
					$cfg['id'] = 1;
				}
			}
			if ($userId > 0) {
				$old = $this->newOwnModelRoot()->where($this->indexOwnModel, $cfg[$this->indexOwnModel])->first();
				if ($old && $old[$this->limitUserId] == null) {
					$own = $this->getOwnModelByRoot($old);
					$old->update($cfg);
					$own->update([$this->limitUserId => $userId]);
					continue;
				}
			}
			if (method_exists($this, 'createOwnModelRoot')) {
				$this->createOwnModelRoot($cfg);
			} else {
				$this->newOwnModelRoot()->create($cfg);
			}
		}
		return response()->json(['success'=>true]);
	}

	public function postDelete(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		$userId = $this->getLimitUserId();
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if (!$cfg['id']) continue;
			if ($userId > 0 && $userId != $cfg[$this->limitUserId])
				return response()->json(['success'=>false]);
			$this->newOwnModel()->where('id', $cfg['id'])->delete();
		}
		return response()->json(['success'=>true]);
	}

	public function postDeleteRoot(Request $request) {
		$root = $this->rootOwnModel;
		if (!$request->isJson() || !$root) {
			return ;
		}
		$userId = $this->getLimitUserId();
		$cfgs = $request->json()->all();
		foreach ($cfgs as $cfg) {
			if (!$cfg['id']) continue;
			$meta = $this->newOwnModel()->where('id', $cfg['id'])->first();
			if ($meta) {
				if ($userId > 0 && $userId != $meta[$this->limitUserId])
					return response()->json(['success'=>false]);
				$meta->$root()->delete();
			}
		}
		return response()->json(['success'=>true]);
	}

	public function postUpdate(Request $request) {
		$userId = $this->getLimitUserId();

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
			if ($userId > 0 && (
					(isset($cfg[$this->limitUserId]) &&
					$userId != $cfg[$this->limitUserId]) ||
					($it[$this->limitUserId] != null &&
					$userId != $it[$this->limitUserId])
					)) {
				return response()->json(['success'=>false]);
			}
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
