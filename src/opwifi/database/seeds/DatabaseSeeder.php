<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserTableSeeder::class);
        DB::table('ow_users')->insert(
            [
                [
                    'username' => 'admin',
                    'password' => Hash::make('admin'),
                    'email'  => 'your@email.com',
                    'timezone' => 'Asia/Shanghai'
                ]
            ]);

        DB::table('ow_system')->insert(
            [
                [
                    'name' => 'site_url',
                    'value' => 'http://localhost'
                ],
                [
                    'name' => '_version_raw',
                    'value' => config('opwifi.version_raw')
                ]
            ]);

        Model::reguard();
    }
}
