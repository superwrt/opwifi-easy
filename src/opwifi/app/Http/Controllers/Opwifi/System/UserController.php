<?php

namespace App\Http\Controllers\Opwifi\System;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwUsers;

class UserController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    protected $indexOwnModel = 'username';
    protected function newOwnModel() {
    	return new OwUsers();
    }

	public function getIndex() {
		return view("opwifi.system.user", $this->viewData);
	}

}