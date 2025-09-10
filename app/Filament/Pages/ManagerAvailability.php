<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Pages\Page;

class ManagerAvailability extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static string $view = 'filament.pages.manager-availability';

    public ?string $start_time = null;
    public ?string $end_time = null;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\DateTimePicker::make('start_time')->required(),
            Forms\Components\DateTimePicker::make('end_time')->required(),
        ];
    }

    public function getAvailableDrivers()
    {
        if (!$this->start_time || !$this->end_time) {
            return [];
        }

        $busyDrivers = Trip::where(function ($query) {
            $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                  ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                  ->orWhere(function ($q2) {
                      $q2->where('start_time', '<=', $this->start_time)
                         ->where('end_time', '>=', $this->end_time);
                  });
        })->pluck('driver_id');

        return Driver::whereNotIn('id', $busyDrivers)->get();
    }

    public function getAvailableVehicles()
    {
        if (!$this->start_time || !$this->end_time) {
            return [];
        }

        $busyVehicles = Trip::where(function ($query) {
            $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                  ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                  ->orWhere(function ($q2) {
                      $q2->where('start_time', '<=', $this->start_time)
                         ->where('end_time', '>=', $this->end_time);
                  });
        })->pluck('vehicle_id');

        return Vehicle::whereNotIn('id', $busyVehicles)->get();
    }
}
