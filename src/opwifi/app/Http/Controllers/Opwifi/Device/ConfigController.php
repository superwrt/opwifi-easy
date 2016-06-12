<?php

namespace App\Http\Controllers\Opwifi\Device;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use Input;

use App\Models\OwDevConfigs;

class ConfigController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    protected $indexOwnModel = 'name';
    protected function newOwnModel() {
    	return new OwDevConfigs();
    }

	public function getIndex(Request $request) {
		return view("opwifi.device.config", $this->viewData);
	}

}