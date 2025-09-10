<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class KpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_calculations()
    {
        $company = Company::create(['name' => 'Test Co']);
        $drivers = collect([
            Driver::create(['name' => 'D1', 'company_id' => $company->id, 'license_number' => 1111]),
            Driver::create(['name' => 'D2', 'company_id' => $company->id, 'license_number' => 2222]),
        ]);

        $vehicles = collect([
            Vehicle::create(['model' => 'V1', 'plate_number' => 1111, 'company_id' => $company->id]),
            Vehicle::create(['model' => 'V2', 'plate_number' => 2222, 'company_id' => $company->id]),
        ]);

        // active trip now
        Trip::create([
            'company_id' => $company->id,
            'driver_id' => $drivers[0]->id,
            'vehicle_id' => $vehicles[0]->id,
            'start_time' => Carbon::now()->subHour()->toDateString(),
            // ensure end_time is after now by using next day
            'end_time' => Carbon::now()->addDay()->toDateString(),
            'status' => 'active',
        ]);

        // completed trip this month
        Trip::create([
            'company_id' => $company->id,
            'driver_id' => $drivers[1]->id,
            'vehicle_id' => $vehicles[1]->id,
            'start_time' => Carbon::now()->subDays(5)->toDateString(),
            'end_time' => Carbon::now()->subDays(4)->toDateString(),
            'status' => 'completed',
        ]);

        // Clear cache to ensure fresh KPI calculations, then instantiate widget
        Cache::flush();
        $widget = new \App\Filament\Widgets\DashboardStats();
        $stats = $widget->getStats();

        // Stat objects provide getValue() method
        $active = collect($stats)->firstWhere(fn ($s) => str_contains($s->getLabel(), 'Active Trips Now'));
        $availableDrivers = collect($stats)->firstWhere(fn ($s) => str_contains($s->getLabel(), 'Available Drivers'));
        $completed = collect($stats)->firstWhere(fn ($s) => str_contains($s->getLabel(), 'Completed Trips'));

        $this->assertEquals(1, $active->getValue());
        $this->assertEquals(1, $completed->getValue());
        $this->assertEquals(1, $availableDrivers->getValue());
    }
}
