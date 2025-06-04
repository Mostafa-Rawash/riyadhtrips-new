<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAndEnhanceTourTimeSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the main time slots table with all enhanced features
        if (!Schema::hasTable('bravo_tour_time_slots')) {
            Schema::create('bravo_tour_time_slots', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('tour_id')->index();
                $table->tinyInteger('day_of_week')->comment('1: Monday, 7: Sunday');
                $table->time('start_time');
                $table->time('end_time')->nullable()->comment('Optional end time for display');
                $table->integer('max_guests')->default(0);
                $table->decimal('price_modifier', 8, 2)->default(0)->comment('Price addition/reduction for this slot');
                $table->text('description')->nullable()->comment('Slot-specific description');
                $table->integer('sort_order')->default(0)->comment('Custom ordering');
                $table->integer('booking_cutoff_hours')->default(2)->comment('Hours before slot when booking closes');
                $table->tinyInteger('active')->default(1);
                $table->timestamps();
                
                // Create indexes for performance
                $table->index(['tour_id', 'day_of_week', 'active']);
                $table->index(['tour_id', 'start_time']);
                $table->index(['day_of_week', 'active']);
            });
        } else {
            // If table exists, add missing columns
            Schema::table('bravo_tour_time_slots', function (Blueprint $table) {
                if (!Schema::hasColumn('bravo_tour_time_slots', 'end_time')) {
                    $table->time('end_time')->nullable()->after('start_time')->comment('Optional end time for display');
                }
                if (!Schema::hasColumn('bravo_tour_time_slots', 'price_modifier')) {
                    $table->decimal('price_modifier', 8, 2)->default(0)->after('max_guests')->comment('Price addition/reduction for this slot');
                }
                if (!Schema::hasColumn('bravo_tour_time_slots', 'description')) {
                    $table->text('description')->nullable()->after('price_modifier')->comment('Slot-specific description');
                }
                if (!Schema::hasColumn('bravo_tour_time_slots', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('description')->comment('Custom ordering');
                }
                if (!Schema::hasColumn('bravo_tour_time_slots', 'booking_cutoff_hours')) {
                    $table->integer('booking_cutoff_hours')->default(2)->after('sort_order')->comment('Hours before slot when booking closes');
                }
            });
        }

        // Create time slot availability cache table for performance
        if (!Schema::hasTable('bravo_tour_time_slot_availability')) {
            Schema::create('bravo_tour_time_slot_availability', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('time_slot_id')->index();
                $table->date('date')->index();
                $table->integer('booked_guests')->default(0);
                $table->integer('remaining_capacity')->default(0);
                $table->boolean('is_sold_out')->default(false);
                $table->timestamp('last_updated')->useCurrent();
                $table->timestamps();
                
                // Unique constraint to prevent duplicates
                $table->unique(['time_slot_id', 'date'], 'unique_slot_date');
                
                // Foreign key constraint
                $table->foreign('time_slot_id', 'fk_availability_slot')
                      ->references('id')->on('bravo_tour_time_slots')
                      ->onDelete('cascade');
                      
                // Additional indexes for performance
                $table->index(['date', 'is_sold_out']);
                $table->index('last_updated');
            });
        }

        // Create time slot booking history table for tracking
        if (!Schema::hasTable('bravo_tour_time_slot_bookings')) {
            Schema::create('bravo_tour_time_slot_bookings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('booking_id')->index();
                $table->bigInteger('time_slot_id')->index();
                $table->date('booking_date');
                $table->integer('guests')->default(1);
                $table->enum('status', ['active', 'cancelled', 'completed', 'refunded'])->default('active');
                $table->json('metadata')->nullable()->comment('Additional booking metadata');
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('booking_id', 'fk_slot_booking_id')
                      ->references('id')->on('bravo_bookings')
                      ->onDelete('cascade');
                      
                $table->foreign('time_slot_id', 'fk_slot_booking_slot')
                      ->references('id')->on('bravo_tour_time_slots')
                      ->onDelete('cascade');
                      
                // Additional indexes for analytics
                $table->index(['booking_date', 'status']);
                $table->index(['time_slot_id', 'booking_date', 'status']);
                $table->index('status');
            });
        }
        
        // Add time_slot_id and start_time to bookings table if they don't exist
        if (Schema::hasTable('bravo_bookings')) {
            Schema::table('bravo_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bravo_bookings', 'time_slot_id')) {
                    $table->bigInteger('time_slot_id')->nullable()->after('end_date')->index();
                }
                if (!Schema::hasColumn('bravo_bookings', 'start_time')) {
                    $table->time('start_time')->nullable()->after('time_slot_id');
                }
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
        // Drop foreign key constraints first
        if (Schema::hasTable('bravo_tour_time_slot_bookings')) {
            Schema::table('bravo_tour_time_slot_bookings', function (Blueprint $table) {
                $table->dropForeign('fk_slot_booking_id');
                $table->dropForeign('fk_slot_booking_slot');
            });
        }
        
        if (Schema::hasTable('bravo_tour_time_slot_availability')) {
            Schema::table('bravo_tour_time_slot_availability', function (Blueprint $table) {
                $table->dropForeign('fk_availability_slot');
            });
        }
        
        // Drop tables in reverse order
        Schema::dropIfExists('bravo_tour_time_slot_bookings');
        Schema::dropIfExists('bravo_tour_time_slot_availability');
        Schema::dropIfExists('bravo_tour_time_slots');
        
        // Remove columns from bookings table
        if (Schema::hasTable('bravo_bookings')) {
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
}
