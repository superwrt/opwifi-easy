<?php

namespace App\Http\Helpers\Install;

use Exception, Log;
use Illuminate\Http\Request;

class EnvironmentManager
{
    /**
     * @var string
     */
    private $envPath;

    /**
     * @var string
     */
    private $envExamplePath;

    /**
     * Set the .env and .env.example paths.
     */
    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
    }

    /**
     * Get the content of the .env file.
     *
     * @return string
     */
    public function getEnvContent()
    {
        if (!file_exists($this->envPath)) {
            if (file_exists($this->envExamplePath)) {
                copy($this->envExamplePath, $this->envPath);
            } else {
                touch($this->envPath);
            }
        }

        return file_get_contents($this->envPath);
    }

    /**
     * Save the edited content to the file.
     *
     * @param Request $input
     * @return string
     */
    public function saveFile(Request $input)
    {
        $message = trans('installer.environment.success');

        try {
            file_put_contents($this->envPath, $input->get('envConfig'));
        }
        catch(Exception $e) {
            $message = trans('installer.environment.errors');
        }

        return $message;
    }

    public function modifyEnv(array $data)
    {
        $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';
     
        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));
     
        $contentArray->transform(function ($item) use ($data){
             foreach ($data as $key => $value){
                 if(str_contains($item, $key)){
                     return $key . '=' . $value;
                 }
             }
     
             return $item;
         });

        $content = implode($contentArray->toArray(), "\n");

        file_put_contents($envPath, $content);
    }
}