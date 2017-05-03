<?php

namespace App\Http\Controllers\Opwifi\System;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB, Auth;

use App\Models\OwUsers;

class UserController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

    protected $limitUserId = 'id';
    protected $indexOwnModel = 'username';
    protected function newOwnModel() {
    	return new OwUsers();
    }

	public function getIndex() {
		return view("opwifi.system.user", $this->viewData);
	}

    public function postAdd(Request $request) {
        if (Auth::User()['right'] != 'admin')
            return response()->json(['success'=>false]);

        return parent::postAdd($request);
    }

    public function postDelete(Request $request) {
        if (Auth::User()['right'] != 'admin')
            return response()->json(['success'=>false]);

        return parent::postDelete($request);
    }

    public function postUpdate(Request $request) {
        $id = $request->get('id');
        if (Auth::User()['right'] != 'admin' &&
                $request->has('right'))
            return response()->json(['success'=>false]);

        return parent::postUpdate($request);
    }
}