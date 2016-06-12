<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/m', ['namespace' => 'Opwifi\Device', 'middleware' => 'auth',
        'as' => 'opwifi::home',
        function () { return view('opwifi.home');}
    ]);

Route::controller('m/auth', 'Auth\AuthController');

Route::group(['prefix' => 'm/device', 'namespace' => 'Opwifi\Device',
    'as' => 'opwifi::', 'middleware' => 'auth'], function()
{
    Route::controller('management', 'MangementController', ['getIndex' => 'device.management']);
    Route::controller('firmware', 'FirmwareController', ['getIndex' => 'device.firmware']);
    Route::controller('config', 'ConfigController', ['getIndex' => 'device.config']);
});

Route::group(['prefix' => 'm/station', 'namespace' => 'Opwifi\Station',
    'as' => 'opwifi::', 'middleware' => 'auth'], function()
{
    Route::controller('/', 'StationController', [
        'getManagement' => 'station.management',
        'getStatus' => 'station.status',
        ]);
});

Route::group(['prefix' => 'm/webportal', 'namespace' => 'Opwifi\Webportal',
	'as' => 'opwifi::', 'middleware' => 'auth'], function()
{
    Route::controller('config', 'ConfigController', ['getIndex' => 'webportal.config']);
    Route::controller('user', 'UserController', ['getIndex' => 'webportal.user']);
    Route::controller('device/management', 'DeviceController', ['getIndex' => 'webportal.device.management']);
    Route::controller('device/status', 'DeviceStatusController', ['getIndex' => 'webportal.device.status']);
    Route::controller('station/status', 'StationStatusController', ['getIndex' => 'webportal.station.status']);
});

Route::group(['prefix' => 'm/system', 'namespace' => 'Opwifi\System',
    'as' => 'opwifi::', 'middleware' => 'auth'], function()
{
    Route::controller('config', 'ConfigController', ['getIndex' => 'system.config']);
    Route::controller('user', 'UserController', ['getIndex' => 'system.user']);
    Route::controller('status', 'StatusController', ['getIndex' => 'system.status']);
    Route::controller('about', 'AboutController', ['getIndex' => 'system.about']);
});

Route::group(['prefix' => 's/dev', 'namespace' => 'Opwifi\Device', 'middleware' => 'sign'], function()
{
    Route::post('1/reg', 'DeviceServController@postReg');
});
Route::group(['prefix' => 's/webp', 'namespace' => 'Opwifi\Webportal'], function()
{
    Route::post('1/ctrl', 'WebportalServController@postCtrl');
});


Route::group(['prefix' => 'webportal', 'namespace' => 'Opwifi\Webportal',
    'as' => 'opwifi::' ], function()
{
    Route::controller('/', 'WebportalWebController');
});


Route::group(['prefix' => 'install', 'as' => 'Installer::', 'middleware' => 'canInstall'], function()
{
    Route::controller('/', 'InstallController', [
            'getConfigs' => 'configs',
            'postConfigsSave' => 'configsSave',
            'getEnvironment' => 'environment',
            'postEnvironmentSave' => 'environmentSave',
            'getRequirements' => 'requirements',
            'getPermissions' => 'permissions',
            'getDatabase' => 'database',
            'getFinal' => 'final',
        ]);
});
