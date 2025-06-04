<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTourDatesForTimeSlots extends Migration
{
    public function up()
    {
        Schema::table('bravo_tour_dates', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('end_date');
            $table->integer('time_slot_id')->nullable()->after('start_time');
        });
    }

    public function down()
    {
        Schema::table('bravo_tour_dates', function (Blueprint $table) {
            $table->dropColumn('start_time');
            $table->dropColumn('time_slot_id');
        });
    }
}
