<?php

namespace App\Http\Controllers\Opwifi\Device;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Storage, Log, File;

use App\Models\OwDevices;
use App\Models\OwDevicemeta;
use App\Models\OwDevConfigs;
use App\Models\OwDevFirmwares;
use App\Models\OwSystem;

use App\Http\Middleware\HttpSignatures;

class DeviceServController extends Controller {

	private $metaId = 0;

	private $isFit, $devParam;

	private $enCrypted = array();

	private function getAesKey($req) {
		if (isset($req['crypto']) && isset($req['crypto']['key'])) {
			$key = $req['crypto']['key'];
			if (isset($key['type']) && $key['type'] == 'aes_128_cbc') {
				$aesKey = null;
				if (isset($key['key_rsa'])){
					$privKey = HttpSignatures::getPrivKey();
					if ($privKey) {
						if (openssl_private_decrypt(base64_decode($key['key_rsa']), $aesKey, $privKey))
							return $aesKey;
					}
				}
			}
		}
		return null;
	}

	private function encryptoCtx($req, &$rep, $data) {
		$aesKey = $this->getAesKey($req);
		if (!$aesKey)
			return false;
		$iv = str_random(16);
		$j = json_encode($data);
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
		if (isset($req['crypto']['data'])) {
			$d = $req['crypto']['data'];
			if (isset($d['iv']) && isset($d['ctx'])) {
				$ctx = base64_decode($d['ctx']);
				return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aesKey, $ctx, MCRYPT_MODE_CBC, $d['iv']));
			}
		}
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

	private function updateDevInfo($req, $ip) {
		if (!isset($req['mac'])) {
			return false;
		}

		Log::info("Update Device Info:".var_export($req, true));

		if (isset($req['mode']) && $req['mode']=='fit')
			$this->isFit = true;
		$this->devParam = isset($req['param'])?$req['param']:[];

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

		$res = OwDevices::where('mac', $req['mac'])->first();
		if ($res) {
			$resm = $res->meta()->first();
			if ($resm) {
				$resm->update($meta);
			} else {
				$resm = $res->meta()->updateOrCreate($meta);
			}
			$this->metaId = $resm['id'];
		} else {
			$dev = ['mac' => $req['mac']];
			$res = OwDevices::create($dev);
			$resm = $res->meta()->updateOrCreate($meta);
			$this->metaId = $resm['id'];
		}
		return $this->metaId;
	}

	private function appendOperation($req, &$rep) {
		if (!$this->metaId) return;
		$res = OwDevicemeta::where('id', $this->metaId)->first();
		if (!$res) return;

		$reboot = false;

		/* For test */
		/* end */

		if ($this->isFit &&
				$res['op_config_id']) {//Only support Fit now!
			$cfg = $res->config()->first();
			if ($cfg) {
				if (isset($this->devParam['fit_config_md5'])) {
					if ($this->devParam['fit_config_md5'] != $cfg['md5']) {
						//先按简单方式，重启设备，重新加载。
						$reboot = true;
					}
				} else if ($cfg['config']){
					$this->enCrypted['config'] = array(
						'set' => json_decode($cfg['config']),
					);
					$this->enCrypted['param'] = array(
						'fit_config_md5' => $cfg['md5'],
					);
				}
			}
		}

		if ($res['op_reboot'] || $reboot) {
			if (!isset($rep['config'])) $rep['config'] = array();
			if (!isset($rep['config']['task'])) $rep['config']['task'] = array();
			$rep['config']['task'][] = array('name'=>'sys.reboot');
			$res->update(['op_reboot'=>false]);
			return;
		}

		if ($res['op_upgrade_id']){
			$fw = $res->upgrade()->first();
			if ($fw && $res['m_fullver'] != $fw['version']) {
				$rep['upgrade'] = array(
					"firmware"=> [
						"upgrade"=> [
							"url"=>OwSystem::getValue('site_url').$fw['url'],
							"sha1"=>$fw['sha1'],
						]
					]
				);
			} else {
				$res->update(['op_upgrade_id'=>null]);
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

		$this->appendOperation($req, $rep);
		$this->appendEncrypted($req, $rep);

		Log::debug("rep:".var_export($rep, true));
		return response()->json($rep);
	}

	// 	命令示例：
	// 		$this->encryptoCtx($req, $rep,array(
	// 			'config'=>array(
	// 				'set'=> array(
	// 					'wlan'=> array(
	// 						'vap'=>array(
	// 					 		array('name'=>'wlan0', 'ssid'=>'Hi'),
	//							array('_o'=>'add','name'=>'wlan10', 'radio'=>'phy0','ssid'=>'Hi', 'enable'=>true, 'vlan'=>10, 'mode'=>'ap',"auth"=>"open"),
	//							array('_o'=>'del','name'=>'wlan10')
	// 					 	 )
	// 					 )
	//				),
	// 				'get'=>true,
	// 				'task'=>array(
	// 				 	array('name'=>'sys.reboot'),
	// 				)
	// 			),
	//			'notice' => array(
	// 				'upgrade' => array(
	// 					"type"=> "firmware",
	// 					"version"=> "tiny\/0.1.1",
	// 					"description"=> "改进了xxx",
	// 					"web"=> "http://baidu.com",
	// 					"url"=> "http://192.168.17.10/superwrt-qca-ar934x-tiny.spkg",
	// 					"sha1"=> "8aa1a20ecdc53e3fbd7c00088eba6fadc790aa14",
	// 				),
	//			),
	// 			'upgrade' => array(
	// 		         "firmware"=> array(//Write to config file
	// 		             "recovery"=>array(
	// 		                 "url"=>"http://superwrt.com/superwrt-qca-ar934x-tiny.spkg",
	// 		                 "sha1"=>"8aa1a20ecdc53e3fbd7c00088eba6fadc790aa14"
	// 		             ),
	// 		              "upgrade"=>array(
	// 		                  "url"=>"http://superwrt.com/superwrt-qca-ar934x-tiny.spkg",
	// 		                  "sha1"=>"8aa1a20ecdc53e3fbd7c00088eba6fadc790aa14"
	// 		              ),
	// 		         )
	// 			)
	// 		));

}