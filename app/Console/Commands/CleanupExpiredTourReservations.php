<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Tour\Models\Tour;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTourReservations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tour:cleanup-reservations 
                           {--dry-run : Show what would be cleaned up without actually doing it}
                           {--force : Force cleanup even if recently run}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired temporary time slot reservations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');
        
        $this->info('Starting cleanup of expired time slot reservations...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No actual cleanup will be performed');
        }
        
        try {
            // Prevent running too frequently unless forced
            $lastRunKey = 'last_reservation_cleanup';
            $lastRun = Cache::get($lastRunKey);
            
            if (!$isForced && $lastRun && now()->diffInMinutes($lastRun) < 5) {
                $this->warn('Cleanup was run recently. Use --force to override.');
                return 0;
            }
            
            $cleaned = $this->cleanupExpiredReservations($isDryRun);
            $this->cleanupOrphanedReservations($isDryRun);
            $this->cleanupUserReservationRegistry($isDryRun);
            
            if (!$isDryRun) {
                Cache::put($lastRunKey, now(), 60 * 60); // Remember for 1 hour
            }
            
            $message = $isDryRun 
                ? "Would clean up {$cleaned} expired reservations"
                : "Successfully cleaned up {$cleaned} expired reservations";
                
            $this->info($message);
            
            // Log the cleanup activity
            Log::info('Time slot reservation cleanup completed', [
                'cleaned_count' => $cleaned,
                'dry_run' => $isDryRun,
                'forced' => $isForced
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to cleanup expired reservations: ' . $e->getMessage());
            Log::error('Time slot reservation cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Clean up expired temporary reservations
     */
    protected function cleanupExpiredReservations($isDryRun = false)
    {
        $cleaned = 0;
        
        try {
            // Use Redis pattern matching if available
            if (method_exists(Cache::store(), 'getRedis')) {
                $redis = Cache::store()->getRedis();
                $keys = $redis->keys('temp_reservation_*');
                
                $this->info("Found " . count($keys) . " reservation keys to check");
                
                foreach ($keys as $key) {
                    $reservation = Cache::get($key);
                    
                    if ($reservation && isset($reservation['expires_at'])) {
                        $expiresAt = \Carbon\Carbon::parse($reservation['expires_at']);
                        
                        if (now()->gt($expiresAt)) {
                            if ($isDryRun) {
                                $this->line("Would delete expired reservation: {$key}");
                            } else {
                                Cache::forget($key);
                            }
                            $cleaned++;
                        } else {
                            $minutesLeft = now()->diffInMinutes($expiresAt);
                            $this->line("Active reservation: {$key} (expires in {$minutesLeft} minutes)");
                        }
                    } else {
                        // Malformed reservation data
                        if ($isDryRun) {
                            $this->line("Would delete malformed reservation: {$key}");
                        } else {
                            Cache::forget($key);
                        }
                        $cleaned++;
                    }
                }
            } else {
                $this->warn('Redis not available - using fallback cleanup method');
                $cleaned = $this->cleanupWithoutRedis($isDryRun);
            }
            
        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            throw $e;
        }
        
        return $cleaned;
    }
    
    /**
     * Fallback cleanup method for non-Redis cache drivers
     */
    protected function cleanupWithoutRedis($isDryRun = false)
    {
        // This is less efficient but works with other cache drivers
        $cleaned = 0;
        
        // Try to get a registry of reservation keys (if implemented)
        $registryPattern = 'temp_reservation_registry_*';
        
        // For now, we'll rely on the scheduled cleanup in the Tour model
        // which is called automatically during availability checks
        
        $this->warn('Non-Redis cache detected. Relying on automatic cleanup during booking checks.');
        
        return $cleaned;
    }
    
    /**
     * Clean up orphaned reservation registries
     */
    protected function cleanupOrphanedReservations($isDryRun = false)
    {
        try {
            if (method_exists(Cache::store(), 'getRedis')) {
                $redis = Cache::store()->getRedis();
                $registryKeys = $redis->keys('user_reservations_*');
                
                foreach ($registryKeys as $registryKey) {
                    $reservationKeys = Cache::get($registryKey, []);
                    $activeKeys = [];
                    
                    foreach ($reservationKeys as $reservationKey) {
                        if (Cache::has($reservationKey)) {
                            $activeKeys[] = $reservationKey;
                        }
                    }
                    
                    if (count($activeKeys) !== count($reservationKeys)) {
                        if ($isDryRun) {
                            $this->line("Would update registry: {$registryKey}");
                        } else {
                            if (empty($activeKeys)) {
                                Cache::forget($registryKey);
                            } else {
                                Cache::put($registryKey, $activeKeys, 60 * 60);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not cleanup reservation registries: ' . $e->getMessage());
        }
    }
    
    /**
     * Clean up user reservation registry
     */
    protected function cleanupUserReservationRegistry($isDryRun = false)
    {
        // Clean up old draft bookings that might be clogging the system
        try {
            $cutoffTime = now()->subHours(2); // Remove drafts older than 2 hours
            
            $query = \Modules\Booking\Models\Booking::where('status', 'draft')
                ->where('object_model', 'tour')
                ->where('created_at', '<', $cutoffTime)
                ->whereNotNull('time_slot_id');
                
            $count = $query->count();
            
            if ($count > 0) {
                if ($isDryRun) {
                    $this->line("Would delete {$count} old draft bookings");
                } else {
                    $query->delete();
                    $this->info("Deleted {$count} old draft bookings");
                }
            }
            
        } catch (\Exception $e) {
            $this->warn('Could not cleanup old draft bookings: ' . $e->getMessage());
        }
    }
}
