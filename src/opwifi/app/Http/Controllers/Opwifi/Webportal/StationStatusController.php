<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwWebportalDevices;

class StationStatusController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {

    }

    protected function newOwnModel() {
    	return OwWebportalStationStatus::with('device');
    }

	public function getIndex() {
		return view("opwifi.webportal.station_status", $this->viewData);
	}

}