<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Trip;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\TripResource\Pages;
use Illuminate\Database\Eloquent\Builder;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),

                Forms\Components\Select::make('driver_id')
                    ->relationship('driver', 'name')
                    ->required(),

                Forms\Components\Select::make('vehicle_id')
                    ->relationship('vehicle', 'plate_number')
                    ->required(),

                Forms\Components\DateTimePicker::make('start_time')
                    ->required(),

                Forms\Components\DateTimePicker::make('end_time')
                    ->required()
                    ->after('start_time')
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $driverId = $get('driver_id');
                        $vehicleId = $get('vehicle_id');
                        $start = $get('start_time');
                        $end = $state;

                        if (TripResource::hasOverlap('driver_id', $driverId, $start, $end, $get('id') ?? null)) {
                            throw \Filament\Forms\ValidationException::withMessages([
                                'end_time' => "This driver already has a trip that overlaps with the selected time."
                            ]);
                        }

                        if (TripResource::hasOverlap('vehicle_id', $vehicleId, $start, $end, $get('id') ?? null)) {
                            throw \Filament\Forms\ValidationException::withMessages([
                                'end_time' => "This vehicle is already booked for another trip during the selected time."
                            ]);
                        }
                    }),
            ]);
    }


    public static function hasOverlap($field, $value, $start, $end, $ignoreId = null)
    {
        $query = Trip::where($field, $value)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('ID'),
                Tables\Columns\TextColumn::make('vehicle.plate_number')->label('Vehicle')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('driver.name')->label('Driver')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_time')->label('Start Time')->dateTime('M d, Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('end_time')->label('End Time')->dateTime('M d, Y H:i')->sortable(),
                Tables\Columns\TagsColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'primary' => 'active',
                        'success' => 'completed',
                        'danger' => 'canceled',
                    ])
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['driver', 'vehicle', 'company']); 
    }

    public static function testableSchema(): array
    {
        return [
            'form' => ['company_id', 'driver_id', 'vehicle_id', 'start_time', 'end_time'],
            'table' => ['id', 'vehicle.plate_number', 'driver.name', 'start_time', 'end_time', 'status'],
        ];
    }

}
