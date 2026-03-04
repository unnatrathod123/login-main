<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Application;


class LatestApplications extends BaseWidget
{
    protected static ?string $heading = 'Latest Pending Applications';

    protected int | string | array $columnSpan = 'full'; // optional (makes it full width)

    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ...
                Application::query()
                    ->where('status', 'applied') // adjust if your column name is different
                    ->whereNotNull('name')   // ✅ name must not be NULL
                    ->latest()
                    ->limit(5)

            )
            ->columns([
                // ...

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Applied On')
                    ->dateTime()
                    ->sortable(),
            ])

            ->actions([
                Tables\Actions\Action::make('Schedule Interviews')
                ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn (Application $record) => 
                        $record->update(['status' => 'interview_scheduled'])
                    ),

                Tables\Actions\Action::make('Reject')
                    ->color('danger')
                    ->icon('heroicon-m-x-circle')
                    ->action(fn (Application $record) => 
                        $record->update(['status' => 'rejected'])
                    ),
            ]);
    }
    
}
