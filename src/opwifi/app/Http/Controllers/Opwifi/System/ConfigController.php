<?php

namespace App\Http\Controllers\Opwifi\System;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use App\Models\OwSystem;

class ConfigController extends Controller {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    private $config_items = ['site_url'];

    private function loadConfigs() {
    	$this->viewData['configs'] = OwSystem::getValue($this->config_items);
    }

	public function getIndex() {
		$this->loadConfigs();
		return view("opwifi.system.config", $this->viewData);
	}

	public function postIndex(Request $request) {
        OwSystem::saveValues($request->only($this->config_items));
        return $this->getIndex();
	}

}