<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwWebportalDevices;
use App\Models\OwDevtagRelationships;

class DeviceController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    protected $rootOwnModel = 'device';
    protected $indexOwnModel = 'mac';
    protected function newOwnModel() {
    	return OwWebportalDevices::with('device')->with('config');
    }
    protected $indexOwnModelTag = 'dev_id';
    protected function newOwnModelTagRelationships() {
        return new OwDevtagRelationships();
    }

	public function getIndex() {
		return view("opwifi.webportal.device_management", $this->viewData);
	}

}