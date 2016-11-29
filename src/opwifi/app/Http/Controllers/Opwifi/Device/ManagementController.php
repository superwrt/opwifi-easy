<?php

namespace App\Http\Controllers\Opwifi\Device;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use Input;

use App\Models\OwDevices;
use App\Models\OwDevicemeta;
use App\Models\OwDevtags;
use App\Models\OwDevtagRelationships;

use App\Http\Helpers\Opwifi\SqlFakeScheduler;

class ManagementController extends OwCRUDController {

	protected $viewData = array(
	);

    protected $rootOwnModel = 'device';
    protected $indexOwnModel = 'mac';
    protected function newOwnModelRoot() {
    	return new OwDevices();
    }
    protected function newOwnModel() {
    	return OwDevicemeta::with('device')->with('config')->with('upgrade');
    }
    protected $indexOwnModelTag = 'dev_id';
    protected function newOwnModelTagRelationships() {
        return new OwDevtagRelationships();
    }

	public function getIndex(Request $request) {
		return view("opwifi.device.management", $this->viewData);
	}

	public function getGroups(Request $request) {
		$r = OwDevtags::groups('text');
		return response()->json($r);
	}

	public function postGroups(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		OwDevtags::modifyGroups($request->json()->all(), 'text');
		$r = OwDevtags::groups('text');
		return response()->json($r);
	}

	public function postTagRelationships(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		OwDevtagRelationships::bind($request->json()->all());
		return response()->json(['success'=>true]);
	}

	public function getTags(Request $request) {
		$mac = $request->input('mac');
		$id = $request->input('id');
		if (!$id) {
			if ($mac)
				$dev = OwDevices::where('mac', $mac)->first();
		} else {
			$meta = OwDevicemeta::where('id', $id)->first();
			if ($meta)
				$dev = $meta->device()->first();
		}
		if (!$dev) return;
		$tags = $dev->tags()->get();
		return response()->json($tags);
	}

	public function postRename(Request $request) {
		$id = $request->get('id');
		if (!$id) {
			$id = $request->get('pk');
			$name = $request->get('value');
		} else {
			$name = $request->get('name');
		}
		$meta = OwDevices::where('id', $id)->first();
		if ($meta) {
			$dev = $meta->device()->first();
			if ($dev) {
				$dev->update(['name'=>$name]);
				return response()->json(['success'=>true]);
			}
		}
		return response()->json(['success'=>false]);
	}

	public function postReboot(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		$devs = $request->json()->all();
		foreach ($devs as $dev) {
			if (!$dev['id']) continue;
			$d = OwDevices::where('id', $dev['id'])->first();
			if ($d->count()) {
				$m = $d->meta();
				if ($m->count()) {
					$m->update(['op_reboot'=>true]);
				}
			}
		}
		return response()->json(['success'=>true]);
	}

}