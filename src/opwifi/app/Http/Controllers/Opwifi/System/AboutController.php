<?php

namespace App\Http\Controllers\Opwifi\System;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;


class AboutController extends Controller {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

	public function getIndex() {
		return view("opwifi.system.about", $this->viewData);
	}
}