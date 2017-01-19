<?php

namespace App\Http\Controllers\Opwifi\Device;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Storage, Log, File;

use App\Models\OwDevices;
use App\Models\OwDevicemeta;
use App\Models\OwDevConfigs;
use App\Models\OwDevFirmwares;
use App\Models\OwStations;
use App\Models\OwStationmeta;
use App\Models\OwSystem;

use App\Http\Middleware\HttpSignatures;
use App\Http\Helpers\Opwifi\DeviceConfigApply;

class DeviceServController extends Controller {

	private $meta;

	private $isFit, $devParam, $configSha1;

	private $enCrypted = array();

	private function getAesKey($req) {
		try {
			$key = $req['crypto']['key'];
			if ($key['type'] == 'aes_128_cbc') {
				$aesKey = null;
				$privKey = HttpSignatures::getPrivKey();
				if ($privKey) {
					if (openssl_private_decrypt(base64_decode($key['key_rsa']), $aesKey, $privKey))
						return $aesKey;
				}
			}
		} catch(Exception $e) {}
		return null;
	}

	private function encryptoCtx($req, &$rep, $data) {
		$aesKey = $this->getAesKey($req);
		if (!$aesKey)
			return false;
		$iv = str_random(16);
		$j = json_encode($data);
		Log::debug("Encrypto:".var_export($j, true));
		$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $aesKey, $j, MCRYPT_MODE_CBC, $iv);
		$ctx = base64_encode($encrypted);
		if (!isset($rep['crypto'])) {
			$rep['crypto'] = array();
		}
		$rep['crypto']['data'] = array(
				'iv' => $iv,
				'ctx' => $ctx,
			);
		return true;
	}

	private function decryptoCtx($req) {
		$aesKey = $this->getAesKey($req);
		if (!$aesKey)
			return null;
		try {
			$d = $req['crypto']['data'];
			$ctx = base64_decode($d['ctx']);
			return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aesKey, $ctx, MCRYPT_MODE_CBC, $d['iv']));
		} catch(Exception $e) {}
		return null;
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

	private function updateStationStat($stat) {
		foreach ($stat as $wlan) {
			if (!isset($wlan["stations"]))
				continue;
			foreach ($wlan["stations"] as $sta) {
				try {
					$meta = [
						'last_show' => date("Y-m-d H:i:s",time()),
						'last_ondev' => $this->devMac,
						'last_onbssid' => $wlan['bssid'],
						'last_onssid' => $wlan['ssid'],
						'last_signal' => $sta['signal'],
						'last_txbytes' => $sta['tx_bytes'],
						'last_rxbytes' => $sta['rx_bytes'],
					];
					$osta = OwStations::firstOrCreate(['mac' => $sta['mac']]);
					if ($osta) {
						$resm = $osta->meta()->first();
						if ($resm) {
							$resm->update($meta);
						} else {
							$resm = $osta->meta()->create($meta);
						}
					}
				} catch(\ErrorException $e) {
					/* Station maybe not include some item, skip it */
					Log::debug("Update Station status error:".$e->getMessage());
				}
			}
		}
	}

	private function updateDevInfo($req, $ip) {
		if (!isset($req['mac'])) {
			return false;
		}
		$this->devMac = $req['mac'];

		Log::info("Update Device Info:".var_export($req, true));

		$meta = self::arrayConvert($req, [
			['firmware.version', 'fwver'],
			['firmware.full_version', 'fullver'],
			['sbi.info', 'sbiinfo'],
			['sbi.sha1', 'sbisha1'],
			['sbi.loc', 'sbiloc'],
			['hardware.cpu', 'cpuinfo'],
			['hardware.ram', 'ramsize'],
			['hardware.free', 'ramfree'],
			['hardware.mtd.firmware.size', 'flashfwsize']]);

		$meta = array_merge($meta, [
			'lastshow' => date("Y-m-d H:i:s",time()),
			'lastip' => $ip,
			'online' => true
		]);

		$res = OwDevices::firstOrCreate(['mac' => $req['mac']]);
		if ($res) {
			$resm = $res->meta()->first();
			if ($resm) {
				$resm->update($meta);
			} else {
				$resm = $res->meta()->create($meta);
			}
			$this->meta = $resm;
		}
		return;
	}

	private function updateOperationStatus($status)
	{
		foreach ($status as $st) {
			if ($st["status"] != "success")
				continue;
			switch ($st["name"]) {
			case "wlan.stations":
				if (isset($st["result"]))
					$this->updateStationStat($st["result"]["status"]);
				break;
			}
		}
	}

	private function updateOperation($req) {
		if (isset($req['mode']) && $req['mode']=='fit')
			$this->isFit = true;
		$this->devParam = isset($req['param'])?$req['param']:[];

		if (isset($req['config']) && is_array($req['config'])) {
			$cfg = $req['config'];
			$this->configSha1 = $cfg['sha1'];
			//Check return set_sha1!!
			if (isset($cfg['set_ret']) && $cfg['set_ret']) {
				DeviceConfigApply::devmeta($this->meta)->update($this->configSha1);
			}

			if (isset($cfg['status']) && is_array($cfg['status'])) {
				$this->updateOperationStatus($cfg['status']);
			}
		}
	}

	private function appendOperation($req, &$rep) {
		$reboot = false;

		$this->updateOperation($req);

		/* For test */
		/* end */

		if ($this->meta->op_config_id && $this->configSha1 &&
				isset($req['count']) && $req['count'] == 0) {
			/* 简单处理，如果更改过，就进行下发。只第一次上报时下发，防止一直循环。 */
			$setting = DeviceConfigApply::devmeta($this->meta)->check($this->configSha1);
			if ($setting) {
				$this->enCrypted['config'] = array(
					'set' => $setting,
					'set_sha1' => $this->configSha1,
				);
				/* We must use apply time, not return time!!! */
				$this->meta->update(['op_configed_last' => date("Y-m-d H:i:s",time())]);
			}
		}

		if ($this->meta['op_reboot'] || $reboot) {
			if (!isset($rep['config'])) $rep['config'] = array();
			if (!isset($rep['config']['task'])) $rep['config']['task'] = array();
			$rep['config']['task'][] = array('name'=>'sys.reboot');
			$this->meta->update(['op_reboot'=>false]);
			return;
		}

		if ($this->meta['op_upgrade_id']){
			$fw = $this->meta->upgrade()->first();
			if ($fw && $this->meta['fullver'] != $fw['version'] &&
				$this->meta['op_upgrade_trys'] != 1) {
				$rep['upgrade'] = array(
					"firmware"=> [
						"upgrade"=> [
							"url"=>OwSystem::getValue('site_url').$fw['url'],
							"sha1"=>$fw['sha1'],
						]
					]
				);
				if ($this->meta['op_upgrade_trys'] == 0) {
					$this->meta->op_upgrade_trys = 5;
				} else {
					$this->meta->op_upgrade_trys--;
				}
				$this->meta->save();
			} else {
				$this->meta->update(['op_upgrade_id'=>null, 'op_upgrade_trys'=>0]);
			}
		}

		if (isset($req['count']) && $req['count'] == 0) {
			if(!isset($req['config']) || !isset($req['config']['status'])) {
				$report = [ 'status'=>[] ];
				if (OwSystem::getValue('fn_sta_status')) {
					$report['status'][] = ["name" => "wlan.stations"];
				}
				if (count($report['status']) > 0) {
					$rep['config']['report'] = $report;
				}
			}
		}
	}

	private function appendEncrypted($req, &$rep) {
		if ($this->enCrypted) {
			$this->encryptoCtx($req, $rep, $this->enCrypted);
		}
	}

	public function postReg(Request $request) {
		if (!$request->isJson())
			return;

		$req = $request->json()->all();

		$endata = $this->decryptoCtx($req);
		if ($endata) {
			$enjson = json_decode($endata, true);
			if ($enjson) {
				$req = array_merge_recursive($req, $enjson);
			}
		}

		$this->updateDevInfo($req, $request->ip());

		$rep = [];

		if (!isset($req['date']) || abs(strtotime($req['date']." UTC")-time()) > 30) {
			$rep['date'] = [
					"set" => gmdate("Y-m-d H:i:s",time()),
				];
		}
		if (isset($req['seq'])) {
			$rep['seq'] = $req['seq'];
		}

		$this->appendOperation($req, $rep);
		$this->appendEncrypted($req, $rep);

		Log::debug("rep:".var_export($rep, true));
		return response()->json($rep);
	}

