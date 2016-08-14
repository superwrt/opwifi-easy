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
    private $wpConfig;

    private $ctrlReq;
    private $ctrlRep;

    /**
     * Pick different value from array1 and array2.
     *
     * @param array $aArray1
     * @param array $aArray2
     * @return array
     */
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

    /**
     * Convert value in $old by $conv, then return the new array.
     *
     * @param array $old
     * @param array $conv
     * @return array
     */
    static private function arrayConvert($old, $conv) {
    	$arr = array();
    	foreach($conv as $v){
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

    static public function authUser($mac, $usermac, $redir, $user) {
    	$token = str_random(32);
    	$data = ['token'=>$token, 'mac'=>$mac, 'usermac'=>$usermac];
    	if ($user) {
            if ($user->disable) {
                return null;
            }
            if ($user->time_limit) {
                if ($user->time_used >= $user->time_limit)
                    return null;
                $data['time_limit'] = $user->time_limit - $user->time_used;
            }
            if ($user->trx_limit) {
                if ($user->trx_used >= $user->trx_limit)
                    return null;
                $data['trx_limit'] = $user->trx_limit - $user->trx_used;
            }
            $data['tx_rate'] = $user->tx_rate;
            $data['rx_rate'] = $user->rx_rate;
            $data['user_id'] = $user['id'];
        }
    	if ($redir) $data['redirect'] = $redir;
    	OwWebportalTokens::create($data);
    	return $token;
    }

    static public function userStatus($mac, $usermac) {
    	$sta = OwWebportalStationStatus::where(['mac'=>$usermac])->first();
    	if ($sta) {
			//TODO: 先检查用户的状态，是否是设备掉线，造成的未解认证用户。
    		return $sta;
    	}
    	return null;
    }

    private function syncConfig(Array $old) {
        $rep = &$this->ctrlRep;
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
		$user = $sta->user()->first();
		if ($user) {
    		if ($offline) {
    			$user->trx_used += $sta->trx_total;
    			$user->time_used += $sta->time_total;
                $user->last_offline = date("Y-m-d H:i:s",time());
    		} else {
                $user->last_online = date("Y-m-d H:i:s",time());
            }
    		$user->last_stamac = $sta->mac;
    		$user->last_devmac = $sta->ondev;
    		
    		$user->save();
        }
        if ($offline) {
            $sta->trx_history += $sta->trx_total;
            $sta->time_history += $sta->time_total;
            $sta->trx_total = 0;
            $sta->time_total = 0;
            $sta->save();
        }
	}

    private function stationAuth($mac, $user) {
        if ($user && $user->force_timeout) {
            $tmout = $user->force_timeout;
        } else {
            $tmout = $wpConfig->force_timeout;
        }
        if (!$tmout) {
            /* 正常不应该走到这里，不允许超过30天。 */
            $tmout = 2592000;
        }

        $st = ['authed' => true, 'online' => true,
            'ondev' => $devMac, 'authdev_id' => $wpDev->id,
            'last_auth' => date("Y-m-d H:i:s",time()),
            'last_deadline' => date("Y-m-d H:i:s", time() + $tmout),
            'user_id' => $user ? $user->id : null,
            ];
        $oldSt = OwWebportalStationStatus::where('mac', $mac)->first();
        if ($oldSt) {
            if ($oldSt->authed) {
                if ($oldSt->last_deadline > date("Y-m-d H:i:s", time())) {
                    /* Reauth, don't update last auth time. */
                    unset($st['last_auth']);
                }
            }
            $oldSt->update($st);
        } else {
            OwWebportalStationStatus::create(
                array_merge(['mac'=>$mac], $st));
        }
    }

    private function stationDeauth($mac) {
        /* TODO: Remove idle_time(last_seen) from time_total */
        $sta = OwWebportalStationStatus::where('mac', $mac)->first();
        if ($sta && $sta->authed) {
            $sta->authed = false;
            $sta->last_deadline = date("Y-m-d H:i:s",time());
            if ($sta->time_used) {
                $sta->time_total += $sta->time_used;
                $sta->time_used = 0;
            }
            if ($sta->trx_used) {
                $sta->trx_total += $sta->trx_used;
                $sta->trx_used = 0;
            }
            $sta->save();
            $this->updateUserStat($sta, true);
        }
    }

	private function stationUpdate($stas) {
		foreach ($stas as $s) {
			if (!isset($s['mac'])) {
				continue;
			}
			$st = [];
			if (isset($s['status']) && is_array($s['status'])) {
				$ust = $s['status'];
				$st = array_merge($st,
					self::arrayConvert($ust, [
						['authed', 'auth'], ['online','time_used'],
						['trx_used']])
					);
				if (!isset($st['trx_used']) && isset($ust['tx_used'], $ust['rx_used'])) {
					$st['trx_used'] = $ust['tx_used'] + $ust['rx_used'];
				}
			}
			$oldSt = OwWebportalStationStatus::where('mac', $s['mac'])->first();
			if ($oldSt) {
                if ($oldSt->authed) {
                    if ($oldSt->last_deadline < date("Y-m-d H:i:s", time())) {
                        /* To deadline, unauth device. */
                        $this->stationUnpermit($s['mac']);
                        $st['authed'] = false;
                    } else if (!$oldSt->authdev_id) {
                        /* Roaming, and change auth to new device */
                        $st['authdev_id'] = $this->wpDev->id;
                    }
                }
                if ($oldSt->ondev != $this->devMac) {
                    $st['ondev'] = $this->devMac;
                }
				$oldSt->update($st);
			} else {
				$oldSt = OwWebportalStationStatus::create(array_merge(
					['mac'=>$s['mac'], 'ondev'=>$this->devMac], $st));
			}
		}
	}
    
    private function stationUnpermit($mac) {
        $this->ctrlRep['cmd']['users'][] = [
                "mac" => $mac,
                'permit' => false,
            ];
    }

    private function stationPermit($mac, $cfg) {
        $this->ctrlRep['cmd']['users'][] = [
                "mac" => $mac,
                'permit' => true,
                'name' => $cfg->username,
                'force_timeout' => $cfg->time_limit,
                'tx_rate' => $cfg->tx_rate,
                'rx_rate' => $cfg->rx_rate,
                'trx_limit' => $cfg->trx_limit
            ];
    }

    /**
     * Handle user online/offline event in post.
     *
     * @param  array $evs
     * @return
     */
    private function handleUserEvent($ev) {
    	$st = [];
    	$calcSt = false;
    	$info = $ev['info'];

    	if (!isset($info['mac']) || empty($info['mac'])) {
    		Log::notice('No user mac: '.var_export($ev, true));
    		return;
    	}

    	switch ($ev['event']) {
    	case 'user offline':
		/* 更新： mac,indev,bssid,ssid,authed,online,trx_used */
    		$st['online'] = false;
			$st['time_used'] = isset($info['online'])?$info['online']:0;
			$st['trx_used'] = isset($info['tx_used'])&&isset($info['rx_used'])?
                        $info['tx_used']+$info['rx_used']:0;
            $st['last_offline'] = date("Y-m-d H:i:s",time());
            if ($info['authed']) {
                $calcSt = true;
            }
            break;
		case 'user online':
		/* 更新： mac,indev,bssid,ssid */
        /* 新sta上线。如果sta是authed了，进行下面判断。
         * 如果配置允许漫游，则允许设备通过，同时将online设备设为上报设备。
         * 如果配置不允许漫游，则需要重新认证。
         */
			$st['online'] = true;
            $st['ondev'] = $this->devMac;
			if (isset($info['bssid'])) $st['bssid'] = $info['bssid'];
			if (isset($info['ssid'])) $st['ssid'] = $info['ssid'];
            $st['last_online'] = date("Y-m-d H:i:s",time());
			break;
		default:
			Log::notice('Unknown user event: '.$ev);
			return;
		}

		$oldSt = OwWebportalStationStatus::where('mac', $info['mac'])->first();
		if ($oldSt) {
            $authdev = $oldSt->authdev()->first();
            $isAuthdev = $authdev && $authdev->device->first()['mac'] == $this->devMac;
            if ($st['online'] && $wpConfig &&
                    (($wpConfig->roaming && $oldSt['authed']) ||
                     $isAuthdev)) {
                /* 处理允许漫游和设备意外重启。 */
                if (time() < $oldSt->last_deadline) {
                    $this->stationPermit($usermac, $oldSt);
                } else {
                    /* 更正authed值 */
                    $st['authed'] = false;
                    $st['authdev_id'] = null;
                }
            }

			if ($calcSt && $isAuthdev) {
				//兼容掉线及错误情况。
				$trx_used = ($st['trx_used'] > $oldSt->trx_used) ? $st['trx_used'] : $oldSt->trx_used;
				$st['trx_total'] = $oldSt->trx_total + $trx_used;
                $st['trx_used'] = 0;

				$time_used = ($st['time_used'] > $oldSt->time_used) ? $st['time_used'] : $oldSt->time_used;
				$st['time_total'] = $oldSt->time_total + $time_used;
                $st['time_used'] = 0;
			}
			$oldSt->update($st);//XXX: Sta changed???
		} else {
			if ($calcSt) {
				$st['trx_total'] = $st['lasttrx_total'] = $oldSt['trx_used'];
				$st['online_total'] = $st['online_time'];
			}
			$oldSt = OwWebportalStationStatus::create(array_merge(
				['mac'=>$info['mac'], 'ondev'=>$this->devMac], $st));
		}
		if ($calcSt && $isAuthdev) {
			$this->stationDeauth($usermac, true);
		}
	}

    /**
     * Handle device events in post.
     *
     * @param  array $evs
     * @return
     */
    private function handleEvent($evs) {
    	foreach ($evs as $ev) {
    		if (!(isset($ev['event'], $ev['info']) && is_array($ev['info']))) {
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

    /**
     * Handle user auth confirm from device in post.
     *
     * @param  array $evs
     * @return
     */
    private function handleConfirm($cf) {
        $rep = &$this->ctrlRep;
    	$permit = false;
    	$redirect = null;
    	$tk = null;

    	$confirmIsPost = (isset($cf['post']) && is_bool($cf['post'])) ? $cf['post'] : false;
    	$usermac = (isset($cf['usermac']) && is_string($cf['usermac'])) ? $cf['usermac'] : null;

    	/* Check tokens in database */
    	if (isset($cf['args']) && is_array($cf['args']) && $usermac) {
    		$args = $cf['args'];
    		$redirect = isset($args['redir'])?$args['redir']:null;

    		if (isset($args['mac'], $args['username']) &&
    			$usermac == $args['usermac']) {

    			if (isset($args['token']) && is_string($cf['token'])) {
    				$tk = OwWebportalTokens::where('token', $args['token'])->first();
    				if ($tk) {
    					$redirect = $tk['redirect'];
                        $permit = true;
                        /* Check trx_limit, when login */
    					$this->stationPermit($usermac, $tk);
                        $this->stationAuth($usermac, $tk->user()->first());
    				}
    			} else if (isset($args['logout'])) {
    				$rep['cmd']['users'][] = ["mac" => $usermac,"permit" => false];
					//TODO: 虽然在event中处理了logout，这里还是要处理一下，以防止之后的通信异常。
					$this->stationDeauth($usermac);
    			}
    		}
    	}

    	$rep['confirm'] = $confirmIsPost ?
	    	["args" => [
				"permit" => $permit,
				"redirect" => $redirect? $redirect : '',
	    		],
	    	] : [/* Redirect to config redirect with old args */
				"url" => $redirect ? $redirect : ''
	    	];

    }

    /**
     * Get device from devices table.
     *
     * @param  Request $request
     * @return
     */
    private function getDevice($mac) {
    	$online = ['online'=>true, 'lastshow'=>date("Y-m-d H:i:s",time())];

    	$this->devMac = $mac;
    	$dev = OwDevices::with('webportal')->where('mac', $mac)->first();
    	if (!$dev) {
    		$dev = OwDevices::create(['mac'=>$mac]);
    	}

    	$wpdev = $dev->webportal()->first();
    	if (!$wpdev) {
    		$wpdev = $dev->webportal()->create($online);
    	} else {
    		$wpdev->update($online);
    	}
    	$this->wpDev = $wpdev;
    }

    /**
     * Device api, handle device post request.
     *
     * @param  Request $request
     * @return (Response Json)
     */
    public function postCtrl(Request $request) {
    	if (!$request->isJson())
    		return;

    	$this->ctrlReq = $request->json()->all();
    	Log::debug("webp ctrl:".var_export($this->ctrlReq, true));
        $req = $this->ctrlReq;

    	if (!isset($req['mac']))
    		return;

    	$this->getDevice($req['mac']);

    	$this->ctrlRep = ["cmd" => ["users" => [] ] ];

    	if (isset($req['events']) && is_array($req['events'])) {
    		/* 必须在状态处理前 */
    		$this->handleEvent($req['events']);
    	}

    	/* Event上报时，无status，以减少无用流量 */
    	if (isset($req['status']) && is_array($req['status'])) {
    		$status = $req['status'];
    		/* 如果没有cfg，说明需要下发配置。TODO: 更新配置，在配置里存在个时间。 */
    		$this->syncConfig(
    			(isset($status['cfg']) && is_array($status['cfg'])) ?
    			$status['cfg'] : []);

    		if (isset($status['users']) && is_array($status['users'])) {
    			/* 更新用户状态 */
    			$this->stationUpdate($status['users']);
    		}
    	}

    	if (isset($req['confirm']) && is_array($req['confirm'])) {
    		$this->handleConfirm($req['confirm']);
    	}

    	Log::debug("webp return:".var_export($this->ctrlRep, true));

    	return response()->json($this->ctrlRep);
    }

    /**
     * For 3part api, handle outside auth post request.
     *
     * @param  Request $request
     * @return (Response Json)
     */
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