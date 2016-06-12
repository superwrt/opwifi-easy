<?php

namespace App\Http\Helpers\Install;

use Exception;
use Illuminate\Support\Facades\Artisan;

class DatabaseManager
{

    /**
     * Migrate and seed the database.
     *
     * @return array
     */
    public function migrateAndSeed()
    {
        return $this->migrate();
    }

    /**
     * Run the migration and call the seeder.
     *
     * @return array
     */
    private function migrate()
    {
        try{
            Artisan::call('migrate');
        }
        catch(Exception $e){
            return $this->response($e->getMessage());
        }

        return $this->seed();
    }

    /**
     * Seed the database.
     *
     * @return array
     */
    private function seed()
    {
        try{
            Artisan::call('db:seed');
        }
        catch(Exception $e){
            return $e->getMessage();
        }

        return trans('installer.final.finished');
    }

    /**
     * Return a formatted error messages.
     *
     * @param $message
     * @param string $status
     * @return array
     */
    private function response($message, $status = 'danger')
    {
        return array(
            'status' => $status,
            'message' => $message
        );
    }
}