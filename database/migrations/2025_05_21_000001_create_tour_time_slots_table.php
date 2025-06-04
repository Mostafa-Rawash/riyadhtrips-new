<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourTimeSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('bravo_tour_time_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tour_id'); // Foreign key to bravo_tours
            $table->integer('day_of_week'); // 1-7 for Monday-Sunday
            $table->time('start_time'); // Time slot start (e.g., 09:00:00)
            $table->integer('max_guests')->default(0); // Max guests for this time slot
            $table->tinyInteger('active')->default(1); // Whether this time slot is active
            $table->integer('create_user')->nullable(); // User ID who created this time slot
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['tour_id', 'day_of_week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bravo_tour_time_slots');
    }
}
