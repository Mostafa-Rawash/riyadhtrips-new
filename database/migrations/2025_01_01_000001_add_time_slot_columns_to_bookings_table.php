<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeSlotColumnsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bravo_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bravo_bookings', 'time_slot_id')) {
                $table->bigInteger('time_slot_id')->nullable()->after('end_date')->index();
            }
            if (!Schema::hasColumn('bravo_bookings', 'start_time')) {
                $table->time('start_time')->nullable()->after('time_slot_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bravo_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bravo_bookings', 'time_slot_id')) {
                $table->dropColumn('time_slot_id');
            }
            if (Schema::hasColumn('bravo_bookings', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });
    }
}