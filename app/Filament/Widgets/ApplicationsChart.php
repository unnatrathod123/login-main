<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

use App\Models\Application;
use Illuminate\Support\Carbon;

class ApplicationsChart extends ChartWidget
{
   // protected static ?string $heading = 'Chart';
    protected static ?string $heading = 'Applications (Last 30 Days)';

    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
         $data = collect(range(29, 0))->map(function ($day) {
            return Application::whereDate(
                'created_at',
                Carbon::today()->subDays($day)
            )->count();
        });

        return 
        [
            'datasets' => 
            [
                [
                    'label' => 'Applications',
                    'data' => $data,
                ],
            ],
            'labels' => collect(range(29, 0))->map(fn ($day) =>
                Carbon::today()->subDays($day)->format('d M')
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
