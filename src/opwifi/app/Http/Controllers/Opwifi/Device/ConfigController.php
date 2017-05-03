<?php

namespace App\Http\Controllers\Opwifi\Device;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use Input;

use App\Models\OwDevConfigs;

//use App\Http\Helpers\Opwifi\DeviceConfigApply;

class ConfigController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    protected $limitUserId = 'mnger_id';
    protected $indexOwnModel = 'name';
    protected function newOwnModel() {
    	return new OwDevConfigs();
    }

	public function getIndex(Request $request) {
		return view("opwifi.device.config", $this->viewData);
	}

    public function getEdit(Request $request, $id) {
        $cfg = OwDevConfigs::where("id", $id)->first();
        return view("opwifi.device.config_edit", $this->viewData)->with('config', $cfg);
    }

    public function postEdit(Request $request, $id) {
        $cfg = OwDevConfigs::where("id", $id)->first();
        $cfg->update($request->all());
        return redirect()->route('opwifi::device.config');
    }
}