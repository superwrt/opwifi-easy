<?php

namespace App\Http\Controllers\Opwifi\Station;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;


class StationController extends Controller {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

	public function getManagement() {
		return view("opwifi.station.management", $this->viewData);
	}

	public function getStatus() {
		return view("opwifi.station.status", $this->viewData);
	}

}