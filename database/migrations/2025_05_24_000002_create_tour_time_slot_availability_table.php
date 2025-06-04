<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourTimeSlotAvailabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bravo_tour_time_slot_availability')) {
            Schema::create('bravo_tour_time_slot_availability', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('time_slot_id');
                $table->date('date');
                $table->integer('booked_guests')->default(0);
                $table->integer('remaining_capacity')->default(0);
                $table->boolean('is_sold_out')->default(false);
                $table->timestamp('last_updated')->nullable();
                $table->timestamps();
                
                $table->foreign('time_slot_id')->references('id')->on('bravo_tour_time_slots')->onDelete('cascade');
                $table->unique(['time_slot_id', 'date']);
                $table->index(['date', 'is_sold_out']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bravo_tour_time_slot_availability');
    }
}
