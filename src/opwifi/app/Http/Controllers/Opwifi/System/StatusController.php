<?php

namespace App\Http\Controllers\Opwifi\System;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;


class StatusController extends Controller {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

	public function getIndex() {
		return view("opwifi.system.status", $this->viewData);
	}
}