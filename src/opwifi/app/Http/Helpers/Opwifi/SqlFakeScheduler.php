<?php

namespace App\Http\Helpers\Opwifi;

use Exception, DB, Log;

class SqlFakeScheduler
{
	static private $hasSqlScheduler = false;

	static public function getRules() {
		return [
        ['name'=>'owOutDevOnline', 'comment'=>'Update Online status', 'every'=>'10 MINUTE', 'sql'=>'
UPDATE `ow_devicemeta` SET `online` =\'0\', `updated_at` = CURRENT_TIMESTAMP WHERE `online` =\'1\' AND TIMESTAMPDIFF(MINUTE, lastshow, CURRENT_TIMESTAMP) > 20;
UPDATE `ow_webportal_devices` SET `online` =\'0\', `updated_at` = CURRENT_TIMESTAMP WHERE `online` =\'1\' AND  TIMESTAMPDIFF(MINUTE, lastshow, CURRENT_TIMESTAMP) > 10;
'],
        ['name'=>'owCleaner', 'comment'=>'Clean unneed resource', 'every'=>'1 DAY', 'start'=>'2000-01-01 02:30:00', 'sql'=>'
DELETE FROM `ow_webportal_tokens` WHERE TIMESTAMPDIFF(DAY, updated_at, CURRENT_TIMESTAMP) > 1;
DELETE FROM `ow_webportal_station_status` WHERE TIMESTAMPDIFF(MONTH, updated_at, CURRENT_TIMESTAMP) > 1;
'],
    	];
	}

	static private function check()
	{
		if (self::$hasSqlScheduler) {
			return true;
		}
		
        $es = DB::select('SELECT @@event_scheduler');
        if (count($es)) {
        	$es = (array)$es[0];
    		if ($es['@@event_scheduler'] == 'ON') {
	        	self::$hasSqlScheduler = true;
	            return true;
	        }
        }
        return false;
	}

	static private function schedule()
	{
		try {
			$str = file_get_contents(storage_path('sqlScheduler'));
			$arr = unserialize($str);
		} catch (\Exception $e) {
			$arr = [];
		}

		$ss = self::getRules();
		foreach ($ss as $s) {
			$done = isset($arr[$s['name']])?$arr[$s['name']]:0;
			$last = strtotime(strtolower($s['every']).' ago');
			if ($last < $done) {
				continue;
			}
			try {
				DB::unprepared($s['sql']);
			} catch (\Exception $e) {
				Log::error($e->getMessage());
			}
			$arr[$s['name']] = time();
		}
		
		$str = serialize($arr);
		file_put_contents(storage_path('sqlScheduler'), $str);
	}

	static public function update()
	{
		if (!self::check()) {
			self::schedule();
		}
	}
}
