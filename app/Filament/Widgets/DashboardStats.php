<?php

namespace App\Filament\Widgets;

use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active Trips Now', Cache::remember('active_trips', 300, function () {
                return Trip::where('status', 'active')
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->count();
            })),

            Stat::make('Available Drivers', Cache::remember('available_drivers', 300, function () {
                $busyDrivers = Trip::where('status', 'active')
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->pluck('driver_id');

                return Driver::whereNotIn('id', $busyDrivers)->count();
            })),

            Stat::make('Completed Trips (This Month)', Cache::remember('completed_trips', 300, function () {
                return Trip::where('status', 'completed')
                    ->whereMonth('end_time', now()->month)
                    ->count();
            })),
        ];
    }
}
