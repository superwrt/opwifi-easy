<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

use App\Http\Helpers\Opwifi\SqlFakeScheduler;

class CreateOwEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::unprepared('SET GLOBAL event_scheduler = ON;');
        } catch (\Exception $e) {};

        $ss = SqlFakeScheduler::getRules();
        foreach ($ss as $s) {
            DB::unprepared('
CREATE EVENT `'.$s['name'].'`
ON SCHEDULE EVERY '.$s['every'].' '.(isset($s['start'])?'STARTS \''.$s['start'].'\'':'').'
ON COMPLETION PRESERVE ENABLE
'.(isset($s['comment'])?'COMMENT \''.$s['comment'].'\'':'').'
DO BEGIN '.$s['sql'].' END;');
        }

        try {
            /* When mysql enable binlog, need SUPER privilege. Skip, let script do it! */
            DB::unprepared('
CREATE TRIGGER `owNewDevice` AFTER INSERT ON  `ow_devices` 
FOR EACH
ROW BEGIN
INSERT INTO ow_devicemeta( dev_id, created_at, updated_at ) VALUES (NEW.id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO ow_webportal_devices( dev_id, created_at, updated_at ) VALUES (NEW.id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
END
            ');

            DB::unprepared('
CREATE TRIGGER `owNewStation` AFTER INSERT ON  `ow_stations` 
FOR EACH
ROW BEGIN
INSERT INTO ow_stationmeta( sta_id, created_at, updated_at ) VALUES (NEW.id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
END
            ');
        } catch (\Exception $e) {};

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DELETE TRIGGER owNewStation');
        DB::unprepared('DELETE TRIGGER owNewDevice');
        $ss = SqlFakeScheduler::getRules();
        foreach ($ss as $s) {
            DB::unprepared('DELETE EVENT `'.$s['name'].'`;');
        }
    }
}
