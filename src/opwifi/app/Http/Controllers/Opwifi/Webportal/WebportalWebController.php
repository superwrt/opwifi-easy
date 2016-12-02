<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

use DB, Log, Redirect;

use App\Models\OwWebportalUsers;
use App\Http\Controllers\Opwifi\Webportal\UserController;

class WebportalWebController extends Controller {

	protected $viewData = array();
	private $checkArgs = ['mac', 'usermac', 'redir', 'gatewayip'];
	private $checkLogin = ['username', 'password', 'mac', 'usermac', 'redir', 'gatewayip'];
	private $checkArgsOut = ['mac', 'usermac', 'gatewayip'];

    public function __construct()
    {

    }

	public function getLogin(Request $request) {
		//Redirect from device.
		if (!$request->has($this->checkArgs)) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求中不包含登录用信息。']));
		}
		$input = $request->only($this->checkArgs);
		$cfg = WebportalServController::getDevConfig($input['mac']);
		if (!$cfg) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求未管理的设备。']));
		}
		return view("opwifi.webportal.web.login", array_merge($this->viewData,
			['title' => $cfg['name'], 'mode' => $cfg['mode'], 'from' => $input]));
	}

	public function postLogin(Request $request) {
		$user = null;
		if (!$request->has($this->checkArgs)) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求中不包含登录用信息。']));
		}
		$input = $request->only($this->checkLogin);
		$cfg = WebportalServController::getDevConfig($input['mac']);
		if (!$cfg) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求未管理的设备。']));
		}
		if ($cfg['mode'] == 'login') {
			$ckUser = ['username', 'password'];
			if ($request->has($ckUser)) {
				$in = $request->only($ckUser);
				$user = OwWebportalUsers::where('username', $input['username'])->first();
				if ($user) {
					$pwd = md5("opwifi"+$input['password']);
				}
			}
			if (!$user || $pwd != $user['shadow']) {
				$this->viewData['error']='用户不存在，或密码错误。';
				return view("opwifi.webportal.web.login", array_merge($this->viewData,
					['title' => $cfg['name'], 'mode' => $cfg['mode'], 'error' => '用户不存在，或密码错误。', 'from' => $input]));
			}
		} else if ($cfg['mode'] == 'confirm') {

		} else {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'内部错误。']));
		}
		$token = WebportalServController::authUser($input['mac'], $input['usermac'], $cfg['success_redirect'], $user);
		if (!$token) {
			return view("opwifi.webportal.web.login", array_merge($this->viewData,
					['title' => $cfg['name'], 'mode' => $cfg['mode'], 'error' => '该账号不允许登录。可能是流量或时长超出限制。', 'from' => $input]));
		}

		/* 使用oplogin.com而不是gateway! */
		$path = 'http://oplogin.com/api/webp/1/confirm?'.
				http_build_query(array_merge($input, ['token'=>$token]));

		return Redirect::to($path, 302);
	}
		

	public function postLogout(Request $request) {
		if (!$request->has($this->checkArgsOut)) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求中不包含登出用信息。']));
		}
		$input = $request->only($this->checkArgsOut);
		$cfg = WebportalServController::getDevConfig($input['mac']);
		if (!$cfg) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求未管理的设备。']));
		}
		$r = array_merge($input, ['logout'=>'1']);
		if ($cfg['success_redirect']) {
			$r['redir'] = $cfg['success_redirect'];
		}
		$path = 'http://oplogin.com/api/webp/1/confirm?'.http_build_query($r);
		return Redirect::to($path, 302);
	}

	public function getIndex(Request $request) {
		//未登录用户，跳转到login
		if (!$request->has($this->checkArgsOut)) {
			return view("opwifi.webportal.web.failed", array_merge($this->viewData,
				['error'=>'请求中信息不全。']));
		}
		$input = $request->only($this->checkArgsOut);
		$sta = WebportalServController::userStatus($input['mac'], $input['usermac']);
		if (!$sta || !$sta['auth']) {
			return $this->getLogin($request);
		}
		return view("opwifi.webportal.web.index", $this->viewData);
	}
}