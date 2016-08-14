<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Opwifi\OwCRUDController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwWebportalDevices;
use App\Models\OwDevtagRelationships;

class DeviceStatusController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct() {

    }

    protected $indexOwnModelTag = 'dev_id';
    protected function newOwnModelTagRelationships() {
        return new OwDevtagRelationships();
    }
    protected function newOwnModel() {
    	return OwWebportalDevices::with('device');
    }


	public function getIndex() {
		return view("opwifi.webportal.device_status", $this->viewData);
	}

}