<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Opwifi\OwCRUDController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB;

use App\Models\OwWebportalUsers;

class UserController extends OwCRUDController {

	protected $viewData = array(
	);

    public function __construct()
    {

    }

    protected $indexOwnModel = 'username';
    protected function newOwnModel() {
    	return new OwWebportalUsers();
    }

    static function passwordShadow($username, $password) {
        return md5($password);
    }

	public function getIndex() {
		return view("opwifi.webportal.user", $this->viewData);
	}

    public function postReset(Request $request) {
        if (!$request->isJson()) {
            return ;
        }
        $users = $request->json()->all();
        foreach ($users as $user) {
            if (!$user['id']) continue;
            $this->newOwnModel()->where('id', $user['id'])->update([
                'trx_used'=>0, 'time_used'=>0]);
        }
        return response()->json(['success'=>true]);
    }

}