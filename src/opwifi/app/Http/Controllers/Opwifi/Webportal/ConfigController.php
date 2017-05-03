<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwWebportalConfigs;

class ConfigController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    protected $limitUserId = 'mnger_id';
    protected $hasOwnModelDefault = true;
    protected $indexOwnModel = 'name';
    protected function newOwnModel() {
    	return new OwWebportalConfigs();
    }

	public function getIndex() {
		return view("opwifi.webportal.config", $this->viewData);
	}

}