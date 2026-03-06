<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Intern;
use App\Models\Application;
use App\Models\Task;
use App\Models\Event;

use Illuminate\Support\Carbon;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {

    $applicationsLast7Days = collect(range(6, 0))->map(function ($day) {
        return \App\Models\Application::whereDate(
            'created_at',
            Carbon::today()->subDays($day)
        )->count();
    });

    return [
        Stat::make('Total Interns', Intern::count())
            ->description('Total interns')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('primary')
            ->chart($applicationsLast7Days->toArray()),

        // Stat::make('Total Interns', Intern::where('status', 'active')->count())
        //     ->description('Active interns')
        //     ->descriptionIcon('heroicon-m-user-group')
        //     ->color('primary')
        //     ->chart($applicationsLast7Days->toArray()),

        Stat::make('Pending Applications', Application::where('status', 'applied')->count())
            ->description('Awaiting review')
            ->descriptionIcon('heroicon-m-clock')
            ->color('warning')
            ->chart($applicationsLast7Days->toArray()),

        Stat::make('Total Tasks Assigned', Task::count())
            ->description('All created tasks')
            ->descriptionIcon('heroicon-m-clipboard-document-list')
            ->color('success')
            ->chart([3, 5, 2, 8, 6, 9, 4]), // temporary sample

        Stat::make('Total Events', Event::count())
            ->description('Events Created')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('primary')
            ->chart($applicationsLast7Days->toArray()),

    ];
    }
}
