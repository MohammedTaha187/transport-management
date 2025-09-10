<?php

namespace App\Observers;

use App\Models\Trip;
use App\Filament\Resources\TripResource;
use Illuminate\Support\Facades\Cache;

class TripObserver
{
    public function creating(Trip $trip)
    {
        // Server-side overlap prevention with a short cache lock to reduce race conditions
        $lockKey = 'trip_booking_lock_' . ($trip->driver_id ?? 'na') . '_' . ($trip->vehicle_id ?? 'na');

        $lock = Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            // Could not obtain lock; fail safe and prevent creation
            throw new \Exception('Unable to obtain booking lock; please retry.');
        }

        try {
            // Re-check inside the lock/transactional window
            if (TripResource::hasOverlap('driver_id', $trip->driver_id, $trip->start_time, $trip->end_time)) {
                throw new \Exception('Driver is already booked for the selected time range.');
            }

            if (TripResource::hasOverlap('vehicle_id', $trip->vehicle_id, $trip->start_time, $trip->end_time)) {
                throw new \Exception('Vehicle is already booked for the selected time range.');
            }
        } finally {
            $lock->release();
        }
    }

    public function created(Trip $trip)
    {
        // Invalidate KPIs and topbar cache after changes
        Cache::forget('active_trips');
        Cache::forget('available_drivers');
        Cache::forget('completed_trips');
        Cache::forget('filament.active_trips_count');
    }

    public function updated(Trip $trip)
    {
        $this->created($trip);
    }

    public function deleted(Trip $trip)
    {
        $this->created($trip);
    }
}
