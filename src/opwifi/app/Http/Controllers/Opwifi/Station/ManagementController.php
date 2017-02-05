<?php

namespace App\Http\Controllers\Opwifi\Station;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use Input;

use App\Models\OwStations;
use App\Models\OwStationmeta;
use App\Models\OwStatags;
use App\Models\OwStatagRelationships;

use App\Http\Helpers\Opwifi\SqlFakeScheduler;

class ManagementController extends OwCRUDController {

	protected $viewData = array(
	);

    protected $rootOwnModel = 'station';
    protected $indexOwnModel = 'mac';
    protected function newOwnModelRoot() {
    	return new OwStations();
    }
    protected function newOwnModel() {
    	return OwStationmeta::with('station');
    }
    protected function createOwnModelRoot($cfg) {
    	$sta = OwStations::create($cfg);
    	$sta->meta()->firstOrCreate([]);
    }
    protected $indexOwnModelTag = 'sta_id';
    protected function newOwnModelTagRelationships() {
        return new OwStatagRelationships();
    }

	public function getIndex(Request $request) {
		return view("opwifi.station.management", $this->viewData);
	}

	public function getGroups(Request $request) {
		$r = OwStatags::groups('text');
		return response()->json($r);
	}

	public function postGroups(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		OwStatags::modifyGroups($request->json()->all(), 'text');
		$r = OwStatags::groups('text');
		return response()->json($r);
	}

	public function postTagRelationships(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		OwStatagRelationships::bind($request->json()->all());
		return response()->json(['success'=>true]);
	}

	public function getTags(Request $request) {
		$mac = $request->input('mac');
		$id = $request->input('id');
		if (!$id) {
			if ($mac)
				$sta = OwStations::where('mac', $mac)->first();
		} else {
			$meta = OwStationmeta::where('id', $id)->first();
			if ($meta)
				$sta = $meta->station()->first();
		}
		if (!$sta) return;
		$tags = $sta->tags()->get();
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
		$meta = OwStations::where('id', $id)->first();
		if ($meta) {
			$sta = $meta->station()->first();
			if ($sta) {
				$sta->update(['name'=>$name]);
				return response()->json(['success'=>true]);
			}
		}
		return response()->json(['success'=>false]);
	}

}