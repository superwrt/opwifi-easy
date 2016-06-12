<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Input, Log;

use App\Models\OwDevices;
use App\Models\OwWebportalConfigs;
use App\Models\OwWebportalStationStatus;
use App\Models\OwWebportalTokens;

class WebportalServController extends Controller {

	private $devMac;
	private $wpDev;

	static private function arrayRecursiveDiff($aArray1, $aArray2) {
        $aReturn = array();
      
        foreach ($aArray1 as $mKey => $mValue) {
          if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
              $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
              if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
            } else {
              if ($mValue != $aArray2[$mKey]) {
                $aReturn[$mKey] = $mValue;
              }
            }
          } else {
            $aReturn[$mKey] = $mValue;
          }
        }
        return $aReturn;
    }

	static private function arrayConvert($old, $c) {
		$arr = array();
		foreach($c as $v){
			$nms = explode(".", $v[0]);
			$it = $old;
			foreach($nms as $nm){
				if (!isset($it[$nm]))
					continue 2;
				$it = $it[$nm];
			}
			$arr[isset($v[1])?$v[1]:$v[0]] = $it;
		}
		return $arr;
	}

	static public function getDevConfig($mac) {
		$dev = OwDevices::with('webportal')->where('mac', $mac)->first();
		$cfg = null;
		if ($dev && isset($dev['webportal'])) {
			$cfg = $dev['webportal']->config()->first();
		}
		if (!$cfg) {
			$cfg = OwWebportalConfigs::where('id', 1)->first();
		}
		return $cfg;
	}

	static public function auth($mac, $usermac, $user, $redir) {
		$token = str_random(32);
		$data = ['token'=>$token, 'mac'=>$mac, 'usermac'=>$usermac];
		if ($user && $user->count()) $data['user_id'] = $user['id'];
		if ($redir) $data['redirect'] = $redir;
		OwWebportalTokens::create($data);
		return $token;
	}

	static public function userStatus($mac, $usermac) {
		$sta = OwWebportalStationStatus::where(['mac'=>$usermac, 'ondev'=>$mac])->first();
		if ($sta) {
			//TODO: 先检查用户的状态，是否是设备掉线，造成的未解认证用户。
			return $sta;
		}
		return null;
	}

	private function syncConfig(Array &$rep, Array $old) {
		$cfg = self::getDevConfig($this->devMac);
		if ($cfg) {
			$jcfg = array(
					"white_ip" => explode(',',$cfg['white_ip']),
					"white_domain" => explode(',',$cfg['white_domain']),
					"redirect" => $cfg['redirect'],
					"idle_timeout" => $cfg['idle_timeout'],
					"force_timeout" => $cfg['force_timeout'],
					"period" => $cfg['period'],
				);
			if (!$old ||
					self::arrayRecursiveDiff($jcfg, $old)) {
				$rep['cmd']['cfg'] = $jcfg;
			}
		}
	}

	private function updateUserStat($sta, $offline) {
		$user = $sta->user();
		if ($user->count() == 0)
			return;
		if ($offline) {
			$user['trx_used'] += $sta['trx_used'];
			$user['time_used'] += $sta['online_time'];
		} else {
			$user['laststamac'] = $sta['mac'];
			$user['lastdevmac'] = $sta['ondev'];
			$user['lastonline'] = date("Y-m-d H:i:s",time());
		}
		$user->save();
	}

	private function updateUserAuth($mac, $usermac, $user, $cfg, $auth) {
		$user = $sta->user();
		if ($user->count() == 0)
			return;
		$sta['auth'] = $auth;
		if ($auth) {
			$tmout = null;
			//更新lasttimeout，用于在设备异常离线时，判断是否更新用户为下线状态。
			$sta['lastauth'] = date("Y-m-d H:i:s",time());
			if ($user && $user->count()) {
				$sta['user_id'] = $user['id'];
				$tmout = $user['force_timeout'];
			}
			if (!$tmout) {
				$cfg = self::getDevConfig($mac);
				if ($cfg->count()) {
					$tmout = $cfg['force_timeout'];
				}
			}
			if (!$tmout) {
				/* 正常不应该走到这里，默认一天。 */
				$tmout = 86400;
			}
			$sta['lastdeadline'] = date("Y-m-d H:i:s",time()+$tmout);
		}
		$user->save();
	}

	private function updateUserStatus($users) {
		foreach ($users as $user) {
			if (!isset($user['mac'])) {
				continue;
			}
			$st = [];
			if (isset($user['status']) && is_array($user['status'])) {
				$ust = $user['status'];
				$st = array_merge($st,
					self::arrayConvert($ust, [
						['authed', 'auth'], ['online','online_time'],
						['trx_used']
					])
				);
				if (!isset($st['trx_used'])) {
					$st['trx_used'] = isset($ust['tx_used'])&&isset($ust['rx_used'])?$ust['tx_used']+$ust['rx_used']:0;
				}
			}
			$sta = OwWebportalStationStatus::where('mac', $user['mac'])->first();
			if ($sta) {
				if (isset($st['auth']) && boolval($sta['auth']) != boolval($st['auth'])
						&& $this->devMac == $sta['ondev']) {
					/* 更新上次认证时流量，可能是设备异常掉线造成的。
					 * 如果是漫游到其它设备上，还未认证，则先不更新。
					 */
					$this->updateUserStat($sta, $st['auth']);
				}
				$sta->update($st);
			} else {
				$sta = OwWebportalStationStatus::create(array_merge(
						['mac'=>$user['mac'], 'ondev'=>$this->devMac], $st));
			}
		}
	}

	private function handleUserEvent($ev) {
		$st = [];
		$recalc = false;
		$info = $ev['info'];

		if (!isset($info['mac']) || empty($info['mac'])) {
			Log::notice('No user mac: '.var_export($ev, true));
			return;
		}

		switch ($ev['event']) {
		case 'user offline':
		// mac,indev,bssid,ssid,authed,online,tx_used,rx_used
			$st['online'] = false;
			$st['auth'] = false; //User leave, so it must be changed to unauth. 
			$st['online_time'] = isset($info['online'])?$info['online']:0;
			if (isset($info['authed']) && $info['authed']) {
				// Only update used when it is authed user.
				$st['trx_used'] = isset($info['tx_used'])&&isset($info['rx_used'])?$info['tx_used']+$info['rx_used']:0;
				$recalc = true;
			}
		case 'user online':
		// mac,indev,bssid,ssid
			if (!isset($st['online'])) {
				$st['online'] = true;
			}
			if (isset($info['bssid'])) $st['bssid'] = $info['bssid'];
			if (isset($info['ssid'])) $st['ssid'] = $info['ssid'];
			break;
		default:
			Log::notice('Unknown user event: '.$ev);
			return;
		}

		$sta = OwWebportalStationStatus::where('mac', $info['mac'])->first();
		if ($sta) {//FIXME: 这里需要重新写一下。
			if ($recalc) {
				//兼容掉线及错误情况。
				$used = ($st['trx_used'] > $sta['trx_used']) ? $st['trx_used'] : $sta['trx_used'];
				$st['trx_total'] = $st['lasttrx_total'] = $sta['lasttrx_total'] + $used;

				$used = ($st['online_time'] > $sta['online_time']) ? $st['online_time'] : $sta['online_time'];
				$st['online_total'] = $sta['online_total'] + $used;
			} else if ($sta['auth']) {//可能是设备掉线，更新最后的used数据。
				$recalc = true;
				$st['auth'] = false;
				$st['trx_total'] = $st['lasttrx_total'] = $sta['lasttrx_total'] + $sta['trx_used'];
				$st['online_total'] = $sta['online_total'] + $sta['online_time'];
			}
			$sta->update($st);//XXX: Sta changed???
		} else {
			if ($recalc) {//出很碰巧的错时，可能会走到这里。
				$st['trx_total'] = $st['lasttrx_total'] = $sta['trx_used'];
				$st['online_total'] = $st['online_time'];
			}
			$sta = OwWebportalStationStatus::create(array_merge(
					['mac'=>$info['mac'], 'ondev'=>$this->devMac], $st));
		}
		if ($recalc) {
			$this->updateUserStat($sta, true);
		}
	}

	private function handleEvent($evs) {
		foreach ($evs as $ev) {
			if (!(isset($ev['event'],$ev['info']) && is_array($ev['info']))) {
				continue;
			}
			$evarr = explode(' ',$ev['event']);
			switch ($evarr[0]) {
			case 'user':
				$this->handleUserEvent($ev);
				break;
			default:
				Log::notice('Unknown event: '.var_export($ev, true));
				break;
			}
		}
	}

	private function handleConfirm(&$rep, $cf) {	
		$confirmIsPost = false;
		$usermac = null;
		$permit = false;
		$redirect = null;
		$tk = null;

		if (isset($cf['post']) && is_bool($cf['post'])) {
			$confirmIsPost = $cf['post'];
		}
		if (isset($cf['usermac']) && is_string($cf['usermac'])) {
			$usermac = $cf['usermac'];
		}
		/* Check tokens in database */
		if (isset($cf['args']) && is_array($cf['args']) && $usermac) {
			$args = $cf['args'];
			$redirect = isset($args['redir'])?$args['redir']:null;

			if (isset($args['mac'], $args['username']) &&
					$usermac == $args['usermac']) {

				if (isset($args['token']) ) {
					$tk = OwWebportalTokens::where('token', $args['token'])->first();
					if ($tk) {
						$r = ["mac" => $usermac,"permit" => true];
						$permit = true;
						$user = $tk->user()->first();
						if ($user) {
							$trx_limit = 0;
							if ($user['trx_limit']) {
								$trx_limit = $user['trx_limit'] - $user['trx_used'];
								if ($trx_limit == 0) $trx_limit = -1;
							}
							//TODO: time limit
							if ($trx_limit < 0) {
								$r['permit'] = false;
							} else {
								$r['name'] = $user['username'];
								$r['force_timeout'] = $user['force_timeout'];
								$r['tx_rate'] = $user['tx_rate'];
								$r['rx_rate'] = $user['rx_rate'];
								$r['trx_limit'] = $trx_limit;
							}
						}
						$rep['cmd']['users'][] = $r;
					}
				} else if (isset($args['logout'])) {
					$rep['cmd']['users'][] = ["mac" => $usermac,"permit" => false];
					//TODO: 虽然在event中处理了logout，这里还是要处理一下，以防止之后的通信异常。
				}
			}
		}

		if ($permit && $tk && $tk['redirect']) {
			$redirect = $tk['redirect'];
		} else {
			$redirect = $redirect?$redirect:'';
		}

		$rep['confirm'] = $confirmIsPost ?
			["args" => [
					"permit" => $permit,
					"redirect" => $redirect,
				],
			] : [/* Redirect to config redirect with old args */
				"url" => $redirect
			];

	}

	private function getDevice($mac) {
		$dev = OwDevices::with('webportal')->where('mac', $mac)->first();
		if ($dev->count() == 0) {
			$dev = OwDevices::create(['mac'=>$mac]);
		}
		$wpdev = $dev->webportal()->first();
		if ($wpdev->count() == 0) {
			$wpdev = $wpdev->create([]);
		}
		$wpdev->update(['online'=>true, 'lastshow'=>date("Y-m-d H:i:s",time())]);
		$this->wpDev = $wpdev;
	}

	public function postCtrl(Request $request) {
		if (!$request->isJson())
			return;

		$req = $request->json()->all();

		Log::debug("webp ctrl:".var_export($req, true));

		if (!isset($req['mac']))
			return;
		$this->devMac = $req['mac'];

		$rep = array("cmd" => array(
				"users" => array(),
			));

		if (isset($req['events']) && is_array($req['events'])) {
			/* 必须在状态处理前 */
			$this->handleEvent($req['events']);
		}

		/* Event上报时，无status，以减少无用流量 */
		if (isset($req['status']) && is_array($req['status'])) {
			$status = $req['status'];
			/* 如果没有cfg，说明需要下发配置。TODO: 更新配置。 */
			$this->syncConfig($rep,
					(isset($status['cfg']) && is_array($status['cfg'])) ?
						$status['cfg'] : []);

			if (isset($status['users']) && is_array($status['users'])) {
				/* 更新用户状态 */
				$this->updateUserStatus($status['users']);
			}
		}

		if (isset($req['confirm']) && is_array($req['confirm'])) {
			$this->handleConfirm($rep, $req['confirm']);
		}

		Log::debug("webp return:".var_export($rep, true));

		return response()->json($rep);
	}

	/* For 3part api. */
	public function postAuth(Request $request) {
		if (!$request->isJson())
			return;

		$req = $request->json()->all();
		if (!isset($req['mac'], $req['usermac'], $req['redir'], $req['gatewayip'], $req['access_token'])) {
			return;
		}
		$cfg = self::getDevConfig($req['mac']);
		if (!$cfg || $cfg['access_token'] != $req['access_token']) {
			return response()->json(['success'=>false]);
		}
		$token = self::auth($req['mac'], $req['usermac'], null, $req['redir']);
		return response()->json(['success'=>true, 'token'=>$token]);
	}

}