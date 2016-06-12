<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use App\Http\Helpers\Install\EnvironmentManager;
use App\Http\Helpers\Install\RequirementsChecker;
use App\Http\Helpers\Install\PermissionsChecker;
use App\Http\Helpers\Install\DatabaseManager;

class InstallController extends Controller
{

    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @var RequirementsChecker
     */
    protected $requirements;

    /**
     * @var PermissionsChecker
     */
    protected $permissions;

    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager,
    	RequirementsChecker $rChecker,
    	PermissionsChecker $pChecker,
    	DatabaseManager $databaseManager)
    {
        $this->EnvironmentManager = $environmentManager;
        $this->requirements = $rChecker;
        $this->permissions = $pChecker;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Display the installer welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('installer.welcome');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function getEnvironment()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('installer.environment', compact('envConfig'));
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function getConfigs()
    {
        return view('installer.configs', ['configs'=>[
                ['title'=>'MYSQL地址', 'name'=>'DB_HOST', 'value'=>'127.0.0.1'],
                ['title'=>'MYSQL库名', 'name'=>'DB_DATABASE', 'value'=>'opwifi'],
                ['title'=>'MYSQL用户名', 'name'=>'DB_USERNAME', 'value'=>'opwifi'],
                ['title'=>'MYSQL密码', 'name'=>'DB_PASSWORD', 'value'=>'opwifi'],
            ]]);
    }


    /**
     * Processes the newly saved environment configuration and redirects back.
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postConfigsSave(Request $request)
    {
        $input = $request->only(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD']);

        $input['APP_DEBUG'] = 'false';
        $input['APP_KEY'] = str_random(32);

        $this->EnvironmentManager->modifyEnv($input);

        return redirect()->route('Installer::requirements');
    }

    /**
     * Processes the newly saved environment configuration and redirects back.
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEnvironmentSave(Request $input, Redirector $redirect)
    {
        $message = $this->EnvironmentManager->saveFile($input);

        return $redirect->route('Installer::environment')
                        ->with(['message' => $message]);
    }

    /**
     * Display the requirements page.
     *
     * @return \Illuminate\View\View
     */
    public function getRequirements()
    {
        $requirements = $this->requirements->check(
            config('installer.requirements')
        );

        return view('installer.requirements', compact('requirements'));
    }

    /**
     * Display the permissions check page.
     *
     * @return \Illuminate\View\View
     */
    public function getPermissions()
    {
        $permissions = $this->permissions->check(
            config('installer.permissions')
        );

        return view('installer.permissions', compact('permissions'));
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function getDatabase()
    {
        $msg = $this->databaseManager->migrateAndSeed();

        return view('installer.finished', ['message' => $msg]);
        // return redirect()->route('Installer::final')
        //                  ->with(['message' => $response]);
    }

    /**
     * Update installed file and display finished view.
     *
     * @param InstalledFileManager $fileManager
     * @return \Illuminate\View\View
     */
    public function getFinal()
    {
        file_put_contents(storage_path('installed'), '');

        return redirect('/');
    }
}
