<?php

namespace App\Http\Helpers\Opwifi;

use App\Models\OwDevices;
use App\Models\OwDevicemeta;
use App\Models\OwDevConfigs;

class DeviceConfigApply
{
	private $devMeta;

	private function settingConvert($path, $cfg) {
		switch($path) {
		case '/wlan':
			if (count($cfg)) {
				$set = [
					'radio' => [],
					'vap' => []
				];
				if (isset($cfg['_0'], $cfg['_o'])) {
					$set['vap'] = ['_0'=>[], '_o'=>$cfg['_o']];
					$vaps = &$set['vap']['_0'];
					$wlans = $cfg['_0'];
				} else {
					$vaps = &$set['vap'];
					$wlans = $cfg;
				}
				foreach ($wlans as $k => $v) {
					if (!isset($v['name']))
						continue;
					if (isset($v['vaps'])) {
						foreach ($v['vaps'] as $vk => $vv) {

							$vaps[] = array_merge($vv,
									['radio' => $v['name']]);
						}
						unset($v['vaps']);
					}
					$set['radio'][] = $v;
				}
				return $set;
			}
		}
		return $cfg;
	}

	private function getSetting($path, $cfg) {
		$s = array();
		foreach ($cfg as $k => $v) {
			if (!(isset($v['v']) && (!isset($v['c']) || $v['c']))) { 
				continue;
			}
			$subPath = (strlen($path)>1?$path.'.':$path).$k;
			if (isset($v['m']) &&
					$v['m'] == 'replace' &&
					is_array($v['v'])) {
				$r = array(
						'_o' => 'replace',
						'_0' => $this->getSetting($subPath, $v['v'])
					);
			} else if (is_array($v['v'])) {
				$r = $this->getSetting($subPath, $v['v']);
			} else {
				$r = $v['v'];
			}
			$r = $this->settingConvert($subPath, $r);
			if ($r)
				$s[$k] = $r;
		}
		return $s;
	}

	static public function devmeta($meta) {
		$s = new self;
		$s->devMeta = $meta;
		return $s;
	}

	public function check($sha1) {
		$meta = $this->devMeta;

		$cfg = $meta->config()->first();
		if ($cfg && $cfg->pdata) {
			$pdata = json_decode($cfg->pdata, true);
			if (isset($pdata['config'])) {
				$config = $pdata['config'];
			}
		}
		
		if ($config && $meta &&
				$sha1 != $meta->op_configed_sha1) {
			$setting = $this->getSetting('/', $config);
			if (count($setting) > 0) {
				return $setting;
			} else {
				$meta->update(['op_configed_sha1' => $sha1]);
			}
		}
		return null;
	}

	public function update($sha1) {
		$this->devMeta->update(['op_configed_sha1' => $sha1]);
	}
}