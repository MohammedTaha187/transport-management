<x-filament::page>
    <div>
        {{ $this->form }}

        @if($start_time && $end_time)
            <div class="mt-6">
                <h2 class="text-lg font-bold">Available Drivers</h2>
                <ul>
                    @foreach($this->getAvailableDrivers() as $driver)
                        <li>{{ $driver->name }}</li>
                    @endforeach
                </ul>

                <h2 class="text-lg font-bold mt-4">Available Vehicles</h2>
                <ul>
                    @foreach($this->getAvailableVehicles() as $vehicle)
                        <li>{{ $vehicle->model }} - {{ $vehicle->plate_number }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-filament::page>
