<?php

namespace App\Http\Controllers\Opwifi\Webportal;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Input, Log;

use App\Models\OwDevices;
use App\Models\OwStations;
use App\Models\OwWebportalConfigs;
use App\Models\OwWebportalStationStatus;
use App\Models\OwWebportalTokens;
use App\Models\OwSystem;

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

    static public function userStatus($usermac) {
    	$sta = OwWebportalStationStatus::where(['mac'=>$usermac])->first();
    	if ($sta) {
			//TODO: 先检查用户的状态，是否是设备掉线，造成的未解认证用户。
    		return $sta;
    	}
    	return null;
    }

    private function checkUserMac($mac) {
        if (!$this->wpConfig)
            return true;
        $type = $this->wpConfig->mac_filter_type;
        $tagid = $this->wpConfig->mac_filter_tag;
        if ($tagid && ($type == "allow" || $type == "deny")) {
            $tag = null;
            $sta = owStations::where(['mac'=>$mac])->first();
            if ($sta)
                $tag = $sta->tags()->find($tagid);
            if ((!$tag && $type == "allow") || ($tag &&  $type == "deny"))
                return false;
        }
        return true;
    }

    private function syncConfig(Array $old) {
        $rep = &$this->ctrlRep;
    	$cfg = self::getDevConfig($this->devMac);
    	if ($cfg) {
            $jcfg = array(
                "white_ip" => strlen($cfg->white_ip)?explode(',',$cfg->white_ip):[],
                "white_domain" => strlen($cfg->white_domain)?explode(',',$cfg->white_domain):[],
                "idle_timeout" => $cfg->idle_timeout,
                "force_timeout" => $cfg->force_timeout,
                "period" => $cfg->period,
                );
            if ($cfg->mode == 'partner') {
                $jcfg['redirect'] = $cfg->redirect;
            } else {
                /* site_url maybe changed after redirect setted.*/
                $jcfg['redirect'] = OwSystem::getValue('site_url').'/webportal';
            }
            if (preg_match('@^(?:http|https)://([^/:]+)@i', $jcfg['redirect'], $addr)) {
                if (filter_var($addr[1], FILTER_VALIDATE_IP)) {
                    $jcfg['white_ip'][] = $addr[1];
                } else {
                    $jcfg['white_domain'][] = $addr[1];
                }
            }

    		if (!$old || self::arrayRecursiveDiff($jcfg, $old)) {
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
            $tmout = $this->wpConfig->force_timeout;
        }
        if (!$tmout) {
            /* 正常不应该走到这里，不允许超过30天。 */
            $tmout = 2592000;
        }

        $st = ['authed' => true, 'online' => true,
            'ondev' => $this->devMac, 'authdev_id' => $this->wpDev->id,
            'mnger_id' => $this->wpDev->mnger_id,
            'config_id' => $this->wpConfig->id,
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
						['online','time_used'],
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
                } else { /* Not authed device, maybe admin deauth it! */
                    $this->stationUnpermit($s['mac']);
                }
                if ($oldSt->ondev != $this->devMac)
                    $st['ondev'] = $this->devMac;
                if ($this->wpDev->mnger_id != $oldSt->mnger_id)
                    $st['mnger_id'] = $this->wpDev->mnger_id;
				$oldSt->update($st);
			} else {
				OwWebportalStationStatus::create(array_merge(
					['mac'=>$s['mac'], 'ondev'=>$this->devMac, 'mnger_id' => $this->wpDev->mnger_id], $st));
			}
		}
	}

    private function stationBlock($mac) {
        $this->ctrlRep['cmd']['users'][] = [
                "mac" => $mac,
                'permit' => false,
                'block' => true, /* Don't allowed any type traffic! */
            ];
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

        $usermac = $info['mac'];

    	switch ($ev['event']) {
    	case 'user offline':
		/* 更新： mac,indev,bssid,ssid,authed,online,trx_used */
    		$st['online'] = false;
			$st['time_used'] = isset($info['online'])?$info['online']:0;
			$st['trx_used'] = isset($info['tx_used'])&&isset($info['rx_used'])?
                        $info['tx_used']+$info['rx_used']:0;
            $st['last_offline'] = date("Y-m-d H:i:s",time());
            if (isset($info['authed']) && $info['authed']) {
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
            $st['mnger_id'] = $this->wpDev->mnger_id;
			if (isset($info['bssid'])) $st['bssid'] = $info['bssid'];
			if (isset($info['ssid'])) $st['ssid'] = $info['ssid'];
            $st['last_online'] = date("Y-m-d H:i:s",time());
			break;
		default:
			Log::notice('Unknown user event: '.$ev);
			return;
		}

		$permit = false;
		$oldSt = OwWebportalStationStatus::where('mac', $usermac)->first();
		if ($oldSt) {
			$authdev = $oldSt->authdev()->first();
			$isAuthdev = $authdev && $authdev->device->first()['mac'] == $this->devMac;
			if ($st['online'] && $this->wpConfig &&
                            ($oldSt['authed'] && ($isAuthdev ||
                            ($this->wpConfig->roaming && $oldSt['config_id'] == $this->wpConfig->id)))) {
				/* 处理允许漫游和设备意外重启。 */
				if (date("Y-m-d H:i:s",time()) < $oldSt->last_deadline) {
					$permit = true;
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
				['mac'=>$usermac, 'ondev'=>$this->devMac], $st));
		}
		if ($st['online']) {
			if (!$this->checkUserMac($usermac)) {
				$permit = false;
				$this->stationBlock($usermac);
			} else if ($this->wpConfig && $this->wpConfig->mode == "pass") {
				$this->stationAuth($usermac, null);
				$permit = true;
			}
		}
		if ($permit) {
			$this->stationPermit($usermac, $oldSt);
		} else if ($calcSt && $isAuthdev) {
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

    		if (isset($args['mac'], $args['usermac']) &&
    			$usermac == $args['usermac']) {

    			if (isset($args['token']) && is_string($args['token']) &&
                        $this->checkUserMac($args['usermac'])) {
    				$tk = OwWebportalTokens::where('token', $args['token'])->first();
    				if ($tk && !$tk->used) {
                        if ($usermac == $tk->usermac && $this->devMac == $tk->mac &&
                                $tk->created_at > date("Y-m-d H:i:s", time() - 120)) {
                            if ($tk['redirect'])
        					   $redirect = $tk['redirect'];
                            $permit = true;
                            /* Check trx_limit, when login */
        					$this->stationPermit($usermac, $tk);
                            $this->stationAuth($usermac, $tk->user()->first());
                        }
                        $tk->update(['used'=>true]);
    				}
    			} else if (isset($args['logout'])) {
    				$rep['cmd']['users'][] = ["mac" => $usermac,"permit" => false];
					//TODO: 虽然在event中处理了logout，这里还是要处理一下，以防止之后的通信异常。
					$this->stationDeauth($usermac);
    			} else if (isset($args['check'])) {
                    $st = OwWebportalStationStatus::where('mac', $usermac)->first();
                    if ($st) {/* 漫游设备可能没上报Event，或弹出速度快于应用速度，在这里处理一下。 */
                        if ($st['authed']) {
                            if($st['last_deadline'] > date("Y-m-d H:i:s", time())) {
                                if ($this->wpConfig->roaming && $oldSt['config_id'] == $this->wpConfig->id) {
                                    $this->stationPermit($usermac, $st);
                                    $permit = true;
                                }
                            } else {
                                $st->update(['authed'=>false]);
                            }
                        }
                    }
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
    private function getDevice($mac, $update) {
        if ($update) {
    	   $online = ['online'=>true, 'last_show'=>date("Y-m-d H:i:s",time())];
           if (is_array($update))
                $online = array_merge($online, $update);
        }

    	$this->devMac = $mac;
    	$dev = OwDevices::with('webportal')->where('mac', $mac)->first();
    	if (!$dev) {
    		$dev = OwDevices::create(['mac'=>$mac]);
    	}

    	$wpdev = $dev->webportal()->first();
        if ($update) {
        	if (!$wpdev) {
        		$wpdev = $dev->webportal()->create($online);
        	} else {
        		$wpdev->update($online);
        	}
        } else if (!$wpdev) {
            $wpdev = $dev->webportal()->create();
        }
    	$this->wpDev = $wpdev;
        $this->wpConfig = $wpdev->config()->first();
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

        $update = true;
        if (isset($req['status']) && is_array($req['status']) &&
                isset($req['status']['users']) && is_array($req['status']['users'])) {
            $update = ['users'=>count($req['status']['users'])];
        }

    	$this->getDevice($req['mac'], $update);

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

        Log::debug('Webportal Auth: Request:'.var_export($req, true));

        if (!isset($req['mac'], $req['usermac'], $req['access_token'])) {
            Log::debug('Webportal Auth: Invalid args!');
            return response()->json(['status'=>'failed', 'error'=>'Invalid input.', 'errtag'=>'invalid']);
        }
        $req['mac'] = strtolower($req['mac']);
        $req['usermac'] = strtolower($req['usermac']);

        $this->getDevice($req['mac'], false);
        if (!$this->wpConfig) {
            Log::info('Webportal Auth: Not managed device '.$req['mac']);
            return response()->json(['status'=>'failed', 'error'=>'Not managed device.', 'errtag'=>'notmngdev']);
        }

        if (isset($req['op'])) {
            if ($req['op'] == 'status') {
                $sta = self::userStatus($req['usermac']);
                if ($sta) {
                    $rst = array_intersect_key($sta->toArray(),
                        array_flip(array('mac','config_id','ondev','online','authed','ssid','bssid',
                            'time_limit','time_used','time_total','trx_limit','trx_used','trx_total')));
                } else {
                    $rst = ['mac'=>$req['usermac'], 'online'=>false, 'authed'=>false];
                }
                $rst['block'] = !$this->checkUserMac($req['usermac']);
                $rst['config'] = ['roaming'=>$this->wpConfig->roaming, 'id'=>$this->wpConfig->id];
                return response()->json(['status'=>'success', 'result'=>$rst]);
            } else if ($req['op'] == 'deauth') {
                $this->stationDeauth($req['usermac']);
                return response()->json(['status'=>'success']);
            }
        }
    	if (!isset($req['redir'], $req['gatewayip'])) {
            Log::debug('Webportal Auth: Invalid input!');
    		return response()->json(['status'=>'failed', 'error'=>'Invalid input.', 'errtag'=>'invalid']);
    	}

        if ($this->wpConfig['access_token'] != $req['access_token']) {
            Log::info('Webportal Auth: Invalid access token '.$req['access_token']);
    		return response()->json(['status'=>'failed', 'error'=>'Invalid access token.', 'errtag'=>'denytoken']);
    	} else if (!$this->checkUserMac($req['usermac'])) {
            Log::debug('Webportal Auth: Blocked user mac '.$req['usermac']);
            return response()->json(['status'=>'failed', 'error'=>'Blocked user mac.', 'errtag'=>'blockusermac']);
        }

    	$token = self::authUser($req['mac'], $req['usermac'], $req['redir'], null);
        Log::debug('Webportal Auth: Authed usermac '.$req['usermac'].' in '.$req['mac']);
    	return response()->json(['status'=>'success', 'result'=>['token'=>$token, 'mac'=>$req['mac'], 'usermac'=>$req['usermac']]]);
    }


}