/*
	命令示例：
		$this->encryptoCtx($req, $rep,array(
			'config'=>array(
				'set'=> array(
					'wlan'=> array(
						'vap'=>array(
					 		array('name'=>'wlan0', 'ssid'=>'Hi'),
							array('_o'=>'add','name'=>'wlan10', 'radio'=>'phy0','ssid'=>'Hi', 'enable'=>true, 'vlan'=>10, 'mode'=>'ap',"auth"=>"open"),
							array('_o'=>'del','name'=>'wlan10')
					 	 )
					 )
				),
				'get'=>true,
				'task'=>array(
				 	array('name'=>'sys.reboot'),
				)
			),
			'notice' => array(
				'upgrade' => array(
					"type"=> "firmware",
					"version"=> "tiny\/0.1.1",
					"description"=> "改进了xxx",
					"web"=> "http://baidu.com",
					"url"=> "http://192.168.17.10/superwrt-qca-ar934x-tiny.spkg",
					"sha1"=> "8aa1a20ecdc53e3fbd7c00088eba6fadc790aa14",
				),
			),
			'upgrade' => array(
		         "firmware"=> array(//Write to config file
		             "recovery"=>array(
		                 "url"=>"http://superwrt.com/superwrt-qca-ar934x-tiny.spkg",
		                 "sha1"=>"8aa1a20ecdc53e3fbd7c00088eba6fadc790aa14"
		             ),
		              "upgrade"=>array(
		                  "url"=>"http://superwrt.com/superwrt-qca-ar934x-tiny.spkg",
		                  "sha1"=>"8aa1a20ecdc53e3fbd7c00088eba6fadc790aa14"
		              ),
		         )
			)
		));
*/

}