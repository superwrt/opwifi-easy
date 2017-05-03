<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwWebportalStationStatus;

class StationStatusController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct() {

    }

    protected $limitUserId = 'mnger_id';
    protected function newOwnModel() {
    	return new OwWebportalStationStatus();
    }

	public function getIndex() {
		return view("opwifi.webportal.station_status", $this->viewData);
	}

    private function setAuth($request, $auth) {
        if (!$request->isJson()) {
            return ;
        }
        $cfgs = $request->json()->all();
        foreach ($cfgs as $cfg) {
            if (!$cfg['id']) continue;
            $this->newOwnModel()->where('id', $cfg['id'])->update(['authed'=>$auth]);
        }
        return response()->json(['success'=>true]);
    }

    public function postKick(Request $request) {
        return $this->setAuth($request, false);
    }

    public function postAuth(Request $request) {
        return $this->setAuth($request, true);
    }

}