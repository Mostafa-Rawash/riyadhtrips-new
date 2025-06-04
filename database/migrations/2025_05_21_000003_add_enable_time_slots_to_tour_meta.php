<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnableTimeSlotsToTourMeta extends Migration
{
    public function up()
    {
        Schema::table('bravo_tour_meta', function (Blueprint $table) {
            if (!Schema::hasColumn('bravo_tour_meta', 'enable_time_slots')) {
                $table->boolean('enable_time_slots')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('bravo_tour_meta', function (Blueprint $table) {
            if (Schema::hasColumn('bravo_tour_meta', 'enable_time_slots')) {
                $table->dropColumn('enable_time_slots');
            }
        });
    }
}