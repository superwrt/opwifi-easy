<?php
    $menu = [
		['name' => 'home', 'string' => '首页', 'url' => URL::route('opwifi::home'), 'iconClass' => 'glyphicon glyphicon-home'],
		['name' => 'devices', 'string' => '设备', 'url' => '#', 'iconClass' => 'glyphicon glyphicon-hdd', 'sub' => [
				['name' => 'device_mng', 'string' => '设备管理', 'url' => URL::route('opwifi::device.management') ],
				['name' => 'device_fw', 'string' => '设备固件', 'url' => URL::route('opwifi::device.firmware') ],
				['name' => 'device_cfg', 'string' => '配置管理', 'url' => URL::route('opwifi::device.config') ],
			] ],
		['name' => 'webportal', 'string' => '网页认证', 'url' => '#', 'iconClass' => 'glyphicon glyphicon-log-in', 'sub' => [
				['name' => 'device_cfg', 'string' => '配置管理', 'url' => URL::route('opwifi::webportal.config') ],
				['name' => 'user_mng', 'string' => '用户管理', 'url' => URL::route('opwifi::webportal.user') ],
				['name' => 'device_mng', 'string' => '设备管理', 'url' => URL::route('opwifi::webportal.device.management') ],
				['name' => 'device_st', 'string' => '设备状态', 'url' => URL::route('opwifi::webportal.device.status') ],
				['name' => 'station_st', 'string' => '终端状态', 'url' => URL::route('opwifi::webportal.station.status') ],
			] ],
		['name' => 'stations', 'string' => '终端', 'url' => '#', 'iconClass' => 'glyphicon glyphicon-phone', 'sub' => [
				['name' => 'station_mng', 'string' => '终端管理', 'url' => URL::route('opwifi::station.management') ],
			] ],
		['name' => 'system', 'string' => '系统', 'url' => '#', 'iconClass' => 'glyphicon glyphicon-cog','sub' => [
				['name' => 'config', 'string' => '系统配置', 'url' => URL::route('opwifi::system.config') ],
				['name' => 'user', 'string' => '用户管理', 'url' => URL::route('opwifi::system.user') ],
				['name' => 'status', 'string' => '系统状态', 'url' => URL::route('opwifi::system.status') ],
				['name' => 'about', 'string' => '关于', 'url' => URL::route('opwifi::system.about') ],
			] ]
	];
?>